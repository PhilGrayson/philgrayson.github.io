<?php
  namespace Application\Model;

  class Model {
    public function __construct(\Silex\Application $app) {
      $this->app = $app;
    }
  }
