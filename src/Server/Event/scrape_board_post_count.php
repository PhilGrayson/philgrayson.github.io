<?php
return array(
  'topic'    => 'BoardLoad',
  'function' => function(\Silex\Application $app, $data)
  {
    if (!isset($data['board'])) {
      throw new \Exception("Missing data key 'board'");
    }
    if (!isset($data['contents'])) {
      throw new \Exception("Missing data key 'contents'");
    }

    $dom = new \DomDocument();
    @$dom->loadHTML($data['contents']);
    $xpath = new \DomXPath($dom);
    $query = "//*[contains(@class, 'post reply')]/@id";

    $postNumbers = @$xpath->query($query);

    if (empty($postNumbers)) {
      throw new \Exception("Could not parse HTML for $board");
    }

    $count = 0;
    foreach($postNumbers as $post) {
      $post = substr($post->nodeValue, 1);
      if ($post > $count) {
        $count = $post;
      }
    }

    if ($count <= 0) {
      throw new \Exception('Could not find any posts for ' . $data['board']);
    }

    if (!isset($data['dry-run']) || !$data['dry-run']) {
      $boardsRepo = $app['db.orm.em']->getRepository(
        'Application\Model\fourChanDash\Board'
      );

      $board = $boardsRepo->findOneByName($data['board']);
      if (!($board instanceOf Application\Model\fourChanDash\Board)) {
        throw new \Exception('Error finding board /' . $data['board'] . '/ in ' . __FILE__);
      }

      $post = new Application\Model\fourChanDash\Post();
      $post->setBoard($board);
      $post->setCount($count);
      $post->setTimestamp(new \DateTime('now'));
      $app['db.orm.em']->persist($post);
      $app['db.orm.em']->flush();
    }
  }
);
