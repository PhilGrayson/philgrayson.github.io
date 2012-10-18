<?php
namespace Application\Provider;

class SecurityServiceProvider implements \Silex\ServiceProviderInterface
{
  public function register(\Silex\Application $app)
  {
    $app['security'] = $app->share(function () use ($app)
    {
      return new \Library\Security($app);
    });
  }

  public function boot(\Silex\Application $app) {}
}
