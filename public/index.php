<?php
define('root_dir', __DIR__ . '/../');
require_once root_dir . 'vendor/autoload.php';

$app = new \Silex\Application();
$app['root_dir'] = root_dir;

// Application configs
$app['app_config'] = \Symfony\Component\Yaml\Yaml::parse(
  root_dir . 'data/config/config.yaml'
);

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
  'dbs.options' => $app['app_config']['dev']['doctrine']
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

// Setup twig
$app->register(new \Silex\Provider\TwigServiceProvider(), array(
  'twig.path' => root_dir . 'src/Application/Views'
));

// Homepage/Blog routes
$app->mount('/', new \Application\Controller\Blog());

// Misc tools routes
$app->mount('/misc-tools', new \Application\Controller\MiscTools());

// 4chan graph routes
$app->mount('/fourchandash', new \Application\Controller\FourChanDash());

// Default error handler
// This will most likey run when a NotFoundHttpException is thrown
$app->error(function(\Exception $e) use ($app)
{
  return $app->redirect('/');
});

$app['debug'] = true;
$app->run();
