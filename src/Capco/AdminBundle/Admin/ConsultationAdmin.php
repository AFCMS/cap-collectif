<?php

namespace Capco\AdminBundle\Admin;

use Capco\AppBundle\Repository\OpinionTypeRepository;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class ConsultationAdmin extends AbstractAdmin
{
    protected $classnameLabel = 'consultation';
    protected $datagridValues = [
        '_sort_order' => 'ASC',
        '_sort_by' => 'title',
    ];

    private $opinionTypeRepository;

    public function __construct(
        $code,
        $class,
        $baseControllerName,
        OpinionTypeRepository $opinionTypeRepository
    ) {
        parent::__construct($code, $class, $baseControllerName);
        $this->opinionTypeRepository = $opinionTypeRepository;
    }

    public function getTemplate($name)
    {
        if ('edit' === $name) {
            return 'CapcoAdminBundle:Consultation:edit.html.twig';
        }

        return $this->getTemplateRegistry()->getTemplate($name);
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title', null, [
                'label' => 'global.title',
            ])
            ->add('opinionTypes', null, [
                'label' => 'admin.fields.consultation.opinion_types',
            ])
            ->add('updatedAt', null, [
                'label' => 'global.maj',
            ])
            ->add('createdAt', null, [
                'label' => 'global.creation',
            ]);
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        unset($this->listModes['mosaic']);

        $listMapper
            ->addIdentifier('title', null, [
                'label' => 'global.title',
            ])
            ->add('step', null, [
                'label' => 'global.participative.project.label',
            ])
            ->add('opinionTypes', ModelType::class, [
                'label' => 'admin.fields.consultation.opinion_types',
            ])
            ->add('updatedAt', null, [
                'label' => 'global.maj',
            ])
            ->add('_action', 'actions', [
                'label' => 'link_actions',
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper->with('admin.fields.step.group_general')->add('title', null, [
            'label' => 'global.title',
        ]);
        if ($this->getSubject()->getId()) {
            $formMapper
                ->add('description', CKEditorType::class, [
                    'label' => 'global.description',
                    'config_name' => 'admin_editor',
                    'required' => false,
                ])
                ->add('illustration', ModelListType::class, [
                    'required' => false,
                    'label' => 'global.illustration',
                    'help' => 'help-text-description-step',
                ])
                ->add('step', null, [
                    'label' => 'admin.label.step',
                    'required' => false,
                ]);
        }
        $formMapper->end();
        if ($this->getSubject()->getId()) {
            $formMapper
                ->with('plan-consultation')
                ->add('opinionCountShownBySection', null, [
                    'label' => 'admin.fields.step.opinionCountShownBySection',
                    'required' => true,
                ])
                ->add('opinionTypes', ModelType::class, [
                    'label' => 'admin.fields.consultation.opinion_types',
                    'query' => $this->createQueryForOpinionTypes(),
                    'by_reference' => false,
                    'multiple' => true,
                    'expanded' => true,
                    'required' => true,
                    'tree' => true,

                    'disabled' => true,
                ])
                ->end()
                ->with('submission-of-proposals')
                ->add('titleHelpText', null, [
                    'label' => 'admin.fields.proposal_form.title_help_text',
                    'required' => false,
                    'help' => 'admin.fields.proposal_form.help_text_title_help_text',
                ])
                ->add('descriptionHelpText', null, [
                    'label' => 'admin.fields.proposal_form.description_help_text',
                    'required' => false,
                    'help' => 'admin.fields.proposal_form.help_text_description_help_text',
                ])
                ->end()
                ->with('admin.label.settings.notifications', [
                    'description' => 'receive-email-notification-when-a-contribution-is',
                ])
                ->add('moderatingOnReport', CheckboxType::class, [
                    'label' => 'reported',
                    'mapped' => false,
                    'value' => true,
                    'disabled' => true,
                    'attr' => ['readonly' => true, 'checked' => true],
                ])
                ->add('moderatingOnCreate', null, ['label' => 'admin.fields.synthesis.enabled'])
                ->add('moderatingOnUpdate', null, [
                    'label' => 'global.modified',
                ])
                ->end()
                ->with('admin.fields.step.advanced')
                ->add('metaDescription', null, [
                    'label' => 'global.meta.description',
                    'required' => false,
                    'help' => 'admin.help.metadescription',
                ])
                ->add('customCode', null, [
                    'label' => 'admin.customcode',
                    'required' => false,
                    'help' => 'admin.help.customcode',
                    'attr' => [
                        'rows' => 10,
                        'placeholder' => '<script type="text/javascript"> </script>',
                    ],
                ])
                ->end();
        }
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('title', null, [
                'label' => 'global.title',
            ])
            ->add('opinionTypes', null, [
                'label' => 'admin.fields.consultation.opinion_types',
            ])
            ->add('updatedAt', null, [
                'label' => 'global.maj',
            ])
            ->add('createdAt', null, [
                'label' => 'global.creation',
            ]);
    }

    private function createQueryForOpinionTypes()
    {
        $subject = $this->getSubject()->getId() ? $this->getSubject() : null;

        return $this->opinionTypeRepository->getOrderedRootNodesQuery($subject);
    }
}
