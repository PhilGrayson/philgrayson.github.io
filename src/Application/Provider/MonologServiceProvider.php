<?php
namespace Application\Provider;

class MonologServiceProvider implements \Silex\ServiceProviderInterface
{
  public function register(\Silex\Application $app)
  {
    $app['monolog'] = $app->share(function () use ($app)
    {
      $monolog = new \Monolog\Logger($app['monolog.name']);
      $monolog->pushHandler($app['monolog.handler']);

      return $monolog;
    });
  }

  public function boot(\Silex\Application $app) {}
}
