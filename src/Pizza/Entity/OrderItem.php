<?php

namespace Pizza\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="orderitem")
 */
class OrderItem
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Order
     * @ORM\ManyToOne(targetEntity="Order", inversedBy="orderitems")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     */
    protected $order;

    /**
     * @var string $eat
     * @ORM\Column(name="eat", type="string", nullable=true)
     */
    protected $eat;

    /**
     * @var string $drink
     * @ORM\Column(name="drink", type="string", nullable=true)
     */
    protected $drink;

    /**
     * @var string $employee
     * @ORM\Column(name="employee", type="string", nullable=true)
     */
    protected $employee;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $order
     * @return OrderItem
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param $eat
     * @return OrderItem
     */
    public function setEat($eat)
    {
        $this->eat = $eat;
        return $this;
    }

    /**
     * @return string
     */
    public function getEat()
    {
        return $this->eat;
    }

    /**
     * @param $drink
     * @return OrderItem
     */
    public function setDrink($drink)
    {
        $this->drink = $drink;
        return $this;
    }

    /**
     * @return string
     */
    public function getDrink()
    {
        return $this->drink;
    }

    /**
     * @param $employee
     * @return OrderItem
     */
    public function setEmployee($employee)
    {
        $this->employee = $employee;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmployee()
    {
        return $this->employee;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getEat() . ' || ' . $this->getDrink() . ' || ' . $this->getEmployee();
    }
}