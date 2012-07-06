<?php

define('root_dir', __DIR__ . '/../');
require_once root_dir . 'vendor/autoload.php';

$app = new Silex\Application();

// Application configs
$config = \Symfony\Component\Yaml\Yaml::parse(root_dir . 'config/config.yaml');

// Setup Doctrine
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
  'dbs.options' => $config['live']['doctrine']
));

return $app;
