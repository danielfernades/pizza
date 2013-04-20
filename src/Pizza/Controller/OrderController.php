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
        $orders = $this->getEntityManager()->getRepository(get_class(new Order()))->findAll();

        // return the rendered template
        return $this->renderView('Order/list.html.twig', array('orders' => $orders));
    }

    /**
     * @return string
     */
    public function createAction()
    {
        // check permission
        if(!$this->getSecurity()->isGranted('ROLE_ADMIN')) {
            $this->app->abort(403, "You have not the permission to create a order!");
        }

        // create a new order
        $order = new Order();
        $order->setOrderDatetime(new \DateTime());

        // persists the order
        $this->getEntityManager()->persist($order);
        $this->getEntityManager()->flush();

        // redirect to the edit mask
        return $this->app->redirect($this->getUrlGenerator()->generate('order_item_list', array('id' => $order->getId())), 302);
    }

    /**
     * @param $id
     * @return RedirectResponse
     */
    public function showAction($id)
    {
        // get the order
        $order = $this->getEntityManager()->getRepository(get_class(new Order()))->find($id);

        // check if order exists
        if(is_null($order))
        {
            $this->app->abort(404, "Order with id {$id} not found!");
        }

        // return the rendered template
        return $this->renderView('Order/show.html.twig', array('order' => $order));
    }

    /**
     * @param $id
     * @return RedirectResponse
     */
    public function deleteAction($id)
    {
        // check permission
        if(!$this->getSecurity()->isGranted('ROLE_ADMIN')) {
            $this->app->abort(403, "You have not the permission to delete a order!");
        }

        // get the order
        $order = $this->getEntityManager()->getRepository(get_class(new Order()))->find($id);

        // check if order exists
        if(is_null($order))
        {
            $this->app->abort(404, "Order with id {$id} not found!");
        }

        // remove the order
        $this->getEntityManager()->remove($order);
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
        $order = $this->getEntityManager()->getRepository(get_class(new Order()))->find($id);

        // check if order exists
        if(is_null($order))
        {
            $this->app->abort(404, "Order with id {$id} not found!");
        }

        // return the rendered template
        return $this->renderView('Order/listitem.html.twig', array('order' => $order));
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
            $orderItem = $this->getEntityManager()->getRepository(get_class(new OrderItem()))->find($itemid);
            /** @var OrderItem $orderItem */

            // check if order exists
            if(is_null($orderItem))
            {
                $this->app->abort(404, "Orderitem with id {$itemid} of order {$id} not found!");
            }

            if(!$this->getSecurity()->isGranted('ROLE_ADMIN') &&
                $orderItem->getUser()->getId() != $this->getUser()->getId()) {
                $this->app->abort(403, "You have not the permission to edit a orderitem of another user!");
            }
        }
        else
        {
            // create a new order
            $orderItem = new OrderItem();

            // get the order
            $order = $this->getEntityManager()->getRepository(get_class(new Order()))->find($id);
            /** @var Order $order */

            // set order
            $orderItem->setOrder($order);
        }

        // create user form
        $orderItemForm = $this->getFormFactory()->create(new OrderItemType(
            $this->getUser(),
            $this->getSecurity()->isGranted('ROLE_ADMIN')
        ), $orderItem);

        if('POST' == $this->getRequest()->getMethod())
        {
            // bind request
            $orderItemForm->bind($this->getRequest());

            // check if the input is valid
            if($orderItemForm->isValid())
            {
                // persists the order
                $this->getEntityManager()->persist($orderItem);
                $this->getEntityManager()->flush();

                // redirect to the edit mask
                return $this->app->redirect($this->getUrlGenerator()->generate('order_item_list', array('id' => $orderItem->getOrder()->getId())), 302);
            }
        }

        // return the rendered template
        return $this->renderView('Order/edititem.html.twig', array
        (
            'orderitem' => $orderItem,
            'orderitemform' => $orderItemForm->createView(),
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
        $orderItem = $this->getEntityManager()->getRepository(get_class(new OrderItem()))->find($itemid);
        /** @var OrderItem $orderItem */

        // check if order exists
        if(is_null($orderItem))
        {
            $this->app->abort(404, "Orderitem with id {$itemid} of order {$id} not found!");
        }

        if(!$this->getSecurity()->isGranted('ROLE_ADMIN') &&
           $orderItem->getUser()->getId() != $this->getUser()->getId()) {
            $this->app->abort(403, "You have not the permission to delete a orderitem of another user!");
        }

        // remove the orderitem
        $this->getEntityManager()->remove($orderItem);
        $this->getEntityManager()->flush();

        // redirect to the list
        return $this->app->redirect($this->getUrlGenerator()->generate('order_item_list', array('id' => $id)), 302);
    }
}