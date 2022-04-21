<?php

namespace Capco\AdminBundle\Admin;

use Capco\AppBundle\Entity\Section;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class SectionAdmin extends Admin
{
    protected $datagridValues = [
        '_sort_order' => 'ASC',
        '_sort_by' => 'position',
    ];

    public function createQuery($context = 'list')
    {
        $manager = $this->getConfigurationPool()->getContainer()->get('capco.toggle.manager');
        $em = $this->getConfigurationPool()->getContainer()->get('doctrine')->getManager();

        $all = $em->getRepository('CapcoAppBundle:Section')->findAll();

        $ids = [];
        foreach ($all as $section) {
            if ($manager->containsEnabledFeature($section->getAssociatedFeatures())) {
                $ids[] = $section->getId();
            }
        }

        $query = parent::createQuery($context);
        $query->andWhere(
            $query->expr()->in($query->getRootAliases()[0] . '.id', ':ids')
        );
        $query->setParameter('ids', $ids);

        return $query;
    }

    public function getBatchActions()
    {
        return [];
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title', null, [
                'label' => 'admin.fields.section.title',
            ])
            ->add('position', null, [
                'label' => 'admin.fields.section.position',
            ])
            ->add('teaser', null, [
                'label' => 'admin.fields.section.teaser',
            ])
            ->add('body', null, [
                'label' => 'admin.fields.section.body',
            ])
            ->add('enabled', null, [
                'label' => 'admin.fields.section.enabled',
            ])
            ->add('createdAt', null, [
                'label' => 'admin.fields.section.created_at',
            ])
            ->add('updatedAt', null, [
                'label' => 'admin.fields.section.updated_at',
            ])
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        unset($this->listModes['mosaic']);

        $listMapper
            ->add('move_actions', 'actions', [
                'label' => 'admin.action.highlighted_content.move_actions.label',
                'template' => 'SonataAdminBundle:CRUD:list__action.html.twig',
                'type' => 'action',
                'code' => 'Action',
                'actions' => [
                    'up' => [
                        'template' => 'CapcoAdminBundle:Section:list__action_up.html.twig',
                    ],
                    'down' => [
                        'template' => 'CapcoAdminBundle:Section:list__action_down.html.twig',
                    ],
                ],
            ])
            ->addIdentifier('title', null, [
                'label' => 'admin.fields.section.title',
            ])
            ->add('enabled', null, [
                'label' => 'admin.fields.section.enabled',
                'editable' => true,
            ])
            ->add('updatedAt', null, [
                'label' => 'admin.fields.section.updated_at',
            ])
            ->add('_action', 'actions', [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => ['template' => 'CapcoAdminBundle:Section:list__action_delete.html.twig'],
                ],
            ])
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $fields = Section::$fieldsForType[$this->getSubject()->getType()];
        $subject = $this->getSubject();

        if ($fields['title']) {
            $formMapper->add('title', null, [
                'label' => 'admin.fields.section.title',
            ]);
        } else {
            $formMapper->add('title', null, [
                'label' => 'admin.fields.section.title',
                'read_only' => true,
            ]);
        }

        $formMapper
            ->add('enabled', null, [
                'label' => 'admin.fields.section.enabled',
                'required' => false,
            ])
            ->add('position', null, [
                'label' => 'admin.fields.section.position',
            ])
        ;

        if ($fields['teaser']) {
            $formMapper->add('teaser', null, [
                'label' => 'admin.fields.section.teaser',
                'required' => false,
            ]);
        }

        if ($fields['body']) {
            $formMapper->add('body', CKEditorType::class, [
                'label' => 'admin.fields.section.body',
                'config_name' => 'admin_editor',
            ]);
        }

        if ($fields['nbObjects']) {
            $formMapper->add('nbObjects', null, [
                'label' => 'admin.fields.section.nb_objects',
            ]);
        }

        if ($subject && $subject->getType() === 'proposals') {
            $formMapper->add('step', 'sonata_type_model', [
                'label' => 'admin.fields.section.collect_step',
                'required' => true,
                'query' => $this->createQueryForCollectSteps(),
            ]);
        }
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('title', null, [
                'label' => 'admin.fields.section.title',
            ])
            ->add('enabled', null, [
                'label' => 'admin.fields.section.enabled',
            ])
            ->add('position', null, [
                'label' => 'admin.fields.section.position',
            ])
            ->add('teaser', CKEditorType::class, [
                'label' => 'admin.fields.section.teaser',
                'config_name' => 'admin_editor',
            ])
            ->add('body', CKEditorType::class, [
                'label' => 'admin.fields.section.body',
                'config_name' => 'admin_editor',
            ])
            ->add('nbObjects', null, [
                'label' => 'admin.fields.section.nb_objects',
            ])
            ->add('createdAt', null, [
                'label' => 'admin.fields.section.created_at',
            ])
            ->add('updatedAt', null, [
                'label' => 'admin.fields.section.updated_at',
            ])
        ;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('down', $this->getRouterIdParameter() . '/down');
        $collection->add('up', $this->getRouterIdParameter() . '/up');
    }

    private function createQueryForCollectSteps()
    {
        $qb = $this->getConfigurationPool()
            ->getContainer()
            ->get('doctrine')
            ->getRepository('CapcoAppBundle:Steps\CollectStep')
            ->createQueryBuilder('cs')
            ->where('cs.isEnabled = :enabled')
            ->setParameter('enabled', true)
        ;

        return $qb->getQuery();
    }
}
