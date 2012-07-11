<?php

namespace Pekkis\PeksuticBundle\Service;

use Symfony\Bundle\AsseticBundle\FilterManager;
use Assetic\AssetManager;
use Assetic\Factory\AssetFactory;

use \RecursiveDirectoryIterator;
use \DirectoryIterator;
use \RecursiveIteratorIterator;

/**
 * Description of AssetManager
 *
 * @author pekkis
 */
class AssetizerService
{

    private $am;
    
    private $fm;
        
    private $collections = array();
    
    private $parsers = array();
    
    private $assetFactory;
    
    
        
    public function __construct(AssetManager $assetManager, FilterManager $filterManager, $path)
    {
        $this->am = $assetManager;
        $this->fm = $filterManager;
        
        $this->path = realpath($path);
    }
    
    
    public function addCollection($collection)
    {
        $this->collections[] = $collection;
    }
    
    
    public function addParser($parser)
    {
        $this->parsers[] = $parser;
    }
    
    
    
    public function getCollections()
    {
        return $this->collections;
    }
    
    
    public function getParsers()
    {
        return $this->parsers;
    }
        
    
    /**
     * @return AssetManager
     */
    private function getAssetManager()
    {
        return $this->am;
    }
    
    /**
     * @return FilterManager
     */
    private function getFilterManager()
    {
        return $this->fm;
    }
    
    
    private function getPath()
    {
        return $this->path;
    }
    
    
    /**
     * Returns asset factory
     * 
     * @return AssetFactory
     */
    public function getAssetFactory()
    {
        if(!$this->assetFactory) {
            $this->assetFactory = new AssetFactory($this->getPath());
            $this->assetFactory->setAssetManager($this->getAssetManager());
            $this->assetFactory->setFilterManager($this->getFilterManager());
        }
        return $this->assetFactory;
    }

    
    
    
    public function dumpAssets()
    {
        
        
        $writer = new \Assetic\AssetWriter($this->getPath());
                
        
        $fm = $this->getFilterManager();
        
        foreach ($this->getParsers() as $parser) {
                
            $filters = array();
            foreach ($parser['files'] as $key => $file) {

                $filters[$key] = array();

                if(isset($file['filters'])) {
                    foreach($file['filters'] as $f) {

                        if (substr($f, 0, 1) == '?') {
                            $fn = substr($f, 1);
                            $init = (bool) !$parser['debug'];
                        } else {
                           $fn = $f;
                           $init = true;
                        }

                        if($init) {
                            if($fm->has($fn)) {
                                $filters[$key][] = $fm->get($fn);
                            }
                        }
                    }
                }

            }

            
            var_dump($filters);
            
            // die();
            
            
            $diterator = new \RecursiveDirectoryIterator($parser['directory']);
            $riterator = new RecursiveIteratorIterator($diterator, \RecursiveIteratorIterator::SELF_FIRST);

            foreach ($riterator as $file) {
                
                $skip = false;
                
                if(isset($parser['blacklist'])) {
                    foreach($parser['blacklist'] as $bl) {
                        if (preg_match($bl, $file->getPathName())) {
                            $skip = true;
                            continue; 
                        }
                    }
                }

                if ($skip) {
                    continue;
                }
                
                foreach($parser['files'] as $key => $fopts) {

                    $ppinfo = pathinfo($fopts['output']);

                    if ($file->isFile() && preg_match($fopts['pattern'], $file->getFilename())) {

                        $pinfo = pathinfo($file);

                        $pinfo['dirname'] = str_ireplace($parser['directory'], $ppinfo['dirname'], $pinfo['dirname']);
                        $pinfo['extension'] = $ppinfo['extension'];

                        $fasset = new \Assetic\Asset\FileAsset($file->getPathName(), $filters[$key]);
                        $fasset->setTargetPath($pinfo['dirname'] . '/' . $pinfo['filename'] . '.' . $pinfo['extension']);

                        $fassets[] = $fasset;

                    }

                }
            }
        }







        

        
        // Collections
        
        $woptions = array();
        
        $f = $this->getAssetFactory();
        
        foreach ($this->getCollections() as $key => $coll) {

            $woptions[$coll['options']['name'] . '_' . $key] = $coll['write'];
            
            $asset = $f->createAsset($coll['inputs'], $coll['filters'], $coll['options']);
            //if($coll['cache']) {
            //    $asset = new \Assetic\Asset\AssetCache($asset, $this->getAssetCache());                     
            // }
            $this->getAssetManager()->set($coll['options']['name'] . '_' . $key, $asset);
        }
        
        // Dumbsta
        
        
        foreach ($fassets as $fasset) {
            if (file_exists($this->getPath() . '/' . $fasset->getTargetPath())) {
                $amod = filemtime($this->getPath() . '/' . $fasset->getTargetPath());
                if ($fasset->getLastModified() <= $amod) {
                    continue;
                }
            }
            $writer->writeAsset($fasset);
            
        }

        
        
        
        $am = $this->getAssetManager();
        
        foreach ($am->getNames() as $name) {
            
            $asset = $am->get($name);
            
            if(file_exists($this->getPath() . '/' . $asset->getTargetPath())) {
                $amod = filemtime($this->getPath() . '/' . $asset->getTargetPath());
                if ($asset->getLastModified() <= $amod) {
                    continue;
                }
            }

            $writeOptions = $woptions[$name];
            
            if($writeOptions['combined']) {
                $writer->writeAsset($asset);
            }
            
            if($writeOptions['leaves']) {
                foreach($asset as $leaf) {
                    $writer->writeAsset($leaf);                    
                }
            }            
        }
                
    }
    
    
    
    
}
