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

    /**
     * Array of prototypes of top children
     * @type Array 
     */
    topChildrenPrototypes: [],

    /**
     * Array of top children instances (of Pwg_Menu_Item or its descendants)
     */
    topChildren: [],

    /**
     * Registry of all children {id: Pwg_Menu_Item}
     */
    allChildren: {},

    /**
     * Menu instance if it's already created
     * @type YAHOO.widget.Menu
     */
    yuiMenu: false,

    iProps: [
        'id', 'transport', 'visible', 'disabled', 'attribs', 'classname', 'style',
        'autosubmenudisplay', 'clicktohide', 'hidedelay', 'keepopen', 'maxheight',
        'minscrollheight', 'scrollincrement', 'shadow', 'showdelay',
        'submenualignment', 'submenudelay', 'isHorizontal', 'topChildrenPrototypes'
    ],

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
        }
    },

    refresh: function() {
    },

    initializeChildContainer: Pwg_Controller.prototype.initializeChildContainer,

    doOnDelete: function() {
//    	if (this.yuiMenu) {
//    		this.yuiMenu.clearContent();
//    		delete this.yuiMenu;
//    	}
//    	delete this.element;
    },

    /**
     * Create and add child items to the menu
     *
     * This methods creates child instances, adds them to the menu or it's child and re-draws new parent if it's
     * already rendered.
     *
     * @param {Array[]} childrenArray Prototypes of child Pwg_Menu_Item's
     * @param {String} [childId] Id of already registered menu item to add given children to (or will be added to menu root)
     * @param {String} [beforeChildId] Id of menu item within menu root or child#childId' children so new items will be placed before it
     */
    addChildren: function(childrenArray, childId, beforeChildId) {
        
    },

    /**
     * Move items within the menu
     * 
     * Items identified by ids are removed from their parents and then inserted to new parent (as in addChildren).
     * New parent is re-drawn if it's already on screen.
     * 
     * @param {String[]} ids Id's of children to move
     * @param {String} [childId] Id of new parent (or children will
     */
    moveChildren: function(ids, childId, beforeChildId) {

    },

    /**
     * Deletes child items and respective javascript instances; redraws their parents that are on-screen
     *
     * @param {String[]} ids Id's of children to destroy
     */
    destroyItems: function(ids) {

    },

    /**
     * Destroys all child items from children childId (or current menu if it's not given). Redraws node identified by childId
     * if it's onscreen and had any children.
     *
     * @param {String} [childId] Id of item to destroy children (or current menu will be used).
     */
    destroyChildren: function(childId) {

    },

    /**
     * Calls method of child item.
     *
     * @param {String} childId Id of child item
     * @param {String|Function} methodName Name of method to call or method itself
     * @param {Array} [args] Arguments to pass to the method
     */
    callChildMethod: function(childId, methodName, args) {

    },

    /**
     * Handles child event and marshals it back to the server.
     *
     * @param {String} childId Id of child item
     * @param {String} eventType Name of event
     * @param {Array} [params] Event parameters
     */
    handleChildEvent: function(childId, eventType, params) {

    }

}

Pwg_Util.extend(Pwg_Menu, Pwg_Element);
Pwg_Util.augment(Pwg_Menu.prototype, Pwg_Control_Parent_Functions);