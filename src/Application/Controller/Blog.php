<?php
namespace Application\Controller;

use \Silex\ControllerProviderInterface;
use \Silex\ControllerCollection;

class Blog implements ControllerProviderInterface
{
  public function connect(\Silex\Application $app)
  {
    $blog = new ControllerCollection();

    /*
     * index action
     * Display all blog posts
     */
    $blog->get('/', function() use($app)
    {
      try {
        $posts = \Application\Model\Blog::getAll();
      } catch (\Exception $e) {
        throw new Exception\BlogException('500');
      }

      // Place the posts into an array of arrays based on year month day
      $sorted = array();
      if (count($posts) > 0) {
        foreach($posts as $post) {
          $year  = (int) $post['date']['year'];
          $month = (int) $post['date']['month'];
          $day   = (int) $post['date']['day'];
          $sorted[$year][$month][$day][] = $post;
        }

        // Arrange to be latest first
        krsort($sorted[$year][$month][$day]);
        krsort($sorted[$year][$month]);
        krsort($sorted[$year]);
      }

      return $app['twig']->render('Blog/index.twig',
                                  array('title' => 'Phil Grayson blog',
                                        'posts' => $sorted));
    });

    /*
     * Show action
     * Display an individual blog entry
     */
    $blog->get('/{year}/{month}/{name}', function($year, $month, $name) use ($app)
    {
      try {
        $post = \Application\Model\Blog::get($year, $month, $name);
      } catch (\Exception $e) {
        // Something terrible has happened
        throw new Exception\BlogException('500');
      }

      if (is_array($post)) {
        return $app['twig']->render('Blog/show.twig', $post);
      }

      // Cannot find that post
      throw new Exception\BlogException('404');
    });

    /**
     * Error handler
     */
    $app->error(function(Exception\BlogException $e) use($app)
    {
      $code = $e->getMessage();
      switch ($code) {
      case '404':
        $vars = array('title' => 'I cannot find that post!');
        break;
      default:
        $vars = array('title' => "It's all gone horribly wrong! I recommend panicing");
      }

      return $app['twig']->render('Blog/404.twig', $vars);
    });

    return $blog;
  }
}
