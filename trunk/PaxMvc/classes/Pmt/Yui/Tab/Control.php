<?php

class Pmt_Yui_Tab_Control extends Pmt_Composite_Display {
    
    protected $allowedChildrenClass = 'Pmt_Yui_Tab_Sheet';

    protected $allowedDisplayChildrenClass = 'Pmt_Yui_Tab_Sheet';
    
    protected $containerAttribs = array('class' => 'yui-skin-sam');
    
    /**
     * @var Pmt_Yui_Tab_Sheet
     */
    protected $currentTab = false;
    
    /**
     * @return Pmt_Yui_Tab_Sheet
     */
    function getCurrentTab() {
        if ($this->currentTab === false && count($odc = $this->getOrderedDisplayChildren())) {
            $this->currentTab = $odc[0];
        }
        return $this->currentTab;
    }
        
    function updateTabTitle(Pmt_Yui_Tab_Sheet $tab) {
        if ($tab->getParent() !== $this) throw new Exception("Tab {$tab} is not a child of '{$this}'");
        $this->sendMessage('setTabTitle', array($tab->getId(), $tab->getTitle()));
    }
    
    function updateTabOrder(Pmt_Yui_Tab_Sheet $tab) {
        if ($tab->getParent() !== $this) throw new Exception("Tab {$tab} is not a child of '{$this}'");
        $this->sendMessage('setTabIndex', array($tab->getId(), $tab->getDisplayOrder()));
    }
    
    function updateTabVisibility(Pmt_Yui_Tab_Sheet $tab) {
        if ($tab->getParent() !== $this) throw new Exception("Tab {$tab} is not a child of '{$this}'");
        $this->sendMessage('setTabVisibility', array($tab->getId(), $tab->getVisible()));
    }
    
    /**
     * @param string $id
     * @return Pmt_Yui_Tab_Sheet
     */
    function getControl($id) {
        return parent::getControl($id);
    }
    
    function setCurrentTab(Pmt_Yui_Tab_Sheet $tab) {
        if ($this->currentTab !== $tab) {
            $this->currentTab = $tab;
            $this->sendMessage('setCurrentTab', array($tab->getId()));
            $this->triggerEvent('currentTabChange', array('currentTab' => $tab)); 
        }
    }
    
    function triggerFrontendTabSelected($id) {
        if ($tab = $this->getControl($id)) {
            $this->lockMessages++;
            $this->setCurrentTab($tab);
            $this->lockMessages--;
        }
    }
    
//  function triggerFrontendActiveTabChange($tabId) {
//      $this->controller->logMessage("Active tab changed: ", $tabId);
//  }
    
//  Template methods    
    
    protected function doGetContainerBody() {
        return '';
    }
    
    protected function doFrontendUpdateChildPosition(Pmt_I_Control $child, $oldIndex, $newIndex) {
        $this->updateTabOrder($child);
    }
    
    function hasJsObject() {
        return true;
    }
    
    function hasContainer() {
        return true;
    }
        
    protected function doGetAssetLibs() {
        return array_merge(parent::doGetAssetLibs(), array(
            //'{YUI}/yahoo/yahoo-dom-event.js',
            '{YUI}/yahoo/yahoo.js',
            '{YUI}/dom/dom.js',
            '{YUI}/event/event.js',
            '{YUI}/element/element.js',
            '{YUI}/tabview/tabview.js',
            'widgets.js',
            'widgets/yui.js',
            '{YUI}/fonts/fonts-min.css',
            '{YUI}/tabview/assets/skins/sam/tabview.css',
        ));
    }
    
    protected function doOnGetInitializer(Pm_Js_Initializer $initializer) {
        parent::doOnGetInitializer($initializer);
        //Pmt_Conversation::log($this->)
        
        $initializer->constructorParams[0]['tabs'] = $this->getOrderedDisplayChildren(); 
    }
    
}

?>