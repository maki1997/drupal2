<?php


namespace Drupal\books\Controller;


use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

class AddBookController extends  ControllerBase
{

  public function addBook(){
    $title = !empty(\Drupal::request()->get('title')) ? \Drupal::request()->get('title') : '';
    $price = !empty(\Drupal::request()->get('price')) ? \Drupal::request()->get('price') : '';
    $isbn = !empty(\Drupal::request()->get('isbn')) ? \Drupal::request()->get('isbn') : '';
    $comment = !empty(\Drupal::request()->get('comment')) ? \Drupal::request()->get('comment') : '';
    $comms[]=$comment;
    $newBook = array();
    $newBook['type'] = 'book';
    $newBook['title'] = $title;
    $newBook['field_price'] = $price;
    $newBook['field_isbn'] = $isbn;
    $newBook['field_comments'] = $comms;
    $bookToCreate = \Drupal::entityTypeManager()->getStorage('node')->create($newBook);
    $bookToCreate->save();
    return new Response("Book added.");
  }

}
