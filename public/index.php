<?php
	define('root_dir', __DIR__ . '/../');
	require_once root_dir . 'Application/vendor/silex.phar';

	$app = new Silex\Application();

	// Setup twig
	$app->register(new Silex\Provider\TwigServiceProvider(), array(
		'twig.path'       => root_dir . 'Application/views',
		'twig.class_path' => root_dir . 'Application/vendor/twig/lib',
	));

	// Setup Doctrine
	$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
		'db.options' => array(
			  'driver' => 'pdo_mysql',
			  'dbname' => '4changraph',
			    'user' => 'root',
			'password' => '',
			    'host' => 'localhost',
		),
		'db.dbal.class_path'   => root_dir . 'Application/vendor/doctrine-dbal/lib',
		'db.common.class_path' => root_dir . 'Application/vendor/doctrine-common/lib',
	));

	// Add Application namespace to autoloader
	$app['autoloader']->registerNamespace('Application', root_dir);
	
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
