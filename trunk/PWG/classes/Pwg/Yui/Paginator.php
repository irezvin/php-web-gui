<?php

class Pwg_Yui_Paginator extends Pwg_Base implements Pwg_I_Control_Paginator, Pwg_I_Observer {

    const TEMPLATE_DEFAULT = -1;
    
    const TEMPLATE_ROWS_PER_PAGE = -2;
    
    protected $extraContainerIds = false;

    protected $rowsPerPage = 20;

    protected $totalRecords = 0;

    protected $template = false;

    protected $currentPage = 0;
    
    protected $containerAttribs = array('class' => 'yui-skin-sam');
    
    protected $visible = true;
    
    protected $localization = true;
    
    protected $pageLinks = 10;
    
    protected $pageLinks = 10;
    
    protected $rowsPerPageOptions = array(
        5, 10, 15, 20, 50, 100, 200, 1000
    );
    
    /**
     * @var Pwg_Data_Source
     */
    protected $dataSource = false;
    
    function __construct(array $options = array()) {
    	if (defined('_DEPLOY_DEFAULT_ROWS_PER_PAGE'))
    		$this->rowsPerPage = _DEPLOY_DEFAULT_ROWS_PER_PAGE;
    	parent::__construct($options);
    }
    
    protected function doGetAssetLibs() {
        return array (
            '{YUI}/fonts/fonts.css',
            '{YUI}/paginator/assets/skins/sam/paginator.css',
            '{YUI}/yahoo/yahoo.js',
            '{YUI}/dom/dom.js',
            '{YUI}/event/event.js',
            '{YUI}/element/element.js',
            '{YUI}/paginator/paginator.js',
            'widgets.js',
            'widgets/yui.js',
        ); 
    }
    
    function setOffset($recordOffset) {
        $this->setCurrentPage($this->getPageForOffset($recordOffset));
        $cp = $this->getCurrentPage();
    }

    function getOffset() {
        return intval($this->currentPage)*$this->rowsPerPage;
    }
    
    function getMaxPage() {
        return floor($this->totalRecords / $this->rowsPerPage); 
    }

    function getPageForOffset($offset) {
        return floor(min($this->totalRecords, $offset) / $this->rowsPerPage);
    }
    
    function setCurrentPage($currentPage) {
        if ($currentPage !== ($oldCurrentPage = $this->currentPage)) {
            $oldOffset = $this->getOffset();
            $this->currentPage = $currentPage;
            $newOffset = $this->getOffset();
            if ($newOffset !== $oldOffset) $this->triggerEvent('offsetChanged', array('offset' => $newOffset)); 
            $this->triggerEvent('pageChange', array('page' => $this->currentPage));
            $this->sendMessage(__FUNCTION__, array($currentPage));
            if ($this->dataSource) {
                $dataPage = $this->getPageForOffset($this->dataSource->getRecordNo());
                if ($dataPage !== $this->currentPage) $this->dataSource->setRecordNo($this->getOffset());
            }
        }
    }

    function getCurrentPage() {
        return $this->currentPage;
    }
    
    function setRowsPerPage($rowsPerPage) {
        $rowsPerPage = intval($rowsPerPage);
        if ($rowsPerPage !== ($oldRowsPerPage = $this->rowsPerPage)) {
            if ($rowsPerPage <= 0) 
                throw new Exception("rowsPerPage property must be greater than zero", E_USER_ERROR);            
            $oldOffset = $this->getOffset();
            $this->rowsPerPage = $rowsPerPage;
            if ($this->dataSource) $this->dataSource->setGroupSize($this->rowsPerPage);
            $newOffset = $this->getOffset();
            $this->triggerEvent('rowsPerPageChange', array('rowsPerPage' => $this->rowsPerPage));
            $this->triggerEvent('limitChanged', array('limit' => $this->rowsPerPage));
            if ($newOffset !== $oldOffset) $this->triggerEvent('offsetChanged', array('offset' => $newOffset));
            $this->sendMessage(__FUNCTION__, array($rowsPerPage));
        }
    }

    function getRowsPerPage() {
        return $this->rowsPerPage;
    }

    function setTotalRecords($totalRecords) {
        $totalRecords = intval($totalRecords);
        $oldOffset = $this->getOffset();
        if ($totalRecords < 0) throw new Exception("totalRecords property should be greater than or equal to zero");
        if ($totalRecords !== ($oldTotalRecords = $this->totalRecords)) {
            $this->totalRecords = $totalRecords;
            $this->sendMessage(__FUNCTION__, array($totalRecords));
        }
        if ($totalRecords < $this->totalRecords) {
            // Correct current page if necessary
            $this->setOffset($this->getOffset());
            $newOffset = $this->getOffset();
            if ($newOffset !== $oldOffset) $this->triggerEvent('offsetChanged', array('offset' => $newOffset));
        }
    }

    function getTotalRecords() {
        return $this->totalRecords;
    }

    function setTemplate($template) {
        if ($template !== ($oldTemplate = $this->template)) {
            $this->template = $template;
            $this->sendMessage(__FUNCTION__, array($this->jsGetTemplate()));
        }
    }

    function getTemplate() {
        return $this->template;
    }
    
    protected function jsGetTemplate() {
        if ($this->template === false && $this->rowsPerPageOptions) $this->template = self::TEMPLATE_ROWS_PER_PAGE;
        if ($this->template === self::TEMPLATE_DEFAULT) $res = new Ac_Js_Var('YAHOO.widget.Paginator.TEMPLATE_DEFAULT');
        elseif ($this->template === self::TEMPLATE_ROWS_PER_PAGE) $res = new Ac_Js_Var('YAHOO.widget.Paginator.TEMPLATE_ROWS_PER_PAGE');
        
        else $res = $this->template;
        
        return $res; 
    }
    
