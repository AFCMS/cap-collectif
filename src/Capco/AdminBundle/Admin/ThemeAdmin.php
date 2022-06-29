<?php

namespace Capco\AdminBundle\Admin;

use Capco\AppBundle\Elasticsearch\Indexer;
use Capco\AppBundle\Entity\Theme;
use Capco\AppBundle\Filter\KnpTranslationFieldFilter;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\DoctrineORMAdminBundle\Filter\ModelAutocompleteFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Form\Type\ModelListType;

class ThemeAdmin extends AbstractAdmin
{
    protected $classnameLabel = 'theme';
    protected $datagridValues = [
        '_sort_order' => 'ASC',
        '_sort_by' => 'position',
    ];
    private $indexer;

    public function __construct(
        string $code,
        string $class,
        string $baseControllerName,
        Indexer $indexer
    ) {
        $this->indexer = $indexer;
        parent::__construct($code, $class, $baseControllerName);
    }

    // For mosaic view
    public function getObjectMetadata($object)
    {
        $media = $object->getMedia();
        if ($media) {
            $provider = $this->getConfigurationPool()
                ->getContainer()
                ->get($media->getProviderName());
            $format = $provider->getFormatName($media, 'form');
            $url = $provider->generatePublicUrl($media, $format);

            return new Metadata($object->getTitle(), $object->getBody(), $url);
        }

        return parent::getObjectMetadata($object);
    }

    public function getFeatures()
    {
        return ['themes'];
    }

    public function preRemove($object)
    {
        $this->indexer->remove(\get_class($object), $object->getId());
        $this->indexer->finishBulk();
        parent::preRemove($object); // TODO: Change the autogenerated stub
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title', KnpTranslationFieldFilter::class, [
                'label' => 'global.title',
            ])
            ->add('position', null, [
                'label' => 'global.position',
            ])
            ->add('isEnabled', null, [
                'label' => 'global.published',
            ])
            ->add('projects', null, [
                'label' => 'global.participative.project',
            ])
            ->add('events', null, [
                'label' => 'global.events',
            ])
            ->add('posts', null, [
                'label' => 'global.articles',
            ])
            ->add('createdAt', null, [
                'label' => 'global.creation',
            ])
            ->add('updatedAt', null, [
                'label' => 'global.maj',
            ])
            ->add(
                'author',
                ModelAutocompleteFilter::class,
                [
                    'label' => 'global.author',
                ],
                null,
                [
                    'property' => 'email,username',
                    'to_string_callback' => function ($entity, $property) {
                        return $entity->getEmail() . ' - ' . $entity->getUsername();
                    },
                ]
            );
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title', null, [
                'label' => 'global.title',
            ])
            ->add('author', null, [
                'label' => 'global.author',
            ])
            ->add('status', null, [
                'label' => 'global.status',
                'template' => 'CapcoAdminBundle:Theme:status_list_field.html.twig',
                'statusesLabels' => Theme::$statusesLabels,
            ])
            ->add('position', null, [
                'label' => 'global.position',
            ])
            ->add('eventsCount', null, [
                'label' => 'global.events',
                'template' => 'CapcoAdminBundle:Theme:events_count_list_field.html.twig',
            ])
            ->add('postsCount', null, [
                'label' => 'global.articles',
                'template' => 'CapcoAdminBundle:Theme:posts_count_list_field.html.twig',
            ])
            ->add('isEnabled', null, [
                'editable' => true,
                'label' => 'global.published',
            ])
            ->add('updatedAt', 'datetime', [
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
        $editMode = $this->getSubject() && $this->getSubject()->getId() ? true : false;

        $formMapper->add('title', TextType::class, [
            'label' => 'global.title',
            'required' => true,
        ]);

        if ($editMode) {
            $formMapper->add('slug', TextType::class, [
                'disabled' => true,
                'attr' => ['readonly' => true],
                'label' => 'global.link',
            ]);
        }
        $formMapper
            ->add('author', ModelAutocompleteType::class, [
                'label' => 'global.author',
                'property' => 'username,email',
                'to_string_callback' => function ($entity, $property) {
                    return $entity->getEmail() . ' - ' . $entity->getUsername();
                },
            ])
            ->add('isEnabled', null, [
                'label' => 'global.published',
                'required' => false,
            ])
            ->add('position', IntegerType::class, [
                'label' => 'global.position',
            ])
            ->add('teaser', TextareaType::class, [
                'label' => 'global.subtitle',
                'required' => false,
            ])
            ->add('body', CKEditorType::class, [
                'label' => 'global.description',
                'config_name' => 'admin_editor',
                'required' => false,
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'global.status',
                'choices' => Theme::$statusesLabels,
                'translation_domain' => 'CapcoAppBundle',
                'required' => false,
                'placeholder' => 'global.no_status',
            ])
            ->add(
                'media',
                ModelListType::class,
                [
                    'required' => false,
                    'label' => 'global.image',
                ],
                [
                    'link_parameters' => [
                        'context' => 'default',
                        'hide_context' => true,
                        'provider' => 'sonata.media.provider.image',
                    ],
                ]
            )
            ->end();
        $formMapper
            ->with('admin.fields.page.advanced')
            ->add('metaDescription', TextType::class, [
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

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('title', null, [
                'label' => 'global.title',
            ])
            ->add('isEnabled', null, [
                'label' => 'global.published',
            ])
            ->add('teaser', null, [
                'label' => 'global.subtitle',
            ])
            ->add('body', null, [
                'label' => 'global.description',
            ])
            ->add('status', null, [
                'label' => 'global.status',
                'template' => 'CapcoAdminBundle:Theme:status_show_field.html.twig',
                'statusesLabels' => array_flip(Theme::$statusesLabels),
            ])
            ->add('position', null, [
                'label' => 'global.position',
            ])
            ->add('author', null, [
                'label' => 'global.author',
            ])
            ->add('projects', null, [
                'label' => 'global.participative.project',
            ])
            ->add('events', null, [
                'label' => 'global.events',
            ])
            ->add('posts', null, [
                'label' => 'global.articles',
            ])
            ->add('media', null, [
                'template' => 'CapcoAdminBundle:Theme:media_show_field.html.twig',
                'label' => 'global.image',
            ])
            ->add('createdAt', null, [
                'label' => 'global.creation',
            ])
            ->add('updatedAt', null, [
                'label' => 'global.maj',
            ]);
    }
}
