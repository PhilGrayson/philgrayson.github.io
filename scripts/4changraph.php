#!/usr/bin/env php

<?php
  $app = require 'index.php';

  use Symfony\Component\Console\Application;
  use Symfony\Component\Console\Input\InputOption;
  use Symfony\Component\Console\Input\InputInterface;
  use Symfony\Component\Console\Output\OutputInterface;

  $console = new Application();

  $console->register('update')
          ->setDescription('Update the supplied board\'s post count')
          ->addOption('dry-run',
                      null,
                      INPUTOPTION::VALUE_NONE,
                      'Don\'t insert into the DB')
          ->addOption('boards',
                      null,
                      INPUTOPTION::VALUE_REQUIRED,
                      'Space seperated list of boards to update')
          ->setCode(function(InputInterface $input, OutputInterface $output) use ($app) {
            //
            // Loop through the supplied boards and grab the post count
            //

            $redis  = new Predis\Client();
            $insert = true;

            if ($input->getOption('dry-run')) {
              $insert = false;
            }

            if ($input->getOption('boards')) {
              $boards = explode(' ', $input->getOption('boards'));
            } else {
              $boards = unserialize($redis->get('boards'));

              if (!is_array($boards)) {
                // Not found... Grab it from the DB and update redis
                $query   = 'SELECT handle FROM boards';
                $stmt    = $app['dbs']['4changraph']->executeQuery($query);
                $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                $boards  = array();

                foreach($records as $record) {
                  $boards[] = $record['handle'];
                }

                $redis->set('boards', serialize($boards));
              }
            }

            // To hold all the boards and their highest post count
            $posts  = array();

            foreach ($boards as $board) {
              $board = trim($board);
              // Load the first page into a DomDocument and run an XPath to grab the
              // post numbers
              
              $now = date('Y-m-d H:i:s');
              $output->write("$now : Looking at <info>/$board/</info>...");
              
              $html = @file_get_contents('http://boards.4chan.org/' . $board . '/');

              if (!$html) {
                continue;
              }

              $dom = new DomDocument();
              if (!@$dom->loadHTML($html)) {
                continue;
              }

              $numbers = array();
              $xpath   = new DomXPath($dom);
              $query   = "//div[contains(@class, 'postContainer')]/div[contains(@class, 'post')]/@id";
              $postNumbers = @$xpath->query($query);
              if (empty($postNumbers)) {
                $output->write('<error>Script failed to find the parse the HTML correctly.</error>');
                $output->writeln('');
                continue;
              }

              foreach ($postNumbers as $post) {
                try {
                  // Post IDs start with p...
                  if (strpos($post->nodeValue, 'p') === 0) {
                    $numbers[] = substr($post->nodeValue, 1);
                  }
                } catch (Exception $e) 
                {}
              }

              if (empty($numbers)) {
                $output->write('<error>Script failed to find the post numbers.</error>');
                $output->writeln('');
                continue;
              }

              $posts[$board] = max($numbers);

              $output->write(" Found : <info>$posts[$board]</info>");
              $output->writeln('');
            }

            if ($insert) {
              // Insert the gathered post data into the database
              if (count($posts) > 0) {
                foreach ($posts as $board => $count) {
                  // Check Redis for the board Id
                  $boardId = $redis->get("board:id:$board");

                  if (!$boardId > 0) {
                    // Not found... Grab it from the DB and update redis
                    $query   = 'SELECT id FROM boards WHERE handle = :handle';
                    $record  = $app['dbs']['4changraph']->fetchAssoc($query, array('handle' => $board));
                    $boardId = $record['id'];

                    $redis->set("board:name:$boardId", $board);
                    $redis->set("board:id:$board", $boardId);
                  }

                  // Perform the insert
                  $app['dbs']['4changraph']->insert('posts', array('board_id' => $boardId,
                                                      'number' => $count));

                  $redis->set("board:$board:total", $count);
                }
              }
            }
            $output->writeln('');
          });

  $console->run();
