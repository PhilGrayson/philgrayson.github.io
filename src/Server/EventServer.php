<?php
namespace Server;

class EventServer
{
  private $app;
  public function __construct(\Silex\Application $app)
  {
    $this->app = $app;
  }

  public function trigger($event, $data)
  {
    $class   = '\Server\Event\\' . $event . 'Event';
    $handler = new $class;

    if (!($handler instanceOf \Server\Event\EventInterface)) {
      throw new \Exception($event . ' is not an event');
    }

    return $handler->handle($this->app, $data);
  }
}
