<?php

namespace Pizza\Controller;

use Silex\ControllerCollection;
use Pizza\Entity\User;
use Pizza\Form\UserType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
     * @throws AccessDeniedException
     */
    public function listAction()
    {
        // check permissions
        if(!$this->getSecurity()->isGranted('ROLE_ADMIN'))
        {
            throw new AccessDeniedException("ROLE_ADMIN is needed!");
        }

        // get orders
        $arrUsers = $this->getEntityManager()->getRepository("Pizza\\Entity\\User")->findAll();

        // return the rendered template
        return $this->renderView('User/list.html.twig', array('users' => $arrUsers));
    }

    /**
     * @param $id
     * @return string
     * @throws AccessDeniedException
     */
    public function editAction($id)
    {
        // check permissions
        if(!$this->getSecurity()->isGranted('ROLE_ADMIN'))
        {
            throw new AccessDeniedException("ROLE_ADMIN is needed!");
        }

        if(!is_null($id))
        {
            // get user
            $objUser = $this->getEntityManager()->getRepository("Pizza\\Entity\\User")->find($id);
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
     * @throws AccessDeniedException
     */
    public function deleteAction($id)
    {
        // check permissions
        if(!$this->getSecurity()->isGranted('ROLE_ADMIN'))
        {
            throw new AccessDeniedException("ROLE_ADMIN is needed!");
        }

        // get the user
        $objUser = $this->getEntityManager()->getRepository("Pizza\\Entity\\User")->find($id);

        // check if user exists
        if(is_null($objUser))
        {
            $this->app->abort(404, "User with id {$id} not found!");
        }

        // remove the user
        $this->getEntityManager()->remove($objUser);
        $this->getEntityManager()->flush();

        // redirect to the list
        return $this->app->redirect($this->getUrlGenerator()->generate('user_list'), 302);
    }
}