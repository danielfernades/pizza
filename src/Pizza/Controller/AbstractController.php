<?php

namespace Pizza\Controller;

use Doctrine\ORM\EntityManager;
use Pizza\Entity\User;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Translation\Translator;

abstract class AbstractController implements ControllerProviderInterface
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @param Application $app
     * @return ControllerCollection
     */
    public function connect(Application $app)
    {
        $this->app = $app;
        return $this->getRoutes($this->getControllerFactory());
    }

    /**
     * @return ControllerCollection
     */
    protected function getControllerFactory()
    {
        return $this->app['controllers_factory'];
    }

    /**
     * @return HttpKernel
     */
    protected function getKernel()
    {
        return $this->app['kernel'];
    }

    /**
     * @return Request
     */
    protected function getRequest()
    {
        return $this->app['request'];
    }

    /**
     * @return FormFactory
     */
    protected function getFormFactory()
    {
        return $this->app['form.factory'];
    }

    /**
     * @return UrlGenerator
     */
    protected function getUrlGenerator()
    {
        return $this->app['url_generator'];
    }

    /**
     * @return \Twig_Environment
     */
    protected function getTwig()
    {
        return $this->app['twig'];
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->app['orm.em'];
    }

    /**
     * @return Translator
     */
    protected function getTranslator()
    {
        return $this->app['translator'];
    }

    /**
     * @return SecurityContext
     */
    protected function getSecurity()
    {
        return $this->app['security'];
    }

    /**
     * @return User|Null|string
     */
    protected function getUser()
    {
        if(is_null($this->getSecurity()->getToken())) {
            return null;
        }

        return $this->getSecurity()->getToken()->getUser();
    }

    /**
     * @param $view
     * @param array $parameters
     * @return string
     */
    protected function renderView($view, array $parameters = array())
    {
        return $this->getTwig()->render($view, $parameters);
    }
}