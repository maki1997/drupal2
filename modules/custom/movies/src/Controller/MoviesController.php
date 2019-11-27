<?php

 namespace Drupal\movies\Controller;

 use \Drupal\Core\Controller\ControllerBase;
 use Drupal\Core\Entity\Query\QueryFactory;
 use http\Env\Response;

 class MoviesController extends ControllerBase {

  protected $entityQuery;
  protected $entityTypeManager;

  public function __constructor(QueryFactory $entityQuery,EntityTypeManager $entityTypeManager){
    $this->entityQuery = $entityQuery;
    $this->entityTypeManager = $entityTypeManager;
   }

  public function getMovies() {

    $ids = \Drupal::entityQuery('node')
      ->condition('type','movie')
      ->execute();

    $moviesList = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($ids);
    $movies = [];

    foreach($moviesList as $movie){

        $movies[] = array(
          'title' => $movie->getTitle(),
          'description' =>  $movie->get('field_description')->value,
          'image' => $movie->get('field_image1')->entity->uri->value
       );
    }
    return $movies;

  }


  public function movies(){

    return array(
      '#theme' => 'movies',
      '#movies' => $this->getMovies()
    );

    /*$build['#theme'] = 'movies';
    return $build;

    $output = \Drupal::Service('renderer')->renderRoot($build);
    $response = new Response();
    $response.setContent($output);
    return $response;*/
  }

 }
