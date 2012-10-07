<?php

define('root_dir', __DIR__ . '/../');
require_once root_dir . 'vendor/autoload.php';

$app = new Silex\Application();
$app['root_dir'] = root_dir;

// Application configs
$app['app_config'] = \Symfony\Component\Yaml\Yaml::parse(
  root_dir . 'data/config/config.yaml'
);

// Setup Doctrine
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
  'dbs.options' => $app['app_config']['doctrine']
));

$app->register(new Application\Provider\DoctrineORMServiceProvider(), array(
  'db.orm.proxies_dir'           => $app['root_dir'] . 'data/doctrine/proxy',
  'db.orm.proxies_namespace'     => 'Application\Proxies',
  'db.orm.auto_generate_proxies' => true,
  'db.orm.entities'              => array(array(
    'type'      => 'yml',
    'path'      => $app['root_dir'] . '/data/doctrine/entities',
    'namespace' => 'Application'
  ))
));

$app->register(new Application\Provider\EventServiceProvider(), array());

return $app;
