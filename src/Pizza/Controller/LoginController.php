<?php

namespace Pizza\Controller;

use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;

class LoginController extends AbstractController
{
    /**
     * @return string
     */
    public function getMount()
    {
        return '/';
    }

    /**
     * @param ControllerCollection $controllers
     * @return ControllerCollection
     */
    protected function addRoutes(ControllerCollection $controllers)
    {
        $controllers->get('/login', array($this, 'loginAction'))->bind('login');
        return $controllers;
    }

    public function loginAction(Request $request)
    {
        // return the rendered template
        return $this->renderView('@Pizza/Login/login.html.twig', array(
            'error'         => $this->container['security.last_error']($request),
            'last_username' => $this->getSession()->get('_security.last_username'),
        ));
    }
}