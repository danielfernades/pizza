<?php

namespace Pizza\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class LoginController extends AbstractController
{
    public static function addRoutes(Application $app, $serviceId)
    {
        $app->get('/login', $serviceId . ':loginAction')->bind('login');
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
