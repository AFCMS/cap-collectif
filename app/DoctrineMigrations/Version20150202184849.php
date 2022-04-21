<?php

namespace Application\Migrations;

use Capco\AppBundle\Entity\SiteImage;
use Capco\ClassificationBundle\Entity\Category;
use Capco\ClassificationBundle\Entity\Context;
use Capco\MediaBundle\Entity\Media;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150202184849 extends AbstractMigration implements ContainerAwareInterface
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
        if (null == $siteImage) {
            $date = (new \DateTime())->format('Y-m-d H:i:s');
            $this->connection->insert('site_image', array('keyname' => 'image.default_avatar', 'Media_id' => null, 'is_enabled' => 0, 'title' => 'Avatar par défaut', 'position' => 4, 'created_at' => $date, 'updated_at' => $date));
        }
    }

    public function down(Schema $schema)
    {

    }

    public function postDown(Schema $schema)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        $query = $em->createQuery("SELECT si.id FROM Capco\AppBundle\Entity\SiteImage si JOIN si.Media m WHERE si.keyname = :keyname");
        $query->setParameter('keyname', 'image.default_avatar');
        $siteImage = $query->getOneOrNullResult();
        if (null != $siteImage) {
            $mediaId = $siteImage['m'];
            if (null != $mediaId) {
                $this->connection->delete('media__media', array('id' => $mediaId));
            }
            $this->connection->delete('site_image', array('id' => $siteImage['id']));
        }
    }
}
