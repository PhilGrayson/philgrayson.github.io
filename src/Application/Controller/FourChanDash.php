<?php
namespace Application\Controller;

class FourChanDash implements \Silex\ControllerProviderInterface
{
  public function connect(\Silex\Application $app)
  {
    $chanDash = $app['controllers_factory'];

    /**
     * idex action
     * Povide controls to query the board post counts
     */
    $chanDash->get('/', function() use ($app)
    {
      try {
        $vars = array ('title' => 'Misc Tools',
                       'boards' => \Application\Model\chanGraph::$boards);
      } catch (\Exception $e) {
        throw new Exception\chanGraphException('500');
      }

      return $app['twig']->render('chanGraph/index.twig', $vars);
    });

    $chanDash->get('/search', function() use ($app)
    {
      try {
        $boards = $app['request']->get('boards');
        $boards = str_getcsv($boards);

        if (empty($boards)) {
          // Get all boards
          $boards = array();
          foreach(\Application\Model\chanGraph::$boards as $category => $list) {
            $boards = array_merge($boards, array_values($list));
          }
        }

        $from = new \DateTime($app['request']->get('from'));
        $to   = new \DateTime($app['request']->get('to'));

        if (!($from && $to)) {
          // Set  default time range
          $from = new \DateTime('1 day ago');
          $to   = new \DateTime();
        }
      } catch (\Exception $e) {
        $app['monolog']->addCritical($e->getMessage());
        throw new Exception\chanGraphException('500');
      }

      try {
        $chanGraph = new \Application\Model\chanGraph($app['dbs']['chanGraph']);
      } catch (\Exception $e) {
        $app['monolog']->addCritical($e->getMessage());
        throw new Exception\chanGraphException('500');
      }

      $content   = array();

      try {
        $counts  = $chanGraph->getPostCount($boards);
        $posts   = $chanGraph->getPosts($boards, $from, $to);
      } catch (\Exception $e) {
        $app['monolog']->addCritical($e->getMessage());
        throw new Exception\chanGraphException('500');
      }

      // Build the response
      if (count($counts) > 0 && count($posts) > 0) {
        foreach ($counts as $board => $count) {
            if (!empty($count['number'])) {
              $content['boards'][$board]['total'] = $count;
            }
        }

        foreach($posts as $post) {
          if (!empty($post)) {
            $postData = array(
              'count' => $post['number'],
              'date' => $post['date']
            );

            $content['boards'][$post['board']]['posts'][] = $postData;
          }
        }
      }

      return $app->json($content);
    });

    /**
     * Error handler
     */
    $app->error(function(Exception\chanGraphException $e) use($app)
    {
      $code = $e->getMessage();
      switch ($code) {
      default:
        $vars = array('title' => "It's all gone horribly wrong! I recommend panicing");
      }

      return $app['twig']->render('chanGraph/404.twig', $vars);
    });

    return $chanDash;
  }
}
