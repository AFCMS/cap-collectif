<?php

namespace Capco\AppBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'show_unlink' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$options['show_unlink']) {
            $builder->add('unlink',
                HiddenType::class, [
                'mapped' => false,
                'data' => false,
                'required' => false,
                ])
            ;
        } else {
            $builder->add('unlink',
                CheckboxType::class, [
                'label' => 'media.form.unlink',
                'translation_domain' => 'CapcoAppBundle',
                'mapped' => false,
                'data' => false,
                'required' => false,
            ]);
        }
        $builder
            ->add('binaryContent',
                FileType::class, [
                'label' => 'media.form.binary_content',
                'translation_domain' => 'CapcoAppBundle',
                'required' => false,
            ])
            ->remove('contentType')
        ;
    }

    public function getExtendedType(): string
    {
        return 'sonata_media_type';
    }
}
