<?php

namespace Drupal\books\Controller;

use \Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

class BooksController extends  ControllerBase
{
  public function books(){
    // http://www.chilkatsoft.com/xml-samples/bookstore.xml
    return new Response('asdasdasd');

}

}
