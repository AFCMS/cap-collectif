<?php

namespace Capco\AppBundle\Controller\Site;

use Capco\AppBundle\Entity\NewsletterSubscription;
use Capco\AppBundle\Entity\Section;
use Capco\AppBundle\Form\NewsletterSubscriptionType;
use JMS\Serializer\SerializationContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class HomepageController extends Controller
{
    /**
     * @Route("/", name="app_homepage")
     * @Cache(smaxage="60", public=true)
     * @Template("CapcoAppBundle:Homepage:homepage.html.twig")
     */
    public function homepageAction(Request $request)
    {
        $sections = $this->get('capco.section.resolver')->getDisplayableEnabledOrdered();
        $newsletterActive = $this->get('capco.toggle.manager')->isActive('newsletter');

        $translator = $this->get('translator');
        $deleteType = $request->get('deleteType');
        $form = null;

        if ($deleteType) {
            $flashBag = $this->get('session')->getFlashBag();
            if ('SOFT' === $deleteType) {
                $flashBag->add('success', $translator->trans('account-and-contents-anonymized'));
            } elseif ('HARD' === $deleteType) {
                $flashBag->add('success', $translator->trans('account-and-contents-deleted'));
            }
        }

        // Subscription to newsletter
        if ($newsletterActive) {
            $subscription = new NewsletterSubscription();
            $flashBag = $this->get('session')->getFlashBag();

            $form = $this->createForm(NewsletterSubscriptionType::class, $subscription);
            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                $em = $this->getDoctrine()->getManager();

                if ($form->isValid()) {
                    // TODO: move this to a unique constraint in form instead
                    $email = $em->getRepository('CapcoAppBundle:NewsletterSubscription')
                                ->findOneByEmail($subscription->getEmail());

                    if ($email) {
                        $flashBag->add('info', $translator->trans('homepage.newsletter.already_subscribed'));
                    } else {
                        $em->persist($subscription);
                        $em->flush();
                        $flashBag->add('success', $translator->trans('homepage.newsletter.success'));
                    }
                } else {
                    $flashBag->add('danger', $translator->trans('homepage.newsletter.invalid'));
                }

                return $this->redirect($this->generateUrl('app_homepage'));
            }
        }

        return [
            'form' => $newsletterActive ? $form->createView() : false,
            'sections' => $sections,
        ];
    }

    /**
     * @Template("CapcoAppBundle:Homepage:highlighted.html.twig")
     */
    public function highlightedContentAction(Section $section = null)
    {
        $serializer = $this->get('jms_serializer');
        $highlighteds = $this->get('capco.highlighted.repository')->getAllOrderedByPosition(4);
        $props = $serializer->serialize([
            'highlighteds' => $highlighteds,
        ], 'json', SerializationContext::create()
            ->setSerializeNull(true));

        return [
            'props' => $props,
            'section' => $section,
        ];
    }

    /**
     * @Template("CapcoAppBundle:Homepage:videos.html.twig")
     */
    public function lastVideosAction(int $max = null, int $offset = null, Section $section = null)
    {
        $max = $max ?? 4;
        $offset = $offset ?? 0;
        $videos = $this->get('capco.video.repository')->getLast($max, $offset);

        return [
            'videos' => $videos,
            'section' => $section,
        ];
    }

    /**
     * @Cache(smaxage="60", public=true)
     * @Template("CapcoAppBundle:Homepage:lastProposals.html.twig")
     */
    public function lastProposalsAction(int $max = null, int $offset = null, Section $section = null)
    {
        $max = $max ?? 4;
        $offset = $offset ?? 0;
        $em = $this->getDoctrine()->getManager();
        if ($section->getStep() && $section->getStep()->isCollectStep()) {
            $proposals = $em
                ->getRepository('CapcoAppBundle:Proposal')
                ->getLastByStep($max, $offset, $section->getStep())
            ;
        } else {
            $proposals = $em
                ->getRepository('CapcoAppBundle:Proposal')
                ->getLast($max, $offset)
            ;
        }

        return [
            'proposals' => $proposals,
            'section' => $section,
        ];
    }

    /**
     * @Cache(smaxage="60", public=true)
     * @Template("CapcoAppBundle:Homepage:lastThemes.html.twig")
     */
    public function lastThemesAction(int $max = null, int $offset = null, Section $section = null)
    {
        $max = $max ?? 4;
        $offset = $offset ?? 0;
        $topics = $this->getDoctrine()->getManager()->getRepository('CapcoAppBundle:Theme')->getLast($max, $offset);

        return [
            'topics' => $topics,
            'section' => $section,
        ];
    }

    /**
     * @Cache(smaxage="60", public=true)
     * @Template("CapcoAppBundle:Homepage:lastPosts.html.twig")
     */
    public function lastPostsAction(int $max = null, int $offset = null, Section $section = null)
    {
        $max = $max ?? 4;
        $offset = $offset ?? 0;
        $posts = $this->get('capco.blog.post.repository')->getLast($max, $offset);

        return [
            'posts' => $posts,
            'section' => $section,
        ];
    }

    /**
     * @Cache(smaxage="60", public=true)
     * @Template("CapcoAppBundle:Homepage:lastProjects.html.twig")
     */
    public function lastProjectsAction(int $max = null, int $offset = null, Section $section = null)
    {
        $max = $max ?? 3;
        $offset = $offset ?? 0;
        $serializer = $this->get('jms_serializer');
        $count = $this->getDoctrine()->getRepository('CapcoAppBundle:Project')->countPublished();
        $props = $serializer->serialize([
            'projects' => $this
                ->getDoctrine()
                ->getManager()
                ->getRepository('CapcoAppBundle:Project')
                ->getLastPublished($max, $offset),
        ], 'json', SerializationContext::create()->setGroups(['Projects', 'UserDetails', 'Steps', 'Themes', 'ProjectType']));

        return [
            'max' => $max,
            'props' => $props,
            'count' => $count,
            'section' => $section,
        ];
    }

    /**
     * @Cache(smaxage="60", public=true)
     * @Template("CapcoAppBundle:Homepage:lastEvents.html.twig")
     */
    public function lastEventsAction(int $max = null, int $offset = null, Section $section = null)
    {
        $max = $max ?? 3;
        $offset = $offset ?? 0;
        $events = $this->get('capco.event.repository')->getLast($max, $offset);

        return [
            'events' => $events,
            'section' => $section,
        ];
    }

    /**
     * @Template("CapcoAppBundle:Homepage:socialNetworks.html.twig")
     */
    public function socialNetworksAction(Section $section = null)
    {
        $socialNetworks = $this->getDoctrine()->getManager()->getRepository('CapcoAppBundle:SocialNetwork')->getEnabled();

        return [
            'socialNetworks' => $socialNetworks,
            'section' => $section,
        ];
    }
}
