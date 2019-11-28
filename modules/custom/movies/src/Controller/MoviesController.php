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
     $creator = new static(
       $container->get('entity_type.manager'),
       $container->get('config.factory'),
     );
     return $creator;
   }

  public function getMovies() {
    $configFormCount = \Drupal::config('movie.settings')->get('movies_count');
      $ids = \Drupal::entityQuery('node')
      ->condition('type','movie')
      ->sort('title','DESC')
      ->pager($configFormCount)
      ->execute();
    $moviesList = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($ids);
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


  public function movies(){

    return array('movies' => [
      '#theme' => 'movies',
      '#movies' => $this->getMovies(),
      ],
      'pager' => [
        '#type' => 'pager',
      ]);

  }

 }
