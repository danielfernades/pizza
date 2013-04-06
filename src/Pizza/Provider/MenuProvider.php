<?php

namespace Pizza\Provider;

use Knp\Menu\MenuFactory;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\Translation\Translator;

class MenuProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['main_menu'] = function($app) {
            $menu = $this->getMenuFactory($app)->createItem('root');
            $menu->addChild($this->getTranslator($app)->trans('orders', array(), 'Frontend'), array('route' => 'order_list'));
            $menu->addChild($this->getTranslator($app)->trans('users', array(), 'Frontend'), array('route' => 'user_list'));
            $menu->addChild($this->getTranslator($app)->trans('logout', array(), 'Frontend'), array('route' => 'logout'));
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
}