<?php

namespace Capco\AdminBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ReportingController extends Controller
{
    public function disableAction()
    {
        $id = $this->get('request')->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        $related = $object->getRelatedObject();

        if ($related != null) {
            $related->setIsEnabled(false);
            $this->get('capco.notify_manager')->notifyModeration($related);
        }

        $this->admin->update($related);

        $object->setIsArchived(true);
        $this->admin->update($object);

        $this->addFlash('sonata_flash_success', 'admin.action.reporting.disable.success');

        return new RedirectResponse($this->admin->generateUrl(
            'list',
            ['filter' => $this->admin->getFilterParameters()]
        ));
    }

    public function trashAction()
    {
        $id = $this->get('request')->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        $related = $object->getRelatedObject();

        if ($related != null) {
            $related->setIsTrashed(true);
            $this->get('capco.notify_manager')->notifyModeration($related);
        }

        $this->admin->update($related);

        $object->setIsArchived(true);
        $this->admin->update($object);

        $this->addFlash('sonata_flash_success', 'admin.action.reporting.trash.success');

        return new RedirectResponse($this->admin->generateUrl(
            'list',
            ['filter' => $this->admin->getFilterParameters()]
        ));
    }

    public function archiveAction()
    {
        $id = $this->get('request')->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        $object->setIsArchived(true);
        $this->admin->update($object);

        $this->addFlash('sonata_flash_success', 'admin.action.reporting.archive.success');

        return new RedirectResponse($this->admin->generateUrl(
            'list',
            ['filter' => $this->admin->getFilterParameters()]
        ));
    }
}
