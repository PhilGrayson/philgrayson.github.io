<?php

define('root_dir', __DIR__ . '/../');
require_once root_dir . 'vendor/autoload.php';

$app = new \Silex\Application();
$app['root_dir'] = root_dir;

$config = $app['root_dir'] . 'data/config/config.yml';

if (!is_readable($config)) {
  echo "Cannot read $config\n";
  exit(1);
}
// Application configs
$app['app_config'] = \Symfony\Component\Yaml\Yaml::parse($config);

$env = $app['app_config']['environment']['active'];

// Setup Doctrine
$app->register(new \Silex\Provider\DoctrineServiceProvider(), array(
  'dbs.options' => $app['app_config']['doctrine']
));

$app->register(new \Application\Provider\DoctrineORMServiceProvider(), array(
  'db.orm.proxies_dir'           => $app['root_dir'] . 'data/doctrine/proxy',
  'db.orm.proxies_namespace'     => 'Application\Proxies',
  'db.orm.auto_generate_proxies' => true,
  'db.orm.entities'              => array(array(
    'type'      => 'yml',
    'path'      => $app['root_dir'] . '/data/doctrine/entities',
    'namespace' => 'Application'
  ))
));

$app->register(new \Silex\Provider\SessionServiceProvider(), array(
  'session.storage.save_path' => $app['root_dir'] . '/data/sessions'
));
$app->register(new \Application\Provider\SecurityServiceProvider());
$app->register(new \Application\Provider\EventServiceProvider());

$app->register(new \Application\Provider\MonologServiceProvider(), array(
  'monolog.loggers' => array(
    'FourChanDash'=> array (
      'handlers' => array
      (
        new \Monolog\Handler\FingersCrossedHandler(
          new \Monolog\Handler\BufferHandler(
            new \Monolog\Handler\NativeMailerHandler(
              $app['app_config']['environment'][$env]['errors']['email']['to'],
              $app['app_config']['environment'][$env]['errors']['email']['subject'],
              $app['app_config']['environment'][$env]['errors']['email']['from'],
              \Monolog\Logger::INFO
            ),
            \Monolog\Logger::ERROR
          ),
          \Monolog\Logger::ERROR
        ),
        new \Monolog\Handler\FingersCrossedHandler(
          new \Monolog\Handler\StreamHandler(
            $app['root_dir'] . $app['app_config']['environment'][$env]['errors']['file'] . '/FourChanDash',
            \Monolog\Logger::INFO
          ),
          \Monolog\Logger::WARNING
        ),
      ),
    ),
  )
));

return $app;
