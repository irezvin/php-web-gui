Pmt_Checklist = function (options) {
    this.initialize(options);    
}

Pmt_Checklist.prototype = {
	jsClassName: "Pmt_Checklist",
    attribs: {},
    style: {},
    className : false,
    container: false,
    id: false,
    transport: false,
    listOptions: [],
    element: false,
    multiple: false,
    selectedIndices : [],
    disabled: false,
    readOnly: false,

    observeSelectionChange: false,

    autoEvents: ['click', 'dblclick', 'mouseover', 'mouseout', 'mousedown', 'mouseup', 'mousemove', 'selectionChange'],
    
    renderElement: function() {
        this.getContainer();
        var ic = this.getInnerContainer();
        if (ic) {
            this.element = document.createElement('div');
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
        this.setDisabled();
    },
    
    handleItemClick: function(event, listItem) {
        if (this.disabled || this.readOnly) YAHOO.util.Event.stopEvent(event);
        else {
            if (!this.multiple) { // uncheck other options
                for (var item = this.element.firstChild; item; item = item.nextSibling) {
                    if (item !== listItem) this.setItemChecked(item, false);
                }
            }
            if (this.observeSelectionChange) this.handleEventSelectionChange(event);
        }
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
    
    clearListOptions: function() {
        this.setListOptions([]);
    },
    
    redrawOptions: function(listOptions) {
        for (var i = 0; i < listOptions.length; i++) {
            var ou = listOptions[i];
            var e = this.getOptionElementByIndex(ou.index);
            if (e) {
                e.getElementsByTagName('span')[0].innerHTML = ou.label;
                e.getElementsByTagName('input')[0].setAttribute('value', ou.value);
            }
        }        
    },
    
    setItemChecked: function(div, checked) {
        if (checked === undefined) checked = true;
        div.getElementsByTagName('input')[0].checked = checked;
    },

    setItemDisabled: function(div, disabled) {
        if (disabled === undefined) disabled = true;
        div.getElementsByTagName('input')[0].disabled = disabled;
    },
    
    getItemChecked: function(div) {
        return div.getElementsByTagName('input')[0].checked;
    },
    
    createOptionElement: function(opt) {
        var o = document.createElement('div');
        var label = document.createElement('span');
        var check = document.createElement('input');
        check.setAttribute('type', 'checkbox');
        o.appendChild(check);
        o.appendChild(label);
        YAHOO.util.Event.addListener(check, 'click', this.handleItemClick, o, this);
        YAHOO.util.Event.addListener(label, 'click', this.handleItemClick, o, this);
        //$(check).observe('click', this.handleItemClick.bindAsEventListener(this, o));
        //$(label).observe('click', function() {this.click()}.bindAsEventListener(check));
        if (opt.label !== false) label.innerHTML = opt.label;
        if (opt.value !== false) check.setAttribute('value', opt.value);
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
        if (element && element.parentNode == this.element) {
            element.parentNode.removeChild(element);
            YAHOO.util.Event.purgeElement(element)
        }
    },
    
    optionSelected: function (index) {
        if (!this.multiple) {
                var i = 0;
                for (var o = this.element.firstChild; o; o = o.nextSibling) {
                    if (i++ != index) {
                        this.setItemChecked(o, true);
                    }
                }
        }
        var element = this.getOptionElementByIndex(index);
        if (element) {
            this.setItemChecked(element, true);
        }
    },    
       
    optionDeselected: function (index) {
        var element = this.getOptionElementByIndex(index);
        if (element) {
            this.setItemChecked(element, false);
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
            this.setItemChecked(o, selected);
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
    },
    
    setDisabled: function(value) {
        if (value != undefined) {
            this.disabled = value;
        }
        for (var item = this.element.firstChild; item; item = item.nextSibling) {
            this.setItemDisabled(item, this.disabled);
        }
    },    
    
    setClassName: function(value) {
        if (value != undefined) this.className = value;
        if (this.element) {
            this.element.className = this.className;
        }
    },
    
    bindEventSelectionChange: function(handlerVarName, doBind) {
        if (doBind) {
            this.observeSelectionChange = true;
        } else {
            this.observeSelectionChange = false;
        }
    },
        
    handleEventSelectionChange: function(event) {
        var ns = new Array;
        var i = 0;
        for (var opt = this.element.firstChild; opt; opt = opt.nextSibling) {
            if (this.getItemChecked(opt)) ns[ns.length] = i;
            i++;
        }
        if (this.transport) {
            if (this.transport) this.transport.pushMessage(this.id, 'selectionChange', [ns]);
        }
    },
    
    doOnDelete: function() {
    	this.clearListOptions();
    	Pmt_Element.prototype.doOnDelete.apply(this, arguments);
    }

};


Pmt_Util.extend(Pmt_Checklist, Pmt_Element);