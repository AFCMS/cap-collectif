<?php

namespace Capco\AppBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Capco\AppBundle\Repository\ThemeRepository;
use Capco\AppBundle\Repository\ProjectRepository;
use Capco\AppBundle\Toggle\Manager;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventSearchType extends AbstractType
{
    private $toggleManager;

    public function __construct(Manager $toggleManager)
    {
        $this->toggleManager = $toggleManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('term',
                SearchType::class, [
                'required' => false,
                'label' => 'event.searchform.term',
                'translation_domain' => 'CapcoAppBundle',
            ])
        ;

        if ($this->toggleManager->isActive('themes')) {
            $builder
                ->add(
                    'theme',
                    EntityType::class,
                    [
                        'required' => false,
                        'class' => 'CapcoAppBundle:Theme',
                        'property' => 'title',
                        'label' => 'event.searchform.theme',
                        'translation_domain' => 'CapcoAppBundle',
                        'query_builder' => function (ThemeRepository $tr) {
                            return $tr->createQueryBuilder('t')
                                ->where('t.isEnabled = :enabled')
                                ->setParameter('enabled', true);
                        },
                        'empty_value' => 'event.searchform.all_themes',
                        'attr' => ['onchange' => 'this.form.submit()'],
                    ]
                );
        }

        $builder->add('project',
            EntityType::class, [
            'required' => false,
            'class' => 'CapcoAppBundle:Project',
            'property' => 'title',
            'label' => 'event.searchform.project',
            'translation_domain' => 'CapcoAppBundle',
            'query_builder' => function (ProjectRepository $cr) {
                return $cr->createQueryBuilder('c')
                    ->where('c.isEnabled = :enabled')
                    ->setParameter('enabled', true);
            },
            'empty_value' => 'event.searchform.all_projects',
            'attr' => ['onchange' => 'this.form.submit()'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
