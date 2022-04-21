<?php

namespace Capco\AdminBundle\Admin;

use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Capco\AppBundle\Entity\Theme;
use Sonata\CoreBundle\Model\Metadata;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ThemeAdmin extends Admin
{
    protected $datagridValues = [
        '_sort_order' => 'ASC',
        '_sort_by' => 'position',
    ];

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title', null, [
                'label' => 'admin.fields.theme.title',
            ])
            ->add('position', null, [
                'label' => 'admin.fields.theme.position',
            ])
            ->add('isEnabled', null, [
                'label' => 'admin.fields.theme.is_enabled',
            ])
            ->add('projects', null, [
                'label' => 'admin.fields.theme.projects',
            ])
            ->add('ideas', null, [
                'label' => 'admin.fields.theme.ideas',
            ])
            ->add('events', null, [
                'label' => 'admin.fields.theme.events',
            ])
            ->add('posts', null, [
                'label' => 'admin.fields.theme.posts',
            ])
            ->add('createdAt', null, [
                'label' => 'admin.fields.theme.created_at',
            ])
            ->add('updatedAt', null, [
                'label' => 'admin.fields.theme.updated_at',
            ])
            ->add('Author', 'doctrine_orm_model_autocomplete', [
                'label' => 'admin.fields.theme.author',
            ], null, [
                'property' => 'username',
            ])
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title', null, [
                'label' => 'admin.fields.theme.title',
            ])
            ->add('Author', null, [
                'label' => 'admin.fields.theme.author',
            ])
            ->add('status', null, [
                'label' => 'admin.fields.theme.status',
                'template' => 'CapcoAdminBundle:Theme:status_list_field.html.twig',
                'statusesLabels' => Theme::$statusesLabels,
            ])
            ->add('position', null, [
                'label' => 'admin.fields.theme.position',
            ])
            ->add('ideasCount', null, [
                'label' => 'admin.fields.theme.ideas_count',
                'template' => 'CapcoAdminBundle:Theme:ideas_count_list_field.html.twig',
            ])
            ->add('projectsCount', null, [
                'label' => 'admin.fields.theme.projects_count',
                'template' => 'CapcoAdminBundle:Theme:projects_count_list_field.html.twig',
            ])
            ->add('eventsCount', null, [
                'label' => 'admin.fields.theme.events_count',
                'template' => 'CapcoAdminBundle:Theme:events_count_list_field.html.twig',
            ])
            ->add('postsCount', null, [
                'label' => 'admin.fields.theme.posts_count',
                'template' => 'CapcoAdminBundle:Theme:posts_count_list_field.html.twig',
            ])
            ->add('isEnabled', null, [
                'editable' => true,
                'label' => 'admin.fields.theme.is_enabled',
            ])
            ->add('updatedAt', 'datetime', [
                'label' => 'admin.fields.theme.updated_at',
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
                'label' => 'admin.fields.theme.title',
                'required' => true,
            ])
            ->add('Author', 'sonata_type_model_autocomplete', [
                'label' => 'admin.fields.theme.author',
                'property' => 'username',
            ])
            ->add('isEnabled', null, [
                'label' => 'admin.fields.theme.is_enabled',
                'required' => false,
            ])
            ->add('position', IntegerType::class, [
                'label' => 'admin.fields.theme.position',
            ])
            ->add('teaser', TextareaType::class, [
                'label' => 'admin.fields.theme.teaser',
                'required' => false,
            ])
            ->add('body', CKEditorType::class, [
                'label' => 'admin.fields.theme.body',
                'config_name' => 'admin_editor',
                'required' => false,
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'admin.fields.theme.status',
                'choices' => Theme::$statusesLabels,
                'translation_domain' => 'CapcoAppBundle',
                'required' => false,
                'empty_value' => 'admin.fields.theme.no_status',
            ])
            ->add('Media', 'sonata_type_model_list', [
                'required' => false,
                'label' => 'admin.fields.theme.media',
            ], [
                'link_parameters' => [
                    'context' => 'default',
                    'hide_context' => true,
                    'provider' => 'sonata.media.provider.image',
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
                'label' => 'admin.fields.theme.title',
            ])
            ->add('isEnabled', null, [
                'label' => 'admin.fields.theme.is_enabled',
            ])
            ->add('teaser', null, [
                'label' => 'admin.fields.theme.teaser',
            ])
            ->add('body', null, [
                'label' => 'admin.fields.theme.body',
            ])
            ->add('status', null, [
                'label' => 'admin.fields.theme.status',
                'template' => 'CapcoAdminBundle:Theme:status_show_field.html.twig',
                'statusesLabels' => Theme::$statusesLabels,
            ])
            ->add('position', null, [
                'label' => 'admin.fields.theme.position',
            ])
            ->add('Author', null, [
                'label' => 'admin.fields.theme.author',
            ])
            ->add('projects', null, [
                'label' => 'admin.fields.theme.projects',
            ])
            ->add('ideas', null, [
                'label' => 'admin.fields.theme.ideas',
            ])
            ->add('events', null, [
                'label' => 'admin.fields.theme.events',
            ])
            ->add('posts', null, [
                'label' => 'admin.fields.theme.posts',
            ])
            ->add('Media', null, [
                'template' => 'CapcoAdminBundle:Theme:media_show_field.html.twig',
                'label' => 'admin.fields.theme.media',
            ])
            ->add('createdAt', null, [
                'label' => 'admin.fields.theme.created_at',
            ])
            ->add('updatedAt', null, [
                'label' => 'admin.fields.theme.updated_at',
            ])
        ;
    }

    // For mosaic view
    public function getObjectMetadata($object)
    {
        $media = $object->getMedia();
        if ($media != null) {
            $provider = $this->getConfigurationPool()->getContainer()->get($media->getProviderName());
            $format = $provider->getFormatName($media, 'form');
            $url = $provider->generatePublicUrl($media, $format);

            return new Metadata($object->getTitle(), $object->getBody(), $url);
        }

        return parent::getObjectMetadata($object);
    }

    public function getFeatures()
    {
        return [
            'themes',
        ];
    }
}
