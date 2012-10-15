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
      $stmt = $conn->prepare('p.number, p.date, b.handle FROM posts p INNER JOIN boards b ON p.board_id = b.id');
      $stmt->execute();

      while($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        if (!isset($boardCache[$row['handle']])) {
          $board = $boardRepo->findOneBy(array('name' => $row['handle']));

          if (!$board instanceOf \Application\Model\FourChanDash\Board) {
            throw new \Exception('Cannot find board ' . $row['handle'] . '. Aborting migration');
          }

          $boardCache[$row['handle']] = $board;
        }

        $post = new \Application\Model\FourChanDash\Post;
        $post->setCount($row['number']);
        $post->setTimestamp(new \DateTime($row['timestamp']));
        $post->setBoard($boardCache[$row['handle']]);
        $app['db.orm.em']->persist($post);
      }

      $app['db.orm.em']->flush();
    } catch (\Exception $e) {
      $output->writeln('<error>' . $e->getMessage() . '</error>');
    }
  }
}
