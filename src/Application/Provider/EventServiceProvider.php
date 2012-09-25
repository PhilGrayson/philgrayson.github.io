<?php
namespace Application\Provider;

class EventServiceProvider implements \Silex\ServiceProviderInterface
{
  public function register(\Silex\Application $app)
  {
    $app['event'] = $app->share(function () use ($app)
    {
      $event = new \Server\EventServer($app);

      $dir = new \DirectoryIterator($app['root_dir'] . '/src/Server/Event');
      foreach($dir as $file) {
        $path = $file->getPathName();
        $type = pathinfo($path, PATHINFO_EXTENSION);
        if ($file->isDot() || 'php' != $type) {
          continue;
        }

        $listener = require_once($path);

        if (isset($listener['topic']) &&
            isset($listener['function']) &&
            is_callable($listener['function'])) {
          $event->subscribe($listener['topic'], $listener['function']);
        }
      }

      return $event;
    });
  }

  public function boot(\Silex\Application $app) {}
}
