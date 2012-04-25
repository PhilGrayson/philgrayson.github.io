<?php
  namespace Application\Controller;

  class miscTools extends Controller {
    function index() {
      $vars = array('title' => 'Misc Tools');
      return $this->app['twig']->render('content/misc-tools.twig', $vars);
    }
  }
