<?php

namespace Pizza\Controller;

use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\RedirectResponse;

class IndexController extends AbstractController
{
    /**
     * @return string
     */
    public function getMount()
    {
        return '/';
    }

    /**
     * @param  ControllerCollection $controllers
     * @return ControllerCollection
     */
    protected function addRoutes(ControllerCollection $controllers)
    {
        $controllers->get('/', array($this, 'indexAction'));

        return $controllers;
    }

    /**
     * @return RedirectResponse
     */
    public function indexAction()
    {
        return new RedirectResponse($this->getUrlGenerator()->generate('order_list'), 301);
    }
}
