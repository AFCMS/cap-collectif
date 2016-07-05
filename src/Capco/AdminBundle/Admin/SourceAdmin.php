<?php

namespace Capco\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class SourceAdmin extends Admin
{
    protected $datagridValues = [
        '_sort_order' => 'ASC',
        '_sort_by' => 'title',
    ];

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title', null, [
                'label' => 'admin.fields.source.title',
            ])
            ->add('body', null, [
                'label' => 'admin.fields.source.body',
            ])
            ->add('Author', 'doctrine_orm_model_autocomplete', [
                'label' => 'admin.fields.source.author',
            ], null, [
                'property' => 'username',
            ])
            ->add('Opinion', null, [
                'label' => 'admin.fields.source.opinion',
            ])
            ->add('Category', null, [
                'label' => 'admin.fields.source.category',
            ])
            ->add('link', null, [
                'label' => 'admin.fields.source.link',
            ])
            ->add('votesCount', null, [
                'label' => 'admin.fields.source.vote_count_source',
            ])
            ->add('updatedAt', null, [
                'label' => 'admin.fields.source.updated_at',
            ])
            ->add('createdAt', null, [
                'label' => 'admin.fields.source.created_at',
            ])
            ->add('isEnabled', null, [
                'label' => 'admin.fields.source.is_enabled',
            ])
            ->add('isTrashed', null, [
                'label' => 'admin.fields.source.is_trashed',
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
            ->addIdentifier('title', null, [
                'label' => 'admin.fields.source.title',
            ])
            ->add('Author', 'sonata_type_model', [
                'label' => 'admin.fields.source.author',
            ])
            ->add('Opinion', 'sonata_type_model', [
                'label' => 'admin.fields.source.opinion',
            ])
            ->add('Category', 'sonata_type_model', [
                'label' => 'admin.fields.source.category',
            ])
            ->add('votesCount', null, [
                'label' => 'admin.fields.source.vote_count_source',
            ])
            ->add('isEnabled', null, [
                'editable' => true,
                'label' => 'admin.fields.source.is_enabled',
            ])
            ->add('isTrashed', null, [
                'editable' => true,
                'label' => 'admin.fields.source.is_trashed',
            ])
            ->add('updatedAt', null, [
                'label' => 'admin.fields.source.updated_at',
            ])
            ->add('_action', 'actions', [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('title', null, [
                'label' => 'admin.fields.source.title',
            ])
            ->add('isEnabled', null, [
                'label' => 'admin.fields.source.is_enabled',
                'required' => false,
            ])
            ->add('body', null, [
                'label' => 'admin.fields.source.body',
            ])
            ->add('Author', 'sonata_type_model_autocomplete', [
                'label' => 'admin.fields.source.author',
                'property' => 'username',
            ])
            ->add('Opinion', 'sonata_type_model', [
                'label' => 'admin.fields.source.opinion',
            ])
            ->add('Category', 'sonata_type_model', [
                'label' => 'admin.fields.source.category',
            ])
            ->add('link', null, [
                'label' => 'admin.fields.source.link',
                'attr' => [
                    'placeholder' => 'http://www.cap-collectif.com/',
                ],
            ])
            ->add('expired', null, [
                'label' => 'admin.global.expired',
                'read_only' => true,
                'attr' => [
                  'disabled' => true
                ]
            ])
            ->add('isTrashed', null, [
                'label' => 'admin.fields.source.is_trashed',
                'required' => false,
            ])
            ->add('trashedReason', null, [
                'label' => 'admin.fields.source.trashed_reason',
                'required' => false,
            ])
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $subject = $this->getSubject();
        $showMapper
            ->add('title', null, [
                'label' => 'admin.fields.source.title',
            ])
            ->add('body', null, [
                'label' => 'admin.fields.source.body',
            ])
            ->add('Author', null, [
                'label' => 'admin.fields.source.author',
            ])
            ->add('Opinion', null, [
                'label' => 'admin.fields.source.opinion',
            ])
            ->add('Category', null, [
                'label' => 'admin.fields.source.category',
            ])
            ->add('link', null, [
                'label' => 'admin.fields.source.link',
            ])
            ->add('votesCount', null, [
                'label' => 'admin.fields.source.vote_count_source',
            ])
            ->add('isEnabled', null, [
                'label' => 'admin.fields.source.is_enabled',
            ])
            ->add('createdAt', null, [
                'label' => 'admin.fields.source.created_at',
            ])
            ->add('updatedAt', null, [
                'label' => 'admin.fields.source.updated_at',
            ])
            ->add('isTrashed', null, [
                'label' => 'admin.fields.source.is_trashed',
            ])
        ;
        if ($subject->getIsTrashed()) {
            $showMapper
                ->add('trashedAt', null, [
                    'label' => 'admin.fields.source.trashed_at',
                ])
                ->add('trashedReason', null, [
                    'label' => 'admin.fields.source.trashed_reason',
                ])
            ;
        }
    }

    protected function configureRoutes(RouteCollection $collection)
    {
    }
}
