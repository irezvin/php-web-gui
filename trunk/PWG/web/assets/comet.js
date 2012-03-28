/**
 * Pwg_Comet is based on Pwg_Comet class by Andrea Giammarchi (webreflection.blogspot.com)
 * Mit Style License
 */
Pwg_Comet = function(strUrl, fnOnData, fnOnDisconnect, instanceId, scope, fnOnBadResponse)  {
    if (instanceId === undefined) instanceId = ++ Pwg_Comet._lastInstance;
    Pwg_Comet._instances[instanceId] = this;
    this.instanceId = instanceId;
    this.scope = scope;
    this.strUrl = strUrl.concat(strUrl.indexOf("?") < 0 ? "?" : "&", "cmtInstanceId=", this.instanceId, "&", Math.random());
    if (Pwg_Comet._useIframe) {
        var iframe  = document.createElement("iframe"), style = iframe.style;
        style.position = "absolute";
        style.visibility = "visible";
        style.display = "block";
        style.left = style.top = "-10000px";
        style.width = style.height = "1px";
        iframe.src = this.strUrl;
        document.body.appendChild(iframe);
        this.iframe = iframe;
    } else {
        this.strUrl = this.strUrl + '&cmtXhr=1';
    }
    if ((typeof fnOnData) == 'function') this.onData = fnOnData;
    if ((typeof fnOnDisconnect) == 'function') this.onDisconnect = fnOnDisconnect;
    if ((typeof fnOnBadResponse) == 'function') this.onBadResponse = fnOnBadResponse;
}

Pwg_Comet._instances = [];
Pwg_Comet._lastInstance = 0;
Pwg_Comet._useIframe = /\b(msie|opera)\b/i.test(navigator.userAgent);
Pwg_Comet.head = '------ [cometData] ------';

Pwg_Comet.sendDataToInstance = function(instanceId, data) {
    var instance = Pwg_Comet._instances[instanceId];
    if (instance) {
    	if (instance.scope) instance.onData.call(instance.scope, data);
    		else instance.onData(data);
    }
    else if (instance !== false) {
        throw new Error("Instance #" + conversationId + " not available");
    }
};


Pwg_Comet.makeQuery = function(data, paramName, stripLeadingAmpersand) {
        var res = '';
        if (data instanceof Array) {
            for (var i = 0; i < data.length; i++) {
                res = res + Pwg_Comet.makeQuery(data[i], paramName? paramName + '[' + i + ']' : i);
            }
        } else {
            if ((typeof data) == 'object') {
                for (var i in data) {
                	if (Pwg_Util.hasOwnProperty(data, i)) {
                		res = res + Pwg_Comet.makeQuery(data[i], paramName? paramName + '[' + i + ']' : i);
                	}
                }
            } else {
                res = '&' + paramName + '=' + encodeURIComponent(data);
            }
        }
        if (stripLeadingAmpersand && res.length) res = res.slice(1);
        return res;
};


Pwg_Comet.disconnectInstance = function(instanceId) {
    var instance = Pwg_Comet._instances[instanceId];
    if (instance) {
        instance.disconnect();
    }
}

