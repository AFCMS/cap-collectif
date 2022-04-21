<?php

namespace Capco\AppBundle\Twig;

use Capco\AppBundle\SiteColor\Resolver;

class SiteColorExtension extends \Twig_Extension
{
    protected $resolver;

    public function __construct(Resolver $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'site_color';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('capco_site_color_value', [$this, 'getSiteColorValue'], ['is_safe' => ['html']]),
       ];
    }

    public function getSiteColorValue($key)
    {
        return $this->resolver->getValue($key);
    }
}
