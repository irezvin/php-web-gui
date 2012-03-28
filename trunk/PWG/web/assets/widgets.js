PmtStdKeyVars = ['keyCode', 'charCode', 'metaKey', 'shiftKey', 'altKey'];

Pwg_Element = function() {
};

Pwg_Element.prototype = {
	jsClassName: "Pwg_Element",
    visible: true,
    containerId: false,
    observersMap: {},
    options: false,
    nInnerContainers: 0,
    element: false,

    initialize: function(options) {
    	Pwg_Core.prototype.initialize.apply(this, arguments);
    	this.lazyInitialize1();
    },

    getInnerContainer: function(element, n) {
        if (element === undefined) element = this.getContainer();
        if (n === undefined) n = this.nInnerContainers;
        
        var res = element;
        if (res)
            for (var i = n; i > 0; i--) {
                if (res.firstChild && res.firstChild.childNodes) res = res.firstChild;
            }
        return res;
    },
    	
    lazyInitialize1: function() {
        var options = this.options;
        if (options['container'] != undefined) {
            if (typeof(options['container']) == 'string') {
                this.containerId = options['container'];
                this.container = document.getElementById(options['container']);
            }
                else this.container = options['container'];
        }
        this.renderElement();
        for (var i = 0; i < this.autoEvents.length; i++) {
            this.createAutoEvent(this.autoEvents[i]);
        }
        this.refresh();
    },
    
    doOnDelete: function() {
    	if (this.element) Pwg_Util.deleteNodes(this.element);
    },
    
    createAutoEvent: function(autoEvent) {
        var eventType = null;
        var eventVars = [];
        if (typeof(autoEvent) == 'object') {
            eventType = autoEvent.name;
            eventVars = autoEvent.eventVars;
        } else {
            eventType = autoEvent;
        }
        var handleEventFunction = 'handleEvent' + eventType.slice(0, 1).toUpperCase() + eventType.slice(1); // handleEventFoo
        var handlerVarName = eventType + 'Handler';
        if (!this[handleEventFunction]) {
            var f = function(a, b) {
                return function(event) {
                    if (this.transport) {
                        var params = {};
                        for(var i = 0; i < a.length; i++) if (event[a[i]] != undefined) params[a[i]] = event[a[i]];
                        this.transport.pushMessage(this.id, b, params);
                    }
                };
            } (eventVars, eventType);
            //this[handlerVarName] = f.bindAsEventListener(this);
            this[handleEventFunction] = f;
        }
        this[handlerVarName] = null;
    },

    frontendObserve: function(eventType) {
        var handleEventFunction = 'handleEvent' + eventType.slice(0, 1).toUpperCase() + eventType.slice(1); // handleEventFoo
        var bindEventFunction = 'bindEvent' + eventType.slice(0, 1).toUpperCase() + eventType.slice(1); // bindEventFoo
        var handlerVarName = eventType + 'Handler';
        if (this.element && typeof(this[handleEventFunction]) == 'function' && !this[handlerVarName]) {
            this[handlerVarName] = this[handleEventFunction];
            //this[handlerVarName] = this[handleEventFunction].bindAsEventListener(this);
            if (this[bindEventFunction] && typeof(this[bindEventFunction] == 'function')) {
                this[bindEventFunction](handlerVarName, true);
            } else {
                YAHOO.util.Event.addListener(this.element, eventType, this[handlerVarName], this, this);
                //$(this.element).observe(eventType, this[handlerVarName]);
            }
        }
    },
    
    frontendUnobserve: function(eventType) {
        var mtdName = eventType; 
        var handleEventFunction = 'handleEvent' + mtdName.slice(0, 1).toUpperCase() + mtdName.slice(1); // handleEventFoo
        var bindEventFunction = 'bindEvent' + eventType.slice(0, 1).toUpperCase() + eventType.slice(1); // bindEventFoo
        var handlerVarName = eventType + 'Handler';
        
        if (this.element && typeof(this[handleEventFunction]) == 'function' && this[handlerVarName]) {
            if (this[bindEventFunction] && typeof(this[bindEventFunction] == 'function')) this[bindEventFunction](handlerVarName, false);
                //else $(this.element).stopObserving(eventType, this[handlerVarName]);
                else YAHOO.util.Event.removeListener(this.element, eventType, this[handlerVarName]);
            this[handlerVarName] = null;
        }
    },
    
    setVisible: function(value) {
        if (value != undefined) this.visible = value;
        if (this.container) this.container.style.display = this.visible? '' : 'none';
    },
    
    setStyle: function(value) {
        if (value != undefined) {
        	this.style = value;
        }
        if (this.element) {
            value = this.style;
            if (value === false) value = '';
            if (typeof value === 'string') {
                if (this.element.style && this.element.style.cssText !== undefined)
                	this.element.style.cssText = value;
                else if (this.element.setAttribute) this.element.setAttribute('style', value);
            } else {
                if (typeof value === 'object') {
                    for (var i in value) if (Pwg_Util.hasOwnProperty(value, i))
                        YAHOO.util.Dom.setStyle(this.element, i, value[i]);
                }
            }
        }
    },

    setAttribs: function(value) {
        if (value != undefined) this.attribs = value;
        if (this.element)
            for(var a in this.attribs) {
                if (typeof(this.attribs[a]) == 'string' &&  a.slice(0, 1) != '_') this.element.setAttribute(a, this.attribs[a]);
            }
    },


    setClassName: function(value) {
        if (value != undefined) this.className = value;
        if (this.element) {
        	this.element.className = this.className === false? '' : this.className;
        }
    },

    scrollIntoView: function() {
        if (this.container && this.container.scrollIntoView) this.container.scrollIntoView();
    }

    
}

