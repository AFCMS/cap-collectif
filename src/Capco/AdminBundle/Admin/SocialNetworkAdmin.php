<?php

namespace Capco\AdminBundle\Admin;

use Capco\MediaBundle\Provider\MediaProvider;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\AdminBundle\Form\Type\ModelListType;

class SocialNetworkAdmin extends AbstractAdmin
{
    protected $classnameLabel = 'social_network';
    protected $datagridValues = [
        '_sort_order' => 'ASC',
        '_sort_by' => 'title',
    ];

    private MediaProvider $mediaProvider;

    public function __construct($code, $class, $baseControllerName, MediaProvider $mediaProvider)
    {
        parent::__construct($code, $class, $baseControllerName);
        $this->mediaProvider = $mediaProvider;
    }

    // For mosaic view
    public function getObjectMetadata($object)
    {
        $media = $object->getMedia();
        if ($media) {
            return new Metadata(
                $object->getTitle(),
                null,
                $this->mediaProvider->generatePublicUrl(
                    $media,
                    $this->mediaProvider->getFormatName($media, 'form')
                )
            );
        }

        return parent::getObjectMetadata($object);
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title', null, [
                'label' => 'global.title',
            ])
            ->add('isEnabled', null, [
                'label' => 'global.published',
            ])
            ->add('link', null, [
                'label' => 'global.link',
            ])
            ->add('position', null, [
                'label' => 'global.position',
            ])
            ->add('updatedAt', null, [
                'label' => 'global.maj',
            ]);
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title', null, [
                'label' => 'global.title',
            ])
            ->add('isEnabled', null, [
                'editable' => true,
                'label' => 'global.published',
            ])
            ->add('link', null, [
                'label' => 'global.link',
            ])
            ->add('media', null, [
                'template' => 'CapcoAdminBundle:SocialNetwork:media_list_field.html.twig',
                'label' => 'global.image',
            ])
            ->add('position', null, [
                'label' => 'global.position',
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
        $formMapper
            ->add('title', null, [
                'label' => 'global.title',
            ])
            ->add('isEnabled', null, [
                'label' => 'global.published',
                'required' => false,
            ])
            ->add('link', null, [
                'label' => 'global.link',
            ])
            ->add('position', null, [
                'label' => 'global.position',
            ])
            ->add('media', ModelListType::class, [
                'required' => false,
                'label' => 'global.image',
            ]);
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
            ->add('link', null, [
                'label' => 'global.link',
            ])
            ->add('media', null, [
                'template' => 'CapcoAdminBundle:SocialNetwork:media_show_field.html.twig',
                'label' => 'global.image',
            ])
            ->add('position', null, [
                'label' => 'global.position',
            ])
            ->add('createdAt', null, [
                'label' => 'global.creation',
            ])
            ->add('updatedAt', null, [
                'label' => 'global.maj',
            ]);
    }

    protected function configureRoutes(RouteCollection $collection)
    {
    }
}
