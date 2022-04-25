<?php

namespace Capco\AdminBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\UserBundle\Admin\Model\UserAdmin as BaseAdmin;

class UserAdmin extends BaseAdmin
{
    protected $datagridValues = ['_sort_order' => 'DESC', '_sort_by' => 'updatedAt'];

    public function getBatchActions()
    {
        // have to get at least one batch action to display number of users
        $actions = parent::getBatchActions();
        $actions['nothing'] = [
            'label' => '',
            'translation_domain' => 'SonataAdminBundle',
            'ask_confirmation' => false,
        ];

        return $actions;
    }

    public function getTemplate($name)
    {
        if ('list' === $name) {
            return 'CapcoAdminBundle:User:list.html.twig';
        }

        return $this->getTemplateRegistry()->getTemplate($name);
    }

    public function getExportFormats()
    {
        return ['csv'];
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        unset($this->listModes['mosaic']);
        $listMapper
            ->addIdentifier('username', null, [
                'label' => 'global.fullname',
                'template' => 'CapcoAdminBundle:User:username_list_field.html.twig',
            ])
            ->add('email')
            ->add('roles', null, [
                'label' => 'global.role',
                'template' => 'CapcoAdminBundle:User:roles_list_field.html.twig',
            ])
            ->add('enabled', null)
            ->add('isEmailConfirmed', null, [
                'label' => 'confirmed-by-email',
                'template' => 'CapcoAdminBundle:User:email_confirmed_list_field.html.twig',
            ])
            ->add('locked', null, ['editable' => true])
            ->add('createdAt', null, ['label' => 'global.creation'])
            ->add('deletedAccountAt', null, ['label' => 'admin.fields.proposal.deleted_at']);
    }

    protected function configureDatagridFilters(DatagridMapper $filterMapper): void
    {
        $filterMapper
            ->add('id')
            ->add('username')
            ->add('email')
            ->add('confirmationToken')
            ->add('locked')
            ->add('phone', null, ['translation_domain' => 'CapcoAppBundle']);
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('exportLegacyUsers', 'export_legacy_users');

        $collection->clearExcept(['batch', 'list', 'edit', 'export', 'exportLegacyUsers']);
    }
}
