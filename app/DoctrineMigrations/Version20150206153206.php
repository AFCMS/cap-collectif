<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150206153206 extends AbstractMigration implements ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema)
    {

    }

    public function postUp(Schema $schema)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        $query = $em->createQuery("SELECT si.id FROM Capco\AppBundle\Entity\SiteImage si WHERE si.keyname = :keyname");
        $query->setParameter('keyname', 'image.default_avatar');
        $siteImage = $query->getOneOrNullResult();

        if (null != $siteImage) {
            $this->connection->update('site_image', array('is_enabled' => false), array('id' => $siteImage['id']));
        }
    }

    public function down(Schema $schema)
    {

    }

    public function postDown(Schema $schema)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        $query = $em->createQuery("SELECT si.id FROM Capco\AppBundle\Entity\SiteImage si WHERE si.keyname = :keyname");
        $query->setParameter('keyname', 'image.default_avatar');
        $siteImage = $query->getOneOrNullResult();

        if (null != $siteImage) {
            $this->connection->update('site_image', array('is_enabled' => true), array('id' => $siteImage['id']));
        }
    }
}
