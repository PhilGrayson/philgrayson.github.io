<?php
  define('root_dir', __DIR__ . '/../');
  require_once root_dir . 'vendor/silex.phar';

  $app = new Silex\Application();

  // Add namespaces
  $app['autoloader']->registerNamespace('Symfony', root_dir . 'vendor');
  $app['autoloader']->registerNamespace('Predis', root_dir . 'vendor');

  // Application configs
  $config \Syfony\Component\Yaml\Yaml::parse(root_dir . 'config/config.yaml');

  // Setup Doctrine
  $app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'dbs.options' => $config['live']['doctrine'],
    'db.dbal.class_path'   => root_dir . 'vendor/doctrine-dbal/lib',
    'db.common.class_path' => root_dir . 'vendor/doctrine-common/lib',
  ));

  return $app;
