<?php
namespace Capco\AppBundle\Form;

use Capco\AdminBundle\Form\QuestionValidationRuleType;
use Capco\AppBundle\Entity\Questions\MultipleChoiceQuestion;
use Capco\AppBundle\Form\Type\PurifiedTextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MultipleChoiceQuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id');
        $builder->add('title', PurifiedTextType::class);
        $builder->add('helpText', PurifiedTextType::class);
        $builder->add('description', PurifiedTextType::class);
        $builder->add('private', CheckboxType::class);
        $builder->add('required', CheckboxType::class);
        $builder->add('type', IntegerType::class);
        $builder->add('questionChoices', CollectionType::class, [
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'entry_type' => QuestionChoiceType::class,
        ]);
        $builder->add('randomQuestionChoices', CheckboxType::class);
        $builder->add('otherAllowed', CheckboxType::class);
        $builder->add('validationRule', QuestionValidationRuleType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => MultipleChoiceQuestion::class,
        ]);
    }
}
