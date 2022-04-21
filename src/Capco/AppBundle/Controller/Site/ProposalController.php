<?php

namespace Capco\AppBundle\Controller\Site;

use Capco\AppBundle\Entity\Proposal;
use Capco\AppBundle\Entity\Project;
use Capco\AppBundle\Entity\Steps\CollectStep;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\Serializer\SerializationContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\HttpFoundation\Request;

class ProposalController extends Controller
{
    /**
     * @Route("/projects/{projectSlug}/collect/{stepSlug}/proposals/{proposalSlug}", name="app_project_show_proposal")
     * @ParamConverter("project", options={"mapping": {"projectSlug": "slug"}})
     * @ParamConverter("currentStep", options={"mapping": {"stepSlug": "slug"}})
     * @ParamConverter("proposal", options={"mapping": {"proposalSlug": "slug"}})
     * @Template("CapcoAppBundle:Proposal:show.html.twig")
     * @Cache(smaxage="60", public=true)
     */
    public function showProposalAction(Request $request, Project $project, CollectStep $currentStep, Proposal $proposal)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->get('serializer');

        $refererUri = $request->headers->has('referer')
            && filter_var($request->headers->get('referer'), FILTER_VALIDATE_URL) !== false
                ? $request->headers->get('referer')
                : null;

        $proposalForm = $currentStep->getProposalForm();
        $props = $serializer->serialize([
            'form' => $proposalForm,
            'categories' => $proposalForm ? $proposalForm->getCategories() : [],
        ], 'json', SerializationContext::create()
            ->setSerializeNull(true)
            ->setGroups([
                'ProposalCategories',
                'UserVotes',
                'ProposalForms',
                'Questions',
                'Steps',
                'ThemeDetails',
                'UserMedias',
                'VoteThreshold',
                'Default', // force step_type serialization
            ]))
        ;

        $proposalSerialized = $serializer->serialize($proposal, 'json',
          SerializationContext::create()
            ->setSerializeNull(true)
            ->setGroups([
                'ProposalFusions',
                'ProposalSelectionVotes',
                'ProposalCollectVotes',
                'UsersInfos',
                'UserMedias',
                'Proposals',
                'ProposalCategories',
                'ThemeDetails',
                'ProposalUserData',
                'VoteThreshold',
            ]))
        ;

        $proposalSerializedAsArray = json_decode($proposalSerialized, true);
        $proposalSerializedAsArray['postsCount'] = $em->getRepository('CapcoAppBundle:Post')->countPublishedPostsByProposal($proposal);

        return $this->render('CapcoAppBundle:Proposal:show.html.twig', [
            'project' => $project,
            'currentStep' => $currentStep,
            'props' => $props,
            'proposal' => $proposal,
            'proposalSerialized' => $proposalSerializedAsArray,
            'referer' => $refererUri,
        ]);
    }
}
