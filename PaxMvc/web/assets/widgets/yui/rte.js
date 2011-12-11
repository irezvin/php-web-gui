Pmt_Yui_Rte = function (options) {
    this.createSetters();
    this.initialize(options);

}

Pmt_Yui_Rte.prototype = {
    jsClassName: "Pmt_Yui_Rte",
    attribs: {},
    style: {},
    className: false,
    container: false,
    id: false,
    transport: false,
    isSimple: false,
    visible: true,
    width: null,
    height: null,
    dompath: null,
    animate: null,
    text: null,
    toolbarTitle: null,
    toolbarCollapsed: null,
    extraConfig: {},
    yuiRte: false,
    element : false,
    htmlElement: false,
    disabled: false,
    canEditHtml: false,
    resizeable: false,
    invalidHtmls: false,
    
    configProps: [
        'width', 'height', 'dompath', 'animate'
    ],
    setters: [
        'width', 'height', 'dompath', 'animate'
    ],
    changeCall: false,
    autoEvents: [],
    possibleChangeEvents: [
        'editorClick',
        'editorDoubleClick',
        'editorKeyUp',
        'editorMouseUp',
        'nodeChange'
    ],

    createSetters: function() {
        for (var i = 0; i < this.setters.length; i++) {
            var setterName = 'set' + Pmt_Util.ucFirst(this.setters[i]);
            if (this[setterName] === undefined) {
                this[setterName] = function(propName) {
                    return function(value) {
                        if (value === undefined) value = this[propName];
                            else this[propName] = value;
                        if (this.yuiRte) this.yuiRte.set(propName, value);
                    };
                } (this.setters[i]);
            }
        }
    },

    getRteConfig: function() {
        var res = this.extraConfig;
        for (var i = 0; i < this.configProps.length; i++) {
            if (this[this.configProps[i]] !== null) res[this.configProps[i]] = this[this.configProps[i]];
        }
        if (res.height) res.height = res.height + "px";
        if (res.width) res.width = res.width + "px";
        return res;
    },

    setDisabled: function(disabled) {
        if (disabled !== undefined) this.disabled = disabled;
        this.yuiRte.set('disabled', this.disabled);
//        if (this.yuiRte) {
//            if (disabled) {
//                this.yuiRte._getDoc().body.className = 'yui-noedit';
//                this.yuiRte.set('allowNoEdit', true);
//                this.yuiRte.toolbar.set('disabled', true);
//            } else {
//                this.yuiRte._getDoc().body.className = '';
//                this.yuiRte.set('allowNoEdit', false);
//                this.yuiRte.toolbar.set('disabled', false);
//            }
//        }
    },

    setToolbarTitle: function(toolbarTitle) {
        if (toolbarTitle !== undefined) this.toolbarTitle = toolbarTitle;
        if (this.yuiRte && this.yuiRte.toolbar) this.yuiRte.toolbar.set('titlebar', this.toolbarTitle && this.toolbarTitle.length || !this.toolbarCollapsed ? this.toolbarTitle : '&nbsp;');
    },

    setToolbarCollapsed: function(toolbarCollapsed) {
        if (toolbarCollapsed !== undefined) this.toolbarCollapsed = toolbarCollapsed;
        if (this.yuiRte && this.yuiRte.toolbar) this.yuiRte.toolbar.collapse(this.toolbarCollapsed);
    },

    setText: function(value) {
        if (value !== undefined) this.text = value;
        if (this.yuiRte) this.yuiRte.setEditorHTML(this.text);
    },
    
    setInvalidHtml: function(value) {
    	if (value !== undefined) this.invalidHtml = value;
    	if (this.yuiRte) this.yuiRte.invalidHTML = this.invalidHtml;
    },

    setVisible: function(value) {
        if (value != undefined) this.visible = value;
        if (this.container) this.container.style.display = this.visible? '' : 'none';
    },

    renderElement: function() {
        YAHOO.util.Event.throwErrors = true;
        var cfg = this.getRteConfig();

        this.element = this.getInnerContainer().getElementsByTagName('textarea')[0];
        this.element.value = this.text;

        if (this.isSimple)
            this.yuiRte = new YAHOO.widget.SimpleEditor(this.element, cfg);
        else
            this.yuiRte = new YAHOO.widget.Editor(this.element, cfg);

        // Workaround for second and subsequent Rte instances not able to create blank image
        // in conjunction with Prototype.js (or for some other fsckng reason)

        //this.yuiRte.EDITOR_PANEL_ID = this.id + '-rte'; // this crap didn't help

        this.yuiRte._old_cmd_insertimage = this.yuiRte.cmd_insertimage;
        this.yuiRte.cmd_insertimage = function(value) {
            if (!value && YAHOO.widget.EditorInfo.blankImage) {
                value = YAHOO.widget.EditorInfo.blankImage;
            }
            return this._old_cmd_insertimage(value);
        }

        this.yuiRte.render();
        this.setInvalidHtml();

        if (this.canEditHtml) {
        	
        	 // From YUI example...
        	
        	 this.yuiRte.on('toolbarLoaded', function() {
 	        		this._editCodeState = 'off';
 	        		
        	        var codeConfig = {
        	            type: 'push', label: 'Edit HTML Code', value: 'editcode'
        	        };
        	        this.toolbar.addButtonToGroup(codeConfig, 'insertitem');
        	        
        	        this.toolbar.on('editcodeClick', function() {
        	            var ta = this.get('element'),
        	                iframe = this.get('iframe').get('element');

        	            if (this._editCodeState == 'on') {
        	            	this._editCodeState = 'off';
        	                this.toolbar.set('disabled', false);
        	                this.setEditorHTML(ta.value);
        	                if (!this.browser.ie) {
        	                    this._setDesignMode('on');
        	                }

        	                YAHOO.util.Dom.removeClass(iframe, 'editor-hidden');
        	                YAHOO.util.Dom.addClass(ta, 'editor-hidden');
        	                this.show();
        	                this._focusWindow();
        	            } else {
        	                ta.style.height = this.get('element').clientHeight + 'px';
        	            	this._editCodeState = 'on';
        	                this.cleanHTML();
        	                YAHOO.util.Dom.addClass(iframe, 'editor-hidden');
        	                YAHOO.util.Dom.removeClass(ta, 'editor-hidden');
        	                this.toolbar.set('disabled', true);
        	                this.toolbar.getButtonByValue('editcode').set('disabled', false);
        	                this.toolbar.selectButton('editcode');
        	                this.dompath.innerHTML = 'Editing HTML Code';
        	                this.hide();
        	            }
        	            return false;
        	        }, this, true);

        	        this.on('cleanHTML', function(ev) {
        	            this.get('element').value = ev.html;
        	        }, this, true);
        	        
        	        this.on('afterRender', function() {
        	            var wrapper = this.get('editor_wrapper');
        	            wrapper.appendChild(this.get('element'));
        	            this.setStyle('width', '100%');
        	            this.setStyle('height', '100%');
        	            this.setStyle('visibility', '');
        	            this.setStyle('top', '');
        	            this.setStyle('left', '');
        	            this.setStyle('position', '');

        	            this.addClass('editor-hidden');
        	        }, this, true);
        	    }, this.yuiRte, true);
        	
        }
        
        if (this.resizeable) {
        
        	// from YUI example...
        	
        	var pmControl = this;
        	var resize;
        	var editor = this.yuiRte;
        	
        	this.yuiRte.on('editorContentLoaded', function() {
                 resize = new YAHOO.util.Resize(editor.get('element_cont').get('element'), {
                     handles: ['br'],
                     autoRatio: true,
                     //status: true,
                     proxy: true,
                     setSize: false //This is where the magic happens
                 });

                 pmControl._resize = resize;

                 var startResizeFn = function() {
                     this.hide();
                     this.set('disabled', true);
                     //resize._proxy.style.display="";
                 }

                 var resizeFn = function(args) {
                     var h = args.height;
                     var th = (this.toolbar.get('element').clientHeight + 2); //It has a 1px border..
                     var dh = (this.dompath.clientHeight + 1); //It has a 1px top border..
                     var newH = (h - th - dh);
                     this.set('width', args.width + 'px');
                     this.set('height', newH + 'px');
                     pmControl.width = args.width;
                     pmControl.height = newH;
                     pmControl.sendMessage('resize', args.width, newH);
                     //resize._proxy.style.display="none";
                     this.set('disabled', false);
                     this.show();
                 }
                 resize.on('startResize', startResizeFn, editor, true);
                 resize.on('resize', resizeFn, editor, true);
                 resize._proxy.style.zIndex="-1";
                 resize._proxy.style.left="1px";
                 resize._proxy.style.top="1px";

                 //resize.set('proxy', true);
                 //startResizeFn.call(editor);
                 //resizeFn.call(editor, {height: resize.get('height'), width: resize.get('width')});
                 
             });

        	
        }
        	
        
        this.yuiRte.on('toolbarLoaded', function() {
            this.setToolbarTitle();
            this.setToolbarCollapsed();
            this.yuiRte.toolbar.on('toolbarCollapsed', this.handleToolbarCollapseChange, true, this);
            this.yuiRte.toolbar.on('toolbarExpanded', this.handleToolbarCollapseChange, false, this);
        }, null, this);

        this.changeCall = new Pmt_Util.DelayedCall(this.delayedCheckForChange, null, this, [], this.getDefault('typeDelay', 300), false);

        this.yuiRte.on('editorContentLoaded', function() {
            for (var i = 0; i < this.possibleChangeEvents.length; i++) {
                this.yuiRte.on(this.possibleChangeEvents[i], this.changeCall.call, null, this.changeCall);
            }
            this.setDisabled();
        }, null, this);

    },

    handleToolbarCollapseChange: function (e, collapse) {
        if (collapse !== this.toolbarCollapsed) {
            this.toolbarCollapsed = collapse;
            this.sendMessage('toolbarCollapsed', this.toolbarCollapsed);
        }
    },

    delayedCheckForChange: function() {
        this.yuiRte.saveHTML();
        var text = this.element.value;
        if (text !== this.text) {
            if (this.text !== null) if (this.transport && !this.disabled) {
            	this.sendMessage('change', text);
            }
            this.text = text;
        }
    },

    refresh: function() {
    },

    setAttribs: function(value) {
        if (value !== undefined) this.attribs = value;
        if (this.element)
            for(var a in this.attribs) {
                if (typeof(this.attribs[a]) == 'string' &&  a.slice(0, 1) != '_') this.element.setAttribute(a, this.attribs[a]);
            }
    },

    setClassName: function(value) {
        if (value != undefined) this.className = value;
        if (this.container) {
            this.container.className = this.className;
        }
    },
    
    doOnDelete: function() {
        if (this.yuiRte) {
            try {
                this.yuiRte.destroy();
            } catch (e) {
                console.log("error catched while deleting an editor", e);
            }
            delete this.yuiRte;
        }
    }
}

Pmt_Util.extend(Pmt_Yui_Rte, Pmt_Element);
