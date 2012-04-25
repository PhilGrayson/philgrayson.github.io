<?php
  define('root_dir', __DIR__ . '/../');
	require_once root_dir . 'vendor/silex.phar';

	$app = new Silex\Application();
	$app['autoloader']->registerNamespace('Symfony', root_dir . 'vendor');
	$app['autoloader']->registerNamespace('Predis', root_dir . 'vendor');

	// Setup Doctrine
	$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
		'db.options' => array(
			  'driver' => 'pdo_mysql',
			  'dbname' => '4changraph',
			    'user' => 'dev-user',
			'password' => 'user-dev',
			    'host' => '178.79.189.205',
		),
		'db.dbal.class_path'   => root_dir . 'vendor/doctrine-dbal/lib',
		'db.common.class_path' => root_dir . 'vendor/doctrine-common/lib',
	));
	return $app;
