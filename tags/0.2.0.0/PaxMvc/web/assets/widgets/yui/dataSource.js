Pmt_Yui_DataSource = function(options) {
    var config = options.config || {};
    this.transport = options.transport;
    this.id = options.id;
    this.dataRequestMethod = options.dataRequestMethod || 'dataRequest';
    this.constructor.superclass.constructor.call(this, {}, config);
};

YAHOO.lang.extend(Pmt_Yui_DataSource, YAHOO.util.DataSourceBase, {

    transport: false,
    id: false,
    requests: {},

    handleServerMessage: function(methodName, params) {
        if (typeof(this[methodName]) == 'function') {
            this[methodName].apply(this, params);
        } else {
        }
    },

    dataResponse: function(transactionId, response) {
        this.responseType = YAHOO.util.DataSource.TYPE_JSARRAY;
        //this.handleResponse(oRequest, response, oCallback, oCaller, tId);

        if (this.requests['t_' + transactionId]) {
            var tInfo = this.requests['t_' + transactionId];
            this.handleResponse(tInfo.request, response, tInfo.callback, tInfo.caller, transactionId);
            delete this.requests['t_' + transactionId];
        }
    },

    makeConnection : function(oRequest, oCallback, oCaller) {
        oRequest = decodeURIComponent(oRequest);
        var tId = YAHOO.util.DataSource._nTransactionId++;
        this.requests['t_' + tId] = {request: oRequest, callback: oCallback, caller: oCaller};
        this.fireEvent("requestEvent", {tId:tId,request:oRequest,callback:oCallback,caller:oCaller});
        this.transport.pushMessage(this.id, 'dataRequest', [tId, oRequest], 1);
        return tId;
    }

});

YAHOO.lang.augmentObject(Pmt_Yui_DataSource, YAHOO.util.DataSource);