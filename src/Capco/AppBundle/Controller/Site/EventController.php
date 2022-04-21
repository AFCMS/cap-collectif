<?php

namespace Capco\AppBundle\Controller\Site;

use Capco\AppBundle\Entity\Project;
use Capco\AppBundle\Entity\Theme;
use Capco\AppBundle\Form\EventSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Capco\AppBundle\Entity\Event;
use Capco\AppBundle\Form\EventRegistrationType;
use JMS\Serializer\SerializationContext;

class EventController extends Controller
{
    /**
     * @Route("/events", name="app_event", defaults={"_feature_flags" = "calendar"} )
     * @Route("/events/filter/{theme}", name="app_event_search_theme", defaults={"_feature_flags" = "calendar", "theme" = "all"} )
     * @Route("/events/filter/{theme}/{project}", name="app_event_search_project", defaults={"_feature_flags" = "calendar", "theme" = "all", "project"="all"} )
     * @Route("/events/filter/{theme}/{project}/{term}", name="app_event_search_term", defaults={"_feature_flags" = "calendar", "theme" = "all", "project"="all"} )
     * @Cache(smaxage="60", public=true)
     * @Template("CapcoAppBundle:Event:index.html.twig")
     */
    public function indexAction(Request $request, $theme = null, $project = null, $term = null)
    {
        $em = $this->getDoctrine()->getManager();
        $currentUrl = $this->generateUrl('app_event');

        $form = $this->createForm(new EventSearchType($this->get('capco.toggle.manager')), null, [
            'action' => $currentUrl,
            'method' => 'POST',
        ]);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();

                return $this->redirect($this->generateUrl('app_event_search_term', [
                    'theme' => array_key_exists('theme', $data) && $data['theme'] ? $data['theme']->getSlug() : Theme::FILTER_ALL,
                    'project' => $data['project'] ? $data['project']->getSlug() : Project::FILTER_ALL,
                    'term' => $data['term'],
                ]));
            }
        } else {
            $form->setData([
                'theme' => $em->getRepository('CapcoAppBundle:Theme')->findOneBySlug($theme),
                'project' => $em->getRepository('CapcoAppBundle:Project')->findOneBySlug($project),
                'term' => $term,
            ]);
        }

        $groupedEvents = $this->get('capco.event.resolver')->getEventsGroupedByYearAndMonth(false, $theme, $project, $term);
        $archivedEventsNb = $this->get('capco.event.resolver')->countEvents(true, $theme, $project, $term);

        return [
            'years' => $groupedEvents,
            'form' => $form->createView(),
            'archivedEventsNb' => $archivedEventsNb,
        ];
    }

    /**
     * @Route("/events/archived", name="app_event_archived", defaults={"_feature_flags" = "calendar"} )
     * @Route("/events/archived/{theme}", name="app_event_archived_theme", defaults={"_feature_flags" = "calendar", "theme" = "all"} )
     * @Route("/events/archived/{theme}/{project}", name="app_event_archived_project", defaults={"_feature_flags" = "calendar", "theme" = "all", "project"="all"} )
     * @Route("/events/archived/{theme}/{project}/{term}", name="app_event_archived_term", defaults={"_feature_flags" = "calendar", "theme" = "all", "project"="all"} )
     * @Template("CapcoAppBundle:Event:show_archived.html.twig")
     *
     * @param $theme
     * @param $project
     * @param $term
     * @param $request
     *
     * @return array
     */
    public function showArchivedAction(Request $request, $theme = null, $project = null, $term = null)
    {
        $em = $this->getDoctrine()->getManager();
        $currentUrl = $this->generateUrl('app_event_archived');

        $form = $this->createForm(new EventSearchType($this->get('capco.toggle.manager')), null, [
            'action' => $currentUrl,
            'method' => 'POST',
        ]);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();

                return $this->redirect($this->generateUrl('app_event_archived_term', [
                    'theme' => array_key_exists('theme', $data) && $data['theme'] ? $data['theme']->getSlug() : Theme::FILTER_ALL,
                    'project' => $data['project'] ? $data['project']->getSlug() : Project::FILTER_ALL,
                    'term' => $data['term'],
                ]));
            }
        } else {
            $form->setData([
                'theme' => $em->getRepository('CapcoAppBundle:Theme')->findOneBySlug($theme),
                'project' => $em->getRepository('CapcoAppBundle:Project')->findOneBySlug($project),
                'term' => $term,
            ]);
        }

        $groupedEvents = $this->get('capco.event.resolver')->getEventsGroupedByYearAndMonth(true, $theme, $project, $term);

        return [
            'years' => $groupedEvents,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/events/{slug}", name="app_event_show", defaults={"_feature_flags" = "calendar"})
     * @ParamConverter("event", options={"mapping": {"slug": "slug"}, "repository_method" = "getOne"})
     * @Template("CapcoAppBundle:Event:show.html.twig")
     *
     * @param $request
     *
     * @return array
     */
    public function showAction(Request $request, Event $event)
    {
        $eventHelper = $this->container->get('capco.event.helper');

        $serializer = $this->get('jms_serializer');

        $props = $serializer->serialize([
            'object' => $event->getId(),
            'uri' => 'events',
        ], 'json', SerializationContext::create());

        if (!$eventHelper->isRegistrationPossible($event)) {
            return [
                'event' => $event,
                'props' => $props,
            ];
        }

        $user = $this->getUser();
        $registration = $eventHelper->findUserRegistrationOrCreate($event, $user);
        $form = $this->createForm(new EventRegistrationType($user, $registration->isConfirmed()), $registration);

        if ($request->getMethod() == 'POST') {
            $registration->setIpAddress($request->getClientIp());
            $registration->setUser($user);
            $form->handleRequest($request);
            $registration->setConfirmed(!$registration->isConfirmed());

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($registration);
                $em->flush();
                if ($registration->isConfirmed()) {
                    $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('event_registration.create.register_success'));
                } else {
                    $this->get('session')->getFlashBag()->add('info', $this->get('translator')->trans('event_registration.create.unregister_success'));
                }

                return $this->redirect($this->generateUrl('app_event_show', ['slug' => $event->getSlug()]));
            }
        }

        return [
            'form' => $form->createView(),
            'event' => $event,
            'props' => $props,
        ];
    }

    /**
     * @Cache(expires="+1 minutes", maxage="60", smaxage="60", public="true")
     *
     * @param $max
     * @param $offset
     *
     * @return array
     * @Template("CapcoAppBundle:Event:lastEvents.html.twig")
     */
    public function lastEventsAction($max = 3, $offset = 0)
    {
        $events = $this->get('capco.event.repository')->getLast($max, $offset);

        return ['events' => $events];
    }
}
