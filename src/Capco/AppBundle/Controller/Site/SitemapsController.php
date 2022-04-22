<?php

namespace Capco\AppBundle\Controller\Site;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SitemapsController extends Controller
{
    /**
     * @Route("/sitemap.{_format}", name="app_sitemap", requirements={"_format" = "xml"})
     * @Template("CapcoAppBundle:Sitemaps:sitemap.xml.twig")
     *
     * @return array
     */
    public function sitemapAction()
    {
        $toggleManager = $this->get('capco.toggle.manager');
        $em = $this->getDoctrine()->getManager();
        $urls = [];
        $hostname = $this->get('request_stack')->getCurrentRequest()->getHost();

        // Homepage
        $urls[] = [
            'loc' => $this->get('router')->generate('app_homepage'),
            'changefreq' => 'weekly',
            'priority' => '1.0',
        ];

        // Contact
        $urls[] = [
            'loc' => $this->get('router')->generate('app_contact'),
            'changefreq' => 'yearly',
            'priority' => '0.1',
        ];

        // Pages
        foreach ($em->getRepository('CapcoAppBundle:Page')->findBy(['isEnabled' => true]) as $page) {
            $urls[] = [
                'loc' => $this->get('router')->generate('app_page_show', ['slug' => $page->getSlug()]),
                'lastmod' => $page->getUpdatedAt()->format(\DateTime::W3C),
                'changefreq' => 'monthly',
                'priority' => '0.1',
            ];
        }

        // Themes
        if ($toggleManager->isActive('themes')) {
            $urls[] = [
                'loc' => $this->get('router')->generate('app_theme'),
                'changefreq' => 'weekly',
                'priority' => '0.5',
            ];
            foreach ($em->getRepository('CapcoAppBundle:Theme')->findBy(['isEnabled' => true]) as $theme) {
                $urls[] = [
                    'loc' => $this->get('router')->generate('app_theme_show', ['slug' => $theme->getSlug()]),
                    'lastmod' => $theme->getUpdatedAt()->format(\DateTime::W3C),
                    'changefreq' => 'weekly',
                    'priority' => '0.5',
                ];
            }
        }

        // Blog
        if ($toggleManager->isActive('blog')) {
            $urls[] = [
                'loc' => $this->get('router')->generate('app_blog'),
                'changefreq' => 'daily',
                'priority' => '1.0',
            ];
            foreach ($em->getRepository('CapcoAppBundle:Post')->findBy(['isPublished' => true]) as $post) {
                $urls[] = [
                    'loc' => $this->get('router')->generate('app_blog_show', ['slug' => $post->getSlug()]),
                    'lastmod' => $post->getUpdatedAt()->format(\DateTime::W3C),
                    'changefreq' => 'daily',
                    'priority' => '1.0',
                ];
            }
        }

        // Events
        if ($toggleManager->isActive('calendar')) {
            $urls[] = [
                'loc' => $this->get('router')->generate('app_event'),
                'changefreq' => 'daily',
                'priority' => '1.0',
            ];
            foreach ($em->getRepository('CapcoAppBundle:Event')->findBy(['isEnabled' => true]) as $event) {
                $urls[] = [
                    'loc' => $this->get('router')->generate('app_event_show', ['slug' => $event->getSlug()]),
                    'priority' => '1.0',
                    'lastmod' => $event->getUpdatedAt()->format(\DateTime::W3C),
                    'changefreq' => 'daily',
                ];
            }
        }

        // Projects
        $urls[] = [
            'loc' => $this->get('router')->generate('app_project'),
            'changefreq' => 'weekly',
            'priority' => '0.5',
        ];

        // Steps
        $stepResolver = $this->get('capco.step.resolver');
        foreach ($em->getRepository('CapcoAppBundle:Steps\AbstractStep')->findBy(['isEnabled' => true]) as $step) {
            if ($step->getProject()->canDisplay()) {
                $urls[] = [
                    'loc' => $stepResolver->getLink($step, false),
                    'priority' => '0.5',
                    'lastmod' => $step->getUpdatedAt()->format(\DateTime::W3C),
                    'changefreq' => 'weekly',
                ];
            }
        }

        // Opinions
        foreach ($em->getRepository('CapcoAppBundle:Opinion')->findBy(['isEnabled' => true]) as $opinion) {
            if ($opinion->canDisplay()) {
                $urls[] = [
                    'loc' => $this->get('router')->generate('app_project_show_opinion', ['projectSlug' => $opinion->getStep()->getProject()->getSlug(), 'stepSlug' => $opinion->getStep()->getSlug(), 'opinionTypeSlug' => $opinion->getOpinionType()->getSlug(), 'opinionSlug' => $opinion->getSlug()]),
                    'priority' => '2.0',
                    'lastmod' => $opinion->getUpdatedAt()->format(\DateTime::W3C),
                    'changefreq' => 'hourly',
                ];
            }
        }

        return [
            'urls' => $urls,
            'hostname' => $hostname,
        ];
    }
}
