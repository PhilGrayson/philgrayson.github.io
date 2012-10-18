<?php
namespace Application\Provider;

class MonologServiceProvider implements \Silex\ServiceProviderInterface
{
  public function register(\Silex\Application $app)
  {
    $app['monolog'] = $app->share(function () use ($app)
    {
      $monologs = new \Pimple();
      foreach($app['monolog.loggers'] as $name => $config) {
        $monolog = new \Monolog\Logger($name);
        foreach($config['handlers'] as $handler) {
          $monolog->pushHandler($handler);
        }

        $monologs[$name] = $monolog;
      }

      return $monologs;
    });
  }

  public function boot(\Silex\Application $app) {}
}
