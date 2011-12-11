<?php

class Pmt_Renderer implements Pm_I_Renderer {

    protected $allAssets = array();
    
    protected $allContainersHtml = array();
    
    protected $allInitializers = array();
    
    function renderAssets(array $assets) {
        $this->allAssets = array_unique(array_merge($this->allAssets), $assets);
    }
    
    function renderContainer($containerHtml) {
        if (strlen($containerHtml)) $this->allContainersHtml[] = $containerHtml;
    }
    
    function renderInitializer(Pm_Js_Initializer $initializer) {
        $this->allInitializers[] = $initializer;
    }
    
    function getAllAssets() {
        return $this->allAssets;
    }
    
    function getAllContainersHtml($implodeStr = "\n") {
        if ($implodeStr === false) $res = $this->allContainersHtml;
            else $res = implode($implodeStr, $this->allContainersHtml);
        return $res;
    }
    
    function getAllInitializers(Ae_Js $js = null) {
        if (is_null($js)) $res = $this->allInitializers;
        else {
            $a = array();
            foreach ($this->allInitializers as $i) {
                $a[] = $i->getInitScript($js, 0, true);
            }
            $res = implode("\n", $a);
        }
        return $res;
    }
    
}

?>
