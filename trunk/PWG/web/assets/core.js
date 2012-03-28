// add legacy namespaces support

Pwg_Util = Ae_Util; 

Pwg_Debug = {
        
    debugTypes: {
        'lifecycle': false
    },

    d: function() {return window.Pwg_Debug.debug.apply(window, arguments);},

    debug: function(type) {
        if ((window.Pwg_Debug.debugTypes[type] === undefined) || window.Pwg_Debug.debugTypes[type])
            if (window.console && typeof window.console.log === 'function') console.log.apply (console, arguments);
    }
};


Pwg_Core = function(options) {
	this.initialize(options);	
};

Pwg_Core.prototype = {
	
	jsClassName: "Pwg_Core",
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
        Pwg_Debug.d("lifecycle", 
        			"Creating [" + this.jsClassName 
        				+ "], id: [" + (options.id? options.id : "unknown") 
        				+ "], containerId [" + (options.container? options.container : "unknown")
        				+ "]", [ options ]
        );
    	this.options = options;
        Pwg_Util.override(this, options);
    },
	

    destroy: function() {
    	Pwg_Debug.d("lifecycle", "Destroying [" + this.jsClassName + "], id: [" + (this.id? this.id : " unknown") + "]");
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
    	if (window.Pwg_UiDefaults) {
    		var p = window.Pwg_UiDefaults;
    		if (p[property] !== undefined && p[property] !== null) res = p[property];
    	}
    	return res;
    }
    		
};