    protected function jsGetRowsPerPageOptions() {
        $res = array();
       
        if (is_array($this->rowsPerPageOptions)) foreach ($this->rowsPerPageOptions as $k => $v) {
            if (is_numeric($k)) {
                $k = $v;
            }
            $res[] = array('value' => $k, 'text' => $v);
        }
        
        
        return $res;
    }
    
    function doListPassthroughParams() {
        return array_merge(parent::doListPassthroughParams(), array(
        	'totalRecords', 
        	'currentPage', 
        	'rowsPerPage', 
        	'template', 
        	'extraContainerIds', 
        	'rowsPerPageOptions',
            'localization',
            'visible',
            'pageLinks',
        ));
    }

    protected function setPageLinks($pageLinks) {
        if ($pageLinks !== ($oldPageLinks = $this->pageLinks)) {
            $this->pageLinks = $pageLinks;
        }
    }

    function getPageLinks() {
        return $this->pageLinks;
    }    
    
    function jsGetLocalization() {
        if ($this->localization) {
            $defaults = array(
                'nextPageLinkLabel' => new Pwg_Lang_String('paginator_next_page_link_label', 'Next &gt;'),
            	'previousPageLinkLabel' => new Pwg_Lang_String('paginator_prev_page_link_label', '&lt; Prev'),
            	'firstPageLinkLabel' => new Pwg_Lang_String('paginator_first_page_link_label', '&lt;&lt; First'),
            	'lastPageLinkLabel' => new Pwg_Lang_String('paginator_last_page_link_label', 'Last &gt;&gt;'),
            );
            if (is_array($this->localization)) $res = array_merge($defaults, $this->localization);
                else $res = $defaults;
        } else $res = false;
        return $res;
    }
    
    function triggerFrontendRowsPerPageChange($rowsPerPage) {
        if ((string) $rowsPerPage != (string) $this->rowsPerPage) {
            $this->lockMessages;
            $this->setRowsPerPage($rowsPerPage);
            $this->unlockMessages();
        }
    }
    
    function triggerFrontendPageChange($page) {
        if ((string) $page != (string) $this->currentPage) {
            $this->lockMessages;
            $this->setCurrentPage($page);
            $this->unlockMessages();
        }
    }

    protected function setRowsPerPageOptions(array $rowsPerPageOptions) {
        $this->rowsPerPageOptions = $rowsPerPageOptions;
    }

    function setExtraContainerIds($extraContainerIds) {
        $this->extraContainerIds = $extraContainerIds;
        $this->sendMessage(__FUNCTION__, array($extraContainerIds));
    }

    function getExtraContainerIds() {
        return $this->extraContainerIds;
    }
    
    function setDataSource(Pwg_Data_Source $dataSource = null) {
        if ($dataSource !== ($oldDataSource = $this->dataSource)) {
            if ($oldDataSource) {
                $oldDataSource->unobserve('onRefresh', $this, 'handleDataSourceUpdate');
                $oldDataSource->unobserve('onGroupSizeChange', $this, 'handleDataSourceUpdate');
                $oldDataSource->unobserve('onCurrentRecord', $this, 'handleDataSourceUpdate');
            }
            $this->dataSource = $dataSource;
            if ($this->dataSource) {
                Pwg_Conversation::log("Set data source");
                $this->dataSource->observe('onRefresh', $this, 'handleDataSourceUpdate');
                $this->dataSource->observe('onGroupSizeChange', $this, 'handleDataSourceUpdate');
                $this->dataSource->observe('onCurrentRecord', $this, 'handleDataSourceUpdate');
                $this->refreshFromDataSource();
            }
        }
    }
    
    protected function refreshFromDataSource() {
        $this->setOffset($this->dataSource->getRecordNo());
        $this->setTotalRecords($this->dataSource->getRecordsCount());
        $gs = $this->dataSource->getGroupSize();
        if ((int) $gs) $this->setRowsPerPage((int) $gs);        
    }
    
    function handleDataSourceUpdate(Pwg_Data_Source $dataSource, $eventType, $params) {
        $this->refreshFromDataSource();
    }
    
    protected function setDataSourcePath($dataSourcePath) {
        $this->associations['dataSource'] = $dataSourcePath;
    }
    
//  +--------------------- Pwg_I_Control_Paginator implementation ----------------+    
    
    function getLimit() {
        return $this->getRowsPerPage();
    }
    
    function observeOffsetChanged(Pwg_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array()) {
        return $this->observe('offsetChanged', $observer, $methodName, $extraParams);
    }
    
    function observeLimitChanged(Pwg_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array()) {
        return $this->observe('limitChanged', $observer, $methodName, $extraParams);
    }
    
    
    function unobserveOffsetChanged(Pwg_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array()) {
        return $this->unobserve('offsetChanged', $observer, $methodName, $extraParams);
    }
    
    function unobserveLimitChanged(Pwg_I_Observer $observer, $methodName = 'handleEvent', $extraParams = array()) {
        return $this->unobserve('limitChanged', $observer, $methodName, $extraParams);
    }
    
    function handleEvent(Pwg_I_Observable $observable, $eventType, $params = array()) {}

    function setVisible($visible) {
        $visible = (bool) $visible;
        if ($visible !== ($oldVisible = $this->visible)) {
            $this->visible = $visible;
            $this->sendMessage(__FUNCTION__, array($visible));
        }
    }

    function getVisible() {
        return $this->visible;
    }

    protected function setLocalization($localization) {
        $this->localization = $localization;
    }

    function getLocalization() {
        return $this->localization;
    }
    
    
}

?>