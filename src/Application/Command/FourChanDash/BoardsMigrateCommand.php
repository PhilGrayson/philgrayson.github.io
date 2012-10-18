<?php
namespace Application\Command\FourChanDash;

use Symfony\Component\Console;
use Application\Model\FourChanDash;

class BoardsMigrateCommand extends \Application\Command\Command
{

  protected function configure()
  {
    $this
      ->setName('boards:migrate')
      ->setDescription('Move data from the old DB structure to the new');
  }

  protected function execute(
      Console\Input\InputInterface $input,
      Console\Output\OutputInterface $output)
  {
    set_time_limit(60 * 60 * 2);

    $app = $this->getDIC();
    $dialog = $this->getHelperSet()->get('dialog');

    $user = $dialog->ask($output, 'MySql username : ');
    $pass = $dialog->ask($output, 'Mysql password : ');

    $boardCache = array();
    $boardRepo  = $app['db.orm.em']['FourChanDash']->getRepository(
      'Application\Model\FourChanDash\Board'
    );

    try {
      $conn = new \PDO('mysql:host=localhost;dbname=4changraph', $user, $pass);
      $select = $conn->prepare('SELECT p.number, p.date, b.handle FROM posts p INNER JOIN boards b ON p.board_id = b.id');
      $insert = $conn->prepare('INSERT INTO fourChanDash.Post (count, timestamp, board_id) VALUES(:count, :timestamp, :board)');
      $select->execute();

      while($row = $select->fetch(\PDO::FETCH_ASSOC)) {
        if (!isset($boardCache[$row['handle']])) {
          $board = $boardRepo->findOneBy(array('name' => $row['handle']));

          if (!$board instanceOf \Application\Model\FourChanDash\Board) {
            throw new \Exception('Cannot find board ' . $row['handle'] . '. Aborting migration');
          }

          $boardCache[$row['handle']] = $board;
        }
        
        $insert->bindParam(':count', $row['number']);
	$insert->bindParam(':timestamp', $row['date']);
        $insert->bindParam(':board', $boardCache[$row['handle']]->getId());
        $insert->execute();
      }
    } catch (\Exception $e) {
      $output->writeln('<error>' . $e->getMessage() . '</error>');
    }
  }
}
