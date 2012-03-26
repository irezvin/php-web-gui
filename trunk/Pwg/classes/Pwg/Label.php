<?php

/**
 * Pmt_Label is able to display HTML and handle clicks inside certain elements of it's html.
 * 
 * It handles all clicks on elements with HREF attribute in form "##<string>" on the server.
 * For example:
 * 
 * <code>
 *      $lbl->setHtml('Blah <a href="##foo">Link</a> Blah blah');
 * </code>
 * 
 * - If users clicks outside of the link, Click event is raised without an href parameter ($params['href'] === null);
 * - If users clicks on the link, Click event is raised with parameter $params['href'] === '##foo'.
 * 
 * This feature works *only* for elements with HREF attribute that is prefixed by double hashes ('##').
 * Such feature is extremely useful for providing simple user interactions.
 */

class Pmt_Label extends Pmt_Element {

    protected $html = '';
    
    protected $allowHrefClicks = false;
    
    protected function doListPassthroughParams() {
        return array_merge(parent::doListPassthroughParams(), array(
            'html', 'allowHrefClicks'
        )); 
    }
    
    function setHtml($value) {
    
        $prop = substr(__FUNCTION__, 3); $prop{0} = strtolower($prop{0});

        $oldValue = $this->{$prop}; if ($value !== $this->$prop) {
            $this->$prop = $value; $this->sendMessage(__FUNCTION__, array(is_null($this->$prop)? '' : $this->$prop), 1);
        }
    }
    
    function getHtml() {
        return $this->html;
    }
    
    function appendHtml($html) {
        $this->html .= $html;
        $this->sendMessage(__FUNCTION__, array($html));
    }
    
    function prependHtml($html) {
        $this->html .= $html;
        $this->sendMessage(__FUNCTION__, array($html));
    }
    
    function getContainerAttribs() {
        $res = Ae_Util::m($this->containerAttribs, $this->attribs);
        if ($this->style !== false) $res['style'] = $this->style;
        return $res;
    }
    
    function triggerFrontendClick($href = null) {
        $evt = substr(__FUNCTION__, 15); $evt{0} = strtolower($evt{0}); 
        $this->triggerEvent($evt, array('href' => $href));
    }
    
    function triggerFrontendDblclick($href = null) {
        $evt = substr(__FUNCTION__, 15); $evt{0} = strtolower($evt{0}); 
        $this->triggerEvent($evt, array('href' => $href));
    }

    function setAllowHrefClicks($allowHrefClicks) {
        if ($allowHrefClicks !== ($oldAllowHrefClicks = $this->allowHrefClicks)) {
            $this->allowHrefClicks = $allowHrefClicks;
            $this->sendMessage(__FUNCTION__, array($allowHrefClicks));
        }
    }

    function getAllowHrefClicks() {
        return $this->allowHrefClicks;
    }
    
}

?>