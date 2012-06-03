<?php
define('root_dir', __DIR__ . '/../');
require_once root_dir . 'vendor/autoload.php';

$app = new Silex\Application();

// Application configs
$app['app_config'] = Symfony\Component\Yaml\Yaml::parse(root_dir . 'config/config.yaml');

// Setup twig
$app->register(new Silex\Provider\TwigServiceProvider(), array(
  'twig.path'       => root_dir . 'src/Application/Views'
));

// Homepage/Blog routes
$app->mount('/', new Application\Controller\Blog());

// Misc tools routes
$app->mount('/misc-tools', new Application\Controller\miscTools());

// 4chan graph routes
$app->mount('/4chan-graph', new Application\Controller\chanGraph());

// Default error handler
// This will most likey run when a NotFoundHttpException is thrown
$app->error(function(\Exception $e) use ($app)
{
  return $app->redirect('/');
});

$app['debug'] = true;
$app->run();
