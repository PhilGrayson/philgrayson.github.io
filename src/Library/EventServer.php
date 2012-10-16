<?php
namespace Library;

class EventServer
{
  private $app;
  private $subUid = -1;
  private $topics;

  public function __construct(\Silex\Application $app)
  {
    $this->app    = $app;
    $this->topics = array();
  }

  public function publish($topic, $args)
  {
    if (empty($this->topics[$topic])) {
      return false;
    }

    $subscribers = $this->topics[$topic];
    $len         = $subscribers ? count($subscribers) : 0;

    while ($len--) {
      $subscribers[$len]['function']($this->app, $args);
    }

    return $this;
  }

  public function subscribe($topic, $fn)
  {
    if (!isset($this->topics[$topic])) {
      $this->topics[$topic] = array();
    }

    $this->subUid += 1;
    $this->topics[$topic][] = array(
      'token' => $this->subUid,
      'function' => $fn
    );

    return $this->subUid;
  }

  public function unsubscribe($token)
  {
    foreach($this->topics as $i => $topic) {
      foreach($topic as $j => $event) {
        if ($event['token'] === $token) {
          unset($this->topics[$i][$j]);
          return $token;
        }
      }
    }

    return $this;
  }
}
