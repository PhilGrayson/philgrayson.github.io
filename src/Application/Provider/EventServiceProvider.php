<?php
namespace Application\Provider;

class EventServiceProvider implements \Silex\ServiceProviderInterface
{
  public function register(\Silex\Application $app)
  {
    $app['event'] = $app->share(function () use ($app)
    {
      $server = new \Library\EventServer($app);
      \Application\Event\EventAttacher::attach($server);

      return $server;
    });
  }

  public function boot(\Silex\Application $app) {}
}