Pwg_Util.extend(Pwg_Element, Pwg_Core);

// ------------------------------------------- Pwg_Button ------------------------------------------- //

Pwg_Button = function (options) {
    this.initialize(options);    
}

Pwg_Button.prototype = {
	jsClassName: "Pwg_Button",
    label: 'button',
    disabled: false,
    attribs: {},
    className: false,
    style: {},
    container: false,
    id: false,
    element: false,
    transport: false,
    confirmationMessage : false,
    buttonType: false,

    'autoEvents': ['dblclick', 'mouseover', 'mouseout', 'mousedown', 'mouseup', 'mousemove', 'focus', 'blur', 
        {'name': 'keydown', 'eventVars': PmtStdKeyVars}, {'name': 'keyup', 'eventVars': PmtStdKeyVars}, {'name': 'keypress', 'eventVars': PmtStdKeyVars}
    ],
    
    handleEventClick: function(event) {
        if ((this.confirmationMessage !== false) && !window.confirm(this.confirmationMessage)) {
            return;
        }
        if (this.id && this.transport) this.transport.pushMessage(this.id, 'click', []);
        YAHOO.util.Event.stopEvent(event);
    },
    
    renderElement: function() {
        this.getContainer();
        var ic = this.getInnerContainer();
        if (ic) {
            this.element = document.createElement('input');
            this.element.setAttribute('type', this.buttonType !== false? this.buttonType : 'button');
            while (ic.firstChild) ic.removeChild(ic.firstChild);
            ic.appendChild(this.element);
        }
    },

    refresh: function() {
        this.setLabel();
        this.setStyle();
        this.setDisabled();
        this.setAttribs();
        this.setClassName();
        this.setVisible();
        this.setConfirmationMessage();
    },

    setLabel: function(value) {
        if (value != undefined) this.label = value;
        if (this.element) this.element.setAttribute('value', this.label);
    },

    setConfirmationMessage: function(value) {
        if (value != undefined) this.confirmationMessage = value;
    },
    
    setDisabled: function(value) {
        if (value != undefined) this.disabled = value;
        if (this.element) {
            if (this.disabled) this.element.setAttribute('disabled', 'disabled');
                else this.element.removeAttribute('disabled');
        }
    },
    
    setClassName: function(value) {
        if (value != undefined) this.className = value;
        if (this.element) {
            this.element.className = this.className === false? '' : this.className;
        }
    }
    
};
Pwg_Util.extend(Pwg_Button, Pwg_Element);


// ------------------------------------------- Pwg_Text ------------------------------------------- //

Pwg_Text = function (options) {
    this.initialize(options);    
}

