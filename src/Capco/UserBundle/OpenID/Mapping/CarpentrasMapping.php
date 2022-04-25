<?php

namespace Capco\UserBundle\OpenID\Mapping;

class CarpentrasMapping implements MappingInterface
{
    public function getPaths(): array
    {
        return [
            'identifier' => 'sub',
            'email' => 'email',
            'nickname' => ['given_name', 'family_name'],
        ];
    }
}
