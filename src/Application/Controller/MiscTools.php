<?php
namespace Application\Controller;

class MiscTools implements \Silex\ControllerProviderInterface
{
  public function connect(\Silex\Application $app)
  {
    $miscTools = $app['controllers_factory'];

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
