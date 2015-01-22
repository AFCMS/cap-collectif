<?php

namespace Capco\AppBundle\Form;

use Capco\AppBundle\Entity\Theme;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Capco\AppBundle\Repository\ThemeRepository;

class IdeaType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text')
            ->add('body', 'textarea')
            ->add('Theme', 'entity', array(
                    'class' => 'CapcoAppBundle:Theme',
                    'property' => 'title',
                    'multiple' => false,
                    'expanded' => false,
                    'query_builder' => function(ThemeRepository $tr) {
                        return $tr->createQueryBuilder('t')
                            ->where('t.isEnabled = :enabled')
                            ->setParameter('enabled', true);
                    }
                ))
            ->add('media', 'sonata_media_type', array(
                    'provider' => 'sonata.media.provider.image',
                    'context' => 'default',
                    'required' => false,
                    'label' => 'Image'
                ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Capco\AppBundle\Entity\Idea',
            'csrf_protection' => true,
            'csrf_field_name' => '_token'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'capco_appbundle_idea';
    }
}
