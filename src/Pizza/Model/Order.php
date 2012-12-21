<?php

namespace Pizza\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="ordertable")
 */
class Order
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime $orderdatetime
     * @ORM\Column(name="orderdatetime", type="datetime")
     */
    protected $orderdatetime;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="Pizza\Model\OrderItem", mappedBy="order", cascade={"remove", "persist"}, orphanRemoval=true)
     */
    protected $orderitems;

    public function __construct()
    {
        $this->orderitems = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set orderdatetime
     * @param \DateTime $orderdatetime
     * @return Order
     */
    public function setOrderDatetime(\DateTime $orderdatetime)
    {
        $this->orderdatetime = $orderdatetime;
        return $this;
    }

    /**
     * Get orderdatetime
     * @return \DateTime
     */
    public function getOrderDatetime()
    {
        return $this->orderdatetime;
    }

    /**
     * @param OrderItem $orderItem
     * @return Order
     */
    public function addOrderItem(OrderItem $orderItem)
    {
        $this->orderitems->add($orderItem);
        $orderItem->setOrder($this);
        return $this;
    }

    /**
     * @param OrderItem $orderItem
     * @return Order
     */
    public function removeOrderItem(OrderItem $orderItem)
    {
        $this->orderitems->removeElement($orderItem);
        $orderItem->setOrder(null);
        return $this;
    }

    /**
     * @return Collection
     */
    public function getOrderItems()
    {
        return $this->orderitems;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getOrderDatetime()->format('d.m.Y H:i');
    }
}