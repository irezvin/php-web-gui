Pwg_Anchors = {
    
    defaultStrategy: null
    
};

Pwg_Anchors.Observable = function() {
    this.observers = {};
};

Pwg_Anchors.Observable.prototype = {

    /** @type Array */
    observers: null,
    
    observe: function(eventType, fn, scope, args) {
        var res = false, idx = this.findObserverIndex(eventType, fn, scope, args);
        if (idx < 0) {
            if (!this.observers[eventType]) this.observers[eventType] = [];
            this.observers[eventType].push( {'eventType': eventType, 'fn': fn, 'scope': scope, 'args': Array.prototype.slice.call(arguments, 3)} );
            res = true;
        }
        else res = false;
        return res;
    },
    
    findObserverIndex: function(eventType, fn, scope, args) {
        var res = -1;
        if (this.observers[eventType]) {
            for (var i = this.observers[eventType].length - 1; i >= 0 && res < 0; i--) {
                var o = this.observers[eventType][i];
                if (o.eventType == eventType && o.fn == fn && o.scope == scope) {
                    var a = Array.prototype.slice.call(arguments, 3);
                    if (o.args.length == a.length) {
                        for (var j = o.args.length - 1; j >= 0; j--) if (o.args[i] != a[i]) continue;
                        res = i;
                    }
                }
            }
        }
        return res;
    },

    unobserve: function(eventType, fn, scope, args) {
        var res = false, idx = this.findObserverIndex(eventType, fn, scope, args);
        if (idx >= 0) {
            this.observers[eventType].splice(idx, 1);
            if (!this.observers[eventType].length) delete this.observers[eventType];
            res = true;
        }
        return res;
    },
    
    triggerEvent: function(eventType, args) {
        var res = true;
        if (this.observers[eventType]) {
            var a = Array.prototype.slice.call(arguments, 1);
            for (var i = 0, l = this.observers[eventType].length; i < l; i++) {
                var 
                    o = this.observers[eventType][i], 
                    args = a.concat(o.args), 
                    scope = o.scope? o.scope: this;
                var 
                    r = o.fn.apply(scope, args);
                if (r === false) {
                    res = false;
                }
            }
        }
        return res;
    },
    
    destroy: function() {
        this.triggerEvent('destroy');
        this.observers = [];
    }
    
};

Pwg_Anchors.Box = function(left, top, right, bottom) {
    if (arguments.length == 1 && left instanceof Pwg_Anchors.Box) this.assign(left);
    else {
        this.left = left;
        this.top = top;
        this.right = right;
        this.bottom = bottom;
        
        this.anchors = new Pwg_Anchors.Anchors();
        
        this.dimStrategy = Pwg_Anchors.defaultStrategy;
    }
};

Pwg_Anchors.Box.prototype = {
    
    className: "Pwg_Anchors.Box",
    
    left: null,
    top: null,
    right: null,
    bottom: null,
    
    anchors: null,
    
    dimStrategy: null,

    _lockUpdateElement: 0,    
    _element: null,
    _parentBox: null,
    
    setParentBox: function(parentBox) {
        if (parentBox !== this._parentBox) {
            if (this._parentBox) this._parentBox.unobserve('adjust', this.handleParentAdjust, this);
            if ((this._parentBox = parentBox)) {
                this._parentBox.observe('adjust', this.handleParentAdjust, this);
                this._parentBox.observe('destroy', this.handleParentDestroy, this);
            }
        }
    },
    
    setElement: function(element) {
        if (this._element !== element) {
            if (this._element && this._element._box === this) 
                delete this._element._box;
            this._element = element;
            this._element._box = this;
        }
    },
     
    handleParentAdjust: function(adjustArgs) {
        if (this.anchors) this.anchors.adjustChild(this, adjustArgs);
    },
    
    handleParentDestroy: function() {
        this.setParentBox(null);
    },
    
    equals: function(otherBox) {
        return this.left == otherBox.left && this.right == otherBox.right && this.top == otherBox.top && this.bottom == otherBox.bottom;
    },
    
    substract: function(otherBox) {
        return new Pwg_Anchors.Box(this.left - otherBox.left, this.top - otherBox.top, this.right - otherBox.right, this.bottom - otherBox.bottom);
    },
    
    assign: function(otherBox) {
        this.left = otherBox.left;
        this.top = otherBox.top;
        this.right = otherBox.right;
        this.bottom = otherBox.bottom;
        this.anchors.assign(otherBox.anchors);
    },
    
    clone: function() {
        return new Pwg_Anchors.Box(this);
    },
    
    adjust: function(dLeft, dTop, dRight, dBottom) {
    
        if (dLeft || dRight || dTop || dBottom) {
            
            if (dLeft !== undefined) dLeft = 0;
            if (dRight !== undefined) dRight = 0;
            if (dTop !== undefined) dTop = 0;
            if (dBottom !== undefined) dBottom = 0;
            
            var newLeft = this.left + dLeft, 
                newTop = this.top + dTop,
                newRight = this.right + dRight,
                newBottom = this.bottom + dBottom;
                
            var newWidth = newBottom - newTop, width = this.bottom - this.top, dWidth = newWidth - width, 
                newHeight = newRight - newLeft, height = this.width - this.height, dHeight = newHeight - height;
            
            this.left = newLeft;
            this.top = newTop;
            this.right = newRight;
            this.bottom = newBottom;
            
            var adjustArgs = {
                box: this,
                dLeft: dLeft,
                dRight: dRight,
                dTop: dTop, 
                dBottom: dBottom,
                dWidth: dWidth,
                dHeight: dHeight
            }
            
            this.triggerEvent('adjust', adjustArgs);
            
        }
        
    },
    
    destroy: function() {        
        this.setParentBox(null);
        this.setElement(null);
        
        Pwg_Anchors.prototype.destroy.apply(this);
    }
    
};

Pwg_Util.extend(Pwg_Anchors.Box, Pwg_Anchors.Observable);

Pwg_Anchors.Anchors = function(left, top, right, bottom) {
    if (arguments.length == 1 && left instanceof Pwg_Anchors.Anchors) this.assign(left);
    else {
        this.left = left;
        this.top = top;
        this.right = right;
        this.bottom = bottom;
    }
};

Pwg_Anchors.Anchors.prototype = {
    
    left: false,
    right: false,
    top: false,
    bottom: false,
    
    assign: function(anchors) {
        this.left = anchors.left;
        this.right = anchors.right;
        this.top = anchors.top;
        this.bottom = anchors.bottom;
    },
    
    clone: function() {
        return new Pwg_Anchors.Anchors(this);
    },
    
    adjustChild: function(childBox, adjustArgs) {
        var dLeft = this.left? adjustArgs.dLeft : 0;
        var dTop = this.top? adjustArgs.dTop : 0;
        var dRight = this.right? adjustArgs.dRight : 0;
        var dBottom = this.bottom? adjustArgs.dBottom : 0;
        
        childBox.adjust(dLeft, dTop, dRight, dBottom);
    }
    
};

Pwg_Anchors.DimStrategy = {
    
    getDimensions: function(element) {
        var region = YAHOO.util.Region.getRegion(element);
        
        return {
            left: region.l,
            top: region.t,
            right: region.r,
            bottom: region.b
        }
    },
    
    setDimensions: function(element, left, top, right, bottom) {    
        YAHOO.util.setXY(element, [left, top]);
        element.style.width = (right - left) + "px";
        element.style.height = (bottom - top) + "px";
    }
    
};

Pwg_Anchors.defaultStrategy = Pwg_Anchors.DimStrategy;