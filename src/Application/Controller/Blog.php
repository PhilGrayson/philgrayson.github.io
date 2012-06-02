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
      $posts = \Application\Model\Blog::getAll();

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
       $post = \Application\Model\Blog::get($year, $month, $name);

      if (is_array($post)) {
        return $app['twig']->render('Blog/show.twig', $post);
      }

      $app->abort(404, "Blog post doesn't exist");
    })
    ->assert('year', '\d{4}')
    ->assert('month', '\d{2}');

    /**
     * Error handler
     * Currently uses for all controllers. I'm looking for a way to have
     * controller specific error handlers, possibly through custom Exceptions
     */
    $app->error(function(\Exception $e, $code) use($app)
    {
      switch ($code) {
      case 404:
        $vars = array('title' => 'Cannot find content');  
        break;
      default:
        $vars = array('title' => "It's all gone horribly wrong!");
      }

      return $app['twig']->render('Blog/404.twig', $vars);
    });

    return $blog;
  }
}
