<?php

namespace Capco\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class CategoryAdmin extends Admin
{
    protected $datagridValues = [
        '_sort_order' => 'ASC',
        '_sort_by'    => 'title',
    ];

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title', null, [
                'label' => 'admin.fields.category.title',
            ])
            ->add('Sources', null, [
                'label' => 'admin.fields.category.sources',
            ])
            ->add('isEnabled', null, [
                'editable' => true,
                'label'    => 'admin.fields.category.is_enabled',
            ])
            ->add('createdAt', null, [
                'label' => 'admin.fields.category.created_at',
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
                'label' => 'admin.fields.category.title',
            ])
            ->add('isEnabled', null, [
                'editable' => true,
                'label'    => 'admin.fields.category.is_enabled',
            ])
            ->add('updatedAt', null, [
                'label' => 'admin.fields.category.updated_at',
            ])
            ->add('_action', 'actions', [
                'actions' => [
                    'show'   => [],
                    'edit'   => [],
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
                'label' => 'admin.fields.category.title',
            ])
            ->add('isEnabled', null, [
                'label'    => 'admin.fields.category.is_enabled',
                'required' => false,
            ])
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('title', null, [
                'label' => 'admin.fields.category.title',
            ])
            ->add('isEnabled', null, [
                'editable' => true,
                'label'    => 'admin.fields.category.is_enabled',
            ])
            ->add('updatedAt', null, [
                'label' => 'admin.fields.category.updated_at',
            ])
            ->add('createdAt', null, [
                'label' => 'admin.fields.category.created_at',
            ])
        ;
    }
}
