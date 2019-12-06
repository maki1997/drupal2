<?php

namespace Drupal\books\Controller;

use \Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\Query\QueryInterface;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\Query\Sql\QueryFactory;

class BooksController extends  ControllerBase
{
  protected $httpClient;
  public function __construct(Client $httpClient){
    $this->httpClient = $httpClient;
   }

  public static function create(ContainerInterface $container) {
    return new static(
      //$container->get('entity_type.manager'),
      $container->get('http_client')
    );
  }

  public function getValuesFromExternalUri(){
    $uri = "http://www.chilkatsoft.com/xml-samples/bookstore.xml";
    try {
      $response = $this->httpClient()->get($uri, array('headers' => array('Accept' => 'text/xml')));
      $data = (string) $response->getBody();
      if (empty($data)) {
        return FALSE;
      }
    }
    catch (RequestException $e) {
      return FALSE;
    }
    $xmlToParse = simplexml_load_string($data);
    $xml = json_decode(json_encode($xmlToParse),true);
    return $xml;

}

  private function createContentTypesFromXml($xml){
    foreach($xml as $books){
      foreach($books as $book){
        $title = $book['title'];
        $price = $book['price'];
        $isbn = $book['@attributes']['ISBN'];
        $comments = $book['comments']['userComment'];
        $comms = [];
        if(is_array($comments)){
          foreach($comments as $comment){
            $comm = $comment;
            $comms[] = $comm;
          }
        }else{
          $comm = $comments;
          $comms[] = $comm;
        }
        $newBook = array();
        $newBook['type'] = 'book';
        $newBook['title'] = $title;
        $newBook['field_price'] = $price;
        $newBook['field_isbn'] = $isbn;
        $newBook['field_comments'] = $comms;
        $bookToCreate = $this->entityTypeManager()->getStorage('node')->create($newBook);
        $bookToCreate->save();
      }
    }
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
    //$xml = $this->getValuesFromExternalUri();
    //$this->createContentTypesFromXml($xml);
    return array(
      'books' => [
        '#theme' => 'books',
        '#books' => $this->getBooks()
      ]);

}

}
