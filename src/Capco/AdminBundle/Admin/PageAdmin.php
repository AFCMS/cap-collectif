<?php

// src/Acme/DemoBundle/Admin/PostAdmin.php

namespace Capco\AdminBundle\Admin;

use Capco\AppBundle\Form\Type\PurifiedTextType;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
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
            ->add('slug', null, [
                'label' => 'admin.fields.page.slug',
                'attr' => [
                    'read-only' => true,
                    'disabled' => true,
                ],
            ])
            ->add('body', CKEditorType::class, [
                'label' => 'admin.fields.page.body',
                'config_name' => 'admin_editor',
            ])
            ->add('isEnabled', null, [
                'label' => 'admin.fields.page.is_enabled',
                'required' => false,
            ])->end();
        $formMapper
            ->with('admin.fields.page.advanced')
                ->add('metaDescription', PurifiedTextType::class, [
                    'label' => 'page.metadescription',
                    'required' => false,
                    'help' => 'admin.help.metadescription',
                ])
                ->add('cover', 'sonata_type_model_list', [
                    'required' => false,
                    'label' => 'admin.fields.project.cover',
                    'help' => 'admin.help.social_network_thumbnail',
                ], [
                    'link_parameters' => [
                        'context' => 'default',
                        'hide_context' => true,
                        'provider' => 'sonata.media.provider.image',
                    ],
                ])
                ->add('customCode', null, [
                    'label' => 'admin.customcode',
                    'required' => false,
                    'help' => 'admin.help.customcode',
                    'attr' => ['rows' => 10, 'placeholder' => '<script type="text/javascript"> </script>'],
                ])
                ->end();
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
            ->add('slug', null, [
                'label' => 'admin.fields.page.slug',
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
            ->add('cover', null, [
                'template' => 'CapcoAdminBundle:Page:cover_show_field.html.twig',
                'label' => 'admin.fields.page.cover',
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
