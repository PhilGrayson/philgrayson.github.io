#!/usr/bin/env php

<?php
  $app = require 'index.php';

  use Symfony\Component\Console\Application;
  use Symfony\Component\Console\Input\InputOption;
  use Symfony\Component\Console\Input\InputInterface;
  use Symfony\Component\Console\Output\OutputInterface;

  $console = new Application();

  $console->register('new')
          ->setDescription('Wizard for creating a new blog post')
          ->setCode(function(InputInterface $input, OutputInterface $output) use ($console) {
            $dialog  = $console->getHelperSet()->get('dialog');

            $date  = date('Y-m-d');
            $year  = date('Y');
            $month = date('m');
            $day   = date('d');

            
            do {
              $title = $dialog->ask($output, 'Title : ', '');
              if (empty($title)) {
                $output->writeln('<error>Invalid title. Please at least provide something.</error>');
              }
            } while (empty($title));
            
            do {
              $url = $dialog->ask($output, 'Url : ', '');
              $url = strtolower($url);

              $postUrl = "$year/$month/$url";
              $path = root_dir . "/data/blog/$postUrl.yaml";
              if (empty($url)) {
                $output->writeln('<error>Invalid url. Please at least provide something.</error>');
              } else if (strpos($url, ' ') !== false) {
                $url = '';
                $output->writeln('<error>Invalid url. Spaces are not allowed.</error>');
              } else if (strpos($url, '/') !== false) {
                $url = '';
                $output->writeln('<error>Invalid url. Forward slashes are not allowed.</error>');
              } else if (file_exists($path)) {
                $url = '';  // Force loop
                $output->writeln('<error>Invalid url. It already exists</error>');
              }
            } while (empty($url));

            do {
              $blurb = $dialog->ask($output, 'Blurb : ', '');

              $confirm = false;
              if (empty($blurb)) {
                $confirm = $dialog->askConfirmation($output, 'Are you sure this blog should have no blurb? ', false);
              } else {
                $confirm = true;
              }
            } while (!$confirm);

            do {
              $content = $dialog->ask($output, 'Content : ', '');

              if (empty($content)) {
                $output->writeln('<error>Invalid content. Please at least give something :< </error>');
              }
            } while (empty($content));

            // At this point we should have everything required to build the post yaml
            $arrPost = array('title' => $title,
                             'url' => "$postUrl",
                             'date' => array (
                               'string' => $date,
                               'year'   => $year,
                               'month'  => $month,
                               'day'    => $day,
                             ),
                             'blurb' => $blurb,
                             'content' => $content);

            $yaml = \yaml_emit($arrPost);

            if (!file_exists($path)) {
              $file = fopen($path, 'w');
              if (fwrite($file, $yaml)) {
                $output->writeln('');
                $output->writeln('Post added!');
                $output->writeln("http://philgrayson.com/$postUrl");
              }

              fclose($file);
            }

          });

  $console->run();
