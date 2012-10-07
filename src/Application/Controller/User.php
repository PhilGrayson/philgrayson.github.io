<?php
namespace Application\Controller;

class User implements \Silex\ControllerProviderInterface
{
  public function connect(\Silex\Application $app)
  {
    $user = $app['controllers_factory'];

    $app['twig_user_vars'] = array(
      'title'      => 'Phil Grayson | Users',
    );

    $user->get('/', $this->redirectIndexAction($app));
    $user->get('/users', $this->indexAction($app));
    $blog->get('/users/new', $this->createAction($app))->before($this->checkLoggedIn($app));
    $blog->get('/users/login', $this->loginAction($app))->before($this->checkNotLoggedIn($app));
    $blog->get('/users/logout', $this->logoutAction($app))->before($this->checkLoggedIn($app));

    return $blog;
  }

  private function redirectIndexAction(\Silex\Application $app)
  {
    return function() use ($app)
    {
      return $app->redirect('/users/users');
    };
  }

  private function indexAction(\Silex\Application $app)
  {
    return function() use($app)
    {
      try {
        $postRepository = $app['db.orm.em']['Blog']->getRepository(
          'Application\Model\Blog\User'
        );
        $posts = $postRepository->findAll();

        return $app['twig']->render(
          'User/index.twig',
          array_merge($app['twig_user_vars'], array(
            'users'      => $users
          ))
        );
      } catch (\Exception $e) {
        error_log(__CLASS__ . ' : ' . $e->getMessage());
      }
    };
  }

  private function createAction(\Silex\Application $app)
  {
    return function() use($app)
    {
    
    };
  }

  private function loginAction(\Silex\Application $app)
  {
    return function() use($app)
    {
    
    };
  }

  private function logoutAction(\Silex\Application $app)
  {
    return function() use($app)
    {
    
    };
  }

  private function checkLoggedIn(\Silex\Application $app)
  {
    return function() use ($app)
    {
      if (!$app['session']->has('user.id')) {
        return $app->redirect('/users/login');
      }
    };
  }

  private function checkNotLoggedIn(\Silex\Application $app)
  {
    return function() use ($app)
    {
      if ($app['session']->has('user.id')) {
        return $app->redirect('/users');
      }
    };
  }
}
