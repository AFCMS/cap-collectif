<?php

namespace Capco\UserBundle\Form\Type;

use Capco\AppBundle\Form\Type\PurifiedTextType;
use Capco\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonalDataFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', PurifiedTextType::class, [
                'purify_html' => true,
                'strip_tags' => true,
                'purify_html_profile' => 'default',
            ])
            ->add('lastname', PurifiedTextType::class, [
                'strip_tags' => true,
                'purify_html' => true,
                'purify_html_profile' => 'default',
            ])
            ->add('postalAddress', PurifiedTextType::class, [
                'strip_tags' => true,
                'purify_html' => true,
                'purify_html_profile' => 'default',
            ])
            ->add('address', PurifiedTextType::class, [
                'strip_tags' => true,
                'purify_html' => true,
                'purify_html_profile' => 'default',
            ])
            ->add('address2', PurifiedTextType::class, [
                'strip_tags' => true,
                'purify_html' => true,
                'purify_html_profile' => 'default',
            ])
            ->add('zipCode')
            ->add('city', PurifiedTextType::class, [
                'strip_tags' => true,
                'purify_html' => true,
                'purify_html_profile' => 'default',
            ])
            ->add('phone')
            ->add('email', EmailType::class)
            ->add('phoneConfirmed')
            ->add('birthPlace')
            ->add('dateOfBirth', DateTimeType::class, [
                'widget' => 'single_text',
                'format' => 'Y-MM-dd',
            ])
            ->add('gender', ChoiceType::class, ['choices' => array_keys(User::getGenderList())]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => false,
        ]);
    }
}
