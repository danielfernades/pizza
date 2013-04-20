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
     * @var User
     */
    protected $user;

    /**
     * @var bool
     */
    protected $isGranted;

    /**
     * @param User $user
     * @param bool $isGranted
     */
    public function __construct(User $user, $isGranted = false)
    {
        $this->user = $user;
        $this->isGranted = $isGranted;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $this->user;
        $isGranted = $this->isGranted;

        $builder
            ->add('eat')
            ->add('drink')
            ->add('user', 'entity', array(
                'class' => get_class(new User()),
                'property' => 'username',
                'query_builder' => function(EntityRepository $er) use($user, $isGranted) {
                    $qb = $er->createQueryBuilder('u');
                    if(!$isGranted) {
                        $qb->andWhere('u.id = :id');
                        $qb->setParameter('id', $user->getId());
                    }
                    $qb->orderBy('u.username', 'ASC');
                    return $qb;
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