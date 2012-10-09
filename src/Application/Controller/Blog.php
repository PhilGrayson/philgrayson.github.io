<?php
namespace Application\Controller;

use \Symfony\Component\Validator\Constraints as Assert;

class Blog implements \Silex\ControllerProviderInterface
{
  public function connect(\Silex\Application $app)
  {
    $blog = $app['controllers_factory'];

    $app['twig_blog_vars'] = array(
      'title'      => 'Phil Grayson',
      'categories' => $this->getAllCategories($app)
    );

    $blog->get('/', $this->redirectIndexAction($app));
    $blog->get('/posts', $this->indexAction($app));
    $blog->post('/posts', $this->postCreateAction($app))->before($this->checkIsAdmin($app));
    $blog->get('/posts/new', $this->getCreateAction($app))->before($this->checkIsAdmin($app));
    $blog->get('/posts/{id}', $this->showAction($app));
    $blog->get('/posts/{year}/{month}/{slug}', $this->showSlugAction($app));

    $blog->get('/category/{id}', $this->categoryShowAction($app));

    return $blog;
  }

  private function redirectIndexAction(\Silex\Application $app)
  {
    return function() use ($app)
    {
      return $app->redirect('/blog/posts');
    };
  }

  private function indexAction(\Silex\Application $app)
  {
    return function() use($app)
    {
      try {
        $postRepository = $app['db.orm.em']['Blog']->getRepository(
          'Application\Model\Blog\Post'
        );
        $posts = $postRepository->findBy(array(), array('date' => 'DESC'));

        return $app['twig']->render(
          'Blog/post/index.twig',
          array_merge($app['twig_blog_vars'], array(
            'title'      => 'Phil Grayson blog',
            'posts'      => $posts
          ))
        );
      } catch (\Exception $e) {
        error_log(__CLASS__ . ' : ' . $e->getMessage());
      }
    };
  }

  private function getCreateAction(\Silex\Application $app)
  {
    return function() use($app)
    {
      if (null !== $vars = $app['session']->get('post.create')) {
        $app['session']->remove('post.create');
      } else {
        $vars = array();
      };

      return $app['twig']->render(
        'Blog/post/create.twig',
        array_merge($app['twig_blog_vars'], $vars)
      );
    };
  }

  private function postCreateAction(\Silex\Application $app)
  {
    return function() use($app)
    {
      $validateCategory = function($id) use ($app) {
        return $app['db.orm.em']['Blog']->find('Application\Model\Blog\Category', $id);
      };

      $requestParams = $app['request']->request;
      $vars = array(
        'title'    => $requestParams->get('title'),
        'date'     => $requestParams->get('date'),
        'category' => $requestParams->get('category'),
        'blurb'    => $requestParams->get('blurb'),
        'contents' => $requestParams->get('contents')
      );

      $validations = array();
      $validations['title']    = $app['validator']->validateValue($vars['title'], new Assert\NotBlank);
      $validations['date']     = $app['validator']->validateValue(
        $vars['date'] . ' ' . date('H:i:s'), new Assert\DateTime
      );
      $validations['category'] = $validateCategory($vars['category']) ? true : 'Select a valid category';
      $validations['blurb']    = $app['validator']->validateValue($vars['blurb'], new Assert\NotBlank);
      $validations['contents'] = $app['validator']->validateValue($vars['contents'], new Assert\NotBlank);

      foreach ($validations as $key => $validation) {
        if (count($validation) == 0 || $validation === true) {
          unset($validations[$key]);
        }
      }

      if (count($validations) > 0) {
        $app['session']->set('post.create', array('errors' => $validations, 'vars' => $vars));
        return $app->redirect('/blog/posts/new');
      }

      $post = new \Application\Model\Blog\Post;
      $post->setActive(true);
      $post->setTitle($vars['title']);
      $post->setSlug(preg_replace('/[^A-Za-z0-9-]+/', '-', $vars['title']));
      $post->setDate(new \Datetime($vars['date'] . date('H:i:s')));
      $post->setCategory($validateCategory($vars['category']));
      $post->setBlurb($vars['blurb']);
      $post->setContents($vars['contents']);

      $app['db.orm.em']['Blog']->persist($post);
      $app['db.orm.em']['Blog']->flush();

      return $app->redirect('/blog/posts/' . $post->getId());
    };
  }

  private function showAction(\Silex\Application $app)
  {
    return function($id) use($app)
    {
      try {
        $post = $app['db.orm.em']['Blog']->find('Application\Model\Blog\Post', $id);

        if (!$post instanceOf \Application\Model\Blog\Post) {
          throw new \Exception("Cannot find blog post with ID '$id'");
        }

        return $app['twig']->render(
          'Blog/post/show.twig',
          array_merge($app['twig_blog_vars'], array(
            'title'      => 'Phil Grayson | ' . $post->getTitle(),
            'post'       => $post,
          ))
        );
      } catch (\Exception $e) {
        error_log(__CLASS__ . ' : ' . $e->getMessage());
      }
    };
  }

  private function showSlugAction(\Silex\Application $app)
  {
    return function($year, $month, $slug) use ($app)
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

        $post = $qb->getQuery()->getSingleResult();

        return $app['twig']->render(
          'Blog/post/show.twig',
          array(
            'title'      => 'Phil Grayson | ' . $post['title'],
            'post'       => $post,
            'categories' => $getAllCategories($app)
          )
        );

      } catch (\Exception $e) {
        error_log($e->getMessage());
        throw new Exception\BlogException($e);
      }
    };
  }

  private function categoryShowAction(\Silex\Application $app)
  {
    return function($id) use ($app)
    {
      try
      {
        $category = $app['db.orm.em']['Blog']->find('Application\Model\Blog\Category', $id);

        if (!$category instanceOf \Application\Model\Blog\Category) {
          throw new \Exception("Category $id doesn't not exist");
        }

        return $app['twig']->render(
          'Blog/category/index.twig',
          array_merge($app['twig_blog_vars'], array(
            'title'      => 'Phil Grayson | ' . $category->getName(),
            'category'   => $category
          ))
        );
      } catch (\Exception $e) {
        error_log($e->getMessage());
        throw new Exception\BlogException($e);
      }
    };
  }

  private function getAllCategories(\Silex\Application $app)
  {
    $categoryRepository = $app['db.orm.em']['Blog']->getRepository(
      'Application\Model\Blog\Category'
    );

    return $categoryRepository->findAll();
  }

  private function checkIsAdmin(\Silex\Application $app)
  {
    return function() use ($app)
    {
      $repo = $app['db.orm.em']['User']->getRepository(
        'Application\Model\User\Role'
      );
      $admin = $repo->findOneBy(array('name' => 'ADMIN'));
      if (!$app['security']->isLoggedIn() || !$app['security']->isAuthorized($admin)) {
        return $app->redirect('/users/login');
      }
    };
  }
}
