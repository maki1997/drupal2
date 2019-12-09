<?php

namespace Drupal\books\Controller;

use Drupal\books\Services\BooksService;
use \Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\Query\QueryInterface;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\Query\Sql\QueryFactory;

class BooksController extends  ControllerBase
{
  protected $httpClient;
  protected $bService;
  public function __construct(Client $httpClient,BooksService $booksService){
    $this->httpClient = $httpClient;
    $this->bService = $booksService;
   }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client'),
      $bService = $container->get('books.books_service')
    );
  }


  public function getBooks(){
    $ids = \Drupal::entityQuery('node')
      ->condition('type','book')
      ->sort('created',DESC)
      ->execute();
    $booksList = $this->entityTypeManager()->getStorage('node')->loadMultiple($ids);
    $books = [];
    foreach($booksList as $book){
      $books[] = array(
        'title' => $this->t($book->getTitle(),array(),array('langcode'=>'ar')),// t funkcija
        'price' =>  $book->get('field_price')->value,
        'isbn' =>  $book->get('field_isbn')->value,
        'comments' =>  $this->getComments($book)
      );
    }

    return $books;

  }

  private function getComments($book){
    $comments = [];
    $comField = $book->get('field_comments');
    foreach($comField as $comment){
      $comments[] = $comment->value;
    }
    return $comments;
  }
  public function books(){
    $books = $this->bService->getValuesFromExternalUri();
    $this->bService->createContentTypesFromXml($books);
    return array(
      'books' => [
        '#theme' => 'books',
        '#books' => $this->getBooks()
      ]);

}

}
