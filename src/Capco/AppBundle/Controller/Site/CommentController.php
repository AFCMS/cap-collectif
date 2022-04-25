<?php

namespace Capco\AppBundle\Controller\Site;

use Capco\AppBundle\CapcoAppBundleEvents;
use Capco\AppBundle\Entity\Comment;
use Capco\AppBundle\Event\CommentChangedEvent;
use Capco\AppBundle\Form\CommentType as CommentForm;
use Capco\AppBundle\GraphQL\DataLoader\Commentable\CommentableCommentsDataLoader;
use Capco\AppBundle\Manager\CommentResolver;
use Capco\UserBundle\Security\Exception\ProjectAccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends Controller
{
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @Route("/comments/{commentId}/edit", name="app_comment_edit")
     * @Template("CapcoAppBundle:Comment:update.html.twig")
     * @Entity("comment", class="CapcoAppBundle:Comment", options={"mapping" = {"commentId": "id"}, "repository_method"= "find", "map_method_signature" = true})
     * @Security("has_role('ROLE_USER')")
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws ProjectAccessDeniedException
     */
    public function updateCommentAction(Request $request, Comment $comment)
    {
        if (false === $comment->canContribute($this->getUser())) {
            throw new ProjectAccessDeniedException(
                $this->get('translator')->trans('comment.error.no_contribute', [], 'CapcoAppBundle')
            );
        }

        $userCurrent = $this->getUser();
        $userPostComment = $comment->getAuthor();

        if ($userCurrent !== $userPostComment) {
            throw new ProjectAccessDeniedException(
                $this->get('translator')->trans('comment.error.not_author', [], 'CapcoAppBundle')
            );
        }

        $form = $this->createForm(CommentForm::class, $comment, ['actionType' => 'edit']);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            // We create a session for flashBag
            $flashBag = $this->get('session')->getFlashBag();

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $comment->resetVotes();
                $em->persist($comment);
                $em->flush();
                $this->eventDispatcher->dispatch(
                    CapcoAppBundleEvents::COMMENT_CHANGED,
                    new CommentChangedEvent($comment, 'update')
                );

                $flashBag->add(
                    'success',
                    $this->get('translator')->trans('comment.update.success')
                );

                return $this->redirect(
                    $this->get(CommentResolver::class)->getUrlOfRelatedObject($comment)
                );
            }
            $flashBag->add('danger', $this->get('translator')->trans('comment.update.error'));
        }

        return ['form' => $form->createView(), 'comment' => $comment];
    }

    /**
     * @Route("/comments/{commentId}/delete", name="app_comment_delete")
     * @Entity("comment", options={"mapping": {"commentId" : "id"}})
     * @Template("CapcoAppBundle:Comment:delete.html.twig")
     * @Security("has_role('ROLE_USER')")
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws ProjectAccessDeniedException
     */
    public function deleteCommentAction(Request $request, Comment $comment)
    {
        if (false === $comment->canContribute($this->getUser())) {
            throw new ProjectAccessDeniedException(
                $this->get('translator')->trans('comment.error.no_contribute', [], 'CapcoAppBundle')
            );
        }

        $userCurrent = $this->getUser()->getId();
        $userPostComment = $comment->getAuthor()->getId();

        if ($userCurrent !== $userPostComment) {
            throw new ProjectAccessDeniedException(
                $this->get('translator')->trans('comment.error.not_author', [], 'CapcoAppBundle')
            );
        }

        //Champ CSRF
        $form = $this->createFormBuilder()->getForm();

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            // We create a session for flashBag
            $flashBag = $this->get('session')->getFlashBag();

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($comment);
                $this->eventDispatcher->dispatch(
                    CapcoAppBundleEvents::COMMENT_CHANGED,
                    new CommentChangedEvent($comment, 'remove')
                );
                $em->flush();

                $flashBag->add('info', $this->get('translator')->trans('comment.delete.success'));

                $this->get(CommentableCommentsDataLoader::class)->invalidate(
                    $comment->getRelatedObject()->getId()
                );

                return $this->redirect(
                    $this->get(CommentResolver::class)->getUrlOfRelatedObject($comment)
                );
            }
            $flashBag->add('danger', $this->get('translator')->trans('comment.delete.error'));
        }

        return ['form' => $form->createView(), 'comment' => $comment];
    }
}
