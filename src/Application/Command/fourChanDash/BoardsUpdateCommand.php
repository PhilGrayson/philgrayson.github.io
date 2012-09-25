<?php
namespace Application\Command\fourChanDash;

use Symfony\Component\Console;
use Application\Model\fourChanDash;

class BoardsUpdateCommand extends \Application\Command\Command
{

  protected function configure()
  {
    $this
      ->setName('boards:update')
      ->setDescription('Perform a data scrape from a board')
      ->addOption('boards',
                  null,
                  Console\Input\INPUTOPTION::VALUE_REQUIRED,
                  'Space seperated list of boards to update')
      ->addOption('dry-run',
                  null,
                  Console\Input\INPUTOPTION::VALUE_NONE,
                  'Do not save any data. Used for read-only testing');
  }

  protected function execute(
      Console\Input\InputInterface $input,
      Console\Output\OutputInterface $output)
  {
    $app = $this->getDIC();

    $dryrun = false;
    if ($input->getOption('dry-run')) {
      $dryrun = true;
    }

    $boardRepo = $app['db.orm.em']->getRepository(
      'Application\Model\fourChanDash\Board'
    );

    foreach($boardRepo->findAll() as $board) {
      $app['event']->publish(
        'BoardRequest',
        array(
          'board'   => $board->getName(),
          'dry-run' => $dryrun
        )
      );
    }
  }
}
