
// +------------------------ Pmt_Yui_Paginator ------------------------+

Pmt_Yui_Paginator = function(options) {
    window.Pmt_Element.call(this, options);
    this.initialize(options);
}

Pmt_Yui_Paginator.prototype = {
   jsClassName: "Pmt_Yui_Paginator",
   id: false,
   autoEvents: [],
   container: false,
   yuiPaginator: false,
   extraContainerIds: false,
   rowsPerPage: false,
   rowsPerPageOptions: [],
   totalRecords: false,
   template: false,
   currentPage: false,
   localization: false,

   renderElement: function() {
        var paginatorOptions = {};
        if (this.localization !== null && (typeof this.localization == 'object'))
            Pmt_Util.override(paginatorOptions, this.localization);
        Pmt_Util.override(paginatorOptions, {
            rowsPerPage: this.rowsPerPage,
            totalRecords: this.totalRecords,
            initialPage: this.currentPage + 1,
            updateOnChange: true
        });

        var ic = this.getInnerContainer();
        var containers = new Array(ic);
        if (this.extraContainerIds instanceof Array) {
            containers = containers.concat(this.extraContainerIds);
        }
        paginatorOptions.containers = containers;
        if (this.template) paginatorOptions.template = this.template;
        if ((this.rowsPerPageOptions instanceof Array) && this.rowsPerPageOptions.length)
                paginatorOptions.rowsPerPageOptions = this.rowsPerPageOptions;
        this.yuiPaginator = new YAHOO.widget.Paginator(paginatorOptions);
        this.yuiPaginator.render();
        this.yuiPaginator.subscribe('rowsPerPageChange', this.handleRowsPerPageChange, null, this);
        this.yuiPaginator.subscribe('pageChange', this.handlePageChange, null, this);
        this.setVisible();
   },

   refresh: function() {
//           this.renderElement();
   },

   handleRowsPerPageChange: function() {
       if (this.rowsPerPage != this.yuiPaginator.getRowsPerPage()) {
            this.rowsPerPage = this.yuiPaginator.getRowsPerPage();
            if (this.transport) {
                this.transport.pushMessage(this.id, 'rowsPerPageChange', [this.rowsPerPage]);
            }
       }
   },

   handlePageChange: function() {
       if (this.currentPage != (this.yuiPaginator.getCurrentPage() - 1)) {
            this.currentPage = this.yuiPaginator.getCurrentPage() - 1;
            if (this.transport) {
                this.transport.pushMessage(this.id, 'pageChange', [this.currentPage]);
            }
       }
   },

   setRowsPerPage: function(rowsPerPage) {
       if (this.rowsPerPage != rowsPerPage) {
           this.rowsPerPage = rowsPerPage;
           if (this.yuiPaginator) this.yuiPaginator.setRowsPerPage(this.rowsPerPage);
       }
   },

   setTotalRecords: function(totalRecords) {
       if (this.totalRecords != totalRecords) {
           this.totalRecords = totalRecords;
           if (this.yuiPaginator) this.yuiPaginator.setTotalRecords(this.totalRecords);
       }
   },

   setCurrentPage: function(currentPage) {
       if (this.currentPage != currentPage) {
           this.currentPage = currentPage;
           if (this.yuiPaginator) this.yuiPaginator.setPage(this.currentPage + 1);
       }
   },

   setTemplate: function(template) {
       if (this.template != template) {
           this.template = template;
           if (this.yuiPaginator) this.yuiPaginator.setTemplate(this.template);
       }
   },

   doOnDelete: function() {
       if (this.yuiPaginator) {
           this.yuiPaginator.destroy();
           delete this.yuiPaginator;
       }
   }

};

Pmt_Util.extend(Pmt_Yui_Paginator, Pmt_Element);

// ------------------------------------------- Pmt_Yui_Tab_Control ------------------------------------------- //

Pmt_Yui_Tab_Control = function (options) {
    this.initialize(options);    
}

