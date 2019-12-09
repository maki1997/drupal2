<?php

namespace Drupal\books\Services;


use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BooksService
{

  public function getValuesFromExternalUri(){
    $uri = "http://www.chilkatsoft.com/xml-samples/bookstore.xml";
    try {
      $response = \Drupal::httpClient()->get($uri, array('headers' => array('Accept' => 'text/xml')));
      $data = (string) $response->getBody();
      if (empty($data)) {
        return FALSE;
      }
    }
    catch (RequestException $e) {
      return FALSE;
    }
    $xmlToParse = simplexml_load_string($data);
    $booksArray = json_decode(json_encode($xmlToParse),true);
    return $booksArray;

  }

  public function createContentTypesFromXml($xml){
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
        $bookToCreate = \Drupal::entityTypeManager()->getStorage('node')->create($newBook);
        $bookToCreate->save();
      }
    }
  }

}
