<?php
namespace Application\Event;

interface EventInterface
{
  public function getTopic();
  public function getFunction();
}
