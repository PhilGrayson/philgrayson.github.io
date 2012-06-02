<?php
define('root_dir', __DIR__ . '/../');
require_once root_dir . 'vendor/silex.phar';

$app = new Silex\Application();

// Add namespaces
$app['autoloader']->registerNamespace('Application', root_dir);
$app['autoloader']->registerNamespace('Symfony', root_dir . '/vendor');

// Application configs
$config = \Symfony\Component\Yaml\Yaml::parse(root_dir . 'config/config.yaml');

// Setup twig
$app->register(new Silex\Provider\TwigServiceProvider(), array(
  'twig.path'       => root_dir . 'Application/Views',
  'twig.class_path' => root_dir . 'vendor/Twig/lib',
));

// Setup Doctrine
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
  'dbs.options' => $config['live']['doctrine'],
  'db.dbal.class_path'   => root_dir . 'vendor/doctrine2-dbal/lib',
  'db.common.class_path' => root_dir . 'vendor/doctrine2-common/lib',
));

// Homepage/Blog routes
$app->mount('/', new Application\Controller\Blog());

// Misc tools routes
$app->mount('/misc-tools', new Application\Controller\miscTools());

// 4chan graph routes
$app->mount('/4chan-graph', new Application\Controller\chanGraph());

$app['debug'] = true;
$app->run();
