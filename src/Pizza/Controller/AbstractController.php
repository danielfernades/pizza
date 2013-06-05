<?php

namespace Pizza\Controller;

use Application\Controller\AbstractController as BaseController;

use Pizza\Entity\User;

abstract class AbstractController extends BaseController
{
    /**
     * @return User|Null|string
     */
    protected function getUser()
    {
        if(is_null($this->getSecurity()->getToken())) {
            return null;
        }

        $user = $this->getSecurity()->getToken()->getUser();

        if($user instanceof User) {
            $user = $this->getDoctrine()->getManager()->getRepository(get_class($user))->find($user->getId());
        }

        return $user;
    }
}