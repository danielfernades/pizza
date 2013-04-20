<?php

namespace Pizza\Provider;

use Pizza\Entity\User;
use Silex\Application;
use Silex\ServiceProviderInterface;

class MenuProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['main_menu'] = function($app) {
            $menu = $app['knp_menu.factory']->createItem('root');

            if(is_null($app['security']->getToken())) {
                $user = null;
            } else {
                $user = $app['security']->getToken()->getUser();
            }

            if(!is_null($user)) {
                $menu->addChild($app['translator']->trans('nav.order'), array('route' => 'order_list'));
                if($app['security']->isGranted('ROLE_ADMIN')) {
                    $menu->addChild($app['translator']->trans('nav.user'), array('route' => 'user_list'));
                }
                $menu->addChild($app['translator']->trans('nav.logout'), array('route' => 'logout'));
            }

            return $menu;
        };

        $app['knp_menu.menus'] = array('main' => 'main_menu');
    }

    public function boot(Application $app) {}
}