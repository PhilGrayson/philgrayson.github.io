<?php
namespace Application\Command\FourChanDash;

use Symfony\Component\Console;
use Application\Model\FourChanDash;

class BoardsAddCommand extends \Application\Command\Command
{
  protected function configure()
  {
    $this
      ->setName('boards:add')
      ->setDescription('Add a board');
  }

  protected function execute(
      Console\Input\InputInterface $input,
      Console\Output\OutputInterface $output)
  {
    $app = $this->getDIC();
    $dialog = $this->getHelperSet()->get('dialog');

    $goupRepo = $app['db.orm.em']['FourChanDash']->getRepository(
      'Application\Model\FourChanDash\BoardGroup'
    );

    $groups = $groupRepo->findBy(array(), array('id' => 'ASC'));

    do {
      if (isset($id)) {
        $output->writeln("<info>Board Group $id does not exist</info>");
      }

      $output->writeln('Choose a board group for this new board');
      foreach($groups as $group) {
        $output->writeln($group->getId() . ' : ' . $group->getName());
      }

      $id    = $dialog->ask($output, 'Id : ');
      $group = $groupRepo->find($id); 
    } while (!($group instanceof FourChanDash\BoardGroup));

    $name        = $dialog->ask($output, 'Board id (ie, b, v, sp) : ');
    $description = $dialog->ask($output, 'Board description : ');

    $path = $app['root_dir'] . '/data/FourChanDash/boards.yml';
    if (!is_writable($path)) {
      $output->writeln("<error>$path is not writable! Not adding board</error");
      return;
    }

    $boards = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($path));
    $boards[$group->getName()][$name] = $description;
    $file = fopen($path, 'w+');
    write($file, \Symfony\Component\Yaml\Yaml::dump($boards));
    close($file);

    // all the BoardsUpdate command
    $command = $this->getApplication()->find('boards:update');
    $arrayInput = new Console\Input\ArrayInput(array('command' => 'boards:update'));
    $command->run($arrayInput, $output);
  }
}
