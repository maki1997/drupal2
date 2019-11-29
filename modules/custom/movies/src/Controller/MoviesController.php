<?php

 namespace Drupal\movies\Controller;

 use \Drupal\Core\Controller\ControllerBase;
 use Drupal\Core\Entity\EntityStorageInterface;
 use Drupal\Core\Entity\Query\QueryFactory;
 use http\Env\Response;
 use Symfony\Component\DependencyInjection\ContainerInterface;

 class MoviesController extends ControllerBase {

  protected $entityQuery;
  protected $entityTypeManager;

  public function __constructor(QueryFactory $entityQuery,EntityTypeManager $entityTypeManager){
    $this->entityQuery = $entityQuery;
    $this->entityTypeManager = $entityTypeManager;
   }

   public static function create(ContainerInterface $container) {
     return new static(
       $container->get('entity_type.manager'),
       $container->get('config.factory'),
       $container->get('request_stack'),
       $container->get('entity.query'),
     );
   }

  public function getMovies() {
    $configFormCount = $this->config('movie.settings')->get('movies_count');
      $ids = \Drupal::entityQuery('node')
      ->condition('type','movie')
      ->sort('title','DESC')
      ->pager($configFormCount)
      ->execute();
    $moviesList = $this->entityTypeManager()->getStorage('node')->loadMultiple($ids);
    $movies = [];
    foreach($moviesList as $movie){

        $movies[] = array(
          'title' => $movie->getTitle(),
          'description' =>  $movie->get('field_description')->value,
          'image' => $movie->get('field_image1')->entity->uri->value,
          'genre' => $this->getTaxonomy($movie)
       );
    }

    return $movies;


  }

   public function searchMovies($title){
     $configFormCount = $this->config('movie.settings')->get('movies_count');
     if(empty($title)) {
         $movies = $this->getMovies();
         return $movies;
       } else {
         $nodes = \Drupal::entityQuery('node')
           ->condition('type', 'movie')
           ->condition('title', $title, 'CONTAINS')
           ->sort('title', 'DESC')
           ->pager($configFormCount)
           ->execute();
         $moviesList = $this->entityTypeManager()->getStorage('node')->loadMultiple($nodes);
         $movies = [];
         foreach ($moviesList as $movie) {

           $movies[] = array(
             'title' => $movie->getTitle(),
             'description' => $movie->get('field_description')->value,
             'image' => $movie->get('field_image1')->entity->uri->value,
             'genre' => $this->getTaxonomy($movie)
           );
         }

         return $movies;


       }

     }



   private function getTaxonomy($movie){
     $terms = $movie->get('field_movie_type')->referencedEntities();
     $name = "Genre not defined";
     foreach ($terms as $term){
       if($term != null){
         $name = $term->getName();
       }

     }
     return $name;
   }

   public function getAllMovieTypes(){
     $type = 'movie_type';
     $movieTypes =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($type);
     $movieTypeNames = array();
     foreach ($movieTypes as $movieType) {
       $movieTypeNames[] = array(
          $movieType->name
       );
     }
     return $movieTypeNames;
 }




  public function movies(){
    $title = !empty(\Drupal::request()->get('searchMovies')) ? \Drupal::request()->get('searchMovies') : '';
    $movieType = !empty(\Drupal::request()->get('chosenMovieType')) ? \Drupal::request()->get('chosenMovieType') : '';
    $allMovieTypes = $this->getAllMovieTypes();
    return array(
      'movies' => [
        '#theme' => 'movies',
        '#movies' => $this->searchMovies($title),
      ],
      'pager' => [
        '#type' => 'pager',
      ],
      'filter' => [
        '#title' => $title,
        '#allMovieTypes' => $allMovieTypes,
        '#chosenMovieType' => $movieType
      ]);

  }

 }
