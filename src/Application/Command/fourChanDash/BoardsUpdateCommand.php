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
      ->setDescription('Update the database to include all board groups and boards from the boards.yml file');
  }

  protected function execute(
      Console\Input\InputInterface $input,
      Console\Output\OutputInterface $output)
  {
    $app = $this->getDIC();
    $groups = \Symfony\Component\Yaml\Yaml::parse(
      $app['data.dir'] . '/fourChanDash/boards.yml'
    );

    $groupRepo = $app['db.orm.em']->getRepository(
      'Application\Model\fourChanDash\BoardGroup'
    );

    $boardRepo = $app['db.orm.em']->getRepository(
      'Application\Model\fourChanDash\Board'
    );

    foreach($groups as $group => $boards) {
      $boardGroup = $groupRepo->findOneByName($group);

      if (!($boardGroup instanceOf fourChanDash\BoardGroup)) {
        $boardGroup = new fourChanDash\BoardGroup();
        $boardGroup->setName($group);
        $app['db.orm.em']->persist($boardGroup);
      }

      foreach($boards as $name => $description) {
        $board = $boardRepo->findOneByName($name);

        if (!($board instanceOf fourChanDash\Board)) {
          $board = new fourChanDash\Board();
          $board->setName($name);
          $board->setDescription($description);
          $board->setBoardGroup($boardGroup);
          $app['db.orm.em']->persist($board);
        }
      }
    }

    $app['db.orm.em']->flush();
  }
}
