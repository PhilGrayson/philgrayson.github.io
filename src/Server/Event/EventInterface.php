<?php
namespace Server\Event;

interface EventInterface
{
  public function handle(\Silex\Application $app, $data);
}
