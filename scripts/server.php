<?php

$app = require 'index.php';

use Symfony\Component\Console;

$console = new Console\Application();

$console->register('start')
        ->setDescription('Start the event server')
        ->addOption('port',
                    null,
                    Console\Input\InputOption::VALUE_NONE,
                    'Specify which port to bind')
        ->setCode(function(Console\Input\InputInterface $input,
                           Console\Output\OutputInterface $output)
        use ($console) {
          $port = $input->getOption('port');
          $server = new Server\EventServer($port);
          $server->run();
        });

$console->register('new-worker')
        ->setDescription('Spawn a new worker to listen on a request')
        ->setCode(function(Console\Input\InputInterface $input,
                           Console\Output\OutputInterface $output)
        use ($console) {
          $worker = new Server\EventWorker();
          $worker->run();
        });

$console->run();
