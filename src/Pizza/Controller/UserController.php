<?php

namespace Pizza\Controller;

use Silex\ControllerCollection;
use Pizza\Entity\User;
use Pizza\Form\UserType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;

class UserController extends AbstractController
{
    /**
     * @param ControllerCollection $controllers
     * @return ControllerCollection
     */
    protected function getRoutes(ControllerCollection $controllers)
    {
        $controllers->get('/', array($this, 'listAction'))->bind('user_list');
        $controllers
            ->match('/edit/{id}', array($this, 'editAction'))
            ->value('id', null)
            ->assert('id', '\d+')
            ->bind('user_edit')
        ;
        $controllers->get('/delete/{id}', array($this, 'deleteAction'))->assert('id', '\d+')->bind('user_delete');
        return $controllers;
    }

    /**
     * @return string
     */
    public function listAction()
    {
        // get orders
        $users = $this->getEntityManager()->getRepository(get_class(new User()))->findAll();

        // return the rendered template
        return $this->renderView('User/list.html.twig', array('users' => $users));
    }

    /**
     * @param $id
     * @return string|RedirectResponse
     */
    public function editAction($id)
    {
        if(!is_null($id))
        {
            // get user
            $user = $this->getEntityManager()->getRepository(get_class(new User()))->find($id);
            /** @var User $user */

            if(is_null($user))
            {
                $this->app->abort(404, "user with id {$id} not found!");
            }
        }
        else
        {
            $user = new User();
            $user->setSalt(uniqid(mt_rand()));
        }

        // create user form
        $userForm = $this->getFormFactory()->create(new UserType(), $user);

        if('POST' == $this->getRequest()->getMethod())
        {
            // bind request
            $userForm->bind($this->getRequest());

            // check if the input is valid
            if($userForm->isValid())
            {
                // update password
                if($user->updatePassword($this->app['security.encoder.digest']))
                {
                    // you can't remove admin role on yourself
                    if($user->getId() == $this->getUser()->getId())
                    {
                        $user->addRole(User::ROLE_ADMIN);
                    }

                    // persist the user
                    $this->getEntityManager()->persist($user);
                    $this->getEntityManager()->flush();

                    // redirect to the edit mask
                    return $this->app->redirect($this->getUrlGenerator()->generate('user_edit', array('id' => $user->getId())), 302);
                }
                else
                {
                    $userForm->addError(new FormError($this->getTranslator()->trans("No password set", array(), "frontend")));
                }
            }
        }

        // return the rendered template
        return $this->renderView('User/edit.html.twig', array(
            'user' => $user,
            'userform' => $userForm->createView(),
        ));
    }

    /**
     * @param $id
     * @return RedirectResponse
     */
    public function deleteAction($id)
    {
        // get the user
        $user = $this->getEntityManager()->getRepository(get_class(new User()))->find($id);
        /** @var User $user */

        // check if user exists
        if(is_null($user))
        {
            $this->app->abort(404, "User with id {$id} not found!");
        }

        // check the user doesn't delete himself
        if($user->getId() == $this->getUser()->getId())
        {
            $this->app->abort(500, "You can't delete yourself!");
        }

        // remove the user
        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();

        // redirect to the list
        return $this->app->redirect($this->getUrlGenerator()->generate('user_list'), 302);
    }
}