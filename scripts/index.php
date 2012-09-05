<?php

define('root_dir', __DIR__ . '/../');
require_once root_dir . 'vendor/autoload.php';

$app = new Silex\Application();

// Application configs
$config = \Symfony\Component\Yaml\Yaml::parse(root_dir . 'config/config.yaml');

// Setup Doctrine
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
  'dbs.options' => $config['dev']['doctrine']
));

$app->register(new Nutwerk\Provider\DoctrineORMServiceProvider(), array(
  'db.orm.proxies_dir' => root_dir . 'data/doctrine/proxy',
  'db.orm.entities' => array(array(
    'type' => 'yml',
    'path' => root_dir . 'data/doctrine/entities',
    'namespace' => 'Application\Model\fourChanDash'
  ))
));

$app['event']    = new \Server\EventServer($app);
$app['data.dir'] = root_dir . '/data/';

return $app;
