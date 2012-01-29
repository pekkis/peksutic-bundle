<?php

namespace Pekkis\PeksuticBundle\Twig\Extension;

class PeksuticExtension extends \Twig_Extension
{
    protected $basePath;
    
    public function __construct($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    public function getFunctions()
    {
        return array(
            'asset_url' => new \Twig_Function_Method($this, 'getAssetUrl', array('is_safe' => array('html'))),
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'pekkis_peksutic';
    }
    
    
    public function getAssetUrl($url)
    {
        return $this->baseUrl . $url;
    }
    
    
}
