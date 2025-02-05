<?php

namespace Capco\AppBundle\Notifier;

use Capco\AppBundle\Entity\District\ProjectDistrict;
use Capco\AppBundle\Entity\Project;
use Capco\AppBundle\GraphQL\Resolver\Media\MediaUrlResolver;
use Capco\AppBundle\GraphQL\Resolver\User\UserUrlResolver;
use Capco\AppBundle\Mailer\MailerService;
use Capco\AppBundle\Mailer\Message\District\ProjectDistrictRefererMessage;
use Capco\AppBundle\Resolver\LocaleResolver;
use Capco\AppBundle\Resolver\UrlResolver;
use Capco\AppBundle\SiteParameter\SiteParameterResolver;
use Capco\AppBundle\Twig\SiteImageRuntime;
use Symfony\Component\Routing\RouterInterface;

class ProjectDistrictFollowerNotifier extends BaseNotifier
{
    protected UserUrlResolver $userUrlResolver;
    private UrlResolver $urlResolver;
    private MediaUrlResolver $mediaUrlResolver;
    private SiteImageRuntime $siteImageRuntime;

    public function __construct(
        MailerService $mailer,
        SiteParameterResolver $siteParams,
        RouterInterface $router,
        UserUrlResolver $userUrlResolver,
        LocaleResolver $localeResolver,
        UrlResolver $urlResolver,
        MediaUrlResolver $mediaUrlResolver,
        SiteImageRuntime $siteImageRuntime
    ) {
        parent::__construct($mailer, $siteParams, $router, $localeResolver);
        $this->userUrlResolver = $userUrlResolver;
        $this->urlResolver = $urlResolver;
        $this->mediaUrlResolver = $mediaUrlResolver;
        $this->siteImageRuntime = $siteImageRuntime;
    }

    public function onNewProjectInDistrict(ProjectDistrict $district, Project $project)
    {
        $followers = $district->getFollowers();
        foreach ($followers as $follower) {
            $this->mailer->createAndSendMessage(
                ProjectDistrictRefererMessage::class,
                $project,
                [
                    'logoUrl' => $this->siteImageRuntime->getAppLogoUrl(),
                    'zoneGeoTitle' => $district->getName(),
                    'projectsURL' => $this->urlResolver->getObjectUrl($project),
                    'projectThumb' => $project->getCover()
                        ? $this->mediaUrlResolver->__invoke($project->getCover())
                        : null,
                    'projectTitle' => $project->getTitle(),
                    'siteName' => $this->siteName,
                    'baseUrl' => $this->baseUrl,
                    'siteUrl' => $this->siteUrl,
                ],
                $follower->getUser()
            );
        }
    }
}
