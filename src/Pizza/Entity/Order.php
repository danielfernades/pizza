<?php

namespace Pizza\Entity;

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
     * @var OrderItem[]
     * @ORM\OneToMany(targetEntity="OrderItem", mappedBy="order", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"eat" = "ASC", "drink" = "ASC"})
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
     * @param OrderItem $orderitem
     * @param bool $stopPropagation
     * @return $this
     */
    public function addOrderItem(OrderItem $orderitem, $stopPropagation = false)
    {
        $this->orderitems->add($orderitem);
        if(!$stopPropagation) {
            $orderitem->setOrder($this, true);
        }
        return $this;
    }

    /**
     * @param OrderItem $orderitem
     * @param bool $stopPropagation
     * @return $this
     */
    public function removeOrderItem(OrderItem $orderitem, $stopPropagation = false)
    {
        $this->orderitems->removeElement($orderitem);
        if(!$stopPropagation) {
            $orderitem->setOrder(null, true);
        }
        return $this;
    }

    /**
     * @return OrderItem[]
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