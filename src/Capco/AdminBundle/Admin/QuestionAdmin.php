<?php

namespace Capco\AdminBundle\Admin;

use Capco\AdminBundle\Form\QuestionValidationRuleType;
use Capco\AppBundle\Entity\Questions\MediaQuestion;
use Capco\AppBundle\Entity\Questions\MultipleChoiceQuestion;
use Capco\AppBundle\Entity\Questions\SimpleQuestion;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class QuestionAdmin extends Admin
{
    protected $formOptions = [
        'cascade_validation' => true,
    ];

    public function prePersist($question)
    {
        if ($question instanceof MultipleChoiceQuestion) {
            foreach ($question->getQuestionChoices() as $qc) {
                $qc->setQuestion($question);
            }
        }
    }

    public function preUpdate($question)
    {
        if ($question instanceof MultipleChoiceQuestion) {
            foreach ($question->getQuestionChoices() as $qc) {
                $qc->setQuestion($question);
            }
        }

        $question->updateTimestamp();
    }

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $subject = $this->getSubject();

        if ($subject instanceof SimpleQuestion) {
            $questionTypesLabels = SimpleQuestion::$questionTypesLabels;
        } else {
            $questionTypesLabels = MultipleChoiceQuestion::$questionTypesLabels;
        }

        $formMapper
            ->with('admin.fields.question.group_content')
                ->add('title', null, [
                    'label' => 'admin.fields.question.title',
                    'required' => true,
                ])
                ->add('help_text', 'textarea', [
                    'label' => 'admin.fields.question.help_text',
                    'required' => false,
                ]);

        if (!$subject instanceof MediaQuestion) {
            $formMapper->add(
                    'type',
                    'choice',
                    [
                        'label' => 'admin.fields.question.type',
                        'choices' => $questionTypesLabels,
                        'translation_domain' => 'CapcoAppBundle',
                    ]
                );
        }

        $formMapper->add('required', null, [
                    'label' => 'admin.fields.question.required',
                    'required' => false,
                ])
                ->add('private', null, [
                    'label' => 'admin.fields.question.private',
                    'required' => false,
                ])
            ->end()
        ;

        if ($subject instanceof MultipleChoiceQuestion) {
            $formMapper
                ->with('admin.fields.question.group_question_choices')
                ->add('questionChoices', 'sonata_type_collection', [
                    'label' => 'admin.fields.question.question_choices',
                    'required' => false,
                ], [
                    'edit' => 'inline',
                    'inline' => 'table',
                    'sortable' => 'position',
                ])
                ->add('randomQuestionChoices', null, [
                    'label' => 'admin.fields.question.random_question_choices',
                    'required' => false,
                ])
                ->add('otherAllowed', null, [
                    'label' => 'admin.fields.question.other_allowed',
                    'required' => false,
                    'label_attr' => ['class' => 'hidden'],
                    'attr' => ['class' => 'hidden'],
                ])
                ->end()
                ->with('admin.fields.question.group_validation')
                ->add('validationRule', QuestionValidationRuleType::class, [
                    'required' => false,
                    'label' => 'admin.fields.question.validation_rule',
                    'translation_domain' => 'SonataAdminBundle',
                ])
                ->end()
            ;
        }
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(['create', 'edit', 'delete', 'update']);
    }
}
