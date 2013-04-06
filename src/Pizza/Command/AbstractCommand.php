<?php

namespace Pizza\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Silex\Application;

class AbstractCommand extends Command
{
    /**
     * @var Application
     */
    protected $silex;

    public function setSilex(Application $silex)
    {
        $this->silex = $silex;
    }

    /**
     * @return Application
     */
    public function getSilex()
    {
        if(is_null($this->silex))
        {
            $this->setSilex($this->getHelper('silex')->get());
        }
        return $this->silex;
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getSilex()->offsetGet('orm.em');
    }
}