Pwg_Text.prototype = {
	jsClassName: "Pwg_Text",
    text: '',
    readOnly: false,
    disabled: false,
    attribs: {},
    style: {},
    container: false,
    id: false,
    element: false,
    transport: false,
    size: false,
    className : false,
    multiline : false,
    rows : false,
    isPassword: false,
    dummyText: '',
    inputName: false,
    keypressFilter: false,
    
    'editStarted' : false,

    'autoEvents': ['click', 'dblclick', 'mouseover', 'mouseout', 'mousedown', 'mouseup', 'mousemove', 'focus', 'blur', 'change',
        {'name': 'keydown', 'eventVars': PmtStdKeyVars}, {'name': 'keyup', 'eventVars': PmtStdKeyVars}, {'name': 'keypress', 'eventVars': PmtStdKeyVars}
    ],

    changeCall: false,
    
    getTypeDelay: function() {
        return this.getDefault('typeDelay', 300);
    },
    
    renderElement: function() {
        this.getContainer();
        var ic = this.getInnerContainer();
        if (ic) {
            if (this.multiline) {
                this.element = document.createElement('textarea');
            } else {
                this.element = document.createElement('input');
                this.element.setAttribute('type', this.isPassword? 'password' : 'text');
            }
            if (this.inputName !== false) this.element.setAttribute('name', this.inputName);
            while (ic.firstChild) ic.removeChild(ic.firstChild);
            ic.appendChild(this.element);
            
            this.changeCall = new Pwg_Util.DelayedCall(this.immediateChange, null, this, [], this.getTypeDelay(), false);
            YAHOO.util.Event.addListener(this.element, 'keyup', this.changeCall.call, null, this.changeCall);
            YAHOO.util.Event.addListener(this.element, 'focus', this.handleElementEnter, null, this);
            YAHOO.util.Event.addListener(this.element, 'blur', this.handleElementExit, null, this);
//            var d = $(this.element);
//            d.observe('keyup', this.changeCall.call.bindAsEventListener(this.changeCall));
//            d.observe('focus', this.handleElementEnter.bind(this));
//            d.observe('blur', this.handleElementExit.bind(this));
        } else {
        }
    },

    handleElementEnter: function() {
        if (this.element
                && (this.dummyText !== '')
                && (this.element.value === this.dummyText)
                && (this.text !== this.dummyText)
        ) {
            this.lockMessages();
            this.element.value = this.text;
            this.unlockMessages();
        }
    },

    handleElementExit: function() {
    	if (this.changeCall && this.changeCall.isActive()) this.changeCall.immediate();
        if (this.element
                && (this.dummyText !== '')
                && (this.text === '')
        ) {
            this.lockMessages();
            this.element.value = this.dummyText;
            this.unlockMessages();
        }
    },

    refresh: function() {
        this.setText();
        this.setVisible();
        this.setReadOnly();
        this.setAttribs();
        this.setClassName();
        this.setStyle();
        this.setSize();
        this.setRows();
        this.setDisabled();
    },

    immediateChange: function() {
        var oldText = this.text;
        if (this.element) this.text = this.element.value;
        if ((oldText !== this.text) && this.transport) {
        	this.transport.pushMessage(this.id, 'change', [this.text]);
        }
    },
    
    handleEventChange: function(event) {
    	this.changeCall.call();
    },

    handleEventKeyup: function(event) {
        if (this.element) {
            this.text = this.element.value;
        }
        if (this.changeCall) this.changeCall.call();
    },
    
    handleEventStartEdit: function(event) {
        if (this.element && !this.editStarted) {
            var oldText = this.text;
            var newText = this.element.value;
            if (oldText != newText) {
                this.editStarted = true;
                //this.handleEventChange(event);
                if (this.changeCall) this.changeCall.call();
            }
        }
    },

    setSize: function(value) {
        if (value != undefined) this.size = value;
        if (this.element) {
            if (this.multiline) {
                if (this.size) this.element.setAttribute('cols', this.size);
                    else this.element.removeAttribute('cols');
            } else {
                if (this.size) this.element.setAttribute('size', this.size);
                    else this.element.removeAttribute('size');
            }
        }
    },
    
    setMultiline: function(value) {
        if (value != undefined) this.multiline = value;
        if (this.container) {
            this.renderElement();
            this.refresh();
        }
    },

    setRows: function(value) {
        if (value != undefined) this.rows = value;
        if (this.element) {
            if (this.rows) this.element.setAttribute('rows', this.rows);
                else this.element.removeAttribute('rows');
        }
    },

    setDummyText: function(value) {
        var oldDummyText = this.dummyText;
        if (value === undefined) value = this.dummyText;
            else this.dummyText = value;
        if (this.text === '' && this.element && this.element.value === oldDummyText) this.element.value = this.dummyText;
    },

    setText: function(value) {
        this.editStarted = false;
        if (value != undefined) this.text = value;
        if (this.element) {
            var v = this.text;
            if (v === false || v === null) v = '';
            if (v == '') v = this.dummyText;
            this.element.value = v;
        }
    },
    
    setVisible: function(value) {
        if (value != undefined) this.visible = value;
        if (this.element) this.element.style.visibility = this.visible? '' : 'hidden';
    },
    
    setReadOnly: function(value) {
        if (value != undefined) this.readOnly = value;
        if (this.element) {
            if (this.readOnly) this.element.setAttribute('readonly', 'readonly');
                else this.element.removeAttribute('readonly');
        }
    },

    setDisabled: function(value) {
        if (value != undefined) this.disabled = value;
        if (this.element) {
            if (this.disabled) this.element.setAttribute('disabled', 'disabled');
                else this.element.removeAttribute('disabled');
        }
    },
    
    setClassName: function(value) {
        if (value != undefined) this.className = value;
        if (this.element) {
        	this.element.className = this.className === false? '' : this.className;
        }
    },
    
    doOnDelete: function() {
    	if (this.changeCall) {
    		this.changeCall.contextObject = null;
    		delete this.changeCall;
    	}
    	Pwg_Element.prototype.doOnDelete.apply(this, arguments);
    },
    
    setKeypressFilter: function(keypressFilter) {
    	if (keypressFilter !== undefined) this.keypressFilter = keypressFilter;
    },
    
    handleEventKeypress: function(event) {
        if (this.transport) {
        	if (this.keypressFilter instanceof Array) {
        		if (event.keyCode) { 
	        		if (Pwg_Util.indexOf(event.keyCode, this.keypressFilter) < 0) return;
	    			this.changeCall.cancel();
	    			this.handleEventChange(event);
        		} else return;
        	}
            var params = [{}], a = PmtStdKeyVars;
            for(var i = 0; i < a.length; i++) if (event[a[i]] != undefined) params[0][a[i]] = event[a[i]];
            if (this.changeCall.isActive()) this.changeCall.immediate();
            this.transport.pushMessage(this.id, 'keypress', params);
        }
    }
    
}

