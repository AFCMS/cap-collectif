<?php

namespace Capco\AdminBundle\Admin;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Capco\AppBundle\Entity\FooterSocialNetwork;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Capco\AppBundle\Repository\FooterSocialNetworkRepository;

class FooterSocialNetworkAdmin extends AbstractAdmin
{
    protected $classnameLabel = 'social_network';
    protected $datagridValues = [
        '_sort_order' => 'ASC',
        '_sort_by' => 'title',
    ];

    public function postUpdate($object)
    {
        $entityManager = $this->getConfigurationPool()
            ->getContainer()
            ->get('doctrine.orm.entity_manager');
        $cacheDriver = $entityManager->getConfiguration()->getResultCacheImpl();
        $cacheDriver->delete(FooterSocialNetworkRepository::getEnabledCacheKey());
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
            ->add('style', null, [
                'label' => 'admin.fields.footer_social_network.style',
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
        unset($this->listModes['mosaic']);

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
            ->add('style', 'string', [
                'template' => 'CapcoAdminBundle:FooterSocialNetwork:style_list_field.html.twig',
                'label' => 'admin.fields.footer_social_network.style',
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
            ->add('style', ChoiceType::class, [
                'choices' => FooterSocialNetwork::$socialIcons,
                'label' => 'admin.fields.footer_social_network.style',
            ])
            ->add('position', null, [
                'label' => 'global.position',
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
            ->add('style', null, [
                'template' => 'CapcoAdminBundle:FooterSocialNetwork:style_show_field.html.twig',
                'label' => 'admin.fields.footer_social_network.style',
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
}
