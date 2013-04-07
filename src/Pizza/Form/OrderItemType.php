<?php

namespace Pizza\Form;

use Doctrine\ORM\EntityRepository;
use Pizza\Entity\OrderItem;
use Pizza\Entity\User;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OrderItemType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('eat')
            ->add('drink')
            ->add('user', 'entity', array(
                'class' => get_class(new User()),
                'property' => 'username',
                'query_builder' => function(EntityRepository $er) {
                    return $er
                        ->createQueryBuilder('u')
                        ->orderBy('u.username', 'ASC')
                    ;
                },
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $resolver->setDefaults(array(
            'data_class' => get_class(new OrderItem()),
        ));
    }

    public function getName()
    {
        return 'orderitem';
    }
}