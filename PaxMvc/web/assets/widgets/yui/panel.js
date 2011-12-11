Pmt_Yui_Panel = function (options) {
    this.createSetters();
    this.initialize(options);
}

Pmt_Yui_Panel.getOverlayManager = function() {
    if (!Pmt_Yui_Panel.overlayManager) {
        Pmt_Yui_Panel.overlayManager = new YAHOO.widget.OverlayManager();
    }
    return Pmt_Yui_Panel.overlayManager;
};

Pmt_Yui_Panel.prototype = {

    jsClassName: "Pmt_Yui_Panel",
    attribs: {},
    style: {},
    className: false,
    container: false,
    element : false,
    id: false,
    transport: false,
    resizeable : false,
    focused: false,

    visible: null,
    width: null,
    height: null,

    minWidth: null,
    minHeight: null,

    autoWidthExtra: 40,
    autoHeightExtra: 40,

    draggable: null,
    close: null,
    modal: null,
    underlay: null,
    fixedCenter: null,
    header: '',
    footer: '',
    zIndex: null,
    x: null,
    y: null,
    context: null,
    autoSize: null,
    showAtCenter: null,
    
    hideOnClose: null,
    closeOnOutsideClick: null,

    yuiPanel: false,
    yuiResize: false,
    
    resizeCall: null,
    
    moveCall: null,

    configProps: [
        'width', 'height', 'draggable', 'close',
        'modal', 'underlay', 'fixedCenter', 'header', 'footer', 'visible', 'zIndex', 'x', 'y', 'context'
    ],
    
    setters: [
        'width', 'height', 'draggable', 'underlay', 'header', 'footer', 'visible', 'zIndex', 'x', 'y', 'context'
    ],
    
    autoEvents: [],

    observesClose: false,

    createSetters: function() {
        for (var i = 0; i < this.setters.length; i++) {
            var setterName = 'set' + Pmt_Util.ucFirst(this.setters[i]);
            if (this[setterName] === undefined) {
                this[setterName] = function(propName) {
                    return function(value) {
                        if (value === undefined) value = this[propName];
                            else this[propName] = value;
                        if (this.yuiPanel) this.yuiPanel.cfg.setProperty(propName, value);
                    };
                } (this.setters[i]);
            }
        }
    },
    
    setHideOnClose: function(value) {
        this.hideOnClose = value;
    },

    setCloseOnOutsideClick: function(value) {
        if (value === undefined) value = this.closeOnOutsideClick;
        if (value) YAHOO.util.Event.on(document, "click", this.checkOutsideClickAndClose, null, this);
            else YAHOO.util.Event.removeListener(document, "click", this.checkOutsideClickAndClose);
        this.closeOnOutsideClick = value;
    },

    checkOutsideClickAndClose: function(e) {
        if (this.yuiPanel && this.yuiPanel.cfg.getProperty('visible')) {
            var el = YAHOO.util.Event.getTarget(e);
            var yuiPanelEl = this.yuiPanel.element;
            if (el !== yuiPanelEl && !YAHOO.util.Dom.isAncestor(yuiPanelEl, el)) {
                this.yuiPanelClose();
            }
        }
    },

    yuiPanelClose: function() {
        this.sendMessage('close');
        if (this.hideOnClose) this.yuiPanel.hide();
    },

    bindEventClose: function(handlerVarName, bind) {
        if (bind) {
            this.observesClose = true;
        } else {
            this.observesClose = false;
        }
    },

    yuiPanelMove: function(eventName, args) {
        //console.log("yuiPanelMove");
        this.sendMessage("move", args[0][0], args[0][1]);
    },

    yuiPanelZIndexChange: function(eventName, args) {
        this.sendMessage("zIndexChange", args[0]);
    },

    handleEventClose: function() {
        // stub
    },

    getPanelConfig: function() {
        var res = {};
        for (var i = 0; i < this.configProps.length; i++) {
            if (this.configProps[i] === 'draggable') continue;
            if (this[this.configProps[i]] !== null) res[this.configProps[i]] = this[this.configProps[i]];
        }
        //res.constraintoviewport = true;
        if (this.showAtCenter) {
            res.visible = false;
        }
        return res;
    },

    setHeader: function(value) {
        if (value !== undefined) this.header = value;
        if (this.yuiPanel) this.yuiPanel.setHeader(this.header);
    },

    setFooter: function(value) {
        if (value !== undefined) this.footer = value;
        if (this.yuiPanel) this.yuiPanel.setFooter(this.footer);
    },

    setMinWidth: function(value) {
        if (value !== undefined) this.minWidth = value;
        if (this.yuiResize) this.yuiResize.set("minWidth", this.minWidth);
    },

    setMinHeight: function(value) {
        if (value !== undefined) this.minHeight = value;
        if (this.yuiResize) this.yuiResize.set("minHeight", this.minHeight);
    },

    setVisible: function(value) {
        if (value !== undefined) this.visible = value;
        if (this.yuiPanel) this.yuiPanel.cfg.setProperty("visible", this.visible);
    },

    renderElement: function() {
        YAHOO.widget.Panel.prototype._doClose = function(e) {
            if (this.pmtYuiPanel && this.pmtYuiPanel.observesClose) {
                this.pmtYuiPanel.yuiPanelClose();
            } else {
                YAHOO.util.Event.preventDefault(e);
                this.hide();
            }
        }
        var e = document.getElementById(this.id);
        if (e) {
            if (e.parentNode !== document.body)
                e.parentNode.removeChild(e);
                document.body.appendChild(e);
        }
        this.yuiPanel = new YAHOO.widget.Panel(this.id, this.getPanelConfig());
        this.yuiPanel.registerDragDrop = this.registerYuiPanelDragDrop;
        this.yuiPanel.pmtYuiPanel = this;
        if (e) {
            e.style.display = 'block';
        }
        this.setDraggable();
        this.yuiPanel.render();

        if (this.resizeable) {
            var rcfg = {
                'autoRatio': false,
                'handles': ["br"],
                'proxy': true
            };
            if (this.minWidth) rcfg.minWidth = this.minWidth;
            if (this.minHeight) rcfg.minHeight = this.minHeight;
            this.yuiResize = new YAHOO.util.Resize(this.container, rcfg);
            this.yuiPanel.yuiResize = this.yuiResize;
            this.yuiResize._proxy.style.zIndex="-1";
            this.yuiResize._proxy.style.left="1px";
            this.yuiResize._proxy.style.top="1px";
            

            YAHOO.util.Dom.addClass(this.container, "resizeablePanel");

            this.yuiResize.on("startResize", function(args) {
                if (this.cfg.getProperty("constraintoviewport")) {
                    var D = YAHOO.util.Dom;

                    var clientRegion = D.getClientRegion();
                    var elRegion = D.getRegion(this.element);

                    this.yuiResize.set("maxWidth", clientRegion.right - elRegion.left - YAHOO.widget.Overlay.VIEWPORT_OFFSET);
                    this.yuiResize.set("maxHeight", clientRegion.bottom - elRegion.top - YAHOO.widget.Overlay.VIEWPORT_OFFSET);
                } else {
                    this.yuiResize.set("maxWidth", null);
                    this.yuiResize.set("maxHeight", null);
                }
            }, this.yuiPanel, true);
            
            this.resizeCall = new Pmt_Util.DelayedCall(function(args) {
                if (!args) return;
                if (args[0]) args = args[0];
                var panelHeight = args.height;
                this.cfg.setProperty("height", panelHeight + "px");
                if (this.pmtYuiPanel) {
                    this.pmtYuiPanel.sendMessage("resize", args.width, args.height);
                }
            }, null, this.yuiPanel, [], this.getDefault('reportDelay', 300), true);
            
            this.yuiResize.on("resize", function(args) {
                this.resizeCall.callWithArgs(args);
            }, null, this);

            //if (this.height) this.yuiPanel.cfg.setProperty("height", this.height);
        }
        
        this.moveCall = new Pmt_Util.DelayedCall(this.yuiPanelMove, null, this, [], this.getDefault('reportDelay', 300), false);
        this.yuiPanel.subscribe("move", this.moveCall.callWithArgs, null, this.moveCall);
        
        this.yuiPanel.cfg.subscribeToConfigEvent("zindex", this.yuiPanelZIndexChange, null, this);
        Pmt_Yui_Panel.getOverlayManager().register(this.yuiPanel);
        if (this.focused) this.setFocused();
        
        if (this.yuiPanel.focusEvent) {
            this.yuiPanel.focusEvent.subscribe(this.handleYuiPanelActivate, null, this);
        } else {
            console.log("Problem: yuiPanel doesn't have focusEvent - maybe it was not registered in overlay manager for some reason?");
        }
        
        if (this.zIndex !== null) this.setZIndex();
        this.container = this.yuiPanel.element;
        this.element = this.yuiPanel.body.firstChild;
        
        try {
			this.element.style.display = 'table-cell';
		} catch (e) { 
			// TODO: workaround for IE6
		}

        this.outerContainer = this.yuiPanel.element;
        this.insideContainer = this.yuiPanel.body;

        this.setCloseOnOutsideClick();


        this._justRendered = true;

        //if (!this.height) this.yuiPanel.body.style.height = "";
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

    setFocused: function(focused) {
        if (focused != undefined) this.focused = focused;
        var om = Pmt_Yui_Panel.getOverlayManager();
        if (om && this.yuiPanel) {
            if (this.focused) {
                om.focus(this.yuiPanel);
            } else {
                if (om.getActive() === this.yuiPanel) {
                    om.blurAll();
                }
            }
        }
    },

    setAutoSize: function(autoSize) {
        if (autoSize != undefined) this.autoSize = autoSize;
        if (this.autoSize) this.applyAutoSize();
//        if (this.autoSize) new Pmt_Util.DelayedCall(function() {
//        	console.log("Let's apply auto size");
//        	this.applyAutoSize();
//        }, 'foo', this, [], 2000, true);
    },

    /**
     * @return [left, top, right, bottom]
     */
    getPadding: function(element) {
    	var a = ['paddingLeft', 'paddingTop', 'paddingRight', 'paddingBottom'], res = [];
    	for (var i = 0; i < 4 /*a.length*/; i++) {
    		res.push(parseInt(YAHOO.util.Dom.getComputedStyle(element, a[i])));
    	}
    	return res;
    },
    
    applyAutoSize: function() {
        if (this.yuiPanel) {
            var innerNode = this.yuiPanel.body.firstChild;
            if (innerNode) {
            	
            	var pad = this.getPadding(this.yuiPanel.body);
            	var extraWidth = pad[0] + pad[2];
            	var extraHeight = pad[1] + pad[3];
            	
                var headerHeight = this.yuiPanel.header? this.yuiPanel.header.clientHeight: 0;
                var footerHeight = this.yuiPanel.footer? this.yuiPanel.footer.clientHeight: 0;
                var newWidth = innerNode.clientWidth + extraWidth;
                
                // magic number 6 here are some pixel, probably of borders between footer, header and body of the dialog... 
                // TODO: calculate borders width of visible parts 
                var newHeight = innerNode.clientHeight + headerHeight + footerHeight + extraHeight + 6;
                
                if (this.minWidth && (newWidth < this.minWidth)) newWidth = this.minWidth;
                if (this.minHeight && (newHeight < this.minHeight)) newHeight = this.minHeight;
                this.yuiPanel.cfg.setProperty("width", this.width = newWidth + "px");
                this.yuiPanel.cfg.setProperty("height", this.height = newHeight + "px");
                this.sendMessage("resize", this.width, this.height);
                if (this.fixedCenter) this.yuiPanel.center();
                //console.log(newWidth, newHeight);
            }
        }
    },

    initializeChildContainer: Pmt_Controller.prototype.initializeChildContainer,
    
    doOnDelete: function() {
        this.setCloseOnOutsideClick(false);
        if (this.yuiPanel) {
            delete this.yuiPanel.pmtYuiPanel;
            this.yuiPanel.destroy();
            delete this.yuiPanel;
        }
        if (this.resizeCall) {
            this.resizeCall.contextObject = null; 
            delete this.resizeCall;
        }
        if (this.element) delete this.element;
    },
    
    focus: function() {
        if (this.yuiPanel) this.yuiPanel.focus();
    },
    
    handleYuiPanelActivate: function() {
        this.sendMessage("activate");
    },
    
    registerYuiPanelDragDrop: function () {

        var me = this, Util = YAHOO.util, Overlay = YAHOO.widget.Overlay, Dom = YAHOO.util.Dom;

        if (this.header) {

            if (!Util.DD) {
                return;
            }

            var bDragOnly = (this.cfg.getProperty("dragonly") === true);
            this.dd = new Util.DDProxy(this.element.id, this.id, {dragOnly: bDragOnly});

            if (!this.header.id) {
                this.header.id = this.id + "_h";
            }

            this.dd.startDrag = function () {

                var offsetHeight,
                    offsetWidth,
                    viewPortWidth,
                    viewPortHeight,
                    scrollX,
                    scrollY;

                if (YAHOO.env.ua.ie == 6) {
                    Dom.addClass(me.element,"drag");
                }

                if (me.cfg.getProperty("constraintoviewport")) {

                    var nViewportOffset = Overlay.VIEWPORT_OFFSET;

                    offsetHeight = me.element.offsetHeight;
                    offsetWidth = me.element.offsetWidth;

                    viewPortWidth = Dom.getViewportWidth();
                    viewPortHeight = Dom.getViewportHeight();

                    scrollX = Dom.getDocumentScrollLeft();
                    scrollY = Dom.getDocumentScrollTop();

                    if (offsetHeight + nViewportOffset < viewPortHeight) {
                        this.minY = scrollY + nViewportOffset;
                        this.maxY = scrollY + viewPortHeight - offsetHeight - nViewportOffset;
                    } else {
                        this.minY = scrollY + nViewportOffset;
                        this.maxY = scrollY + nViewportOffset;
                    }

                    if (offsetWidth + nViewportOffset < viewPortWidth) {
                        this.minX = scrollX + nViewportOffset;
                        this.maxX = scrollX + viewPortWidth - offsetWidth - nViewportOffset;
                    } else {
                        this.minX = scrollX + nViewportOffset;
                        this.maxX = scrollX + nViewportOffset;
                    }

                    this.constrainX = true;
                    this.constrainY = true;
                } else {
                    this.constrainX = false;
                    this.constrainY = false;
                }

                me.dragEvent.fire("startDrag", arguments);
            };

            this.dd.endDrag = function () {
                
                //me.cfg.setProperty("x", this.lastPageX, true);
                //me.cfg.setProperty("y", this.lastPageY, true);
                me.cfg.setProperty("xy", [this.lastPageX, this.lastPageY]); 
                 
                me.cfg.refireEvent("iframe");
                if (this.platform == "mac" && YAHOO.env.ua.gecko) {
                    this.showMacGeckoScrollbars();
                }

                me.dragEvent.fire("onDrag", arguments);

                if (YAHOO.env.ua.ie == 6) {
                    Dom.removeClass(me.element,"drag");
                }

                me.dragEvent.fire("endDrag", arguments);
                me.moveEvent.fire(me.cfg.getProperty("xy"));

            };

            this.dd.setHandleElId(this.header.id);
            this.dd.addInvalidHandleType("INPUT");
            this.dd.addInvalidHandleType("SELECT");
            this.dd.addInvalidHandleType("TEXTAREA");
        }
        
    },

    notifyMessageQueueEnd: function() {
        if (this._justRendered) {
            this._justRendered = false;
            this.setAutoSize();

            if (!this.width || !this.height) this.sendMessage("resize",
                this.width = this.yuiPanel.body.parentNode.clientWidth,
                this.height = this.yuiPanel.body.parentNode.clientHeight
            );
            if (this.showAtCenter) {
                this.center();
                if (this.visible) this.setVisible(this.visible);
            }

        }
    },

    center: function() {
        if (this.yuiPanel) this.yuiPanel.center();
    },

    setTransport: function(transport) {
        this.transport = transport;
        this.transport.reportAffectedObserver(this);
    }
}

Pmt_Util.extend(Pmt_Yui_Panel, Pmt_Group);