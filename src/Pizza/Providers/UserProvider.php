<?php

namespace Pizza\Providers;

use Doctrine\ORM\EntityManager;
use Pizza\Entity\User;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class UserProvider implements UserProviderInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function loadUserByUsername($username)
    {
        $objUser = $this->em->getRepository("Pizza\\Entity\\User")->findOneBy(array('username' => $username));

        if(is_null($objUser))
        {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }

        return $objUser;
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === "Pizza\\Entity\\User";
    }
}