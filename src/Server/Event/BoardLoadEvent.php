<?php
namespace Server\Event;

class BoardLoadEvent implements EventInterface
{
  public function handle($data)
  {
    if (!empty($data['board']) &&
        \Application\Model\chanGraph::isValid($data['board'])) {
      $url = 'http://boards.4chan.org/' . $data['board'] . '/';
      $http = new \Server\Http\chanHttp($url);

      $response = $http->sendRequest();

      return $data['pid'];
      return "OKAY";
    }
  }
}
