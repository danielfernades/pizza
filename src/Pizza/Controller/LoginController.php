<?php

namespace Pizza\Controller;

use Silex\Application;
use Saxulum\RouteController\Annotation\DI;
use Saxulum\RouteController\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @DI(injectContainer=true)
 */
class LoginController extends AbstractController
{
    /**
     * @Route("/login", bind="login", method="GET")
     * @param Request $request
     * @return string
     */
    public function loginAction(Request $request)
    {
        // return the rendered template
        return $this->renderView('@Pizza/Login/login.html.twig', array(
            'error'         => $this->container['security.last_error']($request),
            'last_username' => $this->getSession()->get('_security.last_username'),
        ));
    }
}
