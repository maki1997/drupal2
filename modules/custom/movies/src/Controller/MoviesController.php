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
        'genre' => $this->getGenre($movie)
      );
    }

    return $movies;


  }

  public function searchMovies($title,$genre){
    $configFormCount = $this->config('movie.settings')->get('movies_count');
    if(empty($title) && empty($genre)) {
      $movies = $this->getMovies();
      return $movies;
    } else if(empty($genre)){
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
          'genre' => $this->getGenre($movie)
        );
      }

      return $movies;


    }else if(empty($title)){
      $nodes = \Drupal::entityQuery('node')
        ->condition('type', 'movie')
        //condition za pretragu po zanru
        ->pager($configFormCount)
        ->execute();
      $moviesList = $this->entityTypeManager()->getStorage('node')->loadMultiple($nodes);
      $movies = [];
      foreach ($moviesList as $movie) {

        $movies[] = array(
          'title' => $movie->getTitle(),
          'description' => $movie->get('field_description')->value,
          'image' => $movie->get('field_image1')->entity->uri->value,
          'genre' => $this->getGenre($movie),
        );
      }

      return $movies;


    }

    else {
      $nodes = \Drupal::entityQuery('node')
        ->condition('type', 'movie')
        //condition za pretragu po zanru
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
          'genre' => $this->getGenre($movie)
        );
      }

      return $movies;


    }

  }

  public function findMovies($title){

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
          'genre' => $this->getGenre($movie)
        );
      }

      return $movies;

    }
  }



  private function getGenre($movie){
    $terms = $movie->get('field_movie_type')->referencedEntities();
    $name = "Genre not defined";
    foreach ($terms as $term){
      if($term != null){
        $name = $term->getName();
      }

    }
    return $name;
  }


  public function getGenres(){
    $type = 'movie_type';
    $genres =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($type);
    $genreNames = array();
    foreach ($genres as $genre) {
      $genreNames[] = array(
        'name'=>$genre->name
      );
    }
    return $genreNames;
  }




  public function movies(){
    $title = !empty(\Drupal::request()->get('searchMovies')) ? \Drupal::request()->get('searchMovies') : '';
    $genre = !empty(\Drupal::request()->get('chosenGenre')) ? \Drupal::request()->get('chosenGenre') : '';
    return array(
      'movies' => [
        '#theme' => 'movies',
        '#movies' => $this->findMovies($title),
        '#filter' => [
          'title' => $title,
          'allGenres' => $this->getGenres(),
          'genre' => $genre
        ]
      ],
      'pager' => [
        '#type' => 'pager',
      ]);

  }

}
