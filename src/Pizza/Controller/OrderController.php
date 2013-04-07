<?php

namespace Pizza\Controller;

use Pizza\Entity\Order;
use Pizza\Entity\OrderItem;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Pizza\Form\OrderItemType;

class OrderController extends AbstractController
{
    /**
     * @param ControllerCollection $controllers
     * @return ControllerCollection
     */
    protected function getRoutes(ControllerCollection $controllers)
    {
        $controllers->get('/', array($this, 'listAction'))->bind('order_list');
        $controllers->get('/create', array($this, 'createAction'))->bind('order_create');
        $controllers->get('/show/{id}', array($this, 'showAction'))->assert('id', '\d+')->bind('order_show');
        $controllers->get('/delete/{id}', array($this, 'deleteAction'))->assert('id', '\d+')->bind('order_delete');
        $controllers->get('/items/{id}', array($this, 'listitemAction'))->assert('id', '\d+')->bind('order_item_list');
        $controllers
            ->match('/items/{id}/edit/{itemid}', array($this, 'edititemAction'))
            ->value('itemid', null)
            ->assert('id', '\d+')
            ->assert('itemid', '\d+')
            ->bind('order_item_edit')
        ;
        $controllers
            ->get('/items/{id}/delete/{itemid}', array($this, 'deleteitemAction'))
            ->assert('id', '\d+')
            ->assert('itemid', '\d+')
            ->bind('order_item_delete')
        ;
        return $controllers;
    }

    /**
     * @return string
     */
    public function listAction()
    {
        // get orders
        $arrOrders = $this->getEntityManager()->getRepository(get_class(new Order()))->findAll();

        // return the rendered template
        return $this->renderView('Order/list.html.twig', array('orders' => $arrOrders));
    }

    /**
     * @return string
     */
    public function createAction()
    {
        // create a new order
        $objOrder = new Order();
        $objOrder->setOrderDatetime(new \DateTime());

        // persists the order
        $this->getEntityManager()->persist($objOrder);
        $this->getEntityManager()->flush();

        // redirect to the edit mask
        return $this->app->redirect($this->getUrlGenerator()->generate('order_item_list', array('id' => $objOrder->getId())), 302);
    }

    /**
     * @param $id
     * @return RedirectResponse
     */
    public function showAction($id)
    {
        // get the order
        $objOrder = $this->getEntityManager()->getRepository(get_class(new Order()))->find($id);

        // check if order exists
        if(is_null($objOrder))
        {
            $this->app->abort(404, "Order with id {$id} not found!");
        }

        // return the rendered template
        return $this->renderView('Order/show.html.twig', array('order' => $objOrder));
    }

    /**
     * @param $id
     * @return RedirectResponse
     */
    public function deleteAction($id)
    {
        // get the order
        $objOrder = $this->getEntityManager()->getRepository(get_class(new Order()))->find($id);

        // check if order exists
        if(is_null($objOrder))
        {
            $this->app->abort(404, "Order with id {$id} not found!");
        }

        // remove the order
        $this->getEntityManager()->remove($objOrder);
        $this->getEntityManager()->flush();

        // redirect to the list
        return $this->app->redirect($this->getUrlGenerator()->generate('order_list'), 302);
    }

    /**
     * @param $id
     * @return string
     */
    public function listitemAction($id)
    {
        // get the order
        $objOrder = $this->getEntityManager()->getRepository(get_class(new Order()))->find($id);

        // check if order exists
        if(is_null($objOrder))
        {
            $this->app->abort(404, "Order with id {$id} not found!");
        }

        // return the rendered template
        return $this->renderView('Order/listitem.html.twig', array('order' => $objOrder));
    }

    /**
     * @param $id
     * @param null $itemid
     * @return string|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function edititemAction($id, $itemid = null)
    {
        if(!is_null($itemid))
        {
            // get the orderitem
            $objOrderItem = $this->getEntityManager()->getRepository(get_class(new OrderItem()))->find($itemid);
            /** @var OrderItem $objOrderItem */

            // check if order exists
            if(is_null($objOrderItem))
            {
                $this->app->abort(404, "Orderitem with id {$itemid} of order {$id} not found!");
            }
        }
        else
        {
            // create a new order
            $objOrderItem = new OrderItem();

            // get the order
            $objOrder = $this->getEntityManager()->getRepository(get_class(new Order()))->find($id);
            /** @var Order $objOrder */

            // set order
            $objOrderItem->setOrder($objOrder);
        }

        // create user form
        $objOrderItemForm = $this->getFormFactory()->create(new OrderItemType($this->getEntityManager()), $objOrderItem);

        if('POST' == $this->getRequest()->getMethod())
        {
            // bind request
            $objOrderItemForm->bind($this->getRequest());

            // check if the input is valid
            if($objOrderItemForm->isValid())
            {
                // persists the order
                $this->getEntityManager()->persist($objOrderItem);
                $this->getEntityManager()->flush();

                // redirect to the edit mask
                return $this->app->redirect($this->getUrlGenerator()->generate('order_item_list', array('id' => $objOrderItem->getOrder()->getId())), 302);
            }
        }

        // return the rendered template
        return $this->renderView('Order/edititem.html.twig', array
        (
            'orderitem' => $objOrderItem,
            'orderitemform' => $objOrderItemForm->createView(),
        ));
    }

    /**
     * @param $id
     * @param $itemid
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteitemAction($id, $itemid)
    {
        // get the orderitem
        $objOrderItem = $this->getEntityManager()->getRepository(get_class(new OrderItem()))->find($itemid);

        // check if order exists
        if(is_null($objOrderItem))
        {
            $this->app->abort(404, "Orderitem with id {$itemid} of order {$id} not found!");
        }

        // remove the orderitem
        $this->getEntityManager()->remove($objOrderItem);
        $this->getEntityManager()->flush();

        // redirect to the list
        return $this->app->redirect($this->getUrlGenerator()->generate('order_item_list', array('id' => $id)), 302);
    }
}