<?php

namespace Capco\UserBundle\Form\Type;

use Capco\UserBundle\Entity\User;
use Capco\AppBundle\Toggle\Manager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberSearchType extends AbstractType
{
    private $toggleManager;

    public function __construct(Manager $toggleManager)
    {
        $this->toggleManager = $toggleManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sort',
                ChoiceType::class, [
                'required' => false,
                'choices' => User::$sortOrderLabels,
                'translation_domain' => 'CapcoAppBundle',
                'label' => 'user.index.sort.label',
                'empty_value' => false,
                'attr' => ['onchange' => 'this.form.submit()'],
            ])
        ;

        if ($this->toggleManager->isActive('user_type')) {
            $builder->add('userType',
                EntityType::class, [
                'required' => false,
                'class' => 'CapcoUserBundle:UserType',
                'property' => 'name',
                'label' => 'user.index.user_type.label',
                'translation_domain' => 'CapcoAppBundle',
                'empty_value' => 'user.index.user_type.all_types',
                'attr' => ['onchange' => 'this.form.submit()'],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
