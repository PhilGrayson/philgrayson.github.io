<?php

namespace Application\Event;

class EventAttacher
{
  public static function attach(\Library\EventServer &$server)
  {
    $it = new \RecursiveDirectoryIterator(__DIR__ . '/Events');
    $it = new \RecursiveIteratorIterator($it);
    $it = new \RegexIterator($it, '/^.+\.php$/');

    foreach($it as $file) {
      $path = str_replace(__DIR__ . '/Events', '', $file->getPath());
      $path = str_replace('/', '\\', $path);
      $name = $file->getBasename('.php');

      $class = '\\Application\\Event\\Events' . $path . '\\' . $name; 
      $class = new $class;

      if ($class instanceOf \Application\Event\EventInterface) {
        $server->subscribe($class->getTopic(), $class->getFunction());
      }
    }
  }
}
