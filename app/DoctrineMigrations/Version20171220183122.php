<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20171220183122 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE proposal_evaluation CHANGE updated_at updated_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE proposal_evaluation CHANGE updated_at updated_at DATETIME NOT NULL');
    }
}