Pwg_Comet.prototype = {
    instanceId : null,
    strUrl: null,
    iframe: null,
    xhr: null,
    connected: false,
    disconnected: true,
    length: 0,
    scope: null,
    
    onBadResponse: null,
    
    _currMessage: '',
    _messageLength: 5000,
    _headSkip: 0,
    
    connect: function() {
        if (!this.connected) {
            this.connected = true;
            this.disconnected = false;

            var self = this;

            if (Pwg_Comet._useIframe) {

                this.onreadystatechange = function() {Pwg_Comet.prototype._onreadystatechange.call(self);}
                window.attachEvent? 
                		window.attachEvent("onreadystatechange", this.onreadystatechange)
                		: window.addEventListener("readystatechange", this.onreadystatechange, false);
                

                this.onbeforeunload = function(){Pwg_Comet.prototype._disconnect(self);}
                
                window.attachEvent? 
                		window.attachEvent("onbeforeunload", this.onbeforeunload)
                		: window.addEventListener("beforeunload", this.onbeforeunload, false);

                (document.body || document.documentElement).appendChild(this.iframe);
            } else {
                var
                    xhr = new XMLHttpRequest,
                    t = this,
                    length = 1030,
                    //script = /^<script[^>]*>parent\.(.+);<\/script><br\s*\/>$/,
                    responseText;
                xhr.open("get", this.strUrl, true);
                xhr.onreadystatechange  = function(){
                       if(xhr.readyState > 2) {
                        if(xhr.status == 200){
                            responseText = xhr.responseText.substring(this.length);
                            this.length = xhr.responseText.length;
                            //eval(responseText.replace(script, "$1"));
                            t.appendResponse(responseText);
                        } else {
                            Pwg_Comet.prototype._onreadystatechange.call({readyState:"loaded"});
                        }
                    }
                };
                this.xhr = xhr;
                this.onbeforeunload = function() {Pwg_Comet.prototype._disconnect(self);};
                window.addEventListener("beforeunload", this.onbeforeunload, false);
            	window.setTimeout(function(){xhr.send(null);}, 0);
            }
        }
        return this;
    },
    
    _badResponse: function(responseText) {
    	if (this.onBadResponse) {
    		if (this.scope) this.onBadResponse.call(this.scope, this, null, responseText);
    			else this.onBadResponse(this, null, responseText); 
    	}
    	this.disconnect();
    },
    
    appendResponse: function(responseText) {
    	if (!this._messageLength) {
    		var h = Pwg_Comet.head.substr(this._headSkip, Pwg_Comet.head.length), l = h.length;
    		if (responseText.length < l) {
    			l = responseText.length;
    			h = Pwg_Comet.head.substr(this._headSkip, l);
    			this._headSkip += l;
    		} else {
    			this._headSkip = 0;
    		}
    		if (responseText.substr(0, l) !== h) {
    			this._badResponse(responseText);
    		} else {
    			responseText = responseText.substr(l, responseText.length - l);
        		if (this._headSkip && !responseText.length) return;
        			else this._headSkip = 0;
    		}
    		var idx = responseText.indexOf('-');
    		if (idx >= 0) {
    			a = [];
    			a[0] = responseText.substr(0, idx);
    			a[1] = responseText.substr(idx + 1);
	    		this._messageLength = parseInt(a[0]);
	    		window._lastMessageLength = this._messageLength;
	    		this._currMessage = a[1];
	    		window._lastMessageHead = a[1];
	    		window._headResponseText = responseText;
    		}
    	} else {
    		this._currMessage += responseText;
    		window._lastCurrMessage = this._currMessage; 
    	}
    	if (this._currMessage.length >= this._messageLength) {
    		var cm = this._currMessage.slice(0, this._messageLength);
    		var rest = this._currMessage.slice(this._messageLength, this._currMessage.length);
    		window._lastRest = rest;
    		try {
    			eval(cm + ";");
    		} catch (e) {
    			console.log("can\'t eval message: ", cm + ";", e);
    		}
    		this._messageLength = 0;
    		this._currMessage = '';
    		if (rest.length) this.appendResponse(rest);
    	}
    },

    disconnect: function() {
        if (!this.disconnected) {
            this.disconnected = true;
            if (typeof this.onDisconnect == 'function') {
            	if (this.scope) this.onDisconnect.call(this.scope);
            		else this.onDisconnect();
            }
            Pwg_Comet.prototype._disconnect(this);
            Pwg_Comet._instances[this.instanceId] = false;
            if(typeof CollectGarbage == "function")
                CollectGarbage();
        }
        return this;
    },

    _disconnect: function(instance) {
        if (Pwg_Comet._useIframe) {
            var iframe  = instance.iframe;
            if(iframe && iframe.parentNode){
                window.detachEvent?
                		window.detachEvent("onreadystatechange", instance.onreadystatechange)
                		: window.removeEventListener("readystatechange", instance.onreadystatechange, false);
                		
                window.detachEvent?
                		window.detachEvent("onbeforeunload", instance.onbeforeunload)
                		: window.removeEventListener("beforeunload", instance.onbeforeunload, false);
                		
                iframe.src = ".";
                iframe.parentNode.removeChild(iframe);
                delete instance.iframe;
            }
        } else {
            var xhr = instance.xhr;
            if (xhr) {
                window.removeEventListener("beforeunload", instance.onbeforeunload, false);
                xhr.onreadystatechange = function() {};
                xhr.abort();
                delete instance.xhr;
            }
        }
    },

    _onreadystatechange: function() {
        if(/loaded|complete/i.test(this.readyState))
                    throw new Error("Comet server is not available");
    }

}