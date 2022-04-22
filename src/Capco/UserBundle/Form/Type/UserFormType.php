<?php

namespace Capco\UserBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Capco\AppBundle\Toggle\Manager;
use Capco\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('username', null, [
                'required' => true,
            ])
            ->add('email', null, [
                'required' => true,
            ])
            ->add('plainPassword')
            ->add(
                'roles',
                CollectionType::class,
                ['entry_type' => TextType::class, 'allow_add' => true, 'allow_delete' => true, 'by_reference' => false]
            )
            ->add('locked')
            ->add('vip')
            ->add('enabled')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => User::class,
            ]
        );
    }
}