Pwg_Util.extend(Pwg_Text, Pwg_Element);

// ------------------------------------------- Pwg_Checkbox ------------------------------------------- //

Pwg_Checkbox = function (options) {
    this.initialize(options);    
}

Pwg_Checkbox.prototype = {
	jsClassName: "Pwg_Checkbox",
    checked: false,
    readOnly: false,
    disabled: false,
    attribs: {},
    style: {},
    container: false,
    id: false,
    element: false,
    transport: false,
    className: false,

    'autoEvents': ['click', 'dblclick', 'mouseover', 'mouseout', 'mousedown', 'mouseup', 'mousemove', 'focus', 'blur', 'change'],
    
    renderElement: function() {
        this.getContainer();
        var ic = this.getInnerContainer();
        if (ic) {
            this.element = document.createElement('input');
            this.element.setAttribute('type', 'checkbox');
            this.element.setAttribute('value', '1');
            while (ic.firstChild) ic.removeChild(ic.firstChild);
            ic.appendChild(this.element);
        }
    },

    refresh: function() {
        this.setChecked();
        this.setVisible();
        this.setAttribs();
        this.setClassName();
        this.setStyle();
        this.setDisabled();
        this.setReadOnly();
    },
    
    handleEventChange: function(event) {
        if (this.element) this.checked = this.element.checked;
        if (this.transport) this.transport.pushMessage(this.id, 'change', [this.checked? 1: 0]);
    },

    handleEventClick: function(event) {
        if (this.element) {
            var oc = this.checked;
            this.checked = this.element.checked;
            if (oc != this.checked) this.transport.pushMessage(this.id, 'change', [this.checked? 1: 0]);
            if (this.transport) this.transport.pushMessage(this.id, 'click');
        }
    },

    setChecked: function(value) {
        if (value != undefined) this.checked = value? true : false;
        if (this.element) this.element.checked = this.checked;
    },
    
    setVisible: function(value) {
        if (value != undefined) this.visible = value;
        if (this.element) this.element.style.visibility = this.visible? '' : 'hidden';
    },
    
    setReadOnly: function(value) {
        if (value != undefined) this.readOnly = value;
        if (this.element) {
            if (this.readOnly) this.element.setAttribute('disabled', 'disabled');
                else this.element.removeAttribute('disabled');
        }
    },

    setDisabled: function(value) {
        if (value != undefined) this.disabled = value;
        if (this.element) {
            if (this.disabled) this.element.setAttribute('disabled', 'disabled');
                else this.element.removeAttribute('disabled');
        }
    }
}

Pwg_Util.extend(Pwg_Checkbox, Pwg_Element);

//------------------------------------------- Pwg_Label ------------------------------------------- //

Pwg_Label = function (options) {
    this.initialize(options);    
}

Pwg_Label.prototype = {
	jsClassName: "Pwg_Label",
    html: '',
    attribs: {},
    style: {},
    className: false,
    container: false,
    id: false,
    transport: false,
    element: false,
    allowHrefClicks: false,

    _clickCall: null,
    _observeDblClick: false,

    autoEvents: ['dblclick', 'click', 'mouseover', 'mouseout', 'mousedown', 'mouseup', 'mousemove'],

    renderElement: function() {
        this.getContainer();
        var ic = this.getInnerContainer();
        if (ic) {
            this.element = ic;
            while (ic.firstChild) ic.removeChild(ic.firstChild);
            this._clickCall = new Pwg_Util.DelayedCall(this.handleEventClick, null, this, [], this.getDefault('clickDelay', 300), false);
        }
    },

    refresh: function() {
        this.setHtml();
        this.setAttribs();
        this.setClassName();
        this.setStyle();
        this.setVisible();
    },
    
    setAllowHrefClicks: function(value) {
    	this.allowHrefClicks = value;
    },
    
    setHtml: function(value) {
        if (value != undefined) this.html = value;
        var v = this.html;
        if (v === false) v = '';
        if (this.element) this.element.innerHTML = v + '';
    },
    
    appendHtml: function(value) {
        if (value != undefined) this.setHtml (this.html + '' + value);
    },
    
    prependHtml: function(value) {
        if (value != undefined) this.setHtml ('' + value + this.html);
    },
    
    setVisible: function(value) {
        if (value != undefined) this.visible = value;
        if (this.container) this.container.style.display = this.visible? '' : 'none';
    },

    getLocalFragment: function(element) {
    	var res = false, tn, res = false;
    	if (element && element.tagName) {
    		tn = element.tagName.toUpperCase();
    		if (tn == 'A') {
    			if (element.href) {
    				res = Pwg_Util.getLocalFragment(element.href, false);
    				if (!res || (!res.slice(0, 2) == '##')) res = false;
    			}
    		}
    	}
    	return res;
    },
    
    _clickHandler: function(event) {
        if (this.allowHrefClicks) {
            var t = event.target? event.target: event.srcElement;
            if (t.tagName && t.tagName.toUpperCase() === 'A') {
            	if (!this.getLocalFragment(t)) {
            		return true;
            	}
            }
        }
        var tmp = {};
        for (var i in event) tmp[i] = event[i];
    	this._clickCall._double = this._observeDblClick && this._clickCall.isActive();
        this._clickCall.callWithArgs(tmp);
        YAHOO.util.Event.stopEvent(event);
    },

    bindEventClick: function() {
      if (this._observeDblClick) this._clickCall.delay = this.getDefault('dblClickDelay', 300);
      	else this._clickCall.delay = this.getDefault('clickDelay', 300);

      YAHOO.util.Event.addListener(this.element, 'click', this._clickHandler, null, this);
    },

    handleEventClick: function(event) {
        var args = [];
        var href = "";
        var t = event.target? event.target: event.srcElement;
        while (t && (t !== this.container)) {
            if (href = this.getLocalFragment(t)) {
        		args.push(href);
                break;
            }
            else if (this.allowHrefClicks && t.getAttribute && t.getAttribute('href')) {
            	location.href = t.getAttribute('href');
            }
            t = t.parentNode;
        }
        if (this.id && this.transport) this.transport.pushMessage(this.id, this._clickCall._double? 'dblclick' : 'click', args);
    },

    bindEventDblclick: function() {
    	this._observeDblClick = true;
        this._clickCall.delay = this.getDefault('dblClickDelay', 300);
    	YAHOO.util.Event.addListener(this.element, 'dblclick', this.handleEventDblclick, null, this);
    },

    handleEventDblclick: function(event) {
        this._clickCall.cancel();
        var args = [];
        var href = "";
        var target = event.target? event.target: event.srcElement;
        if (href = this.getLocalFragment(target)) {
            args.push(href);
            //Event.stop(event);
        }
        if (this.id && this.transport) this.transport.pushMessage(this.id, 'dblclick', args);
    },

    doOnDestroy: function() {
        if (this._clickCall) {
            this._clickCall.destroy();
            delete this._clickCall;
        }
    }

}

