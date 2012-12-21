<?php

namespace Pizza\Controller;

use Silex\ControllerCollection;

class LoginController extends AbstractController
{
    /**
     * @param ControllerCollection $controllers
     * @return ControllerCollection
     */
    protected function getRoutes(ControllerCollection $controllers)
    {
        $controllers->get('/login', array($this, 'loginAction'))->bind('login');
        return $controllers;
    }

    public function loginAction()
    {
        // return the rendered template
        return $this->renderView('Login/login.html.twig', array(
            'error'         => $this->app['security.last_error']($this->getRequest()),
            'last_username' => $this->app['session']->get('_security.last_username'),
        ));
    }
}