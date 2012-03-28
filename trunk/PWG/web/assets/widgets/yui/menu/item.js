Pmt_Menu_Item = function() {
    
}

Pmt_Menu_Item.factory = function(prototype) {
    var constructor = Pmt_Menu_Item;
    if ((typeof prototype.constructor) == 'string' && (typeof window[prototype.constructor]) == 'function') {
        constructor = window[prototype.constructor];
    } else {
        if ((typeof prototype.constructor) == 'function') constructor = prototype.constructor;
    }
    var res = new constructor (prototype);
    return res;
}

Pmt_Menu_Item.prototype = {

	jsClassName: "Pmt_Menu_Item",
    attribs: {},
    style: {},
    classname: false,
    container: false,
    element : false,
    id: false,
    transport: false,

    visible: null,
    text: null,
    disabled: null,
    checked: null,
    imageUrl: null,
    helptext: null,
    submenuIsHorizontal: null,

    url : null,

    iProps: [
       'visible', 'attribs', 'style', 'classname', 'id', 'transport',
       'text', 'disabled', 'checked', 'imageUrl', 'helpText',
       'childrenConfigs', 'children', 'parentId', 'submenuIsHorizontal',
       'position', 'url'
    ],

    configProps: [
		'disabled', 'classname', 'helptext', 'text', 'checked', 'position', 'url'
    ],

    setters: [
        'disabled', 'classname', 'helptext', 'text', 'checked', 'url'
    ],

    autoEvents: [],

    yuiMenuItem: false,
    childrenPrototypes: [],
    childrenInstances: [],
    menuId: false,

    createSetters: function() {
        for (var i = 0; i < this.setters.length; i++) {
            var setterName = 'set' + Pmt_Util.ucFirst(this.setters[i]);
            if (this[setterName] === undefined) {
                this[setterName] = function(propName) {
                    return function(value) {
                        if (value === undefined) value = this[propName];
                            else this[propName] = value;
                        if (this.yuiMenuItem) this.yuiMenuItem.cfg.setProperty(propName, value);
                    };
                } (this.setters[i]);
            }
        }
    },

    addChild: function(pmtMenuItem) {
        if (this.yuiMenuItem && pmtMenuItem.yuiMenuItem) {
            var sm = this.yuiMenuItem.cfg.getProperty('submenu');
            if (!sm) {
                var smc = this.submenuIsHorizontal? YAHOO.widget.MenuBar : YAHOO.widget.Menu;
                var tmpDiv = document.createElement('div');
                var tmpDivBd = document.createElement('div');
                tmpDivBd.className = 'bd';
                tmpDiv.appendChild(tmpDivBd);
                document.body.appendChild(tmpDiv);
                var smCfg = {parent: this.yuiMenuItem};
                if (this.submenuIsHorizontal) {
                	smCfg.submenuAlignment = ["tl", "bl"];
                }
                sm = new smc(tmpDiv, smCfg);
                sm.ITEM_TYPE = this.submenuIsHorizontal? YAHOO.widget.MenuBarItem : YAHOO.widget.MenuItem;
                sm.render();
                Pmt_Yui_Util.fixMenuDisplay(sm);
                this.yuiMenuItem.cfg.setProperty('submenu', sm);
            }
            if (
            		pmtMenuItem.yuiMenuItem instanceof YAHOO.widget.MenuItem && sm instanceof YAHOO.widget.MenuBar
            		|| pmtMenuItem.yuiMenuItem instanceof YAHOO.widget.MenuBarItem && sm instanceof YAHOO.widget.Menu
            	) {
//            	console.log("Wrong item type!");
            }
            sm.addItem(pmtMenuItem.yuiMenuItem);
            for (var i = 0; i < sm._aListElements.length; i++) {
            	if (!sm._aListElements[i].parentNode)
            		sm.body.appendChild(sm._aListElements[i]);
            }

            for (var i = this.children.length - 1; i >= 0; i--) {
                if (this.children[i] === pmtMenuItem) this.children.splice(i, 1);
            }
        } else {
            this.children.push(pmtMenuItem);
        }
    },

    getParent: function() {
    	var res = null;
        if (this.parentId) {
    		if (typeof(window[this.parentId]) === 'object' && typeof(window[this.parentId].addChild) === 'function')
                res = window[this.parentId];
        }
        return res;
    },

    createMenuItem: function(c) {
        if (!this.yuiMenuItem) {
            var menuItemConfig = {};
            for (var i = 0; i < this.configProps.length; i++) {
                if (this[this.configProps[i]] !== null && this[this.configProps[i]] !== undefined) {
                    menuItemConfig[this.configProps[i]] = this[this.configProps[i]];
                }
            }
            var text = this.text;
            this.yuiMenuItem = new c(text, menuItemConfig);
            this.yuiMenuItem.subscribe('click', this.handleMenuItemClick, this.yuiMenuItem, this);
            this.element = this.yuiMenuItem.element;
            var p = this.getParent();
            if (p) p.addChild(this);
        }
    	return this.yuiMenuItem;
    },

    handleMenuItemClick: function(evtType, args, menuItem) {
    	if (!this.url) this.sendMessage('click');
    },

    renderElement: function() {
        if (!this.yuiMenuItem) {
            var p = this.getParent();
            var c = YAHOO.widget.MenuItem;
            if ((typeof(p) === 'object') && (p.isHorizontal || p.submenuIsHorizontal)) {
            	c = YAHOO.widget.MenuBarItem;
            }
            this.createMenuItem(c);
            for (var i = 0; i < this.children.length; i++) this.children[i].renderElement();
        }
    },

    refresh: function() {
    },

    initializeChildContainer: Pmt_Controller.prototype.initializeChildContainer,

    doOnDelete: function() {
    	if (this.yuiMenuItem && this.yuiMenuItem.cfg.getProperty('submenu')) {
    		try {
                this.yuiMenuItem.cfg.getProperty('submenu').clearContent();
            } catch (e) {
                console.log("Exception catched while clearing submenu content: ", e);
            }
    		try {
                this.yuiMenuItem.cfg.getProperty('submenu').clearContent();
                this.yuiMenuItem.destroy();
            } catch (e) {
                console.log("Exception catched while destoying submenu: ", e);
            }
    		delete this.yuiMenuItem;
    	}
    	if (this.element) {
            if (this.element.parentNode) this.element.parentNode.removeChild(this.element);
        }
        delete this.element;
    }
    

}