Pwg_Util.extend(Pwg_Label, Pwg_Element);


//------------------------------------------- Pwg_List ------------------------------------------- //

Pwg_List = function (options) {
    this.listOptions = [];
    this.selectedIndices = [];
    this.initialize(options);    
}

Pwg_List.prototype = {
	jsClassName: "Pwg_List",
    attribs: {},
    style: {},
    className : false,
    container: false,
    id: false,
    transport: false,
    listOptions: null,
    element: false,
    multiple: false,
    selectedIndices: null,
    readOnly: false,
    disabled: false,

    autoEvents: ['click', 'dblclick', 'mouseover', 'mouseout', 'mousedown', 'mouseup', 'mousemove', 'selectionChange'],
    
    renderElement: function() {
        this.getContainer();
        var ic = this.getInnerContainer();
        if (ic) {
            this.element = document.createElement('select');
            while (ic.firstChild) ic.removeChild(ic.firstChild);
            ic.appendChild(this.element);
            
        }
    },

    refresh: function() {
        this.setVisible();
        this.setAttribs();
        this.setClassName();
        this.setStyle();
        this.setSize();
        this.setMultiple();
        this.setDisabled();
        this.setListOptions();
        this.setSelectedIndices();
        this.selectedIndices = this.getSelectedIndicesFromElement();
    },
    
    clearListOptions: function() {
        return this.setListOptions([]);
    },
    
    setListOptions: function(value) {
        if (value != undefined) this.listOptions = value;
        if (this.element) {
            var o;
            while (o = this.element.firstChild) this.element.removeChild(o);
            for (var i = 0; i < this.listOptions.length; i++) {
                this.element.appendChild(this.createOptionElement(this.listOptions[i]));
            }
        }
    },
    
    redrawOptions: function(listOptions) {
        for (var i = 0; i < listOptions.length; i++) {
            var ou = listOptions[i];
            var e = this.getOptionElementByIndex(ou.index);
            if (e) {
                e.innerHTML = ou.label;
                e.setAttribute('value', ou.value);
            }
        }        
    },
    
    createOptionElement: function(opt) {
        var o = document.createElement('option');
        if (opt.label !== false) o.innerHTML = opt.label;
        if (opt.value !== false) o.setAttribute('value', opt.value);
        return o;
    },
    
    getOptionElementByIndex: function(index) {
        if (typeof(index) == 'boolean' && index == false) return null;
        var res = null;
        var i = 0;
        for (var o = this.element.firstChild; o; o = o.nextSibling) {
            if (i == index) {
                res = o;
                break;
            }
            i++;
        }
        return res;        
    },
    
    addOption: function (option, index) {
        if (typeof(index) == 'boolean' && index == false) index = this.listOptions.length;
        index = Math.max(Math.min(index, this.listOptions.length + 1), 0);
        var i = 0;
        var opt = this.createOptionElement(option);
        if (index >= this.listOptions.length) {
            this.element.appendChild(opt); 
        } else {
            for (var o = this.element.firstChild; o; o = o.nextSibling) {
                if (i == (index)) {
                    this.element.insertBefore(opt, o);
                    break;
                } else {
                }
                i++;
            }
        }
        this.listOptions.splice(index, 0, option);
    },
    
    removeOption: function (index) {
        if (index >= this.listOptions.length || index < 0) return;
        this.listOptions.splice(index, 1);
        var element = this.getOptionElementByIndex(index);
        if (element && element.parentNode == this.element) element.parentNode.removeChild(element);
        if (this.selectedIndices instanceof Array) {
            var idx = Pwg_Util.indexOf(index, this.selectedIndices);
            if (idx >= 0) this.selectedIndices.splice(idx, 1);
        }
    },
    
    optionSelected: function (index) {
        if (!this.multiple) {
                var i = 0;
                for (var o = this.element.firstChild; o; o = o.nextSibling) {
                    if (i++ != index) {
                        o.selected = false;
                    }
                }
        }
        var element = this.getOptionElementByIndex(index);
        if (element) {
            element.selected = true;
        }
        if (this.selectedIndices instanceof Array) {
            var idx = Pwg_Util.indexOf(index, this.selectedIndices);
            if (idx < 0) this.selectedIndices.push(index);
        }
    },    
       
    optionDeselected: function (index) {
        var element = this.getOptionElementByIndex(index);
        if (element) {
            //element.removeAttribute('selected');
            element.selected = false;
        }
        if (this.selectedIndices instanceof Array) {
            var idx = Pwg_Util.indexOf(index, this.selectedIndices);
            if (idx >= 0) this.selectedIndices.splice(idx, 1);
        }
    },    
    
    setSelectedIndices: function(indices) {
 
        if (indices !== undefined) this.selectedIndices = indices;
        indices = this.selectedIndices;
        
        var i = 0;
        for (var o = this.element.firstChild; o; o = o.nextSibling) {
            var selected = false;
            for (var j = 0; j < indices.length; j++) {
                if (indices[j] == i) { 
                    selected = true;
                    indices.splice(j, 1);
                    break;
                }
            }
            if (selected) {
                o.setAttribute('selected', 'selected');
                o.selected = true;
            } else {
                o.removeAttribute('selected');
                o.selected = false;
            }
            i++;
        }
    },
       
    setVisible: function(value) {
        if (value != undefined) this.visible = value;
        if (this.container) this.container.style.display = this.visible? '' : 'none';
    },
    
    setAttribs: function(value) {
        if (value != undefined) this.attribs = value;
        if (this.container) 
            for(var a in this.attribs) {
                if (typeof(this.attribs[a]) == 'string' &&  a.slice(0, 1) != '_') this.container.setAttribute(a, this.attribs[a]);
            }
    },
    
    setSize: function(value) {
        if (value != undefined) this.size = value;
        if (this.element) {
            if (this.size) this.element.setAttribute('size', this.size);
                else this.element.removeAttribute('size');
        }
    },
    
    setMultiple: function(value) {
        if (value != undefined) this.multiple = value;
        if (this.element) {
            if (this.multiple) this.element.setAttribute('multiple', 'multiple');
                else this.element.removeAttribute('multiple');
        }
    },
    
    setDisabled: function(value) {
        if (value != undefined) this.disabled = value;
        if (this.element) {
            if (this.disabled || this.readOnly) this.element.setAttribute('disabled', 'disabled');
                else this.element.removeAttribute('disabled');
        }
    },    

    setReadOnly: function(value) {
        if (value != undefined) this.readOnly = value;
        if (this.element) {
            if (this.disabled || this.readOnly) this.element.setAttribute('disabled', 'disabled');
                else this.element.removeAttribute('disabled');
        }
    },

    setClassName: function(value) {
        if (value != undefined) this.className = value;
        if (this.element) {
        	this.element.className = this.className === false? '' : this.className;
        }
    },
    
    bindEventSelectionChange: function(handlerVarName, doBind) {
        if (doBind) {
            //$(this.element).observe('click', this[handlerVarName]);
            //$(this.element).observe('change', this[handlerVarName]);
            YAHOO.util.Event.addListener(this.element, 'click', this[handlerVarName], null, this);
            YAHOO.util.Event.addListener(this.element, 'change', this[handlerVarName], null, this);
        } else {
            //$(this.element).unobserve('click', this[handlerVarName]);
            //$(this.element).unobserve('change', this[handlerVarName]);
        	YAHOO.util.Event.removeListener(this.element, 'click', this[handlerVarName]);
            YAHOO.util.Event.removeListener(this.element, 'change', this[handlerVarName]);
        }			
    },
    
    getSelectedIndicesFromElement: function(element) {
        if (element === undefined) element = this.element;
        var ns = new Array;
        var i = 0;
        for (var opt = this.element.firstChild; opt; opt = opt.nextSibling) {
            if (opt.selected) ns[ns.length] = i;
            i++;
        }
        return ns;
    },
        
    handleEventSelectionChange: function(event) {
        var ns = this.getSelectedIndicesFromElement();
        if (this.selectedIndices instanceof Array) {
            var diff = Pwg_Util.arrayDiff(ns, this.selectedIndices, false);
            if (!diff.length) return; // Don't do anything if there is no change
        }
        this.selectedIndices = ns;
        if (this.transport) {
            if (this.transport) this.transport.pushMessage(this.id, 'selectionChange', [ns]);
        }
    }
    

};

