<?php

namespace Pizza\Helper;

use Silex\Application;
use Symfony\Component\Console\Helper\Helper;

class SilexHelper extends Helper
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function get()
    {
        return $this->app;
    }

    public function getName()
    {
        return 'silex';
    }
}