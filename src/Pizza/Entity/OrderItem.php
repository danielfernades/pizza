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
     * @var User
     * @ORM\ManyToOne(targetEntity="User", inversedBy="orderitems")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

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
     * @param User $user
     * @return OrderItem
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getEat() . ' || ' . $this->getDrink() . ' || ' . $this->getUser()->getUsername();
    }
}