Pmt_Yui_Tab_Control.prototype = {
	jsClassName: "Pmt_Yui_Tab_Control",
    yuiTabView: false,
    tabs: false,
    yuiTabs: false,
    id: false,
    
    autoEvents: [],

    renderElement: function() {
        var ic = this.getInnerContainer();
        if (ic) {
            this.tabControls = new Array();
            this.yuiTabView = new YAHOO.widget.TabView();
            this.yuiTabView.subscribe("activeTabChange", function(ev) {
                if (this.transport) this.transport.pushMessage(this.id, 'tabSelected', [ev.newValue._paxTabId]);
            }, {}, this);
            this.yuiTabs = new Array();
            for (var i = 0; i < this.tabs.length; i++) {
                var tab = this.tabs[i];
                var yuiTab = this.yuiTabs['tab_' + tab.id] = new YAHOO.widget.Tab(tab);
                yuiTab._paxTabId = tab.id;
                if (tab.visible) this.yuiTabView.addTab(yuiTab, i);
                    else yuiTab._index = i;
            }
            this.yuiTabView.appendTo(ic);
        } else {
        }
    },
    
    getYuiTabById: function(id) {
        var res = null;
        if (this.yuiTabs['tab_' + id]) res = this.yuiTabs['tab_' + id];
        return res;
    },
    
    setTabTitle: function(tabId, tabTitle) {
        var yuiTab = this.getYuiTabById(tabId);
        if (yuiTab) {
            yuiTab.set('label', tabTitle);
        }
    },
    
    setTabDisabled: function(tabId, disabled) {
        var yuiTab = this.getYuiTabById(tabId);
        if (yuiTab) {
            yuiTab.set('label', disabled);
        }
    },
    
    setTabVisibility: function(tabId, visibility) {
        if (visibility) this.showTab(tabId);
            else this.hideTab(tabId);
    },
    
    hideTab: function(tabId) {
        var yuiTab = this.getYuiTabById(tabId);
        if (yuiTab) {
            yuiTab._index = this.yuiTabView.getTabIndex(yuiTab);
            this.yuiTabView.removeTab(yuiTab);
        }        
    },
    
    setCurrentTab: function(tabId) {
        var yuiTab = this.getYuiTabById(tabId);
        if (yuiTab) {
            this.yuiTabView.set('activeTab', yuiTab);
        } else {
        }
    },
    
    showTab: function(tabId) {
        var yuiTab = this.getYuiTabById(tabId);
        if (yuiTab && (yuiTab._index != 'undefined')) {
            this.yuiTabView.addTab(yuiTab, yuiTab._index);
            delete yuiTab._index;
        } else {
        }
    },
    
    setTabIndex: function(tabId, newIndex) {
        var yuiTab = this.getYuiTabById(tabId);
        if (yuiTab) {
            if (yuiTab._index != 'undefined') {
                yuiTab._index = newIndex;
            } else {
                this.yuiTabView.removeTab(yuiTab);
                this.yuiTabView.addTab(yuiTab, newIndex);
            }
        }        
    },

    refresh: function() {        
    },

    
    doOnDelete: function() {
    	if (this.yuiTabView) {
            var yuiTabs = this.yuiTabView.get('tabs');
    		for (var i = 0; i < yuiTabs.length; i++) this.yuiTabView.removeTab(yuiTabs[i]);
    		delete this.yuiTabs;
    		delete this.tabs;
    		delete this.yuiTabView;
    	}
    }

}

Pmt_Util.extend(Pmt_Yui_Tab_Control, Pmt_Element);

//  ---- Pmt_Yui_AutoComplete ----

Pmt_Yui_AutoComplete = function(options) {
    this.initialize(options);
}

Pmt_Yui_AutoComplete.prototype = {
    jsClassName: "Pmt_Yui_AutoComplete",
    yuiAutoComplete: false,
    autoCompleteConfig: {},
    autoCompleteProperties: {},
    dataSourceConfig: {},
    dataSourceProperties: {},
    dataSource: false,
    divElement: false,
    labelKey: false,
    textKey: false,

    setText: function(value) {
        this.editStarted = false;
        if (value != undefined) this.text = value;
        if (this.element) {
            var v = this.text;
            if (v === false) v = '';
            if (this.multiline) {
                this.element.value = v;
                //console.log("Set text to ",this.text);
            }
            else this.element.value = v;
        }
    },

    setTransport: function(transport) {
        this.transport = transport;
        this.dataSource.transport = transport;
    },

    renderElement: function() {
        Pmt_Text.prototype.renderElement.call(this);
        var ic = this.getInnerContainer();
        if (ic) {
            this.divElement = document.createElement('div');
            ic.appendChild(this.divElement);
            if (!this.dataSource) {
                this.dataSource = new Pmt_Yui_DataSource({
                    'id': this.id,
                    'transport': this.transport
                    //'dataRequestMethod': 'autoCompleteRequest',
                });
                this.dataSource.subscribe("requestEvent", this.dataSourceRequestEvent, null, this);
            }
            this.yuiAutoComplete = new YAHOO.widget.AutoComplete(this.element, this.divElement, this.dataSource, this.autoCompleteConfig);
            if (this.labelKey) {
                this.yuiAutoComplete.formatResult = function (foo) { return function() { var args = Array.prototype.slice.apply(arguments); return foo.formatAutoCompleteResult.apply(foo, args); } } (this);
                this.yuiAutoComplete.resultTypeList = false;
            }
            var i;
            if (typeof(this.autoCompleteProperties) == 'object') for (i in this.autoCompleteProperties) this.yuiAutoComplete[i] = this.autoCompleteProperties[i];
            if (typeof(this.dataSourceProperties) == 'object') for (i in this.dataSourceProperties) this.dataSource[i] = this.dataSourceProperties[i];
            try {
                this.yuiAutoComplete.itemSelectEvent.subscribe(function(ev, params) {
                    //console.log('item select', this.element.value, 'ev', ev, params[2]);
                    if (this.element) this.text = this.element.value;
                    if (this.transport) this.transport.pushMessage(this.id, 'itemSelected', [this.text, params[2]]);
                    if (this.transport) this.transport.pushMessage(this.id, 'change', [this.text]);
                }, null, this);
            } catch (e) {
                console.log("There was en error while initializing autoComplete widget: ", e);
            }
        } else {
        }
    },

    formatAutoCompleteResult: function(oResultData, sQuery, sResultMatch) {
        var res;
        console.log(arguments, this.labelKey, oResultData, oResultData[this.labelKey]);
        if (this.labelKey && oResultData[this.labelKey] !== undefined) res = oResultData[this.labelKey];
        return res;
    },

    dataSourceRequestEvent: function() {
    },

    dataResponse: function(transactionId, response) {
        if (this.dataSource && typeof(this.dataSource.dataResponse) == 'function') {
             this.dataSource.dataResponse(transactionId, response);
        }
    },

    doOnDelete: function() {
        if (this.yuiAutoComplete) {
            this.yuiAutoComplete.destroy();
            delete this.yuiAutoComplete;
        }
    }
}

Pmt_Util.extend(Pmt_Yui_AutoComplete, Pmt_Text);
