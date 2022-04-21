<?php

namespace Capco\AppBundle\Controller\Site;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Capco\AppBundle\Form\SearchType as SearchForm;

class SearchController extends Controller
{
    /**
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route("/search", name="app_search", defaults={"_feature_flags" = "search"})
     * @Template("CapcoAppBundle:Default:search.html.twig")
     */
    public function searchAction(Request $request)
    {
        $searchParams = [
            'term' => '',
            'type' => 'all',
            'sort' => 'score'
        ];
        $sortField = '_score';
        $sortOrder = 'desc';

        $page = (int) $request->get('page', 1);

        $form = $this->createForm(new SearchForm($this->get('capco.toggle.manager')), $searchParams, ['method' => 'GET']);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $searchParams = $form->getData();
        }

        if ($searchParams['sort'] && $searchParams['sort'] === 'date') {
            $sortField = 'createdAt';
            $sortOrder = 'desc';
        }

        // Perform the search
        $searchResults = $this->container->get('capco.search.resolver')->searchAll(
            $page,
            $searchParams['term'],
            $searchParams['type'],
            $sortField,
            $sortOrder
        );

        return [
            'form'      => $form->createView(),
            'page'      => $page,
            'q'         => $searchParams,
            'count'     => $searchResults['count'],
            'results'   => $searchResults['results'],
            'nbPages'   => $searchResults['pages'],
        ];
    }
}
