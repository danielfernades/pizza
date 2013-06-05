<?php

use Pizza\Command\UserCreateCommand;

/** @var Silex\Application $app */
/** @var Symfony\Component\Console\Application $console */

$console->add(new UserCreateCommand(null, $app));