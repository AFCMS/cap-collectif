<?php
namespace Capco\AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Capco\AppBundle\Entity\Interfaces\Trashable;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TrashedStatusType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => 'admin.fields.opinion.is_trashed',
            'required' => false,
            'placeholder' => 'synthesis.source_types.none',
            'choices' => [
                'trashed-visible-content' => Trashable::STATUS_VISIBLE,
                'trashed-hidden-content' => Trashable::STATUS_INVISIBLE,
            ],
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
