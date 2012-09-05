#!/usr/bin/env php
<?php

$app = require 'index.php';

use Symfony\Component\Console;
use Application\Command\fourChanDash;

$console = new Console\Application();

$console->add(new fourChanDash\BoardsUpdateCommand($app));
$console->add(new fourChanDash\BoardsAddCommand($app));

$console
  ->register('update')
  ->setDescription('Update the supplied board\'s post count')
  ->addOption('boards',
              null,
              Console\Input\INPUTOPTION::VALUE_REQUIRED,
              'Space seperated list of boards to update')
  ->setCode(function() use ($app) {
    if ($input->getOption('boards')) {
      $boards = explode(' ', $input->getOption('boards'));
    } else {
      $boards = \Application\Model\chanGraph::getBoards();
    }

    foreach ($boards as $board) {
      $app['event']->trigger('BoardRequest', array('board' => $board));
    }
  });

 $console->run();
