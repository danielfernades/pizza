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
        $arrUsers = $this->getEntityManager()->getRepository(get_class(new User()))->findAll();

        // return the rendered template
        return $this->renderView('User/list.html.twig', array('users' => $arrUsers));
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
            $objUser = $this->getEntityManager()->getRepository(get_class(new User()))->find($id);
            /** @var User $objUser */

            if(is_null($objUser))
            {
                $this->app->abort(404, "user with id {$id} not found!");
            }
        }
        else
        {
            $objUser = new User();
            $objUser->setSalt(uniqid(mt_rand()));
        }

        // create user form
        $objUserForm = $this->getFormFactory()->create(new UserType(), $objUser);

        if('POST' == $this->getRequest()->getMethod())
        {
            // bind request
            $objUserForm->bind($this->getRequest());

            // check if the input is valid
            if($objUserForm->isValid())
            {
                // update password
                if($objUser->updatePassword($this->app['security.encoder.digest']))
                {
                    // you can't remove admin role on yourself
                    if($objUser->getId() == $this->getSecurity()->getToken()->getUser()->getId())
                    {
                        $objUser->addRole(User::ROLE_ADMIN);
                    }

                    // persist the user
                    $this->getEntityManager()->persist($objUser);
                    $this->getEntityManager()->flush();

                    // redirect to the edit mask
                    return $this->app->redirect($this->getUrlGenerator()->generate('user_edit', array('id' => $objUser->getId())), 302);
                }
                else
                {
                    $objUserForm->addError(new FormError($this->getTranslator()->trans("No password set", array(), "frontend")));
                }
            }
        }

        // return the rendered template
        return $this->renderView('User/edit.html.twig', array(
            'user' => $objUser,
            'userform' => $objUserForm->createView(),
        ));
    }

    /**
     * @param $id
     * @return RedirectResponse
     */
    public function deleteAction($id)
    {
        // get the user
        $objUser = $this->getEntityManager()->getRepository(get_class(new User()))->find($id);
        /** @var User $objUser */

        // check if user exists
        if(is_null($objUser))
        {
            $this->app->abort(404, "User with id {$id} not found!");
        }

        // check the user doesn't delete himself
        if($objUser->getId() == $this->getSecurity()->getToken()->getUser()->getId())
        {
            $this->app->abort(500, "You can't delete yourself!");
        }

        // remove the user
        $this->getEntityManager()->remove($objUser);
        $this->getEntityManager()->flush();

        // redirect to the list
        return $this->app->redirect($this->getUrlGenerator()->generate('user_list'), 302);
    }
}