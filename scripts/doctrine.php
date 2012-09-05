#!/usr/bin/env php
<?php

$app = require_once 'index.php';

$helpers = new \Symfony\Component\Console\Helper\HelperSet(array(
  'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper(
    $app['db.orm.em']->getConnection()
  ),
  'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper(
    $app['db.orm.em']
  )
));

Doctrine\ORM\Tools\Console\ConsoleRunner::run($helpers);
