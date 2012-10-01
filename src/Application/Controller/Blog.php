<?php
namespace Application\Controller;

class Blog implements \Silex\ControllerProviderInterface
{
  public function connect(\Silex\Application $app)
  {
    $blog = $app['controllers_factory'];

    /**
     * index action
     * display all blog posts
     */
    $blog->get('/', function() use($app)
    {
      try {
        $postRepository = $app['db.orm.em']['Blog']->getRepository(
          'Application\Model\Blog\Post'
        );
        $categoryRepository = $app['db.orm.em']['Blog']->getRepository(
          'Application\Model\Blog\Category'
        );

        $posts      = $postRepository->findBy(array(), array('date' => 'DESC'));
        $categories = $categoryRepository->findAll();
      } catch (\Exception $e) {
        error_log(__CLASS__ . ':' . __LINE__ . ' ' . $e->getMessage());
        throw new Exception\BlogException();
      }

      return $app['twig']->render(
        'Blog/index.twig',
        array(
          'title'      => 'Phil Grayson blog',
          'posts'      => $posts,
          'categories' => $categories
        )
      );
    });

    /**
     * Show action
     * display an individual blog entry
     */
    $blog->get('/{category}/{year}/{name}', function($category, $year, $name) use ($app)
    {
      try {
        $blog = new \Application\Model\Blog($app);
        $post = $blog->get($year, $name);
      } catch (\Exception $e) {
        // Something terrible has happened
        throw new Exception\BlogException('500');
      }

      if (is_array($post)) {
        return $app['twig']->render('Blog/show.twig', $post);
      }

      // cannot find that post
      throw new Exception\BlogException('404');
    });

    /**
     * Eror handler
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
