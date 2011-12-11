<?php

class Pm_Js_Initializer {
    
    var $jsLib = false;
    
    var $varName = false;
    
    var $constructorName = false;
    
    var $constructorParams = array();
    
    var $beforeScript = array();
    
    var $afterScript = array();
    
    var $topHtml = array();
    
    var $bottomHtml = array();
    
    var $initializers = array();
    
    function getInitScriptForChildren(Ae_Js $js = null, $indent = 0) {
        if (is_null($js)) {
            $js = new Ae_Js;
        }
        $r = array();
        foreach ($this->initializers as $i) {
            $s = $i->getInitScript($js, $indent);
            if (strlen(trim($s))) $r[] = $s; 
        }
        return $js->fixIndent(implode("\n", $r), $indent)."\n";
    }
    
    protected function prepareScript(array $script = array(), Ae_Js $js) {
        $res = array();
        foreach ($script as $s) {
            if (is_string($s)) $res[] = $s;
            elseif (is_array($s)) $res[] = $this->prepareScript($s);
            elseif ($s instanceof Ae_Js_Call) $res[] = $s->toJs($js);
            else throw new Exeption("Disallowed type in Ae_Js_Initializer script: ".gettype(($s))." / ".get_class($s));
        }
        return $res;
    }
    
    function getInitScript(Ae_Js $js = null, $indent = 0, $withChildren = true) {
        $implParams = array();
        if (is_null($js)) {
            $js = new Ae_Js;
        }
        $r = array();
        if ($this->beforeScript) $r[] = implode(";\n", $this->prepareScript($this->beforeScript, $js));
        if ($this->varName && $this->constructorName) {
            $implParams = array();
            foreach ($this->constructorParams as $cp) $implParams[] = $js->toJs($cp, $indent);
        }
        if ($this->varName || $this->constructorName) {
            $r[] = $this->varName.' = new '.$this->constructorName.' ('.implode(",\n    ", $implParams).'); ';
        }
        if ($this->afterScript) $r[] = implode(";\n", $this->prepareScript($this->afterScript, $js)).';';
        if ($this->initializers && $withChildren) $r[] = $this->getInitScriptForChildren($js, $indent);
        return $js->fixIndent(implode("\n", $r), $indent); 
    }
    
    function showJavascriptElement(Ae_Js $js = null, $indent = 0) {
        if (is_null($js)) {
            $js = new Ae_Js;
        }
        echo $js->fixIndent("<script type='text/javascript'>\n", $indent);
        echo $js->fixIndent($this->getInitScript()."\n", $indent);
        echo $js->fixIndent("</script>\n", $indent);
    }
    
    function getTopHtml($asArray = false) {
        $arr = $this->topHtml;
        foreach ($this->initializers as $i)
            $arr = array_merge($arr, $i->getTopHtml(true));
        if ($asArray) $res = $arr;
            else $res = implode("\n", $arr);
        return $res;
    }
    
    function getBottomHtml($asArray = false) {
        $arr = $this->bottomHtml;
        foreach ($this->initializers as $i)
            $arr = array_merge($arr, $i->getBottomHtml(true));
        $res = implode("\n", $arr);
        if ($asArray) $res = $arr;
            else $res = implode("\n", $arr);
        return $res;
    }
    
}

?>