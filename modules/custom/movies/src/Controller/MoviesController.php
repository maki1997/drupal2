<?php

namespace Drupal\movies\Controller;

use \Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use http\Env\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class MoviesController extends ControllerBase {

  protected $request;

  public function __construct(){
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
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
        'title' => $this->t($movie->getTitle(),array(),array('langcode'=>'ar')),// t funkcija
        'description' =>  $movie->get('field_description')->value,
        'image' => $movie->get('field_image1')->entity->uri->value,
        'genre' => $this->getGenre($movie),
        'producing_house' => $this->getProducingHouse($movie)

      );
    }

    return $movies;


  }

  public function movieImages() {
    $ids = \Drupal::entityQuery('node')
      ->condition('type','movie')
      ->sort('title','DESC')
      ->execute();
    $moviesList = $this->entityTypeManager()->getStorage('node')->loadMultiple($ids);
    $movies = [];
    foreach($moviesList as $movie){
      $movies[] = array(
        'image' => $movie->get('field_image1')->entity->uri->value,

      );
    }

    return $movies;


  }

  public function getProductionHousesAndTheirChildMovies(){
    $ids = \Drupal::entityQuery('node')
      ->condition('type','producing_house')
      ->execute();
    $producingHouses = $this->entityTypeManager()->getStorage('node')->loadMultiple($ids);
    $productions = [];
    $movieIds = \Drupal::entityQuery('node')
      ->condition('type','movie')
      ->sort('title','DESC')
      ->execute();
    $moviesList = $this->entityTypeManager()->getStorage('node')->loadMultiple($movieIds);
    foreach($producingHouses as $producingHouse){
      foreach($moviesList as $movie){
        if($producingHouse->getTitle() == $this->getProducingHouse($movie)){
          $productions[] = array(
            'name' => $producingHouse->getTitle(),
            'movie' => $movie->getTitle()
          );

        }
      }


    }

    return $productions;
  }

  public function getMoviesWithoutProducingHouse() {
    $title = !empty(\Drupal::request()->get('searchMovies')) ? \Drupal::request()->get('searchMovies') : '';
    $genre = !empty(\Drupal::request()->get('chosenGenre')) ? \Drupal::request()->get('chosenGenre') : '';
    $ids = \Drupal::entityQuery('node')
      ->condition('type','movie')
      ->sort('title','DESC')
      ->execute();
    $moviesList = $this->entityTypeManager()->getStorage('node')->loadMultiple($ids);
    $movies = [];
    foreach($moviesList as $movie){
      if($this->getProducingHouse($movie) == "No producing house"){
      $movies[] = array(
        'title' => $movie->getTitle(),
        'description' =>  $movie->get('field_description')->value,
        'image' => $movie->get('field_image1')->entity->uri->value,
        'genre' => $this->getGenre($movie),
        'producing_house' => $this->getProducingHouse($movie)

      );
      }
    }
    return array(
      'movies' => [
        '#theme' => 'movies',
        '#movies' => $movies,
        '#images' => $this->movieImages(),
        '#filter' => [
          'title' => $title,
          'allGenres' => $this->getGenres(),
          'genre' => $genre]
      ]);


  }



  public function searchMoviesWithoutProducingHouse(){
    $title = !empty(\Drupal::request()->get('searchMovies')) ? \Drupal::request()->get('searchMovies') : '';
    $genre = !empty(\Drupal::request()->get('chosenGenre')) ? \Drupal::request()->get('chosenGenre') : '';
    if(empty($title) && empty($genre)) {
      $movies = $this->getMoviesWithoutProducingHouse();
      return $movies;
    } else if(empty($genre)){
      $nodes = \Drupal::entityQuery('node')
        ->condition('type', 'movie')
        ->condition('title', $title, 'CONTAINS')
        ->sort('title', 'DESC')
        ->execute();
      $moviesList = $this->entityTypeManager()->getStorage('node')->loadMultiple($nodes);
      $movies = [];
      foreach ($moviesList as $movie) {

        if($this->getProducingHouse($movie) == "No producing house"){
          $movies[] = array(
            'title' => $movie->getTitle(),
            'description' =>  $movie->get('field_description')->value,
            'image' => $movie->get('field_image1')->entity->uri->value,
            'genre' => $this->getGenre($movie),
            'producing_house' => $this->getProducingHouse($movie)

          );
        }
      }

      return array(
        'movies' => [
          '#theme' => 'movies',
          '#movies' => $movies,
          '#images' => $this->movieImages(),
          '#filter' => [
        'title' => $title,
        'allGenres' => $this->getGenres(),
        'genre' => $genre]
        ]);

    }else if(empty($title)){
      $nodes = \Drupal::entityQuery('node')
        ->condition('type', 'movie')
        ->condition('field_movie_type.entity:taxonomy_term.name', $genre, '=')
        ->execute();
      $moviesList = $this->entityTypeManager()->getStorage('node')->loadMultiple($nodes);
      $movies = [];
      foreach ($moviesList as $movie) {

        if($this->getProducingHouse($movie) == "No producing house"){
          $movies[] = array(
            'title' => $movie->getTitle(),
            'description' =>  $movie->get('field_description')->value,
            'image' => $movie->get('field_image1')->entity->uri->value,
            'genre' => $this->getGenre($movie),
            'producing_house' => $this->getProducingHouse($movie)

          );
        }
      }

      return array(
        'movies' => [
          '#theme' => 'movies',
          '#movies' => $movies,
          '#images' => $this->movieImages(),
          '#filter' => [
            'title' => $title,
            'allGenres' => $this->getGenres(),
            'genre' => $genre]
        ]);


    }

    else {
      $nodes = \Drupal::entityQuery('node')
        ->condition('type', 'movie')
        ->condition('field_movie_type.entity:taxonomy_term.name', $genre, '=')
        ->condition('title', $title, 'CONTAINS')
        ->sort('title', 'DESC')
        ->execute();
      $moviesList = $this->entityTypeManager()->getStorage('node')->loadMultiple($nodes);
      $movies = [];
      foreach ($moviesList as $movie) {

        if($this->getProducingHouse($movie) == "No producing house"){
          $movies[] = array(
            'title' => $movie->getTitle(),
            'description' =>  $movie->get('field_description')->value,
            'image' => $movie->get('field_image1')->entity->uri->value,
            'genre' => $this->getGenre($movie),
            'producing_house' => $this->getProducingHouse($movie)

          );
        }
      }

      return array(
        'movies' => [
          '#theme' => 'movies',
          '#movies' => $movies,
          '#images' => $this->movieImages(),
          '#filter' => [
            'title' => $title,
            'allGenres' => $this->getGenres(),
            'genre' => $genre]
        ]);


    }

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
          'description' =>  $movie->get('field_description')->value,
          'image' => $movie->get('field_image1')->entity->uri->value,
          'genre' => $this->getGenre($movie),
          'producing_house' => $this->getProducingHouse($movie)
        );
      }

      return $movies;


    }else if(empty($title)){
      $nodes = \Drupal::entityQuery('node')
        ->condition('type', 'movie')
        ->condition('field_movie_type.entity:taxonomy_term.name', $genre, '=')
        ->pager($configFormCount)
        ->execute();
      $moviesList = $this->entityTypeManager()->getStorage('node')->loadMultiple($nodes);
      $movies = [];
      foreach ($moviesList as $movie) {

        $movies[] = array(
          'title' => $movie->getTitle(),
          'description' =>  $movie->get('field_description')->value,
          'image' => $movie->get('field_image1')->entity->uri->value,
          'genre' => $this->getGenre($movie),
          'producing_house' => $this->getProducingHouse($movie)
        );
      }

      return $movies;


    }

    else {
      $nodes = \Drupal::entityQuery('node')
        ->condition('type', 'movie')
        ->condition('field_movie_type.entity:taxonomy_term.name', $genre, '=')
        ->condition('title', $title, 'CONTAINS')
        ->sort('title', 'DESC')
        ->pager($configFormCount)
        ->execute();
      $moviesList = $this->entityTypeManager()->getStorage('node')->loadMultiple($nodes);
      $movies = [];
      foreach ($moviesList as $movie) {

        $movies[] = array(
          'title' => $movie->getTitle(),
          'description' =>  $movie->get('field_description')->value,
          'image' => $movie->get('field_image1')->entity->uri->value,
          'genre' => $this->getGenre($movie),
          'producing_house' => $this->getProducingHouse($movie)
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



  private function getChildMovies($producingHouse){
    $ids = \Drupal::entityQuery('node')
      ->condition('type','movie')
      ->sort('title','DESC')
      ->execute();
    $moviesList = $this->entityTypeManager()->getStorage('node')->loadMultiple($ids);
    $moviesOfProduction = [];
    foreach ($moviesList as $movie){
      if($producingHouse->getTitle() == $this->getProducingHouse($movie)){
        $moviesOfProduction[]=array(
          'title' => $movie->getTitle(),
          'description' =>  $movie->get('field_description')->value,
          'image' => $movie->get('field_image1')->entity->uri->value,
          'genre' => $this->getGenre($movie));
      }

    }
    return $moviesOfProduction;
  }

  private function getProducingHouse($movie){
    $terms = $movie->get('field_producing_house')->referencedEntities();
    $name = "No producing house";
    foreach ($terms as $term){
      if($term != null){
        $name = $term->getTitle();
      }

    }
    return $name;
  }




  public function getGenres(){
    $type = 'movie_type';
    $genres =$this->entityTypeManager()->getStorage('taxonomy_term')->loadTree($type);
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
    $a = $this->movieImages();
    return array(
      'movies' => [
        '#theme' => 'movies',
        '#movies' => $this->searchMovies($title,$genre),
        '#images' => $this->movieImages(),
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
