<?php

// src/Acme/DemoBundle/Admin/PostAdmin.php


namespace Capco\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class PageAdmin extends Admin
{
    protected $datagridValues = [
        '_sort_order' => 'ASC',
        '_sort_by' => 'title',
    ];

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('title', null, [
                'label' => 'admin.fields.page.title',
            ])
            ->add('body', 'ckeditor', [
                'label' => 'admin.fields.page.body',
                'config_name' => 'admin_editor',
            ])
            ->add('isEnabled', null, [
                'label' => 'admin.fields.page.is_enabled',
                'required' => false,
            ])
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title', null, [
                'label' => 'admin.fields.page.title',
            ])
            ->add('isEnabled', null, [
                'label' => 'admin.fields.page.is_enabled',
            ])
            ->add('MenuItems', null, [
                'label' => 'admin.fields.page.menu_items',
            ])
            ->add('updatedAt', null, [
                'label' => 'admin.fields.page.updated_at',
            ])
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        unset($this->listModes['mosaic']);

        $listMapper
            ->addIdentifier('title', null, [
                'label' => 'admin.fields.page.title',
            ])
            ->add('isEnabled', null, [
                'editable' => true,
                'label' => 'admin.fields.page.is_enabled',
            ])
            ->add('MenuItems', null, [
                'label' => 'admin.fields.page.menu_items',
            ])
            ->add('updatedAt', null, [
                'label' => 'admin.fields.page.updated_at',
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
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('title', null, [
                'label' => 'admin.fields.page.title',
            ])
            ->add('isEnabled', null, [
                'label' => 'admin.fields.page.is_enabled',
            ])
            ->add('body', null, [
                'label' => 'admin.fields.page.body',
            ])
            ->add('URL', null, [
                'template' => 'CapcoAdminBundle:Page:url_show_field.html.twig',
                'label' => 'admin.fields.page.url',
            ])
            ->add('MenuItems', null, [
                'label' => 'admin.fields.page.menu_items',
            ])
            ->add('updatedAt', null, [
                'label' => 'admin.fields.page.updated_at',
            ])
            ->add('createdAt', null, [
                'label' => 'admin.fields.page.created_at',
            ])
        ;
    }
}
