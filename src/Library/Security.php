<?php
namespace Library;

use \Symfony\Component\HttpFoundation\Session;

class Security
{
  private $app;
  private $session;

  public function __construct(\Silex\Application &$app)
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

  public function getUser($id)
  {
    if (is_numeric($id)) {
      $user = $this->app['db.orm.em']['User']->find('Application\Model\User\User', $id);
    } else {
      $repo = $this->app['db.orm.em']['User']->getRepository(
        'Application\Model\User\User'
      );

      $user = $repo->findOneBy(array('email' => $id));
    }

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

    $this->session->set('user', $user->getId());
    return $user;
  }

  public function isAuthorized(\Application\Model\User\Role $role)
  {
    if (!$this->isLoggedIn()) {
      return false;
    }

    $user = $this->getUser($this->getSession()->get('user'));

    if (!$user) {
      return false;
    }

    return $user->getRoles()->contains($role);
  }
}
