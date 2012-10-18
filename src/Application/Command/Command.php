<?php
namespace Application\Command;

use Symfony\Component\Console;

class Command extends Console\Command\Command
{
  private $app;
  public function __construct(\Silex\Application $app)
  {
    $this->app = $app;
    parent::__construct();
  }

  protected function getDIC()
  {
    return $this->app;
  }
}
