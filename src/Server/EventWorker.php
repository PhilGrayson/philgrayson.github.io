<?php

namespace Server;

class EventWorker
{
  private $worker;

  public function __construct()
  {
    $context = new \ZMQContext();
    $this->worker = new \ZMQSocket($context, \ZMQ::SOCKET_REP);
    $this->worker->connect('tcp://localhost:7878');
  }

  public function run()
  {
    $message = unserialize($this->worker->recv());
    // do work here
    $this->worker->send(call_user_func($message));
  }
}
