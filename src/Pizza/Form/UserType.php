<?php

namespace Pizza\Form;

use Pizza\Entity\User;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username')
            ->add('plainpassword', 'password', array('required' => false))
            ->add('repeatedpassword', 'password', array('required' => false))
            ->add('email', 'email')
            ->add('roles', 'choice', array(
                'choices' => User::possibleRoles(),
                'multiple' => true,
                'required' => false
            ))
            ->add('enabled', 'checkbox', array('required' => false))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $resolver->setDefaults(array(
            'data_class' => get_class(new User()),
        ));
    }

    public function getName()
    {
        return 'user';
    }
}
