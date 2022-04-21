<?php

namespace Capco\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class ArgumentVoteAdmin extends Admin
{
    protected $datagridValues = [
        '_sort_order' => 'ASC',
        '_sort_by' => 'argument.title',
    ];

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('createdAt', null, [
                'label' => 'admin.fields.opinion_vote.created_at',
            ])
            ->add('argument', null, [
                'label' => 'admin.fields.argument_vote.argument',
            ])
            ->add('user', 'doctrine_orm_model_autocomplete', [
                'label' => 'admin.fields.argument_vote.voter',
            ], null, [
                'property' => 'username',
            ])
            ->add('expired', null, [ 'label' => 'admin.global.expired' ])
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        unset($this->listModes['mosaic']);

        $listMapper
            ->add('argument', 'sonata_type_model', [
                'label' => 'admin.fields.argument_vote.argument',
            ])
            ->add('user', 'sonata_type_model', [
                'label' => 'admin.fields.argument_vote.voter',
            ])
            ->add('createdAt', null, [
                'label' => 'admin.fields.opinion_vote.created_at',
            ])
            ->add('_action', 'actions', [
                'actions' => [
                    'show' => [],
                ],
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $currentUser = $this->getConfigurationPool()->getContainer()->get('security.token_storage')->getToken()->getUser();
        $showMapper
            ->add('argument', 'sonata_type_model', [
                'label' => 'admin.fields.argument_vote.argument',
            ])
            ->add('user', 'sonata_type_model', [
                'label' => 'admin.fields.argument_vote.voter',
            ])
            ->add('expired', null, [
                'label' => 'admin.global.expired',
                'read_only' => !$currentUser->hasRole('ROLE_SUPER_ADMIN'),
                'attr' => [
                  'disabled' => !$currentUser->hasRole('ROLE_SUPER_ADMIN'),
                ],
            ])
            ->add('createdAt', null, [
                'label' => 'admin.fields.argument_vote.created_at',
            ])
        ;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
        $collection->remove('create');
        $collection->remove('edit');
    }
}
