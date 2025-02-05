<?php

namespace Capco\AppBundle\GraphQL\Resolver\Query;

use Capco\AppBundle\Repository\SiteParameterRepository;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class QueryNotificationsFromEmailResolver implements ResolverInterface
{
    private SiteParameterRepository $repository;

    public function __construct(SiteParameterRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(): ?string
    {
        $siteParameter = $this->repository->findOneBy([
            'keyname' => 'admin.mail.notifications.send_address',
        ]);

        return $siteParameter ? $siteParameter->getValue() : null;
    }
}
