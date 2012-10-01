<?php
return array(
  'topic'    => 'BoardRequest',
  'function' => function(\Silex\Application $app, $data)
  {
    if (!isset($data['board'])) {
      throw new \Exception("Missing data key 'board'");
    }

    if (!isset($data['dry-run'])) {
      $data['dry-run'] = false;
    }

    $board     = $data['board'];
    $boardRepo = $app['db.orm.em']['FourChanDash']->getRepository(
      'Application\Model\FourChanDash\Board'
    );

    if (!($boardRepo->findOneByName($board) instanceOf Application\Model\FourChanDash\Board)) {
      throw new \Exception("'$board' is not a valid board");
    }

    $url = "http://boards.4chan.org/$board/";
    $http = new \Server\Http\chanHttp($url);

    $response = $http->sendRequest();

    $app['event']->publish(
      'BoardLoad',
      array(
        'board'    => $board,
        'contents' => $response,
        'dry-run'  => $data['dry-run']
      )
    );
  }
);
