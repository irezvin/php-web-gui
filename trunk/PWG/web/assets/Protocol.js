Pm_Protocol = function (options) {

    if (window.YAHOO && window.YAHOO.util && window.YAHOO.util.Element)
    window.YAHOO.util.Element.prototype.DEFAULT_HTML_SETTER = function(value, key) {
        var el = this.get('element');

        if (el) {
            try {
                el[key] = value;
            } catch(e) {
                //throw(e);
            }
        }
    };
	
	this._observers = {};
	this._affectedObservers = {};
	this._inbox = [];
	this._outbox = [];
	
	if (options && ((typeof options) === 'object')) Pmt_Util.override(this, options);
	
	this._displayStatusDc = new Pmt_Util.DelayedCall(this.setDisplayStatus, null, this, [], 500, false);
	
	this._initialize();
	
};

Pm_Protocol.DISPLAY_STATUS_NORMAL = 'normal';
Pm_Protocol.DISPLAY_STATUS_BUSY = 'busy';
Pm_Protocol.DISPLAY_STATUS_WAIT = 'wait';

Pm_Protocol.ERROR_SERVER_UNAVAILABLE = 'serverUnavailable';
Pm_Protocol.ERROR_UNPARSABLE_RESPONSE = 'unparsableResponse';
Pm_Protocol.ERROR_CLIENT_EXCEPTION = 'clientException';
Pm_Protocol.ERROR_TRANSPORT_ERROR = 'transportError';
Pm_Protocol.ERROR_SERVER_EXCEPTION = 'serverException';

Pm_Protocol.PauseException = {pauseException: true};

