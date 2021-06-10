<?php

namespace Capco\AppBundle\Form;

use Capco\AppBundle\Entity\ProposalForm;
use Capco\AppBundle\Toggle\Manager;
use Capco\AppBundle\Validator\Constraints\CheckMapCenter;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;
use Capco\AppBundle\Entity\Questions\QuestionnaireAbstractQuestion;
use Capco\AppBundle\Entity\District\ProposalDistrict;
use Capco\AppBundle\Entity\ProposalCategory;

class ProposalFormUpdateType extends AbstractType
{
    protected Manager $toggleManager;

    public function __construct(Manager $toggleManager)
    {
        $this->toggleManager = $toggleManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'purify_html' => true,
                'purify_html_profile' => 'admin',
            ])
            ->add('titleHelpText', TextType::class, [
                'purify_html' => true,
                'purify_html_profile' => 'admin',
            ])

            ->add('description', TextType::class, [
                'purify_html' => true,
                'purify_html_profile' => 'admin',
            ])

            ->add('usingDescription', CheckboxType::class)
            ->add('descriptionMandatory', CheckboxType::class)
            ->add('descriptionHelpText', TextType::class, [
                'purify_html' => true,
                'purify_html_profile' => 'admin',
            ])

            ->add('usingSummary', CheckboxType::class)
            ->add('summaryHelpText', TextType::class, [
                'purify_html' => true,
                'purify_html_profile' => 'admin',
            ])

            ->add('usingIllustration', CheckboxType::class)
            ->add('illustrationHelpText', TextType::class, [
                'purify_html' => true,
                'purify_html_profile' => 'admin',
            ])

            ->add('proposalInAZoneRequired', CheckboxType::class)

            ->add('usingThemes', CheckboxType::class)
            ->add('themeMandatory', CheckboxType::class)
            ->add('themeHelpText', TextType::class, [
                'purify_html' => true,
                'purify_html_profile' => 'admin',
            ])

            ->add('usingDistrict', CheckboxType::class)
            ->add('districtMandatory', CheckboxType::class)
            ->add('districtHelpText', TextType::class, [
                'purify_html' => true,
                'purify_html_profile' => 'admin',
            ])

            ->add('usingCategories', CheckboxType::class)
            ->add('categoryMandatory', CheckboxType::class)
            ->add('categoryHelpText', TextType::class, [
                'purify_html' => true,
                'purify_html_profile' => 'admin',
            ])

            ->add('usingAddress', CheckboxType::class)
            ->add('addressHelpText', TextType::class, [
                'purify_html' => true,
                'purify_html_profile' => 'admin',
            ])
            ->add('mapCenter', TextType::class, [
                'constraints' => [new CheckMapCenter()],
            ])
            ->add('zoomMap', IntegerType::class)

            ->add('commentable', CheckboxType::class)
            ->add('costable', CheckboxType::class)
            ->add('suggestingSimilarProposals', CheckboxType::class)

            ->add('categories', CollectionType::class, [
                'entry_type' => ProposalCategoryType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'delete_empty' => function (?ProposalCategory $category = null) {
                    return null === $category || 'NULL' === $category->getName();
                },
            ])

            ->add('districts', CollectionType::class, [
                'entry_type' => ProposalDistrictType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'delete_empty' => function (?ProposalDistrict $district = null) {
                    return null === $district || 'NULL' === $district->getName();
                },
            ])

            ->add('questions', CollectionType::class, [
                'allow_add' => true,
                'allow_delete' => true,
                'entry_type' => QuestionnaireAbstractQuestionType::class,
                'by_reference' => false,
                'delete_empty' => function (
                    ?QuestionnaireAbstractQuestion $questionnaireAbstractQuestion = null
                ) {
                    return null === $questionnaireAbstractQuestion ||
                        null === $questionnaireAbstractQuestion->getQuestion()->getTitle() ||
                        '' === $questionnaireAbstractQuestion->getQuestion()->getTitle();
                },
            ])

            ->add('allowAknowledge', CheckboxType::class)
            ->add('objectType', TextType::class)
            ->add('canContact', CheckboxType::class)
            ->add('isGridViewEnabled', CheckboxType::class)
            ->add('isListViewEnabled', CheckboxType::class)
            ->add('isMapViewEnabled', CheckboxType::class);

        if ($this->toggleManager->isActive(Manager::unstable__tipsmeee)) {
            $builder
                ->add('usingTipsmeee', CheckboxType::class)
                ->add('tipsmeeeHelpText', TextType::class, [
                    'purify_html' => true,
                    'purify_html_profile' => 'default',
                ]);
        }
        $builder
            ->add('usingWebPage', CheckboxType::class)
            ->add('usingFacebook', CheckboxType::class)
            ->add('usingTwitter', CheckboxType::class)
            ->add('usingLinkedIn', CheckboxType::class)
            ->add('usingInstagram', CheckboxType::class)
            ->add('usingYoutube', CheckboxType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => ProposalForm::class,
            'constraints' => new Valid(),
        ]);
    }
}
