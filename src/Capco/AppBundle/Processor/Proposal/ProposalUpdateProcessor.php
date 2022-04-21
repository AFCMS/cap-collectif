<?php

namespace Capco\AppBundle\Processor\Proposal;

use Capco\AppBundle\Notifier\ProposalNotifier;
use Capco\AppBundle\Repository\ProposalRepository;
use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;

class ProposalUpdateProcessor implements ProcessorInterface
{
    private $proposalRepository;
    private $notifier;

    public function __construct(ProposalRepository $proposalRepository, ProposalNotifier $notifier)
    {
        $this->proposalRepository = $proposalRepository;
        $this->notifier = $notifier;
    }

    public function process(Message $message, array $options)
    {
        $json = json_decode($message->getBody(), true);
        $proposal = $this->proposalRepository->find($json['proposalId']);
        $this->notifier->onUpdate($proposal);

        return true;
    }
}
