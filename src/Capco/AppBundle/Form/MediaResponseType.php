<?php

namespace Capco\AppBundle\Form;

use Capco\AppBundle\Entity\Questions\AbstractQuestion;
use Capco\AppBundle\Entity\Responses\AbstractResponse;
use Capco\AppBundle\Entity\Responses\MediaResponse;
use Capco\AppBundle\Form\DataTransformer\EntityToIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaResponseType extends AbstractType
{
    protected $transformer;

    public function __construct(EntityToIdTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->transformer->setEntityClass(AbstractQuestion::class);
        $this->transformer->setEntityRepository('CapcoAppBundle:Questions\AbstractQuestion');

        $builder->add('question', HiddenType::class);
        $builder->get('question')->addModelTransformer($this->transformer);

        $builder->add(AbstractResponse::TYPE_FIELD_NAME, HiddenType::class, [
            'data' => $this->getBlockPrefix(),
            'mapped' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MediaResponse::class,
            'model_class' => MediaResponse::class,
            'csrf_protection' => false,
            'translation_domain' => 'CapcoAppBundle',
            'cascade_validation' => true,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'media_response';
    }
}
