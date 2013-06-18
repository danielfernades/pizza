<?php

namespace Pizza\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\RedirectResponse;

class IndexController extends AbstractController
{
    public static function addRoutes(Application $app, $serviceId)
    {
        $app->get('/', $serviceId . ':indexAction');
    }

    /**
     * @return RedirectResponse
     */
    public function indexAction()
    {
        return new RedirectResponse($this->getUrlGenerator()->generate('order_list'), 301);
    }
}
