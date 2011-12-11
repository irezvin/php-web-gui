// add legacy namespaces support

Pmt_Util = Ae_Util; 

Pm_Debug = {
        
    debugTypes: {
        'lifecycle': false
    },

    d: function() {return window.Pm_Debug.debug.apply(window, arguments);},

    debug: function(type) {
        if ((window.Pm_Debug.debugTypes[type] === undefined) || window.Pm_Debug.debugTypes[type])
            if (window.console && typeof window.console.log === 'function') console.log.apply (console, arguments);
    }
};


Pmt_Core = function(options) {
	this.initialize(options);	
};

Pmt_Core.prototype = {
	
	jsClassName: "Pmt_Core",
	transport : null,
	id : null,
	containerId: false,
	options: false,
    
    getContainer: function() {
        if (!this.container && this.containerId) {
            this.container = document.getElementById(this.containerId);
        }
        return this.container;
    },

    lockMessages: function() {
        if (!this.msgLock) this.msgLock = 0;
        this.msgLock++;
    },

    unlockMessages: function() {
        if (this.msgLock) this.msgLock--;
    },
    
    initialize: function(options) {
        Pm_Debug.d("lifecycle", 
        			"Creating [" + this.jsClassName 
        				+ "], id: [" + (options.id? options.id : "unknown") 
        				+ "], containerId [" + (options.container? options.container : "unknown")
        				+ "]", [ options ]
        );
    	this.options = options;
        Pmt_Util.override(this, options);
    },
	

    destroy: function() {
    	Pm_Debug.d("lifecycle", "Destroying [" + this.jsClassName + "], id: [" + (this.id? this.id : " unknown") + "]");
        if (typeof(this.doOnDelete) == 'function') {
        	this.doOnDelete();
        }
    	if (this.transport) {
            this.transport.unobserveRecipient(this.id);
            this.transport = null;
            this.options = null;
            this.observersMap = null;
        }
        if (this.container && this.container.parentNode) {
            this.container.parentNode.removeChild(this.container);
        }
        YAHOO.util.Event.purgeElement(this.container);
        delete this.container;
        if (this.id && window['v_' + this.id] === this) {
            //delete window['v_' + this.id];
            window['v_' + this.id] = null;
        }
    },
    
    doOnDelete: function() {
    },
    

    handleServerMessage: function(methodName, params) {
        if (typeof(this[methodName]) == 'function') {
            this[methodName].apply(this, params);
        } else {
        }
    },

    /**
     * This function accepts variable number of arguments as message parameters
     */
    sendMessage: function(methodName) {
        if (this.transport && this.id && !this.msgLock) {
            var msgArgs = [];
            for (var i = 1; i < arguments.length; i++) msgArgs[i - 1] = arguments[i];
            this.transport.pushMessage(this.id, methodName, msgArgs);
        }
    },

    frontendObserve: function() {
    },

    frontendUnobserve: function() {
    },
    
    getDefault: function(property, def) {
    	var res = def;
    	if (window.Pmt_UiDefaults) {
    		var p = window.Pmt_UiDefaults;
    		if (p[property] !== undefined && p[property] !== null) res = p[property];
    	}
    	return res;
    }
    		
};
