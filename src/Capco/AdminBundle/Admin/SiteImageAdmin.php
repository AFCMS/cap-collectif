<?php

namespace Capco\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\CoreBundle\Model\Metadata;

class SiteImageAdmin extends Admin
{
    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('isEnabled', null, array(
                'label' => 'admin.fields.site_image.is_enabled',
                'required' => false,
            ))
            ->add('Media', 'sonata_type_model_list', array(
                'required' => false,
                'label' => 'admin.fields.site_image.media',
            ), array(
                'link_parameters' => array(
                    'context' => 'default',
                    'hide_context' => true,
                    'provider' => 'sonata.media.provider.image',
            ), ))
        ;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(['edit']);
    }

    // For mosaic view
    public function getObjectMetadata($object)
    {
        $media = $object->getMedia();
        if ($media != null) {
            $provider = $this->getConfigurationPool()->getContainer()->get($media->getProviderName());
            $format = $provider->getFormatName($media, 'form');
            $url = $provider->generatePublicUrl($media, $format);

            return new Metadata($object->getTitle(), null, $url);
        }

        return parent::getObjectMetadata($object);
    }
}
