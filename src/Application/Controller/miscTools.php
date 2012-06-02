<?php
namespace Application\Controller;

use \Silex\ControllerProviderInterface;
use \Silex\ControllerCollection;

class miscTools implements ControllerProviderInterface
{
  public function connect(\Silex\Application $app)
  {
    $miscTools = new ControllerCollection();

    /**
     * index action
     */
     $miscTools->get('/', function() use ($app)
     {
      $vars = array('title' => 'Misc Tools');
      return $app['twig']->render('miscTools/index.twig', $vars);
    });

     return $miscTools;
  }
}
