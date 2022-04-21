<?php

namespace Capco\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Capco\AppBundle\Entity\Idea;
use Capco\AppBundle\Repository\ThemeRepository;
use Capco\AppBundle\Toggle\Manager;

class IdeaSearchType extends AbstractType
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
            ->add('term', 'search', [
                'required' => false,
                'label' => 'idea.searchform.term',
                'translation_domain' => 'CapcoAppBundle',
            ])
            ->add('sort', 'choice', [
                'required' => false,
                'choices' => Idea::$sortCriterias,
                'translation_domain' => 'CapcoAppBundle',
                'label' => 'idea.searchform.sort',
                'empty_value' => false,
                'attr' => ['onchange' => 'this.form.submit()'],
            ])
        ;

        if ($this->toggleManager->isActive('themes')) {
            $builder->add('theme', 'entity', [
                'required' => false,
                'class' => 'CapcoAppBundle:Theme',
                'property' => 'title',
                'label' => 'idea.searchform.theme',
                'translation_domain' => 'CapcoAppBundle',
                'query_builder' => function (ThemeRepository $tr) {
                    return $tr->createQueryBuilder('t')
                        ->where('t.isEnabled = :enabled')
                        ->setParameter('enabled', true);
                },
                'empty_value' => 'idea.searchform.all_themes',
                'attr' => ['onchange' => 'this.form.submit()'],
            ]);
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'capco_app_search';
    }
}
