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
    $user->post('/users', $this->createPostAction($app));
    $user->get('/users/new', $this->createGetAction($app))->before($this->checkLoggedIn($app));
    $user->get('/login', $this->loginGetAction($app))->before($this->checkNotLoggedIn($app));
    $user->post('/login', $this->loginPostAction($app))->before($this->checkNotLoggedIn($app));
    $user->get('/logout', $this->logoutAction($app));

    return $user;
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
        $usersRepository = $app['db.orm.em']['User']->getRepository(
          'Application\Model\User\User'
        );
        $users = $usersRepository->findAll();

        return $app['twig']->render(
          'User/index.twig',
          array_merge($app['twig_user_vars'], array(
            'users' => $users
          ))
        );
      } catch (\Exception $e) {
        error_log(__CLASS__ . ' : ' . $e->getMessage());
      }
    };
  }

  private function createGetAction(\Silex\Application $app)
  {
    return function() use($app)
    {
      return $app['twig']->render(
        'User/create.twig',
        array_merge($app['twig_user_vars'], array())
      );
    };
  }

  private function createPostAction(\Silex\Application $app)
  {
    return function() use($app)
    {
    };
  }

  private function loginGetAction(\Silex\Application $app)
  {
    return function() use($app)
    {
      $vars = array();
      if ($vars['login'] = $app['session']->get('login')) {
        $app['session']->remove('login');
      }

      return $app['twig']->render(
        'User/login.twig',
        array_merge($app['twig_user_vars'], $vars)
      );
    };
  }


  private function loginPostAction(\Silex\Application $app)
  {
    return function() use($app)
    {
      $email   = $app['request']->request->get('_username');
      $pass    = $app['request']->request->get('_password');

      $repo = $app['db.orm.em']['User']->getRepository(
        'Application\Model\User\User'
      );

      try {
        $user = $repo->findOneBy(array('email' => $email));
        if (!$user instanceOf \Application\Model\User\User) {
          throw new \Exception();
        }

        $passlib = new \PasswordLib\PasswordLib;
        if (!$passlib->verifyPasswordHash($pass, $user->getPassword())) {
          throw new \Exception();
        }

        $app['session']->set('user', $user);
        return $app->redirect('/users/users');
      } catch (\Exception $e) {
        $app['session']->set('login', array(
          'last_email' => $email,
          'error'      => 'Username and/or Password was not recognised'
        ));
        return $app->redirect('/users/login');
      }
    };
  }

  private function logoutAction(\Silex\Application $app)
  {
    return function() use($app)
    {
      $app['session']->remove('user');
      return $app->redirect('/users/users');
    };
  }

  private function checkLoggedIn(\Silex\Application $app)
  {
    return function() use ($app)
    {
      if (!$app['session']->has('user')) {
        return $app->redirect('/users/login');
      }
    };
  }

  private function checkNotLoggedIn(\Silex\Application $app)
  {
    return function() use ($app)
    {
      if ($app['session']->has('user')) {
        return $app->redirect('/users/users');
      }
    };
  }
}
