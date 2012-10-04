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
        $postEntities = $postRepository->findBy(array(), array('date' => 'DESC'));

        $posts = array_map('\Application\Controller\Blog::formatPost', $postEntities);
        return $app['twig']->render(
          'Blog/index.twig',
          array(
            'title'      => 'Phil Grayson blog',
            'posts'      => $posts,
            'categories' => \Application\Controller\Blog::getAllCategories($app)
          )
        );
      } catch (\Exception $e) {
        error_log(__CLASS__ . ' : ' . $e->getMessage());
      }
    });

    /**
     * Show action
     * display an individual blog entry
     */
    $blog->get('/{year}/{month}/{slug}', function($year, $month, $slug) use ($app)
    {
      try {
        $qb = $app['db.orm.em']['Blog']->createQueryBuilder();
        $qb
          ->select(array('p'))
          ->from('\Application\Model\Blog\Post', 'p')
          ->where($qb->expr()->andx(
            $qb->expr()->eq('YEAR(p.date)', ':year'),
            $qb->expr()->eq('MONTH(p.date)', ':month'),
            $qb->expr()->eq('p.slug', ':slug')
          ))
          ->setMaxResults(1)
          ->setParameter('year', $year)
          ->setParameter('month', $month)
          ->setParameter('slug', $slug);

        $post = \Application\Controller\Blog::formatPost(
          $qb->getQuery()->getSingleResult()
        );

        return $app['twig']->render(
          'Blog/show.twig',
          array(
            'title'      => 'Phil Grayson blog | ' . $post['title'],
            'post'       => $post,
            'categories' => \Application\Controller\Blog::getAllCategories($app)
          )
        );

      } catch (\Exception $e) {
        error_log($e->getMessage());
        throw new Exception\BlogException($e);
      }
    });

    $app->get('/{slug}', function($slug) use ($app)
    {
      try
      {
        $categoryRepository = $app['db.orm.em']['Blog']->getRepository(
          'Application\Model\Blog\Category'
        );

        $category = $categoryRepository->findOneBy(array('slug' => $slug));
        $posts = array();
        foreach($category->getPosts() as $post) {
          $posts[] = \Application\Controller\Blog::formatPost($post);
        }

        return $app['twig']->render(
          'Blog/index.twig',
          array(
            'title'      => 'Phil Grayson blog',
            'posts'      => $posts,
            'categories' => \Application\Controller\Blog::getAllCategories($app)
          )
        );
      } catch (\Exception $e) {
        error_log($e->getMessage());
        throw new Exception\BlogException($e);
      }

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

  public static function getAllCategories($app)
  {
    $categoryRepository = $app['db.orm.em']['Blog']->getRepository(
      'Application\Model\Blog\Category'
    );

    $categoryEntities = $categoryRepository->findAll();
    return array_map(
      '\Application\Controller\Blog::formatCategory',
      $categoryEntities
    );
  }

  public static function formatCategory(\Application\Model\Blog\Category $cat)
  {
    return array(
      'name' => $cat->getName(),
      'slug' => $cat->getSlug(),
      'count' => count($cat->getPosts())
    );
  }

  public static function formatPost(\Application\Model\Blog\Post $post)
  {
    return array(
      'title' => $post->getTitle(),
      'slug' => $post->getSlug(),
      'date' => $post->getDate(),
      'blurb' => $post->getBlurb(),
      'contents' => $post->getContents(),
      'category' => $post->getCategory()->getName()
    );
  }
}
