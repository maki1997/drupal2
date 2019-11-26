<?php

namespace Drupal\movies\Services;
use Drupal\modules\custom\Model\Movie;

class MoviesService {
    public function getMovies(){
        $movie1 = new Movie('movie1','desc1',null);
        $movie2 = new Movie('movie2','desc2',null);
        $movie3 = new Movie('movie3','desc3',null);
        $list = new ArrayObject();
        $list.append($movie1);
        $list.append($movie2);
        $list.append($movie3);
        return $list;
    }
}
