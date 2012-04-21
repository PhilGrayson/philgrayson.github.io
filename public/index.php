<?php
	define('root_dir', __DIR__ . '/../');
	require_once root_dir . 'Application/vendor/silex.phar';

	$app = new Silex\Application();

	// Setup twig
	$app->register(new Silex\Provider\TwigServiceProvider(), array(
		'twig.path'       => root_dir . 'Application/Views',
		'twig.class_path' => root_dir . 'Application/vendor/twig/lib',
	));

	// Setup Doctrine
	$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
		'db.options' => array(
			  'driver' => 'pdo_mysql',
			  'dbname' => '4changraph',
			    'user' => 'dev-user',
			'password' => 'user-dev',
			    'host' => 'philgrayson.com',
		),
		'db.dbal.class_path'   => root_dir . 'Application/vendor/doctrine-dbal/lib',
		'db.common.class_path' => root_dir . 'Application/vendor/doctrine-common/lib',
	));

	// Add Application namespace to autoloader
	$app['autoloader']->registerNamespace('Application', root_dir);
	
	$app->get('/', function() use ($app) {
		$controller = new Application\Controller\blog($app);
		return $controller->index();
	});

	$app->get('/{year}/{month}/{name}', function($year, $month, $name) use ($app) {
		$controller = new Application\Controller\blog($app);
		return $controller->show($year, $month, $name);
	})->assert('year', '\d{4}')
	  ->assert('month', '\d{2}');

	// Misc tools routes
	$app->get('/misc-tools', function() use ($app) {
		$controller = new Application\Controller\miscTools($app);
		return $controller->index();
	});

	// 4chan graph routes
	$app->get('/4chan-graph', function() use ($app) {
		$controller = new Application\Controller\chanGraph($app);

		$contentTypes = $app['request']->getAcceptableContentTypes();
		
		if ($contentTypes[0] == 'application/json') {
			return $controller->jsonResponder();
		}
		
		return $controller->index();
	});

	$app['debug'] = true;
	$app->run();
