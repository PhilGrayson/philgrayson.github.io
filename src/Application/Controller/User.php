<?php
namespace Application\Controller;

use Symfony\Component\Validator\Constraints as Assert;

class User implements \Silex\ControllerProviderInterface
{
  public function connect(\Silex\Application $app)
  {
    $user = $app['controllers_factory'];

    $app['twig_user_vars'] = array(
      'title' => 'Phil Grayson | Users',
    );

    $user->get('/', $this->redirectIndexAction($app));
    $user->get('/users', $this->indexAction($app));
    $user->post('/users', $this->createPostAction($app));
    $user->get('/users/{id}', $this->showAction($app))->assert('id', '^\d+$');
    $user->get('/users/new', $this->createGetAction($app))->before($this->checkLoggedIn($app));
    $user->get('/login', $this->loginGetAction($app));
    $user->post('/login', $this->loginPostAction($app));
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
    };
  }

  private function showAction(\Silex\Application $app)
  {
    return function($id) use($app)
    {
      $user = $app['db.orm.em']['User']->find('Application\Model\User\User', $id);

      if (!$user instanceOf \Application\Model\User\User) {
        return $app['twig']->render(
          'User/show_404.twig'
        );
      }

      return $app['twig']->render(
        'User/show.twig',
        array_merge($app['twig_user_vars'], array(
          'user' => $user
        ))
      );
    };
  }

  private function createGetAction(\Silex\Application $app)
  {
    return function() use($app)
    {
      if (null !== $errors = $app['session']->get('user.create')) {
        $app['session']->remove('user.create');
      } else  {
        $errors = array();
      }

      return $app['twig']->render(
        'User/create.twig',
        array_merge($app['twig_user_vars'], $errors)
      );
    };
  }

  private function createPostAction(\Silex\Application $app)
  {
    return function() use($app)
    {
      $requestParams = $app['request']->request;
      $vars = array(
        'name'     => $requestParams->get('_name'),
        'username' => $requestParams->get('_username'),
        'pass'     => $requestParams->get('_password1'),
        'pass2'    => $requestParams->get('_password2')
      );

      $validations = array();

      $validations['name']     = $app['validator']->validateValue($vars['name'], new Assert\NotBlank);
      $validations['username'] = $app['validator']->validateValue($vars['username'], new Assert\Email);
      $validations['password'] = $app['validator']->validateValue($vars['pass'], new Assert\NotBlank);

      if (count($validations['password']) == 0) {
        $validations['password'] = $vars['pass'] !== $vars['pass2'] ? 'The two passwords do not match' : null;
      }

      foreach ($validations as $key => $validation) {
        if (count($validation) == 0) {
          unset($validations[$key]);
        }
      }

      if (count($validations) > 0) {
        $app['session']->set('user.create', array('errors' => $validations, 'vars' => $vars));
        return $app->redirect('/users/users/new');
      }

      $user = new \Application\Model\User\User;
      $user->setName($vars['name']);
      $user->setEmail($vars['username']);
      $user->setPassword($app['security']::generateHash($vars['pass']));

      $app['db.orm.em']['User']->persist($user);
      $app['db.orm.em']['User']->flush();

      $app['security']->login($vars['username'], $vars['pass']);
      return $app->redirect('/users/users/' . $user->getId());
    };
  }

  private function loginGetAction(\Silex\Application $app)
  {
    return function() use($app)
    {
      $vars = array();
      if ($vars['login'] = $app['session']->get('user.login')) {
        // Flash Message
        $app['session']->remove('user.login');
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
      $email = $app['request']->request->get('_username');
      $pass  = $app['request']->request->get('_password');

      if ($user = $app['security']->login($email, $pass)) {
        return $app->redirect('/users/users/' . $user->getId());
      }

      $app['session']->set('user.login', array(
        'last_email' => $email,
        'error'      => 'Username and/or Password was not recognised'
      ));
      return $app->redirect('/users/login');
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
      if(!$app['security']->isLoggedIn()) {
        return $app->redirect('/users/login');
      }
    };
  }
}
