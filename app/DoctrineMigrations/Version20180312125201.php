<?php

namespace Application\Migrations;

use CapCollectif\IdToUuid\IdToUuidMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180312125201 extends IdToUuidMigration
{
    public function postUp(Schema $schema)
    {
        $this->migrate('comment');
    }
}
