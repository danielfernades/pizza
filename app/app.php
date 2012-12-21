<?php

use Dflydev\Silex\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Igorw\Silex\ConfigServiceProvider;
use Pizza\Controller\LoginController;
use Pizza\Controller\OrderController;
use Pizza\Controller\UserController;
use Pizza\Providers\UserProvider;
use Silex\Application;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ValidatorServiceProvider;

// load composer autoload
if (!$loader = @include dirname(__DIR__) . '/vendor/autoload.php')
{
    die("curl -s http://getcomposer.org/installer | php; php composer.phar install");
}

// annotation registry
AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

// intl
if (!function_exists('intl_get_error_code')) {
    require_once $strRootDir . '/vendor/symfony/locale/Symfony/Component/Locale/Resources/stubs/functions.php';
    $loader->add('', $strRootDir . '/vendor/symfony/locale/Symfony/Component/Locale/Resources/stubs');
}

// new application
$app = new Application();
$app['root_dir'] = dirname(__DIR__);
$app['app_dir'] = $app['root_dir'] . '/app';
$app['cache_dir'] = $app['app_dir'] . '/cache';
$app['src_dir'] = $app['root_dir'] . '/src';

// load config
$app->register(new ConfigServiceProvider($app['app_dir'] . '/config.yml'));

// set error reporting
error_reporting(!$app['debug'] ? E_ALL ^ E_NOTICE : E_ALL);

// register doctrine dbal
$app->register(new DoctrineServiceProvider, array(
    "db.options" => $app['doctrine']['dbal']
));

// register doctrine orm
$app->register(new DoctrineOrmServiceProvider, array(
    "orm.proxies_dir" => $app['cache_dir'] . '/doctrine/proxies',
    "orm.em.options" => array(
        "mappings" => array(
            array(
                "type" => "annotation",
                "namespace" => "Pizza\\Entity",
                "path" => $app['src_dir']."/Pizza/Entity",
                "use_simple_annotation_reader" => false,
            ),
        ),
    ),
));

// register form factory
$app->register(new FormServiceProvider(), array(
    'form.secret' => $app['secret']
));

// register validator
$app->register(new ValidatorServiceProvider());

// register url generator
$app->register(new UrlGeneratorServiceProvider());

// register translation
$app->register(new TranslationServiceProvider());

// register session
$app->register(new SessionServiceProvider());

// register twig
$app->register(new TwigServiceProvider(), array(
    'twig.path' => $app['src_dir'] . '/Pizza/View',
    'twig.options' => array('cache' => $app['cache_dir'] . '/twig')
));

// define firewalls
$app['security.firewalls'] = array(
    'login' => array(
        'pattern' => '^/login$',
    ),
    'secured' => array(
        'pattern' => '^.*$',
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

// register security
$app->register(new SecurityServiceProvider());

// redirect to ordercontroller
$app->get('/', function() use($app) {
    return $app->redirect($app['url_generator']->generate('order_list'), 301);
});

// add routes
$app->mount('/', new LoginController());
$app->mount('{_locale}/', new OrderController());
$app->mount('{_locale}/user', new UserController());

// boot the application
$app->boot();

// return the app
return $app;