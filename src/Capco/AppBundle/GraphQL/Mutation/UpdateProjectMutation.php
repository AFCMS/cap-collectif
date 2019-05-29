<?php

namespace Capco\AppBundle\GraphQL\Mutation;

use Psr\Log\LoggerInterface;
use Capco\AppBundle\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Driver\DriverException;
use Capco\UserBundle\Form\Type\ProjectFormType;
use Capco\UserBundle\Repository\UserRepository;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Node\GlobalId;
use Symfony\Component\Form\FormFactoryInterface;
use Capco\AppBundle\Repository\ProjectRepository;
use Capco\AppBundle\Repository\ProjectTypeRepository;
use Capco\AppBundle\Repository\ProjectAuthorRepository;
use Capco\AppBundle\GraphQL\Exceptions\GraphQLException;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UpdateProjectMutation implements MutationInterface
{
    private $em;
    private $formFactory;
    private $logger;
    private $userRepository;
    private $projectTypeRepository;
    private $projectRepository;
    private $projectAuthorRepository;

    public function __construct(
        EntityManagerInterface $em,
        FormFactoryInterface $formFactory,
        LoggerInterface $logger,
        UserRepository $userRepository,
        ProjectTypeRepository $projectTypeRepository,
        ProjectAuthorRepository $projectAuthorRepository,
        ProjectRepository $projectRepository
    ) {
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->logger = $logger;
        $this->userRepository = $userRepository;
        $this->projectRepository = $projectRepository;
        $this->projectTypeRepository = $projectTypeRepository;
        $this->projectAuthorRepository = $projectAuthorRepository;
    }

    public function __invoke(Argument $input): array
    {
        $arguments = $input->getRawArguments();

        $project = $this->projectRepository->find(GlobalId::fromGlobalId($arguments['id'])['id']);
        if (!$project) {
            throw new BadRequestHttpException('Sorry, please retry.');
        }

        foreach ($arguments['authors'] as $userId) {
            $decodedUserId = GlobalId::fromGlobalId($userId)['id'];
            if (!$decodedUserId) {
                throw new BadRequestHttpException('Sorry, please retry.');
            }
            $this->transform($decodedUserId, $project);
        }

        unset($arguments['authors'], $arguments['id']);

        $form = $this->formFactory->create(ProjectFormType::class, $project);

        $form->submit($arguments, false);
        if (!$form->isValid()) {
            $this->logger->error(__METHOD__ . ' : ' . (string) $form->getErrors(true, false));

            throw GraphQLException::fromFormErrors($form);
        }

        try {
            $this->em->flush();
        } catch (DriverException $e) {
            $this->logger->error(
                __METHOD__ . ' => ' . $e->getErrorCode() . ' : ' . $e->getMessage()
            );

            throw new BadRequestHttpException('Sorry, please retry.');
        }

        return ['project' => $project];
    }

    public function transform(string $userId, Project $project)
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw new BadRequestHttpException('Sorry, please retry.');
        }

        $projectAuthor = $this->projectAuthorRepository->findOneBy([
            'project' => $project,
            'user' => $user,
        ]);
        if (!$projectAuthor) {
            $project->addAuthor($user);
        }
    }
}
