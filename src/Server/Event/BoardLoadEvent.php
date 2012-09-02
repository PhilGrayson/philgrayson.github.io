<?php

namespace Server\Event;

class BoardLoadEvent implments EventInterface
{
  public function handle(\Silex\Application $app, $data)
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

    $posts = 0;
    foreach($postNumbers as $post) {
      if ($post->nodeValue > $posts) {
        $posts = $post->nodeValue;
      }
    }

    if ($posts === 0) {
      throw new \Exception("Could not find any posts for $board");
    }

    if (isset($data['insert']) && $data['insert']) {
      $query = 'INSERT INTO posts (number, board_id) '.
                    "SELECT ':number', id FROM boards WHERE handle = :board";
      $app['dbs']['4changraph']->executeQuery($query, array(':board' => $board,
                                                            'number' => $count));
    }
  }
}
