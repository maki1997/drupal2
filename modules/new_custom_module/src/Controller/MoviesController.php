<?php

namespace Drupal\new_custom_module\Controller;

use Drupal\Core\Controller\ControllerBase;

class MoviesController extends ControllerBase
{
  public function movies(){
    return new \Symfony\Component\HttpFoundation\Response("movies");
  }

}
