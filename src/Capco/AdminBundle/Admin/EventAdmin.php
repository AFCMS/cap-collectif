<?php

namespace Capco\AdminBundle\Admin;

use Capco\AppBundle\Entity\Event;
use Geocoder\Provider\GoogleMaps;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Ivory\HttpAdapter\CurlHttpAdapter;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\CoreBundle\Model\Metadata;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class EventAdmin extends Admin
{
    protected $datagridValues = [
        '_sort_order' => 'DESC',
        '_sort_by' => 'updatedAt',
    ];

    public function getFeatures()
    {
        return [
            'calendar',
        ];
    }

    public function prePersist($event)
    {
        $this->setCoord($event);
        $this->checkRegistration($event);
    }

    public function preUpdate($event)
    {
        $this->setCoord($event);
        $this->checkRegistration($event);
    }

    // For mosaic view
    public function getObjectMetadata($object)
    {
        $media = $object->getMedia();
        if ($media) {
            $provider = $this->getConfigurationPool()->getContainer()->get($media->getProviderName());
            $format = $provider->getFormatName($media, 'form');
            $url = $provider->generatePublicUrl($media, $format);

            return new Metadata($object->getTitle(), $object->getBody(), $url);
        }

        return parent::getObjectMetadata($object);
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title', null, [
                'label' => 'admin.fields.event.title',
            ])
        ;

        if ($this->getConfigurationPool()->getContainer()->get('capco.toggle.manager')->isActive('themes')) {
            $datagridMapper->add('themes', null, [
                'label' => 'admin.fields.event.themes',
            ]);
        }

        $datagridMapper
            ->add('projects', null, [
                'label' => 'admin.fields.event.projects',
            ])
            ->add('Author', 'doctrine_orm_model_autocomplete', [
                'label' => 'admin.fields.event.author',
            ], null, [
                'property' => 'username',
            ])
            ->add('isEnabled', null, [
                'label' => 'admin.fields.event.is_enabled',
            ])
            ->add('isCommentable', null, [
                'label' => 'admin.fields.event.is_commentable',
            ])
            ->add('updatedAt', null, [
                'label' => 'admin.fields.event.updated_at',
            ])
            ->add('startAt', 'doctrine_orm_datetime_range', [
                'label' => 'admin.fields.event.start_at',
            ])
            ->add('endAt', 'doctrine_orm_datetime_range', [
                'label' => 'admin.fields.event.end_at',
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
                'label' => 'admin.fields.event.title',
            ])
            ->add('startAt', null, [
                'label' => 'admin.fields.event.start_at',
            ])
            ->add('endAt', null, [
                'label' => 'admin.fields.event.end_at',
            ])
        ;

        if ($this->getConfigurationPool()->getContainer()->get('capco.toggle.manager')->isActive('themes')) {
            $listMapper->add('themes', null, [
                'label' => 'admin.fields.event.themes',
            ]);
        }

        $listMapper
            ->add('projects', null, [
                'label' => 'admin.fields.event.projects',
            ])
            ->add('Author', 'sonata_type_model', [
                'label' => 'admin.fields.event.author',
            ])
            ->add('isEnabled', null, [
                'label' => 'admin.fields.event.is_enabled',
                'editable' => true,
            ])
            ->add('isCommentable', null, [
                'label' => 'admin.fields.event.is_commentable',
                'editable' => true,
            ])
            ->add('commentsCount', null, [
                'label' => 'admin.fields.event.comments_count',
            ])
            ->add('updatedAt', null, [
                'label' => 'admin.fields.event.updated_at',
            ])
            ->add('_action', 'actions', [
                'actions' => [
                    'registrations' => ['template' => 'CapcoAdminBundle:CRUD:list__action_registrations.html.twig'],
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
        // define group zoning
        $formMapper
            ->with('admin.fields.event.group_event', ['class' => 'col-md-12'])->end()
            ->with('admin.fields.event.group_meta', ['class' => 'col-md-6'])->end()
            ->with('admin.fields.event.group_address', ['class' => 'col-md-6'])->end()
            ->end()
        ;

        $formMapper
            ->with('admin.fields.event.group_event')
            ->add('title', null, [
                'label' => 'admin.fields.event.title',
            ])
            ->add('body', CKEditorType::class, [
                'label' => 'admin.fields.event.body',
                'config_name' => 'admin_editor',
            ])
            ->add('Author', 'sonata_type_model_autocomplete', [
                'label' => 'admin.fields.event.author',
                'property' => 'username',
            ])
            ->add('startAt', 'sonata_type_datetime_picker', [
                'label' => 'admin.fields.event.start_at',
                'format' => 'dd/MM/yyyy HH:mm',
                'attr' => [
                    'data-date-format' => 'DD/MM/YYYY HH:mm',
                ],
            ])
            ->add('endAt', 'sonata_type_datetime_picker', [
                'label' => 'admin.fields.event.end_at',
                'format' => 'dd/MM/yyyy HH:mm',
                'attr' => [
                    'data-date-format' => 'DD/MM/YYYY HH:mm',
                ],
                'help' => 'admin.help.event.endAt',
                'required' => false,
            ])
            ->end()
            ->with('admin.fields.event.group_meta')
            ->add('registrationEnable', null, [
                  'label' => 'admin.fields.event.registration_enable',
                  'required' => false,
            ])
            ->add('link', UrlType::class, [
                'label' => 'admin.fields.event.link',
                'required' => false,
                'attr' => [
                    'placeholder' => 'http://',
                ],
            ])
            ->add('Media', 'sonata_type_model_list', [
                'label' => 'admin.fields.event.media',
                'required' => false,
            ], [
                'link_parameters' => [
                    'context' => 'default',
                    'hide_context' => true,
                    'provider' => 'sonata.media.provider.image',
                ],
            ])
            ->end();

        if ($this->getConfigurationPool()->getContainer()->get('capco.toggle.manager')->isActive('themes')) {
            $formMapper->add('themes', 'sonata_type_model', [
                'label' => 'admin.fields.event.themes',
                'required' => false,
                'multiple' => true,
                'by_reference' => false,
                'choices_as_values' => true,
            ]);
        }

        $formMapper
            ->add('projects', 'sonata_type_model', [
                'label' => 'admin.fields.event.projects',
                'required' => false,
                'multiple' => true,
                'by_reference' => false,
                'choices_as_values' => true,
            ])
            ->add('isEnabled', null, [
                'label' => 'admin.fields.event.is_enabled',
                'required' => false,
            ])
            ->add('isCommentable', null, [
                'label' => 'admin.fields.event.is_commentable',
                'required' => false,
            ])
            ->end()
            ->with('admin.fields.event.group_address')
            ->add('address', null, [
                'label' => 'admin.fields.event.address',
                'required' => false,
                'help' => 'admin.help.event.adress',
            ])
            ->add('zipCode', NumberType::class, [
                'label' => 'admin.fields.event.zipcode',
                'required' => false,
            ])
            ->add('city', null, [
                'label' => 'admin.fields.event.city',
                'required' => false,
            ])
            ->add('country', null, [
                'label' => 'admin.fields.event.country',
                'required' => false,
            ])
            ->end()
        ;
        $formMapper
            ->with('admin.fields.page.advanced')
            ->add('metaDescription', null, [
                'label' => 'event.metadescription',
                'required' => false,
                'help' => 'admin.help.metadescription',
            ])
            ->add('customCode', null, [
                'label' => 'admin.customcode',
                'required' => false,
                'help' => 'admin.help.customcode',
                'attr' => ['rows' => 10, 'placeholder' => '<script type="text/javascript"> </script>'],
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
                'label' => 'admin.fields.event.title',
            ])
            ->add('body', null, [
                'label' => 'admin.fields.event.body',
            ])
            ->add('startAt', null, [
                'label' => 'admin.fields.event.start_at',
            ])
            ->add('endAt', null, [
                'label' => 'admin.fields.event.end_at',
            ])
        ;

        if ($this->getConfigurationPool()->getContainer()->get('capco.toggle.manager')->isActive('themes')) {
            $showMapper->add('themes', null, [
                'label' => 'admin.fields.event.themes',
            ]);
        }

        $showMapper
            ->add('project', null, [
                'label' => 'admin.fields.event.project',
            ])
            ->add('Author', null, [
                'label' => 'admin.fields.event.author',
            ])
            ->add('Media', 'sonata_media_type', [
                'template' => 'CapcoAdminBundle:Event:media_show_field.html.twig',
                'provider' => 'sonata.media.provider.image',
                'label' => 'admin.fields.event.media',
            ])
            ->add('isEnabled', null, [
                'label' => 'admin.fields.event.is_enabled',
            ])
            ->add('isCommentable', null, [
                'label' => 'admin.fields.event.is_commentable',
            ])
            ->add('commentsCount', null, [
                'label' => 'admin.fields.event.comments_count',
            ])
            ->add('updatedAt', null, [
                'label' => 'admin.fields.event.updated_at',
            ])
            ->add('createdAt', null, [
                'label' => 'admin.fields.event.created_at',
            ])
            ->add('address', null, [
                'label' => 'admin.fields.event.address',
            ])
            ->add('zipCode', 'number', [
                'label' => 'admin.fields.event.zipcode',
            ])
            ->add('city', null, [
                'label' => 'admin.fields.event.city',
            ])
            ->add('country', null, [
                'label' => 'admin.fields.event.country',
            ])
            ->add('lat', null, [
                'label' => 'admin.fields.event.lat',
            ])
            ->add('lng', null, [
                'label' => 'admin.fields.event.lng',
            ])

        ;
    }

    private function checkRegistration($event)
    {
        if ($event->getLink()) {
            $event->setRegistrationEnable(false);
        }
    }

    private function setCoord(Event $event)
    {
        if (!$event->getAddress() || !$event->getCity()) {
            $event->setLat(null);
            $event->setLng(null);

            return;
        }

        $apiKey = $this->getConfigurationPool()->getContainer()->getParameter('google_maps_key_server');
        $curl = new CurlHttpAdapter();
        $geocoder = new GoogleMaps($curl, null, null, true, $apiKey);

        $address = $event->getAddress() . ', ' . $event->getZipCode() . ' ' . $event->getCity() . ', ' . $event->getCountry();

        $coord = $geocoder->geocode($address)->first()->getCoordinates();

        $event->setLat($coord->getLatitude());
        $event->setLng($coord->getLongitude());
    }
}
