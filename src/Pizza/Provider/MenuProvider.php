<?php

namespace Pizza\Provider;

use Knp\Menu\MenuFactory;
use Pizza\Entity\User;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Translation\Translator;

class MenuProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['main_menu'] = function($app) {
            $menu = $this->getMenuFactory($app)->createItem('root');
            $menu->addChild($this->getTranslator($app)->trans('nav.order'), array('route' => 'order_list'));
            $menu->addChild($this->getTranslator($app)->trans('nav.user'), array('route' => 'user_list'));

            if($this->getUser($app)) {
                $menu->addChild($this->getTranslator($app)->trans('nav.logout'), array('route' => 'logout'));
            }

            return $menu;
        };

        $app['knp_menu.menus'] = array('main' => 'main_menu');
    }

    public function boot(Application $app) {}

    /**
     * @param Application $app
     * @return MenuFactory
     */
    protected function getMenuFactory(Application $app)
    {
        return $app['knp_menu.factory'];
    }

    /**
     * @param Application $app
     * @return Translator
     */
    protected function getTranslator(Application $app)
    {
        return $app['translator'];
    }

    /**
     * @param Application $app
     * @return SecurityContext
     */
    protected function getSecurity(Application $app)
    {
        return $app['security'];
    }

    /**
     * @param Application $app
     * @return User|null
     */
    protected function getUser(Application $app)
    {
        $token = $this->getSecurity($app)->getToken();
        if(is_null($token)) {
            return null;
        }
        return $token->getUser();
    }
}