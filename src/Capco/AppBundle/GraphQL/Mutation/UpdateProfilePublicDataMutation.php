<?php

namespace Capco\AppBundle\GraphQL\Mutation;

use Capco\UserBundle\Entity\User;
use Capco\UserBundle\Form\Type\PublicDataType;
use Doctrine\ORM\EntityManagerInterface;
use GraphQL\Error\UserError;
use Overblog\GraphQLBundle\Definition\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormFactory;

class UpdateProfilePublicDataMutation
{
    private $em;
    private $formFactory;
    private $logger;

    public function __construct(EntityManagerInterface $em, FormFactory $formFactory, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->logger = $logger;
    }

    public function __invoke(Argument $input, User $user): array
    {
        $arguments = $input->getRawArguments();

        $form = $this->formFactory->create(PublicDataType::class, $user, ['csrf_protection' => false]);
        try {
            $form->submit($arguments, false);
        } catch (\LogicException $e) {
            $this->logger->error(__METHOD__ . ' : ' . $e->getMessage());
        }

        if (!$form->isValid()) {
            $this->logger->error(__METHOD__ . ' : ' . (string) $form->getErrors(true, false));
            throw new UserError('Can\'t update !');
        }

        $this->em->flush();

        return ['viewer' => $user];
    }
}
