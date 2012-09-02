#!/usr/bin/env php

<?php
  $app = require 'index.php';

  use Symfony\Component\Console;
  use Symfony\Component\Console\Ouput\OutputInterface;

  $console = new Console\Application();

  $console
    ->register('add')
    ->setDescription('Add a new board')
    ->addOption('board',
                null,
                Console\Input\INPUTOPTION::VALUE_REQUIRED,
                'Board ID (ie, b, sp, r9k) to add')
    ->addOption('description',
                null,
                Console\Input\INPUTOPTION::VALUE_REQUIRED,
                'Description of what the board\'s topic is')
    ->setCode(function(Console\Input\InputInterface $input,
                       OutputInterface $output) use ($app) {
      if($input->getOption('board') && $input->getOption('description')) {
        $handle = $input->getOption('board');
        $name   = $input->getOption('description');
        $query = 'INSERT IGNORE INTO boards (handle, name) VALUES (:handle, :name)';
        $app['dbs']['chanGraph']->executeQuery($query, array(':handle' => $handle,
                                                              ':name'   => $name));

        $output->writeln("Added $handled");
      } else {
        $ouput->writeln("Could not add. Parameters missing");
      }
    });

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
      }
    );

  $console->run();
