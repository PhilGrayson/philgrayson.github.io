<?php
namespace Application\Controller;

class miscTools implements \Silex\ControllerProviderInterface
{
  public function connect(\Silex\Application $app)
  {
    $miscTools = new \Silex\ControllerCollection();

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
