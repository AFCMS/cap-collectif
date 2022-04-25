<?php

namespace Capco\AdminBundle\Controller;

use Capco\UserBundle\Security\Exception\ProjectAccessDeniedException;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyPath;

class CommentController extends CRUDController
{
    // same as Sonata\AdminBundle\Controller\CRUDController:deleteAction
    // But we check if we are super admin or author
    public function deleteAction($id)
    {
        $request = $this->getRequest();
        $id = $request->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);

        if (
            (\is_object($this->getUser()) && !$this->getUser()->isSuperAdmin()) ||
            $object->getAuthor() != $this->getUser()
        ) {
            throw new ProjectAccessDeniedException();
        }

        if (!$object) {
            throw $this->createNotFoundException(
                sprintf('unable to find the object with id: %s', $id)
            );
        }

        $this->checkParentChildAssociation($request, $object);

        $this->admin->checkAccess('delete', $object);

        $preResponse = $this->preDelete($request, $object);
        if (null !== $preResponse) {
            return $preResponse;
        }

        if ('DELETE' == $this->getRestMethod()) {
            // check the csrf token
            $this->validateCsrfToken('sonata.delete');

            $objectName = $this->admin->toString($object);

            try {
                $this->admin->delete($object);

                if ($this->isXmlHttpRequest()) {
                    return $this->renderJson(['result' => 'ok'], 200, []);
                }

                $this->addFlash(
                    'sonata_flash_success',
                    $this->trans(
                        'success.delete.flash',
                        ['name' => $this->escapeHtml($objectName)],
                        'SonataAdminBundle'
                    )
                );
            } catch (ModelManagerException $e) {
                $this->handleModelManagerException($e);

                if ($this->isXmlHttpRequest()) {
                    return $this->renderJson(['result' => 'error'], 200, []);
                }

                $this->addFlash(
                    'sonata_flash_error',
                    $this->trans(
                        'error.delete.flash',
                        ['name' => $this->escapeHtml($objectName)],
                        'SonataAdminBundle'
                    )
                );
            }

            return $this->redirectTo($object);
        }

        $template = $this->get('sonata.admin.global_template_registry')->getTemplate('delete');

        return $this->renderWithExtraParams(
            $template,
            [
                'object' => $object,
                'action' => 'delete',
                'csrf_token' => $this->getCsrfToken('sonata.delete')
            ],
            null
        );
    }
}
