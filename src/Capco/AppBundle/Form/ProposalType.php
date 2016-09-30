<?php

namespace Capco\AppBundle\Form;

use Capco\AppBundle\Form\DataTransformer\EntityToIdTransformer;
use Capco\AppBundle\Form\Type\PurifiedTextareaType;
use Capco\AppBundle\Form\Type\PurifiedTextType;
use Capco\AppBundle\Repository\AbstractQuestionRepository;
use Capco\AppBundle\Toggle\Manager;
use Infinite\FormBundle\Form\Type\PolyCollectionType;
use Sonata\MediaBundle\Form\Type\MediaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProposalType extends AbstractType
{
    protected $transformer;
    protected $toggleManager;
    protected $questionRepository;

    public function __construct(EntityToIdTransformer $transformer, Manager $toggleManager, AbstractQuestionRepository $questionRepository)
    {
        $this->transformer = $transformer;
        $this->toggleManager = $toggleManager;
        $this->questionRepository = $questionRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $form = $options['proposalForm'];
        if (!$form) {
            throw new \Exception('A proposal form is needed to create or update a proposal.');
        }

        $builder
            ->add('title', PurifiedTextType::class, ['required' => true])
            ->add('body', PurifiedTextareaType::class, ['required' => true])
        ;

        if ($this->toggleManager->isActive('themes') && $form->isUsingThemes()) {
            $builder->add('theme');
        }

        if ($this->toggleManager->isActive('districts') && $form->isUsingDistrict()) {
            $builder->add('district');
        }

        if ($form->isUsingCategories() && $form->getCategories()->count() > 0) {
            $builder->add('category');
        }

        $builder
            ->add('responses', PolyCollectionType::class, [
                'allow_add' => true,
                'allow_delete' => false,
                'by_reference' => false,
                'types' => [
                    new ValueResponseType($this->transformer, $this->questionRepository),
                    new MediaResponseType($this->transformer, $this->questionRepository),
                ],
                'required' => false,
            ])
        ;

        $builder->add('media', MediaType::class, [
            'required' => false,
            'provider' => 'sonata.media.provider.image',
            'context' => 'default',
        ])
        ->add('delete_media', CheckboxType::class, [
            'required' => false,
            'mapped' => false,
        ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Capco\AppBundle\Entity\Proposal',
            'csrf_protection' => false,
            'translation_domain' => 'CapcoAppBundle',
            'cascade_validation' => true,
            'proposalForm' => null,
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'proposal';
    }
}
