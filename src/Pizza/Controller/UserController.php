<?php

namespace Pizza\Controller;

use Silex\Application;
use Pizza\Entity\User;
use Pizza\Form\UserType;
use Saxulum\RouteController\Annotation\DI;
use Saxulum\RouteController\Annotation\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/{_locale}/user")
 * @DI(injectContainer=true)
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", bind="user_list", method="GET")
     * @return string
     */
    public function listAction()
    {
        // get orders
        $users = $this->getDoctrine()->getManager()->getRepository(get_class(new User()))->findAll();

        // return the rendered template
        return $this->renderView('@Pizza/User/list.html.twig', array('users' => $users));
    }

    /**
     * @Route("/edit/{id}", bind="user_edit", asserts={"id"="\d+"}, values={"id"=null})
     * @param Request $request
     * @param $id
     * @return string|RedirectResponse
     * @throws NotFoundHttpException
     */
    public function editAction(Request $request, $id)
    {
        if (!is_null($id)) {
            // get user
            $user = $this->getDoctrine()->getManager()->getRepository(get_class(new User()))->find($id);
            /** @var User $user */

            if (is_null($user)) {
                throw new NotFoundHttpException("user with id {$id} not found!");
            }
        } else {
            $user = new User();
            $user->setSalt(uniqid(mt_rand()));
        }

        // create user form
        $userForm = $this->createForm(new UserType(), $user);

        if ('POST' == $request->getMethod()) {
            // bind request
            $userForm->bind($request);

            // check if the input is valid
            if ($userForm->isValid()) {
                // update password
                if ($user->updatePassword($this->container['security.encoder.digest'])) {
                    // you can't remove admin role on yourself
                    if ($user->getId() == $this->getUser()->getId()) {
                        $user->addRole(User::ROLE_ADMIN);
                    }

                    // persist the user
                    $this->getDoctrine()->getManager()->persist($user);
                    $this->getDoctrine()->getManager()->flush();

                    // redirect to the edit mask
                    return new RedirectResponse($this->getUrlGenerator()->generate('user_edit', array('id' => $user->getId())), 302);
                } else {
                    $userForm->addError(new FormError($this->getTranslator()->trans("No password set", array(), "frontend")));
                }
            }
        }

        // return the rendered template
        return $this->renderView('@Pizza/User/edit.html.twig', array(
            'user' => $user,
            'userform' => $userForm->createView(),
        ));
    }

    /**
     * @Route("/delete/{id}", bind="user_delete", asserts={"id"="\d+"}, method="GET")
     * @param $id
     * @return RedirectResponse
     * @throws \ErrorException
     * @throws NotFoundHttpException
     */
    public function deleteAction($id)
    {
        // get the user
        $user = $this->getDoctrine()->getManager()->getRepository(get_class(new User()))->find($id);
        /** @var User $user */

        // check if user exists
        if (is_null($user)) {
            throw new NotFoundHttpException("User with id {$id} not found!");
        }

        // check the user doesn't delete himself
        if ($user->getId() == $this->getUser()->getId()) {
            throw new \ErrorException("You can't delete yourself!");
        }

        // remove the user
        $this->getDoctrine()->getManager()->remove($user);
        $this->getDoctrine()->getManager()->flush();

        // redirect to the list
        return new RedirectResponse($this->getUrlGenerator()->generate('user_list'), 302);
    }
}
