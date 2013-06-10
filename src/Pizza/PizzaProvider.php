<?php

namespace Pizza;

use Application\Provider\AbstractSilexBundleProvider;
use Knp\Menu\Silex\KnpMenuServiceProvider;
use Knp\Menu\Twig\Helper;
use Knp\Menu\Twig\MenuExtension;
use Pizza\Provider\MenuProvider;
use Pizza\Provider\UserProvider;
use Silex\Application;

class PizzaProvider extends AbstractSilexBundleProvider
{
    public function register(Application $app)
    {
        parent::register($app);

        $app->register(new KnpMenuServiceProvider());
        $app->register(new MenuProvider());

        $app['twig'] = $app->share($app->extend('twig', function($twig) use ($app) {
            $twig->addExtension(new MenuExtension(new Helper(
                    $app['knp_menu.renderer_provider'],
                    $app['knp_menu.menu_provider']))
            );

            return $twig;
        }));

        $app['security.firewalls'] = array(
            'login' => array(
                'pattern' => '^/login$',
            ),
            'secured' => array(
                'form' => array(
                    'login_path' => '/login',
                    'check_path' => '/login_check'
                ),
                'logout' => array(
                    'logout_path' => '/logout'
                ),
                'users' => $app->share(function () use ($app) {
                    return new UserProvider($app['orm.em']);
                }),
            ),
        );

        $app['security.access_rules'] = array(
            array('^/[^/]*/user', 'ROLE_ADMIN'),
        );

        $app['security.role_hierarchy'] = array(
            'ROLE_ADMIN' => array('ROLE_USER'),
        );
    }
}
