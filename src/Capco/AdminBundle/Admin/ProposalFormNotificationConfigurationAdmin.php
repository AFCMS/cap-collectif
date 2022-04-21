<?php

namespace Capco\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class ProposalFormNotificationConfigurationAdmin extends Admin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('onCreate', CheckboxType::class, [
                'label' => 'admin.fields.proposal_form.notification.on_create',
                'required' => false
            ])
            ->add('onUpdate', CheckboxType::class, [
                    'label' => 'admin.fields.proposal_form.notification.on_update',
                    'required' => false
                ])
            ->add('onDelete', CheckboxType::class, [
                'label' => 'admin.fields.proposal_form.notification.on_delete',
                'required' => false
            ])
            ->end();
    }
}