Pm_Protocol.prototype = {
		
	/**
	 * Default response delay of controls' delayed calls (can be 0 for immediate calls) 
	 */
	defaultDelayedCallDelay: 50,
	
	classNameBusyCursor: 'busyCursor',
	classNamePauseCursor: 'pauseCursor',
	classNameNormalCursor: 'normalCursor',

	/**
	 * ID of last sent message
	 */
	lastMsgId: 0,
	
	/**
	 * Transport object (descendant of Pm_Protocol_Transport)
	 */
	transport: null,

	/**
	 * String - URL of server
	 */
	serverUrl: null,
	
	/**
	 * Array of observers
	 */
	_observers: null,

	/**
	 * Array of observers affected by last incoming message group
	 */
	_affectedObservers: null,
	
	_inbox: null,
	
	_outbox: null,
	
	/**
	 * Special item in inbox that is used to separate queues of messages added by addToInbox() call 
	 */
	_queueSeparator: {queueSeparator: true},
	
	_displayStatus: Pm_Protocol.DISPLAY_STATUS_NORMAL,
	
	_errorDiv: null,
	
	/**
	 * Number - time of last received response
	 */
	_lastResponseTime: null,
	
	_displayStatusDc: null,
	
	/**
	 * @param displayStatus one of Pm_Protocol.DISPLAY_STATUS_* constants 
	 */
	setDisplayStatus: function(displayStatus, force, immediate) {
		
		if(!immediate) this._displayStatusDc.callWithArgs(displayStatus, force, true);
		else {
			
			if (force || (displayStatus !== this.displayStatus)) {	
				this._displayStatus = displayStatus;
	
	            var de = document.documentElement;
	            YAHOO.util.Dom.removeClass(de, this.classNameBusyCursor);
	            YAHOO.util.Dom.removeClass(de, this.classNamePauseCursor);
	            YAHOO.util.Dom.removeClass(de, this.classNameNormalCursor);
	
	//			var de = $(document.documentElement);
	//
	//			de.removeClassName(this.classNameBusyCursor);
	//			de.removeClassName(this.classNamePauseCursor);
	//			de.removeClassName(this.classNameNormalCursor);
				
				switch (this._displayStatus) {
					case Pm_Protocol.DISPLAY_STATUS_NORMAL:
	                    YAHOO.util.Dom.addClass(de, this.classNameNormalCursor);
						//de.addClassName(this.classNameNormalCursor);
						break;
					case Pm_Protocol.DISPLAY_STATUS_BUSY:
						//de.addClassName(this.classNameBusyCursor);
	                    YAHOO.util.Dom.addClass(de, this.classNameBusyCursor);
						break;
					case Pm_Protocol.DISPLAY_STATUS_WAIT:
						//de.addClassName(this.classNamePauseCursor);
	                    YAHOO.util.Dom.addClass(de, this.classNamePauseCursor);
						break;
				}
				
				document.body.style.cursor = 'pause';
				document.body.style.cursor = '';
			}
		}
	},
	
	addToInbox: function(messages) {
		if (messages instanceof Array) {
			this._inbox = this._inbox.concat(messages);
			this._inbox.push(this._queueSeparator);
		}
	},
	
	notifyTransportStatusChanged: function() {
		this._updateStatus();
	},
	
	processInbox: function(extraMessages) {
		
		if (extraMessages !== undefined) this.addToInbox(extraMessages);
		var paused = false,
			msg;
		
		while (this._inbox.length && !this._isPause) {
			msg = this._inbox[0];
			if (msg === this._queueSeparator) {
				this._inbox.splice(0, 1);
				for (var i in this._affectedObservers) if (Pmt_Util.hasOwnProperty(this._affectedObservers, i)) {
					if (typeof this._affectedObservers[i].notifyMessageQueueEnd == 'function')
						this._affectedObservers[i].notifyMessageQueueEnd();
				}
				this._affectedObservers = {};
			} else {
				try {
					this._processInboundMessage(msg);
					this._inbox.splice(0, 1);
				} catch (e) {
					if (e === Pm_Protocol.PauseException) {
						this._isPause = true;
						this._updateStatus();
					} else {
						this._inbox.splice(0, 1);
						throw e;
					}
				}
			}
		}
		
		if (this._outbox.length) this.transport.notifyMessagePushed();
		
		this._updateStatus();
	},
	
	reportAffectedObserver: function(o) {
		this._affectedObservers['rcpt-' + o.id] = o;
	},
	
	/**
	 * @param {Array} messageIds IDs of messages that were surely processed by the server
	 */
	reportOutboundProcessed: function(messageIds) {
		var mi = [].concat(messageIds), 
			idx, 
			j, 
			found = false;
		
		for (var i = this._outbox.length - 1; (i >= 0) && mi.length; i--) {
			if ((idx = Pmt_Util.indexOf(this._outbox[i].msgId, mi)) >= 0) {
				this._outbox.splice(i, 1);
				mi.splice(idx, 1);
				found = true;
				continue;
			}
		}
		
		if (found) {
			this._lastResponseTime = this.getTime(); 
			this._updateStatus();
		}
	},

	/**
	 * 
	 * @param {String} recipientId 		ID of recipient on the server
	 * @param {String} methodName 		Name of the method to call
	 * @param {Array} params			Positional method parameters
	 * 
	 * @return {Number} 				ID of the message that's assigned in the outbox
	 */
	pushMessage: function(recipientId, methodName, params) {
		this.lastMsgId++;
		var msg = {'recipientId': recipientId, 'methodName': methodName, 'params' : params, 'msgId': this.lastMsgId};
        this._outbox.push(msg);
        if (!this._inbox.length) this.transport.notifyMessagePushed();
	},
	
	broadcast: function(methodName, params) {
        for (var i in this._observers) if (Pmt_Util.hasOwnProperty(this._observers, i)) {
            if (typeof(this._observers[i][methodName]) == 'function') this._observers[i][methodName](params);
        }
	},
	
	reportError: function(errorText, errorType, exceptionData, isCritical) {
		var errorMessage = '';
		if (isCritical === undefined) isCritical = false;
		errorMessage += isCritical? 'Critical error' : 'Error';
		if (errorType !== undefined) errorMessage += ' (' + errorType + ')';
		if (exceptionData) errorMessage += '\nException data:\n' + exceptionData;
		errorMessage += '\n' + errorText;
		this.showError(errorMessage);
	},
	
	showError: function(message) {
    	this.setDisplayStatus(Pm_Protocol.DISPLAY_STATUS_NORMAL) ;
    	
        if (!this._errorDiv) {
            this._errorDiv = document.createElement('div');
            this._errorDiv.setAttribute('class', 'paxError');
            var lnk = document.createElement('a');
            lnk.appendChild(document.createTextNode('Clear and close'));
            lnk.setAttribute('href', '#');
            this._errorDiv.appendChild(lnk);
            
        	var closeFn = function() {
                this._errorDiv.style.display = 'none';
                var c, cc;
                cc = this._errorDiv.getElementsByTagName('pre');
                for (var i = cc.length - 1; i >= 0 ; i--) this._errorDiv.removeChild(cc[i]);
            };
            
            YAHOO.util.Event.addListener(lnk, 'click', closeFn, null, this);
            //$(lnk).observe('click', closeFn.bind(this));
            if (!document.body.firstChild) document.body.appendChild(this._errorDiv);
                else document.body.insertBefore(this._errorDiv, document.body.firstChild);
        }
        
        var n, p;
        
        n = document.createTextNode(message);
        p = document.createElement('pre');
        p.appendChild(n);
        
        this._errorDiv.appendChild(p);
        if (this._errorDiv.style.display == 'none') this._errorDiv.style.display = '';
	},
	
	observe: function(obj) {
		var recipientId = obj.id;
		this._observers['rcpt-' + recipientId] = obj;
		if (typeof(obj.setTransport) == 'function') obj.setTransport(this); else obj.transport = this;
	},
	
	unobserveRecipient: function(recipientId) {
		if (this._observers['rcpt-' + recipientId]) delete this._observers['rcpt-' + recipientId];
	},
	
	getOutboundMessages: function() {
		return [].concat(this._outbox);
	},
	
	clearOutboundMessages: function() {
		this._outbox = [];
		this._updateStatus();
	},
	
	_initialize: function() {
    	if (!this.transport) this.transport = new Pm_Protocol_AjaxTransport({protocol: this});
    		else this.transport.protocol = this;
    	this.transport.notifyProtocolInitialized();
	},
	
	_processInboundMessage: function(msg) {
		
		var rcptId = msg.recipientId;
        
        if (this._observers['rcpt-' + rcptId]) {
            var o = this._observers['rcpt-' + rcptId];
            this._affectedObservers['rcpt-' + rcptId] = o;
            o.handleServerMessage(msg.methodName, msg.params);
        }
        
	},
	
	pause: function() {
		if (this._isPause) return;
		throw Pm_Protocol.PauseException;
	},
	
	resume: function() {
		this._isPause = false;
		this.processInbox();
	},
	
	_updateStatus: function() {
		if (this._isPause) this.setDisplayStatus(Pm_Protocol.DISPLAY_STATUS_WAIT);
		else {
			if (this._inbox.length || this._outbox.length || this.transport.isRequestPending()) this.setDisplayStatus(Pm_Protocol.DISPLAY_STATUS_BUSY);
			else {
				this.setDisplayStatus(Pm_Protocol.DISPLAY_STATUS_NORMAL);
			}
		}
	},
	
	/**
	 * @return Number		Current time in milliseconds since January, 1, 1970
	 */
	getTime: function() {
		var d = new Date();
		return d.getTime();
	}
			
};

