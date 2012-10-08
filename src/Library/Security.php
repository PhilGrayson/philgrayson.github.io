<?php
namespace Library;

use \Symfony\Component\HttpFoundation\Session;

class Security
{
  private $app;
  private $session;

  public function __construct(\Silex\Application $app)
  {
    $this->app = $app;
    $this->setSession($app['session']);
  }

  public function getSession()
  {
    return $this->session;
  }

  public function setSession(Session\Session $session)
  {
    $this->session = $session;
  }

  public static function generateHash($password)
  {
    $lib = new \PasswordLib\PasswordLib;

    return $lib->createPasswordHash($password, \PasswordLib\Password\Implementation\Blowfish::getPrefix());
  }

  public function getUser($email)
  {
    $repo = $this->app['db.orm.em']['User']->getRepository(
      'Application\Model\User\User'
    );

    $user = $repo->findOneBy(array('email' => $email));
    if (!$user instanceOf \Application\Model\User\User) {
      return false;
    }

    return $user;
  }

  public function isLoggedIn()
  {
    if ($this->getSession()->has('user')) {
      return true;
    }

    return false;
  }

  public function validate($email, $password)
  {
    if (!$user = $this->getUser($email)) {
      return false;
    }

    $lib = new \PasswordLib\PasswordLib;
    return $lib->verifyPasswordHash($password, $user->getPassword());
  }

  public function login($email, $password)
  {
    $user = $this->getUser($email);
    if (!$user) {
      return false;
    }

    if (!$this->validate($user->getEmail(), $password)) {
      return false;
    }

    $this->session->set('user', $user);
    return $user;
  }

  public function isAuthorized($role)
  {
    if (!$this->isLoggedIn()) {
      return false;
    }

    $user = $this->getSession()->get('user');
    if (in_array($role, $user['roles'])) {
      return true;
    }

    return false;
  }
}
