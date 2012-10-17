<?php
namespace Application\Event\Events\FourChanDash;

class ScrapeBoardPostCount implements \Application\Event\EventInterface
{
  public function getTopic()
  {
    return 'BoardLoad';
  }

  public function getFunction()
  {
    return function(\Silex\Application $app, $data)
    {
      if (!isset($data['board'])) {
        throw new \Exception("Missing data key 'board'");
      }
      if (!isset($data['contents'])) {
        throw new \Exception("Missing data key 'contents'");
      }

      $json = json_decode($data['contents']);

      if (!$json) {
        throw new \Exception("Could not parse HTML for $board");
      }

      $count = 0;
      foreach(array_pop($json->threads)->posts as $post) {
        if ($post->no > $count) {
          $count = $post->no;
        }
      }

      if ($count <= 0) {
        throw new \Exception('Could not find any posts for ' . $data['board']);
      }

      if (!isset($data['dry-run']) || !$data['dry-run']) {
        $boardsRepo = $app['db.orm.em']['FourChanDash']->getRepository(
          'Application\Model\FourChanDash\Board'
        );

        $board = $boardsRepo->findOneByName($data['board']);
        if (!($board instanceOf \Application\Model\FourChanDash\Board)) {
          throw new \Exception('Error finding board /' . $data['board'] . '/ in ' . __FILE__);
        }

        $post = new \Application\Model\FourChanDash\Post();
        $post->setBoard($board);
        $post->setCount($count);
        $post->setTimestamp(new \DateTime('now'));
        $app['db.orm.em']['FourChanDash']->persist($post);
        $app['db.orm.em']['FourChanDash']->flush();
      }
    };
  }
}
