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
      $app['monolog']['FourChanDash']->addInfo(__CLASS__ . " handling BoardLoad \n " . print_r($data, 1));
      if (!isset($data['board'])) {
        $app['monolog']['FourChanDash']->addError("Missing data key 'board'");
        return;
      }
      if (!isset($data['contents'])) {
        $app['monolog']['FourChanDash']->addError("Missing data key 'contents'");
        return;
      }

      $app['monolog']['FourChanDash']->addInfo("HTML contents : " . strlen($data['contents']) . " bytes");

      $json = json_decode($data['contents']);

      if (!$json) {
        $app['monolog']['FourChanDash']->addError("JSON string could not be parsed");
        return;
      }

      $count = 0;
      foreach(array_pop($json->threads)->posts as $post) {
        if ($post->no > $count) {
          $count = $post->no;
        }
      }

      if ($count <= 0) {
        $app['monolog']['FourChanDash']->addError("Could not find any posts for '" . $data['board'] . "'");
        return;
      }

      if (!isset($data['dry-run']) || !$data['dry-run']) {
        $boardsRepo = $app['db.orm.em']['FourChanDash']->getRepository(
          'Application\Model\FourChanDash\Board'
        );

        $board = $boardsRepo->findOneByName($data['board']);
        if (!($board instanceOf \Application\Model\FourChanDash\Board)) {
          $app['monolog']['FourChanDash']->addError("Error finding board '" . $data['board'] . "' in the database");
          return;
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
