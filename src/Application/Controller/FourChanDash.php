<?php
namespace Application\Controller;

class FourChanDash implements \Silex\ControllerProviderInterface
{
  public function connect(\Silex\Application $app)
  {
    $chanDash = $app['controllers_factory'];

    /**
     * index action
     * Povide controls to query the board post counts
     */
    $chanDash->get('/', function() use ($app)
    {
      $boardRepo = $app['db.orm.em']['FourChanDash']->getRepository(
        'Application\Model\FourChanDash\Board'
      );

      $boards = $boardRepo->findBy(array(), array('name' => 'ASC'));
      $sorted = array();

      foreach($boards as $board) {
        $sorted[$board->getBoardGroup()->getName()][] = $board;
      }

      $vars = array (
        'title'  => 'FourChanDash',
        'boards' => $sorted
      );

      return $app['twig']->render('FourChanDash/index.twig', $vars);
    });

    $chanDash->get('/search', function() use ($app)
    {
      $getPostCount = function($em, array $boards)
      {
        $counts = array();
        $query = $em->createQuery('SELECT MAX(p.count) as count
                                          Application\Model\FourChanDash\Post as p
                               INNER JOIN p.board as b
                                    WHERE b.name = :board');
        foreach($boards as $board) {
          $query->setParameter('board', $board);
          $count = $query->getResult();
          $counts[$board] = $count[0][1];
        }

        return $counts;
      };

      $getPosts = function($em, array $boards, \DateTime $from, \DateTime $to)
      {
        $posts = array();
        $delta = $from->diff($to);

        if ($delta->y > 0 || $delta->m > 0) {
          $date_format = '%Y-%m-%d';
        } else {
          $date_format = '%Y-%m-%d %H';
        }

        $query = 'SELECT MAX(p.count) - MIN(p.count) AS number, '.
                        "DATE_FORMAT(p.timestamp, '$date_format') AS date ".
                   'FROM Application\Model\FourChanDash\Post as p '.
             'INNER JOIN p.board as b '.
                  'WHERE b.name = :board '.
                    'AND p.timestamp > :from '.
                    'AND p.timestamp < :to '.
               'GROUP BY date '.
               'ORDER BY p.timestamp ASC';
        $query = $em->createQuery($query);

        foreach($boards as $board) {
          $query->setParameter('board', $board);
          $query->setParameter('from', $from);
          $query->setParameter('to', $to);
          $records = $query->getResult();
          foreach($records as $count) {
            $posts[$board][] = array('number' => $count['number'], 'date' => $count['date']);
          }
        }

        return $posts;
      };

      $boards = $app['request']->get('boards');
      $boards = str_getcsv($boards);

      if (empty($boards)) {
        // Get all boards
        $boards = array();
        $boardRepo = $app['db.orm.em']['FourChanDash']->getRepository(
          'Application\Model\FourChanDash\Board'
        );

        foreach($boardRepo->findAll() as $board) {
          $boards[] = $board->getName();
        }
      }

      $from = new \DateTime($app['request']->get('from'));
      $to   = new \DateTime($app['request']->get('to'));

      if (!($from && $to)) {
        // Set  default time range
        $from = new \DateTime('1 day ago');
        $to   = new \DateTime();
      }

      $content = array();

      $counts  = $getPostCount($app['db.orm.em']['FourChanDash'], $boards);
      $posts   = $getPosts($app['db.orm.em']['FourChanDash'], $boards, $from, $to);

      // Build the response
      if (count($counts) > 0 && count($posts) > 0) {
        foreach ($counts as $board => $count) {
            if (!empty($count['number'])) {
              $content['boards'][$board]['total'] = $count;
            }
        }

        foreach($posts as $board => $post) {
          if (!empty($post)) {
            $content['boards'][$board]['posts'][] = $post;
          }
        }
      }

      return $app->json($content);
    });


    return $chanDash;
  }
}
