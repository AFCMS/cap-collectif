<?php

namespace Capco\AppBundle\Controller\Api;

use Capco\AppBundle\Entity\Locale;
use Capco\AppBundle\GraphQL\Mutation\Locale\SetUserDefaultLocaleMutation;
use Capco\AppBundle\Locale\DefaultLocaleCodeDataloader;
use Capco\AppBundle\Repository\LocaleRepository;
use Capco\AppBundle\Repository\PageRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class LocaleController extends AbstractFOSRestController
{
    private $localeRepository;
    private $pageRepository;
    private $router;
    private $userDefaultLocaleMutation;
    private $defaultLocaleCodeDataloader;
    private $translator;

    public function __construct(
        LocaleRepository $localeRepository,
        PageRepository $pageRepository,
        RouterInterface $router,
        DefaultLocaleCodeDataloader $defaultLocaleCodeDataloader,
        SetUserDefaultLocaleMutation $userDefaultLocaleMutation,
        TranslatorInterface $translator
    ) {
        $this->localeRepository = $localeRepository;
        $this->pageRepository = $pageRepository;
        $this->router = $router;
        $this->userDefaultLocaleMutation = $userDefaultLocaleMutation;
        $this->defaultLocaleCodeDataloader = $defaultLocaleCodeDataloader;
        $this->translator = $translator;
    }

    /**
     * @Route("/api/change-locale/{localeCode}", name="change_locale", defaults={"_feature_flags" = "multilangue"})
     */
    public function setUserLocale(Request $request, string $localeCode): JsonResponse
    {
        $user = $this->getUser();
        $routeName = $request->request->get('routeName', 'app_homepage');
        $params = $request->request->get('routeParams', []);
        $keptParams = $params['_route_params'] ?? [];

        if (null !== $user) {
            $this->userDefaultLocaleMutation->setUserDefaultLocale($user, $localeCode);
        } else {
            $locale = $this->localeRepository->findOneBy([
                'code' => $localeCode,
                'published' => true,
            ]);
            if (!$locale || !($locale instanceof Locale)) {
                throw new BadRequestHttpException(
                    "The locale with code ${localeCode} does not exist or is not enabled."
                );
            }
        }
        $request->setLocale($localeCode);
        $keptParams['_locale'] = $localeCode;

        $this->handlePageSlug($routeName, $keptParams);

        try {
            $redirectPath = $this->router->generate($routeName, $keptParams);
            $defaultLocaleCode = $this->defaultLocaleCodeDataloader->__invoke();
            if (
                $localeCode !== $defaultLocaleCode &&
                0 !== strpos($redirectPath, '/' . substr($localeCode, 0, 2) . '/')
            ) {
                $redirectPath = $this->router->generate('app_homepage', $localeCode);
            }
        } catch (\Exception $exception) {
            $redirectPath = $this->router->generate('app_homepage', ['_locale' => $localeCode]);
        }

        return new JsonResponse([
            'locale' => $localeCode,
            'path' => $redirectPath,
        ]);
    }

    private function handlePageSlug(?string $routeName, array &$params): void
    {
        if (
            $routeName &&
            isset($params['slug'], $params['_locale']) &&
            'app_page_show' === $routeName
        ) {
            if (!$this->handleCharterSlug($params)) {
                $page = $this->pageRepository->getBySlug($params['slug']);
                if ($page && ($slug = $page->getSlug($params['_locale']))) {
                    $params['slug'] = $slug;
                }
            }
        }
    }

    /**
     * Charter is a particular case where we have to translate the slug.
     * Return true if slug matches a charter.
     */
    private function handleCharterSlug(array &$params): bool
    {
        foreach ($this->localeRepository->findPublishedLocales() as $locale) {
            $translation = $this->translator->trans(
                'charter',
                [],
                'CapcoAppBundle',
                $locale->getCode()
            );
            if ($params['slug'] === strtolower($translation)) {
                $params['slug'] = strtolower(
                    $this->translator->trans('charter', [], 'CapcoAppBundle', $params['_locale'])
                );

                return true;
            }
        }

        return false;
    }
}
