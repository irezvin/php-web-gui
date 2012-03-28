Pwg_Menu_Item = function (options) {
    this.createSetters();
    this.initialize(options);
}

Pwg_Menu_Item.prototype = {
	jsClassName: "Pwg_Menu_Item",
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
    
    configProps: [
		'disabled', 'classname', 'helptext', 'text', 'checked', 'position', 'url'
    ],
    
    setters: [
        'disabled', 'classname', 'helptext', 'text', 'checked', 'url'
    ],
    
    autoEvents: [],

    yuiMenuItem: false,
    
    children: [],
    parentId: false,

    createSetters: function() {
        for (var i = 0; i < this.setters.length; i++) {
            var setterName = 'set' + Pwg_Util.ucFirst(this.setters[i]);
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
                Pwg_Yui_Util.fixMenuDisplay(sm);
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
    	if (!this.url) {
    		this.sendMessage('click');
    		YAHOO.util.Event.stopEvent(args[0]);
    	}
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

    initializeChildContainer: Pwg_Controller.prototype.initializeChildContainer,
    
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

Pwg_Util.extend(Pwg_Menu_Item, Pwg_Element);
Pwg_Util.augment(Pwg_Menu_Item.prototype, Pwg_Control_Parent_Functions);

Pwg_Menu = function (options) {
    this.createSetters();
    this.initialize(options);
}

Pwg_Menu.prototype = {
	jsClassName: "Pwg_Menu",
    container: false,
    element : false,
    id: false,
    transport: false,

    visible: null,
    disabled: null,
    attribs: {},
    classname: false,
    style: {},

    isHorizontal: null,

    autosubmenudisplay: null,
    clicktohide: null,
    hidedelay: null,
    keepopen: null,
    maxheight: null,
    minscrollheight: null,
    scrollincrement: null,
    shadow: null,
    showdelay: null,
    submenualignment: null,
    submenudelay: null,
    
    delayedRender: null,

    children: [],

    configProps: [
		'autosubmenudisplay', 'clicktohide', 'hidedelay', 'keepopen', 'maxheight',
        'minscrollheight', 'scrollincrement', 'shadow', 'showdelay',
        'submenualignment', 'submenudelay', 'visible', 'disabled', 'classname'
    ],

    setters: [
        'autosubmenudisplay', 'clicktohide', 'hidedelay', 'keepopen', 'maxheight',
        'minscrollheight', 'scrollincrement', 'shadow', 'showdelay',
        'submenualignment', 'submenudelay', 'visible', 'disabled', 'classname'
    ],

    autoEvents: [],

    yuiMenu: false,

    children: [],

    createSetters: function() {
        for (var i = 0; i < this.setters.length; i++) {
            var setterName = 'set' + Pwg_Util.ucFirst(this.setters[i]);
            if (this[setterName] === undefined) {
                this[setterName] = function(propName) {
                    return function(value) {
                        if (value === undefined) value = this[propName];
                            else this[propName] = value;
                        if (this.yuiMenu) this.yuiMenu.cfg.setProperty(propName, value);
                    };
                } (this.setters[i]);
            }
        }
    },

    addChild: function(pmtMenuItem) {
    	if (this.yuiMenu && pmtMenuItem.yuiMenuItem) {
            this.yuiMenu.addItem(pmtMenuItem.yuiMenuItem);
            for (var i = this.children.length - 1; i >= 0; i--) {
                if (this.children[i] === pmtMenuItem) this.children.splice(i, 1);
            }
            this.updateMenuElement();
        } else {
            this.children.push(pmtMenuItem);
        }
    	if (this.delayedRender) this.delayedRender.call();
    },

    createMenu: function(c) {
        var menuConfig = {};
        for (var i = 0; i < this.configProps.length; i++) {
    		if (this[this.configProps[i]] !== null && this[this.configProps[i]] !== undefined) {
    			menuConfig[this.configProps[i]] = this[this.configProps[i]];
    		}
    	}
    	this.yuiMenu = new c(this.container, menuConfig);
    	this.element = this.yuiMenu.element;
    	return this.yuiMenu;
    },
    
    updateMenuElement: function() {
        for (var i = 0; i < this.yuiMenu._aListElements.length; i++) {
        	if (!this.yuiMenu._aListElements[i].parentNode)
        		this.yuiMenu.body.appendChild(this.yuiMenu._aListElements[i]);
        }
    },

    renderElement: function() {
        if (!this.yuiMenu) {
            var c = YAHOO.widget.Menu;
            if (this.isHorizontal) {
                c = YAHOO.widget.MenuBar;
            }
            this.createMenu(c);
            this.yuiMenu.render();
        	Pwg_Yui_Util.fixMenuDisplay(this.yuiMenu);
            for (var i = 0; i < this.children.length; i++) this.children[i].renderElement();
            this.delayedRender = new Pwg_Util.DelayedCall(this.yuiMenu.render, null, this.yuiMenu, [], 150, false);
        }
    },

    refresh: function() {
    },

    initializeChildContainer: Pwg_Controller.prototype.initializeChildContainer,
    
    doOnDelete: function() {
    	if (this.yuiMenu) {
    		this.yuiMenu.clearContent();
    		delete this.yuiMenu;
    	}
    	delete this.element;
    }
}

Pwg_Util.extend(Pwg_Menu, Pwg_Element);
Pwg_Util.augment(Pwg_Menu.prototype, Pwg_Control_Parent_Functions);