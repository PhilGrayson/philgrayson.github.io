<?php
namespace Server\Event;

class BoardRequestEvent implements EventInterface
{
  public function handle(\Silex\Application $app, $data)
  {
    if (!isset($data['board'])) {
      throw new \Exception("Missing data key 'board'");
    }

    $board = $data['board']
    if (!\Application\Model\chanGraph::isValid($board))
    {
      throw new \Exception("'$board' is not a valid board");
    }

    $url = "http://boards.4chan.org/$board/";
    $http = new \Server\Http\chanHttp($url);

    $response = $http->sendRequest();

    $app['event']->trigger('BoardLoad', array('board' => $board, 'contents' => $response));
  }
}
