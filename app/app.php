<?php

use Dflydev\Silex\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Igorw\Silex\ConfigServiceProvider;
use Knp\Menu\Silex\KnpMenuServiceProvider;
use Knp\Menu\Twig\Helper;
use Knp\Menu\Twig\MenuExtension;
use Pizza\Controller\LoginController;
use Pizza\Controller\OrderController;
use Pizza\Controller\UserController;
use Pizza\Provider\MenuProvider;
use Pizza\Provider\UserProvider;
use Pizza\Registry\ManagerRegistry;
use Silex\Application;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\WebProfilerServiceProvider;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;

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

$app['form.extensions'] = $app->share($app->extend('form.extensions', function ($extensions, $app) {
    $managerRegistry = new ManagerRegistry(
        null,
        array('db'),
        array('orm.em'),
        null,
        null,
        'Doctrine\ORM\Proxy\Proxy'
    );
    $managerRegistry->setContainer($app);
    $extensions[] = new DoctrineOrmExtension($managerRegistry);
    return $extensions;
}));


// register service controller
$app->register(new ServiceControllerServiceProvider());

// register validator
$app->register(new ValidatorServiceProvider());

// register url generator
$app->register(new UrlGeneratorServiceProvider());

// register translation
$app->register(new TranslationServiceProvider());

// add translation files
$app['translator'] = $app->share($app->extend('translator', function(Translator $translator, $app) {
    $translator->addLoader('yaml', new YamlFileLoader());
    $translator->addResource('yaml', $app['src_dir'] . '/Pizza/Translation/de.yml', 'de');
    $translator->addResource('yaml', $app['src_dir'] . '/Pizza/Translation/en.yml', 'en');
    return $translator;
}));

// register session
$app->register(new SessionServiceProvider());

// register menu knpmenu
$app->register(new KnpMenuServiceProvider());
$app->register(new MenuProvider());

// register twig
$app->register(new TwigServiceProvider(), array(
    'twig.path' => $app['src_dir'] . '/Pizza/View',
    'twig.options' => array('cache' => $app['cache_dir'] . '/twig')
));

// add twig extension
$app['twig'] = $app->share($app->extend('twig', function($twig) use($app) {
    $twig->addExtension(new MenuExtension(new Helper(
        $app['knp_menu.renderer_provider'],
        $app['knp_menu.menu_provider']))
    );
    return $twig;
}));

// register security
$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'login' => array(
            'pattern' => '^/login$',
        ),
        '_profiler' => array(
            'pattern' => '^/_profiler',
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
    ),
    'security.access_rules' => array(
        array('^/[^/]*/user', 'ROLE_ADMIN'),
    ),
    'security.role_hierarchy' => array(
        'ROLE_ADMIN' => array('ROLE_USER'),
    ),
));


// register webprofiler
$app->register($profiler = new WebProfilerServiceProvider(), array(
    'profiler.cache_dir' => __DIR__.'/cache/profiler',
));

// add routes
$app->mount('{_locale}/order', new OrderController());
$app->mount('{_locale}/user', new UserController());
$app->mount('/_profiler', $profiler);
$app->mount('/', new LoginController());

// redirect to ordercontroller
$app->get('/', function() use($app) {
    return $app->redirect($app['url_generator']->generate('order_list'), 301);
});

// boot the application
$app->boot();

// return the app
return $app;