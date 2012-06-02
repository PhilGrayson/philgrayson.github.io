<?php
  namespace Application\Model;

  use Symfony\Component\Yaml\Yaml;

  class Blog {
    public static function getAll() {
      $basePath = '../data/blog';
      
      $directoryIterator = new \RecursiveDirectoryIterator($basePath);
      $allDirectories    = new \RecursiveIteratorIterator($directoryIterator);

      $fileIterator      = new \RegexIterator($allDirectories, 
                                              '/.yaml$/');
      
      $posts = array();
      $blog  = new Blog();
      foreach ($fileIterator as $file)
      {
        $posts[] = $blog->parsePost($file);
      }

      return $posts;
    }

    public function get($year, $month, $name) {
      $basePath = '../data/blog/';
      $path = $basePath . $year . '/' . $month . '/' . $name . '.yaml';

      $blog = new Blog();
      return $blog->parsePost($path);
    }

    private function parsePost($path) {

      if (file_exists($path)) {
        return Yaml::parse($path);
      }

      return false;
    }
  }