Pwg_Util.extend(Pwg_List, Pwg_Element);

// ------------------------------------------- Pwg_Controller ------------------------------------------- //

Pwg_Controller = function (options) {
    this.initialize(options);    
}

Pwg_Controller.loadedAssets = [];

Pwg_Controller.prototype = {
	jsClassName: "Pwg_Controller",
    id: false,
    transport: false,

    autoEvents: [],

    isCss: function(src) {
    	return src.slice(src.length - 3).toUpperCase() === 'CSS';
	},

    execJavascript: function(javascript) {
        try {
            eval(javascript);
        } catch (e) {
            console.log("Can't eval javascript: ", e, "src is ", javascript);
        }
    },

    initializeControl: function(scriptsArr, controlCreateFn) {
		
		var cssList = [], jsList = [];
		
		for (var i = 0; i < scriptsArr.length; i++) {
			if (this.isCss(scriptsArr[i])) {
				if (!this.hasCss(scriptsArr[i])) cssList.push(scriptsArr[i]);
			} else {
				if (!this.hasScript(scriptsArr[i])) jsList.push(scriptsArr[i]);
			}
		}
		
		if (cssList.length) {
//			console.log("Css", cssList);
			YAHOO.util.Get.css(cssList, {
				onSuccess: function() {
					this.transport.resume();
				},
				onFailure: function() {
					this.transport.axErrorCallback();
				},
				scope: this
			});
			this.transport.pause();
		}
		if (jsList.length) {
//			console.log("Js", jsList);
			YAHOO.util.Get.script(jsList, {
				onSuccess: function() {
					this.transport.resume();
				},
				onFailure: function() {
					this.transport.axErrorCallback();
				},
				scope: this
			});
			this.transport.pause();
		}
    	
    	Pwg_Debug.d("lifecycle", "[" + this.id + "] has loaded all necessary scripts and is creating child control(s)...");
    	
		controlCreateFn();
				
//		for (var i = 0; i < scriptsArr.length; i++) {
//        	var src, content = false;
//            if (typeof(scriptsArr[i]) === 'object') {
//                src = scriptsArr[i].src;
//                content = scriptsArr[i].content? scriptsArr[i].content : false;
//            } else src = scriptsArr[i];
//            console.log("Loading ", src , "has content", !!content);
//            var slc = src.slice(src.length - 3).toUpperCase();
//        	if (slc === 'CSS') this.loadCss(src, content);
//        	else if (slc === '.JS') this.loadScript(src, content);
//        }
//        controlCreateFn();
    },

    renderElement: function() {
    },

    refresh: function() {
    },

    sendLoopbackMessage: function(methodName, params) {
        if (!params) params = {};
        if (this.transport) this.transport.pushMessage(this.id, methodName, params);
    },

    logMessage: function(message) {
        if (window.console) {
            var c = "window.console.log(message[0]";
            for (var i = 1; i < message.length; i++) c = c + ", message["+i+"]";
            c = c + ");";
            eval(c);
        }
    },

    isInLoadedAssets: function(src) {
        for (var i = 0; i < window.Pwg_Controller.loadedAssets.length; i++) {
            if (window.Pwg_Controller.loadedAssets[i] === src) {
                return true;
            }
        }
        return false;
    },

    hasCss: function(css) {
        if (this.isInLoadedAssets(css)) return true;
        var links = document.getElementsByTagName('LINK');
        var res = false;
        for (var i = 0; i < links.length; i++) {
        	var rel = (links[i].getAttribute('rel') + '').toUpperCase();
        	var href = (links[i].getAttribute('href') + '');
            if (rel === 'STYLESHEET' && href === css) {
                res = true;
                break;
            }
        }
        return res;
    },
    
    hasScript: function(src) {
        if (this.isInLoadedAssets(src)) return true;
        var scripts = document.getElementsByTagName('SCRIPT');
        var res = false;
        for (var i = 0; i < scripts.length; i++) {
            if (scripts[i].getAttribute('src') && scripts[i].getAttribute('src') == src) {
                res = true;
                break;
            }
        }
        return res;
    },

    loadScript: function(src, content) {
        // TODO: improve this function (maybe) (i.e. use YAHOO loader or more sane implementation)
        var hasScript = false;
        if (!(hasScript = this.hasScript(src))) {
            if (content) {
                try {
                    eval(content);
                } catch (e) {
                    throw "Exception while loading script " + src + ": " + e;
                }
                window.Pwg_Controller.loadedAssets.push(src);
            } else {
                var el = document.createElement('SCRIPT');
                el.setAttribute("type", "text/javascript");
                el.setAttribute("src", src);
                //el.src = src;
                document.getElementsByTagName('head')[0].appendChild(el);
            }
        } else {

        }
//        console.log("loadScript", src, "has", hasScript);
    },

    loadCss: function(src, content) {
        if (!this.hasCss(src)) {
            if (content) {
                var css;
                css = document.createElement('style');
                css.setAttribute('type', 'text/css');
                css.innerHTML = content;
                document.body.appendChild(css);
                window.Pwg_Controller.loadedAssets.push(src);
            } else {
                var el = document.createElement('LINK');
                el.setAttribute("rel", "stylesheet");
                el.setAttribute("type", "text/css");
                el.setAttribute("href", src);
                document.getElementsByTagName('head')[0].appendChild(el);
            }
        }
    },


    initializeChildContainer: function(html, afterContainerId, childContainerId) {
    	//console.log(this.id, 'initializing child container', html, afterContainerId);
    	
    	Pwg_Debug.d("lifecycle", "[" + this.id + "] initializing child container for [" + childContainerId + "]" 
    			+ (afterContainerId? " after container [" + afterContainerId + "]" : ""), [ html ]);
    	
    	if (childContainerId) {
    		var oldChildContainer = document.getElementById(childContainerId);
    		if (oldChildContainer) {
    			Pwg_Debug.d("lifecycle", "WARN: Deleting obsolete child container ", oldChildContainer);
    			oldChildContainer.parentNode.removeChild(oldChildContainer);
    		}
    	}
    	var aft = null;
        if (afterContainerId) {
        	var aftWidgetId = 'v_' + afterContainerId;
        	if (window[aftWidgetId] && window[aftWidgetId].outerContainer)
        		aft = window[aftWidgetId].outerContainer;
        	else 
        		aft = document.getElementById(afterContainerId);
        }
        if (aft) {
            var tmp = document.createElement('div');
            tmp.innerHTML = html;
            if (aft.nextSibling) {
                var ns = aft.nextSibling;
                while (tmp.firstChild) aft.parentNode.insertBefore(tmp.removeChild(tmp.firstChild), ns);
            } else {
                while (tmp.firstChild) aft.parentNode.appendChild(tmp.removeChild(tmp.firstChild));
            }
        } else {
            var defaultContainer;
            if (this.insideContainer) defaultContainer = this.insideContainer;
            else {
            	if (this.container) defaultContainer = this.container;
            	else defaultContainer = document.body;
            }

            var tmp = document.createElement('div');
            tmp.innerHTML = html;
            while (tmp.firstChild) defaultContainer.appendChild(tmp.removeChild(tmp.firstChild));

            //new Insertion.Bottom(defaultContainer, html);
        }
    }


};

