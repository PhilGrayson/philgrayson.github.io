<?php
namespace Application\Command\FourChanDash;

use Symfony\Component\Console;
use Application\Model\FourChanDash;

class BoardsSyncCommand extends \Application\Command\Command
{

  protected function configure()
  {
    $this
      ->setName('boards:sync')
      ->setDescription('Syncs the database to include all board groups and boards from the boards.yml file');
  }

  protected function execute(
      Console\Input\InputInterface $input,
      Console\Output\OutputInterface $output)
  {
    $app = $this->getDIC();
    $groups = \Symfony\Component\Yaml\Yaml::parse(
      $app['root_dir'] . '/data/fourChanDash/boards.yml'
    );

    $groupRepo = $app['db.orm.em']['FourChanDash']->getRepository(
      'Application\Model\FourChanDash\BoardGroup'
    );

    $boardRepo = $app['db.orm.em']['FourChanDash']->getRepository(
      'Application\Model\FourChanDash\Board'
    );

    foreach($groups as $group => $boards) {
      $boardGroup = $groupRepo->findOneByName($group);

      if (!($boardGroup instanceOf FourChanDash\BoardGroup)) {
        $boardGroup = new FourChanDash\BoardGroup();
        $boardGroup->setName($group);
        $app['db.orm.em']['FourChanDash']->persist($boardGroup);
      }

      foreach($boards as $name => $description) {
        $board = $boardRepo->findOneByName($name);

        if (!($board instanceOf FourChanDash\Board)) {
          $board = new FourChanDash\Board();
          $board->setName($name);
          $board->setDescription($description);
          $board->setBoardGroup($boardGroup);
          $app['db.orm.em']['FourChanDash']->persist($board);
        }
      }
    }

    $app['db.orm.em']['FourChanDash']->flush();
  }
}
