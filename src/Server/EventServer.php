<?php
namespace Server;

use Symfony\Component\EventDispatcher;

class EventServer
{
  private $port;

  public function __construct($port = 6767)
  {
    if (!is_numeric($port)) {
      $this->port = 6767;
    }
  }

  public function run()
  {
    $context = new \ZMQContext();
    $frontend = new \ZMQSocket($context, \ZMQ::SOCKET_XREP);
    $frontend->bind('tcp://*:6767');

    $backend = new \ZMQSocket($context, \ZMQ::SOCKET_XREQ);
    $backend->bind('tcp://*:7878');

    $poll = new \ZMQPoll();
    $poll->add($frontend, \ZMQ::POLL_IN);
    $poll->add($backend, \ZMQ::POLL_IN);

    $read = $write = array();

    while (true) {
      $poll->poll($read, $write);

      foreach ($read as $socket) {
        if ($socket === $frontend) {
        //  error_log("spawning a new worker");
        //  $this->spawnWorker();
          $msg = $frontend->recvMulti();
          $backend->sendMulti($msg);
        } else if ($socket === $backend) {
          $msg = $backend->recvMulti();
          $frontend->sendMulti($msg);
        }
      }
    }
  }

  private function spawnWorker()
  {
    if (pcntl_fork() == 0) {
      `php server.php new-worker`;
      exit;
    }
  }
}
