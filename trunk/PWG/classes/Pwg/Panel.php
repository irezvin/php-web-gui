<?php

class Pwg_Panel extends Pwg_Group {
    
    /**
     * Following placeholders can be used in the template:
     * 
     * - {<controlId>} - child with given Id
     * - {#<controlPath>} - control by given path
     * - {(etc)} - controls that weren't shown in the template
     * - {(id)} - ID of $this element
     * - {(event <eventName> [param1 param2 ...])} - javascript code to call specified event of the panel's controller with provided parameters  
     * 
     * Note that template is evaluated on control rendering, so it can't be changed dynamically/
     */
    protected $template = '{(etc)}';
    
    //protected $controlWrapper = '<span id="{crId}_c_{cid}">{control}</span>';
    protected $controlWrapper = '{control}';
    
    protected function setControlWrapper($controlWrapper) {
        $this->controlWrapper = $controlWrapper;
    }

    function getControlWrapper() {
        return $this->controlWrapper;
    }   
    
    protected function setTemplate($template) {
        $this->template = $template;
    }

    function getTemplate() {
        return $this->template;
    }

    protected function getChunks($template) {
        $res = preg_split('#(\{[^\}]+\})#', $template, -1, PREG_SPLIT_DELIM_CAPTURE);
        return $res;
    }
    
    protected function processChunks($chunks) {
        $etcChunkKey = false;
        $controlsById = array();
        //trigger_error("Warning: '".$this->id."'.processing chunks", E_USER_NOTICE);
        foreach ($this->getOrderedDisplayChildren() as $child) {
            $controlsById[$child->getId()] = $child;
        } 
        $res = array();
        foreach ($chunks as $i => $chunk) {
            $val = $chunk;
            if (preg_match('#^\{([^\}]+)\}$#', trim($chunk), $matches)) {
                if (preg_match('#^\(([^\)]+)\)$#', trim($matches[1]), $m2)) {
                    $m2s = preg_split('#\s+#', $m2[1]);
                    if (count($m2s)) {
                        switch(trim($m2s[0])) {
                            case 'etc':
                                $etcChunkKey = $i;
                                break;
                            case 'id':
                                $val = $this->getContainerId();
                                break;
                            case 'event':
                                $js = new Ae_Js();
                                $jsc = new Ae_Js_Call('window.v_'.$this->getResponderId().'.sendEvent', array_slice($m2s, 1));
                                $val = $jsc->toJs($js, 0, 4, false);
                                break;
                        }
                    }
                } elseif (substr(trim($matches[1]), 0, 1) == '#') {
                    $path = substr(trim($matches[1]), 1);
                    if (($c = $this->getControlByPath($path)) && ($c->getDisplayParent() === $this)) {
                        $val = $this->renderChildContainer($c, $controlsById);
                    }
                } elseif (!strncmp($mc = trim($matches[1]), 'lng:', 4)) {
                	$lngId = substr($matches[1], 4);
                	$val = Pwg_Lang_Resource::getInstance()->getString($lngId);
                } else {
                    $id = trim($matches[1]);
                    if (isset($controlsById[$id])) $val = $this->renderChildContainer($controlsById[$id], $controlsById);
                }
            } 
            $res[$i] = $val;
        }
        if ($etcChunkKey === false) $etcChunkKey = count($chunks);
        $res[$etcChunkKey] = $this->renderEtc($controlsById);
        $res = implode('', $res);
        return $res;
    }
    
    protected function renderChildContainer(Pwg_I_Control $child, & $controlsById) {
        $cid = $child->getId();
        ob_start(); $child->showContainer(); $control = ob_get_clean();
        if (isset($controlsById[$cid])) unset($controlsById[$cid]);
        $res = strtr($this->controlWrapper, array(
            '{crId}' => $this->getContainerId(),
            '{id}' => $this->getId(),
            '{cid}' => $cid,
            '{control}' => $control,
        ));
        return $res;
    }
    
    protected function renderEtc($controls) {
        $x = $controls;
        $r = array();
        foreach ($x as $control) {
            $r[] = $this->renderChildContainer($control, $controls);
        }
        $res = '<span id="'.$this->getContainerId().'_etc">'.implode('', $r).'</span>';
        return $res; 
    }
    
    protected function doGetContainerBody() {
        $chunks = $this->getChunks($this->template);
        $res = $this->processChunks($chunks);
        return $res;
    }
    
    protected function doGetConstructorName() {
        return 'Pwg_Group';
    }
    
}

?>