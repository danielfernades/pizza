<?php

namespace Pizza\Controller;

use Pizza\Entity\Order;
use Pizza\Entity\OrderItem;
use Silex\Application;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Pizza\Form\OrderItemType;
use Saxulum\RouteController\Annotation\DI;
use Saxulum\RouteController\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/{_locale}/order")
 * @DI(injectContainer=true)
 */
class OrderController extends AbstractController
{
    /**
     * @Route("/", bind="order_list", method="GET")
     * @return string
     */
    public function listAction()
    {
        // get orders
        $orders = $this->getDoctrine()->getManager()->getRepository(get_class(new Order()))->findAll();

        // return the rendered template
        return $this->renderView('@Pizza/Order/list.html.twig', array('orders' => $orders));
    }

    /**
     * @Route("/create", bind="order_create", method="GET")
     * @return RedirectResponse
     * @throws AccessDeniedException
     */
    public function createAction()
    {
        // check permission
        if (!$this->getSecurity()->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException("You have not the permission to create a order!");
        }

        // create a new order
        $order = new Order();
        $order->setOrderDatetime(new \DateTime());

        // persists the order
        $this->getDoctrine()->getManager()->persist($order);
        $this->getDoctrine()->getManager()->flush();

        // redirect to the edit mask
        return new RedirectResponse($this->getUrlGenerator()->generate('order_item_list', array('id' => $order->getId())), 302);
    }

    /**
     * @Route("/show/{id}", bind="order_show", asserts={"id"="\d+"}, method="GET")
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function showAction($id)
    {
        // get the order
        $order = $this->getDoctrine()->getManager()->getRepository(get_class(new Order()))->find($id);

        // check if order exists
        if (is_null($order)) {
            throw new NotFoundHttpException("Order with id {$id} not found!");
        }

        // return the rendered template
        return $this->renderView('@Pizza/Order/show.html.twig', array('order' => $order));
    }

    /**
     * @Route("/delete/{id}", bind="order_delete", asserts={"id"="\d+"}, method="GET")
     * @param $id
     * @return RedirectResponse
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     */
    public function deleteAction($id)
    {
        // check permission
        if (!$this->getSecurity()->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException("You have not the permission to delete a order!");
        }

        // get the order
        $order = $this->getDoctrine()->getManager()->getRepository(get_class(new Order()))->find($id);

        // check if order exists
        if (is_null($order)) {
            throw new NotFoundHttpException("Order with id {$id} not found!");
        }

        // remove the order
        $this->getDoctrine()->getManager()->remove($order);
        $this->getDoctrine()->getManager()->flush();

        // redirect to the list
        return new RedirectResponse($this->getUrlGenerator()->generate('order_list'), 302);
    }

    /**
     * @Route("/items/{id}", bind="order_item_list", asserts={"id"="\d+"}, method="GET")
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function listitemAction($id)
    {
        // get the order
        $order = $this->getDoctrine()->getManager()->getRepository(get_class(new Order()))->find($id);

        // check if order exists
        if (is_null($order)) {
            throw new NotFoundHttpException("Order with id {$id} not found!");
        }

        // return the rendered template
        return $this->renderView('@Pizza/Order/listitem.html.twig', array('order' => $order));
    }

    /**
     * @Route("/items/{id}/edit/{itemid}", bind="order_item_edit", asserts={"id"="\d+", "itemid"="\d+"}, values={"itemid"=null})
     * @param Request $request
     * @param $id
     * @param  null                    $itemid
     * @return string|RedirectResponse
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     */
    public function edititemAction(Request $request, $id, $itemid = null)
    {
        if (!is_null($itemid)) {
            // get the orderitem
            $orderItem = $this->getDoctrine()->getManager()->getRepository(get_class(new OrderItem()))->find($itemid);
            /** @var OrderItem $orderItem */

            // check if order exists
            if (is_null($orderItem)) {
                throw new NotFoundHttpException("Orderitem with id {$itemid} of order {$id} not found!");
            }

            if(!$this->getSecurity()->isGranted('ROLE_ADMIN') &&
                $orderItem->getUser()->getId() != $this->getUser()->getId()) {
                throw new AccessDeniedException("You have not the permission to edit a orderitem of another user!");
            }
        } else {
            // create a new order
            $orderItem = new OrderItem();

            // get the order
            $order = $this->getDoctrine()->getManager()->getRepository(get_class(new Order()))->find($id);
            /** @var Order $order */

            // set order
            $orderItem->setOrder($order);
        }

        // create user form
        $orderItemForm = $this->createForm(new OrderItemType(
            $this->getUser(),
            $this->getSecurity()->isGranted('ROLE_ADMIN')
        ), $orderItem);

        if ('POST' == $request->getMethod()) {
            // bind request
            $orderItemForm->bind($request);

            // check if the input is valid
            if ($orderItemForm->isValid()) {
                // persists the order
                $this->getDoctrine()->getManager()->persist($orderItem);
                $this->getDoctrine()->getManager()->flush();

                // redirect to the edit mask
                return new RedirectResponse($this->getUrlGenerator()->generate('order_item_list', array('id' => $orderItem->getOrder()->getId())), 302);
            }
        }

        // return the rendered template
        return $this->renderView('@Pizza/Order/edititem.html.twig', array
        (
            'orderitem' => $orderItem,
            'orderitemform' => $orderItemForm->createView(),
        ));
    }

    /**
     * @Route("/items/{id}/edit/{itemid}", bind="order_item_delete", asserts={"id"="\d+", "itemid"="\d+"}, method="GET")
     * @param $id
     * @param $itemid
     * @return RedirectResponse
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     */
    public function deleteitemAction($id, $itemid)
    {
        // get the orderitem
        $orderItem = $this->getDoctrine()->getManager()->getRepository(get_class(new OrderItem()))->find($itemid);
        /** @var OrderItem $orderItem */

        // check if order exists
        if (is_null($orderItem)) {
            throw new NotFoundHttpException("Orderitem with id {$itemid} of order {$id} not found!");
        }

        if(!$this->getSecurity()->isGranted('ROLE_ADMIN') &&
           $orderItem->getUser()->getId() != $this->getUser()->getId()) {
            throw new AccessDeniedException("You have not the permission to delete a orderitem of another user!");
        }

        // remove the orderitem
        $this->getDoctrine()->getManager()->remove($orderItem);
        $this->getDoctrine()->getManager()->flush();

        // redirect to the list
        return new RedirectResponse($this->getUrlGenerator()->generate('order_item_list', array('id' => $id)), 302);
    }
}
