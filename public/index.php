<?php
define('root_dir', __DIR__ . '/../');
require_once root_dir . 'vendor/autoload.php';

$app = new \Silex\Application();

// Application configs
$app['app_config'] = \Symfony\Component\Yaml\Yaml::parse(root_dir . 'config/config.yaml');

// Setup sessions
$app->register(new Silex\Provider\SessionServiceProvider());
$app['session']->Start();

// Setup twig
$app->register(new \Silex\Provider\TwigServiceProvider(), array(
  'twig.path' => root_dir . 'src/Application/Views'
));

// Setup monolog
$app->register(new Application\Provider\MonologServiceProvider(), array(
  'monolog.logfile' => root_dir . 'data/logs/monolog.log',
  'monolog.name'    => 'philgrayson.com',
  'monolog.handler' => function($app) {
    $streamLogger = new \Monolog\Handler\StreamHandler($app['monolog.logfile']);
    //$streamLogger->pushProcessor(new \Monolog\Processor\WebProcessor());
    //$streamLogger->setFormatter(new \Monolog\Formatter\NormalizerFormatter());

    return $streamLogger;
  }
));

// Homepage/Blog routes
$app->mount('/', new \Application\Controller\Blog());

// Misc tools routes
$app->mount('/misc-tools', new \Application\Controller\miscTools());

// 4chan graph routes
$app->mount('/4chan-graph', new \Application\Controller\chanGraph());

// Default error handler
// This will most likey run when a NotFoundHttpException is thrown
$app->error(function(\Exception $e) use ($app)
{
  return $app->redirect('/');
});

$app->finish(function(\Symfony\Component\HttpFoundation\Request $request,
                      \Symfony\Component\HttpFoundation\Response $response)
use ($app)
{
  // Log the request via monolog
  $app['monolog']->addInfo('phil');
  //$app['monolog']->addInfo($_SESSION);
});

$app['debug'] = true;
$app->run();
