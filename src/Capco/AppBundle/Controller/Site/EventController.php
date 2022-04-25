<?php

namespace Capco\AppBundle\Controller\Site;

use Capco\AppBundle\Entity\Event;
use Capco\AppBundle\Helper\EventHelper;
use Capco\AppBundle\Repository\EventRepository;
use Capco\AppBundle\Security\EventVoter;
use Capco\UserBundle\Entity\User;
use Capco\UserBundle\Security\Exception\ProjectAccessDeniedException;
use Doctrine\ORM\EntityManagerInterface;
use Http\Discovery\Exception\NotFoundException;
use Capco\AppBundle\Form\EventRegistrationType;
use Capco\AppBundle\SiteParameter\SiteParameterResolver;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends Controller
{
    private $eventHelper;
    private $eventRepository;
    private $entityManager;

    public function __construct(
        EventHelper $eventHelper,
        EventRepository $eventRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
        $this->eventHelper = $eventHelper;
        $this->eventRepository = $eventRepository;
    }

    /**
     * @Route("/events", name="app_event", defaults={"_feature_flags" = "calendar"} )
     * @Template("CapcoAppBundle:Event:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $locale = $request->getLocale() ?? 'fr-FR';

        return [
            'props' => [
                'eventPageTitle' => $this->get(SiteParameterResolver::class)->getValue(
                    'events.jumbotron.title',
                    $locale
                ),
                'eventPageBody' => $this->get(SiteParameterResolver::class)->getValue(
                    'events.content.body',
                    $locale
                )
            ]
        ];
    }

    /**
     * @Route("/events/download", name="app_events_download", options={"i18n" = false})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function downloadAction(Request $request)
    {
        $trans = $this->get('translator');

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            throw new ProjectAccessDeniedException($trans->trans('project.error.not_exportable'));
        }

        $path = sprintf('%s/public/export/', $this->container->getParameter('kernel.project_dir'));
        $csvFile = 'events.csv';

        if (!file_exists($path . $csvFile)) {
            // We create a session for flashBag
            $flashBag = $this->get('session')->getFlashBag();

            $flashBag->add('danger', $trans->trans('project.download.not_yet_generated'));

            return $this->redirect($request->headers->get('referer'));
        }

        $filename = $csvFile;
        $contentType = 'text/csv';

        $date = (new \DateTime())->format('Y-m-d');

        $request->headers->set('X-Sendfile-Type', 'X-Accel-Redirect');
        $response = new BinaryFileResponse($path . $filename);
        $response->headers->set('X-Accel-Redirect', '/export/' . $filename);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $date . '_' . $filename
        );
        $response->headers->set('Content-Type', $contentType . '; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');

        return $response;
    }

    /**
     * @Route("/events/{slug}", name="app_event_show", defaults={"_feature_flags" = "calendar"})
     * @Template("CapcoAppBundle:Event:show.html.twig")
     */
    public function showAction(Request $request, $slug)
    {
        $filters = $this->entityManager->getFilters();
        if ($filters->isEnabled('softdeleted')) {
            $filters->disable('softdeleted');
        }
        $event = $this->eventRepository->getOneBySlug($slug);

        if (!$event) {
            throw new NotFoundException();
        }

        if ($event->isDeleted()) {
            return new Response(
                $this->renderView('CapcoAppBundle:Event:cancel.html.twig', ['event' => $event])
            );
        }
        $this->denyAccessUnlessGranted(EventVoter::VIEW, $event);
        /** @var User $viewer */
        $viewer = $this->getUser();
        if (!$this->eventHelper->isRegistrationPossible($event)) {
            return [
                'event' => $event,
                'viewer' => $viewer
            ];
        }

        $user = $this->getUser();
        $registration = $this->eventHelper->findUserRegistrationOrCreate($event, $user);
        $form = $this->createForm(EventRegistrationType::class, $registration, [
            'registered' => $registration->isConfirmed()
        ]);

        if ('POST' === $request->getMethod()) {
            $registration->setIpAddress($request->getClientIp());
            $registration->setUser($user);
            $form->handleRequest($request);
            $registration->setConfirmed(!$registration->isConfirmed());

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($registration);
                $em->flush();

                // We create a session for flashBag
                $flashBag = $this->get('session')->getFlashBag();

                if ($registration->isConfirmed()) {
                    $flashBag->add(
                        'success',
                        $this->get('translator')->trans(
                            'event_registration.create.register_success'
                        )
                    );
                } else {
                    $flashBag->add(
                        'info',
                        $this->get('translator')->trans(
                            'event_registration.create.unregister_success'
                        )
                    );
                }

                return $this->redirect(
                    $this->generateUrl('app_event_show', ['slug' => $event->getSlug()])
                );
            }
        }

        return [
            'viewerIsAuthor' => $event->getAuthor() === $this->getUser(),
            'form' => $form->createView(),
            'event' => $event,
            'viewer' => $viewer
        ];
    }
}
