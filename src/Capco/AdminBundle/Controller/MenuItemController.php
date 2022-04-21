<?php

namespace Capco\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MenuItemController extends Controller
{
    /**
     * @param array $selectedIds
     * @param $allEntitiesSelected
     *
     * @return bool|string
     */
    public function batchActionDeleteIsRelevant(array $selectedIds, $allEntitiesSelected)
    {
        foreach ($selectedIds as $id) {
            $item = $this->container->get('doctrine.orm.entity_manager')->getRepository('CapcoAppBundle:MenuItem')->find($id);
            if (!$item->getIsDeletable()) {
                return 'admin.action.menu_item.batch_delete.denied';
            }
        }

        return true;
    }

    /**
     * Delete action.
     *
     * @param int|string|null $id
     * @param Request         $request
     *
     * @return Response|RedirectResponse
     *
     * @throws NotFoundHttpException If the object does not exist
     * @throws AccessDeniedException If access is not granted
     */
    public function deleteAction($id, Request $request = null)
    {
        $id = $this->get('request')->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);

        if (!$object->getIsDeletable()) {
            throw new AccessDeniedException();
        }

        return parent::deleteAction($id, $request);
    }
}
