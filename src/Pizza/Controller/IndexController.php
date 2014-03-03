<?php

namespace Pizza\Controller;

use Silex\Application;
use Saxulum\RouteController\Annotation\DI;
use Saxulum\RouteController\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @DI(injectContainer=true)
 */
class IndexController extends AbstractController
{
    /**
     * @Route("/", bind="index", method="GET")
     * @return RedirectResponse
     */
    public function indexAction()
    {
        return new RedirectResponse($this->getUrlGenerator()->generate('order_list'), 301);
    }
}
