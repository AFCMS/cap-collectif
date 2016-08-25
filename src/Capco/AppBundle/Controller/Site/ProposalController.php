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
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

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
    public function showProposalAction(Project $project, CollectStep $currentStep, Proposal $proposal)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->get('jms_serializer');

        $firstVotableStep = $this
            ->get('capco.proposal_votes.resolver')
            ->getFirstVotableStepForProposal($proposal)
        ;

        $userHasVote = false;
        if ($this->getUser() && $firstVotableStep) {
            $userVote = $em
                ->getRepository('CapcoAppBundle:ProposalVote')
                ->findOneBy([
                    'selectionStep' => $firstVotableStep,
                    'user' => $this->getUser(),
                    'proposal' => $proposal,
                ]);
            if ($userVote) {
                $userHasVote = true;
            }
        }

        $proposalForm = $currentStep->getProposalForm();
        $props = $serializer->serialize([
            'form' => $proposalForm,
            'categories' => $proposalForm ? $proposalForm->getCategories() : [],
            'votableStep' => $firstVotableStep,
            'userHasVote' => $userHasVote,
        ], 'json', SerializationContext::create()
            ->setSerializeNull(true)
            ->setGroups([
                'Categories',
                'ProposalForms',
                'Questions',
                'Steps',
            ]))
        ;

        $previewedVotes = $em->getRepository('CapcoAppBundle:ProposalVote')->getVotesForProposal($proposal, 6);
        $proposal->setVotes(new ArrayCollection($previewedVotes));

        $proposalSerialized = $serializer->serialize($proposal, 'json',
          SerializationContext::create()
            ->setSerializeNull(true)
            ->setGroups([
                'ProposalVotes',
                'UsersInfos',
                'UserMedias',
                'Proposals',
                'ProposalCategories',
                'ProposalUserData',
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
        ]);
    }
}