Pwg_Util.extend(Pwg_Controller, Pwg_Element);

Pwg_Control_Parent_Functions = {
		
		initializeControl: Pwg_Controller.prototype.initializeControl,
		isCss: Pwg_Controller.prototype.isCss,
		isInLoadedAssets: Pwg_Controller.prototype.isInLoadedAssets,
		hasCss: Pwg_Controller.prototype.hasCss,
		hasScript: Pwg_Controller.prototype.hasScript,
		loadScript: Pwg_Controller.prototype.loadScript,
		loadCss: Pwg_Controller.prototype.loadCss
		
}

// ------------------------------------------- Pwg_Group ------------------------------------------- //

Pwg_Group = function (options) {
    this.initialize(options);
}

Pwg_Group.prototype = {
	jsClassName: "Pwg_Group",
    attribs: {},
    style: {},
    className: false,
    container: false,
    id: false,
    transport: false,
    insideContainer: false,

    autoEvents: [],

    renderElement: function() {
        this.getContainer();
        this.insideContainer = this.getInnerContainer();
    },

    refresh: function() {
        this.setAttribs();
        if (this.className !== false) this.setClassName();
        this.setStyle();
        this.setVisible();
    },

    setVisible: function(value) {
        if (value != undefined) this.visible = value;
        if (this.container) this.container.style.display = this.visible? '' : 'none';
    },

    setAttribs: function(value) {
        if (value != undefined) this.attribs = value;
        if (this.container)
            for(var a in this.attribs) {
                if (typeof(this.attribs[a]) == 'string' &&  a.slice(0, 1) != '_') this.container.setAttribute(a, this.attribs[a]);
            }
    },

    setClassName: function(value) {
        if (value != undefined) this.className = value;
        if (this.container) {
        	this.container.className = this.className === false? '' : this.className;
        }
    },

    initializeChildContainer: Pwg_Controller.prototype.initializeChildContainer
}

Pwg_Util.extend(Pwg_Group, Pwg_Element);

// ------------------------------------------- Pwg_Uploader ------------------------------------------- //


Pwg_Uploader = function (options) {
    this.initialize(options);
}

Pwg_Uploader.prototype = {
	
    jsClassName: "Pwg_Uploader",

    readOnly: false,

    fileChangeFn: function(id) {
        if (this.transport) this.transport.pushMessage(this.id, 'fileChange', [id]);
    }
}

Pwg_Util.extend(Pwg_Uploader, Pwg_Label);