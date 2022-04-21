
<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),

            // Redis
            new Snc\RedisBundle\SncRedisBundle(),

            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),

            // Doctrine candies
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new Hautelook\AliceBundle\HautelookAliceBundle(),

            // Doctrine migrations
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),

            // sonata admin
            new Sonata\CoreBundle\SonataCoreBundle(),
            new Sonata\IntlBundle\SonataIntlBundle(),
            new Sonata\BlockBundle\SonataBlockBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),
            new Sonata\AdminBundle\SonataAdminBundle(),

            // sonata user admin
            new Sonata\EasyExtendsBundle\SonataEasyExtendsBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new Sonata\UserBundle\SonataUserBundle('FOSUserBundle'),

            // oauth user
            new HWI\Bundle\OAuthBundle\HWIOAuthBundle(),

            // sonata media
            new Sonata\MediaBundle\SonataMediaBundle(),
            new Sonata\ClassificationBundle\SonataClassificationBundle(),
            new Liip\ImagineBundle\LiipImagineBundle(),

            // project bundles
            new Capco\AppBundle\CapcoAppBundle(),
            new Capco\AdminBundle\CapcoAdminBundle(),
            new Capco\UserBundle\CapcoUserBundle(),
            new Capco\MediaBundle\CapcoMediaBundle(),
            new Capco\ClassificationBundle\CapcoClassificationBundle(),

            // search
            new FOS\ElasticaBundle\FOSElasticaBundle(),

            // feature activation / deactivation
            new Qandidate\Bundle\ToggleBundle\QandidateToggleBundle(),

            // typography concerns
            new Joli\TypoBundle\JoliTypoBundle(),

            // CKEditor
            new \CoopTilleuls\Bundle\CKEditorSonataMediaBundle\CoopTilleulsCKEditorSonataMediaBundle(),
            new \Ivory\CKEditorBundle\IvoryCKEditorBundle(),

            // NewRelic
            new Ekino\Bundle\NewRelicBundle\EkinoNewRelicBundle(),

            new Caxy\HtmlDiffBundle\CaxyHtmlDiffBundle(),

            // API
            new \FOS\RestBundle\FOSRestBundle(),
            new \Nelmio\ApiDocBundle\NelmioApiDocBundle(),
            new \Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle(),
            new Gfreeau\Bundle\GetJWTBundle\GfreeauGetJWTBundle(),
            new Bazinga\Bundle\HateoasBundle\BazingaHateoasBundle(),

            // GraphQL
            new Overblog\GraphQLBundle\OverblogGraphQLBundle(),

            // Swarrot to publish and consume rabbitmq messages
            new Swarrot\SwarrotBundle\SwarrotBundle(),

            // Server side Js rendering
            new Limenius\ReactBundle\LimeniusReactBundle(),

            new Misd\PhoneNumberBundle\MisdPhoneNumberBundle(),

            // Secure our forms against XSS
            new Exercise\HTMLPurifierBundle\ExerciseHTMLPurifierBundle(),

            // Saml
            new Hslavich\SimplesamlphpBundle\HslavichSimplesamlphpBundle(),

            // Excel files generation
            new Liuggio\ExcelBundle\LiuggioExcelBundle(),

            // PolyCollection support.
            new \Infinite\FormBundle\InfiniteFormBundle(),
        ];

        if (in_array($this->getEnvironment(), ['dev', 'test'])) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new Elao\WebProfilerExtraBundle\WebProfilerExtraBundle();
        }

        return $bundles;
    }

    public function getCacheDir()
    {
        return dirname(__DIR__).'/var/cache/'.$this->environment;
    }
    public function getLogDir()
    {
        return dirname(__DIR__).'/var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
