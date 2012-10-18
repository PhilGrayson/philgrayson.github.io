<?php
namespace Application\Command\User;

use Symfony\Component\Console;

class AddAdminAccountCommand extends \Application\Command\Command
{
  protected function configure()
  {
    $this
      ->setName('user:init')
      ->setDescription('Create an admin role and admin account');
  }

  protected function execute(
    Console\Input\InputInterface $input,
    Console\Output\OutputInterface $output)
  {
    $app = $this->getDIC();
    $dialog = $this->getHelperSet()->get('dialog');

    $name  = $dialog->ask($output, 'Admin name : ');
    $email = $dialog->ask($output, 'Admin email : ');
    $pass  = $dialog->ask($output, 'Admin password : ');

    $role = new \Application\Model\User\Role;
    $role->setName('ADMIN');
    $role->setDescription('Super user account');
    $app['db.orm.em']['User']->persist($role);

    $user = new \Application\Model\User\User;
    $user->setName($name);
    $user->setEmail($email);
    $user->setPassword($app['security']::generateHash($pass));
    $user->addRole($role);
    $app['db.orm.em']['User']->persist($user);

    $app['db.orm.em']['User']->flush();
  }
}
