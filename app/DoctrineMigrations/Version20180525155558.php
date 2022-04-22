<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180525155558 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE step ADD votes_ranking TINYINT(1) DEFAULT \'0\'');
        $this->addSql('ALTER TABLE votes ADD position INT DEFAULT NULL');
    }

    public function down(Schema $schema)
    {

    }
}
