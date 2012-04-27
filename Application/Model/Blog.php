<?php
  namespace Application\Model;

  class blog extends Model {
    public function getAll() {
      $basePath = '../data/blog';
      
      $directoryIterator = new \RecursiveDirectoryIterator($basePath);
      $allDirectories    = new \RecursiveIteratorIterator($directoryIterator);

      $fileIterator      = new \RegexIterator($allDirectories, 
                                              '/.yaml$/');
      
      $posts = array();
      foreach ($fileIterator as $file)
      {
        $posts[] = $this->parsePost($file);
      }

      return $posts;
    }

    public function get($year, $month, $name) {
      $basePath = '../data/blog/';
      $path = $basePath . $year . '/' . $month . '/' . $name . '.yaml';
      
      return $this->parsePost($path);
    }

    private function parsePost($path) {

      if (file_exists($path)) {
        return \yaml_parse_url($path); 
      }

      return false;
    }

  }
