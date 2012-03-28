// ------------------------------------------- Pwg_Table ------------------------------------------- //

Pwg_Table = function (options) {
    this.selectedIndice = [];
    this.rows = [];
    this.toggleableColumns = [];
    this.autoEvents = [];
    this.initialize(options);

}

Pwg_Ref = function(str) {
    this.str = str;
    this.deref = function(root) {
        return root.getAggregateById(str);
    }
}

Pwg_Table.prototype = {
	jsClassName: "Pwg_Table",
    yuiTable: false,
    columnDefs: false,
    dataSource: false,
    configs: false,
    id: false,
    initializerFn: false,
    rows: null,
    selectedIndice: null,
    toggleableColumns: null,
    autoEvents: null,
    contextMenu: false,
    _hasToRender: false,
    rowClassColumnName: null,

    _rowClickCall: null,
    
    getAggregateById: function(id) {
        /* 
            Possible ids are:
                - <empty>
                - columnSet
                - recordSet
                - cellEditor
                - row.<id>
                - record.<id>
                - column.<id>
                - column.<id>.editor                
        */
        
        var res = null, i = id.split(".");
        if (i[0] && i[0] == 'table') {
            res = this.yuiTable;
        } 
        else if (i[0] && i[0] == 'columnSet') {
            if (this.yuiTable) res = this.yuiTable.getColumnSet();
        }
        else if (i[0] && i[0] == 'recordSet') {
            if (this.yuiTable) res = this.yuiTable.getRecordSet();
        }
        else if (i[0] && i[0] == 'cellEditor') {
            if (this.yuiTable) res = this.yuiTable.getCellEditor();
        }
        else if (i[0] && i[0] == 'record' && i[1]) {
            if (this.yuiTable) res = this.getRecordById(i[1]);
        } 
        else if (i[0] && i[0] == 'column' && i[1]) {
            if (this.yuiTable) res = this.yuiTable.getColumn(i[1]);
            if (i[2] && i[2] == 'editor' && res) res = res.get('editor');
        }
        return res;
    },
    
    getRecordById: function (recordId)  {
    },

    renderElement: function() {
        if (this.container) {

            if (this.rowClassColumnName) {
                var t = this;
                this.configs.formatRow = function(elTr, oRecord) {
                    var classes = oRecord.getData(t.rowClassColumnName);
                    if (classes) YAHOO.util.Dom.addClass(elTr, classes);
                    return true;
                }
            }

            if (this.rowClassColumnName) {
                var t = this;
                this.configs.formatRow = function(elTr, oRecord) {
                    var classes = oRecord.getData(t.rowClassColumnName);
                    if (classes) YAHOO.util.Dom.addClass(elTr, classes);
                    return true;
                }
            }

            this.yuiTable = new YAHOO.widget.DataTable(this.container, this.columnDefs, this.dataSource, this.configs);

            if (typeof (this.initializerFn) == 'function') this.initializerFn.call(this);
            if (this.rows instanceof Array) this.setRows(this.rows);
            this.yuiTable.subscribe("rowMouseoverEvent", this.yuiTable.onEventHighlightRow);
            this.yuiTable.subscribe("rowMouseoutEvent", this.yuiTable.onEventUnhighlightRow);

            if (1 || Prototype.Browser.IE) {
                this.yuiTable.subscribe("rowClickEvent", this.yuiTable.onEventSelectRow, null, this.yuiTable);
            } else {
                this._rowClickCall = new Pwg_Util.DelayedCall(this.yuiTable.onEventSelectRow, null, this.yuiTable, [], this.getDefault('clickDelay', 300), false);
                this.yuiTable.subscribe("rowClickEvent", function(args) {
                    this._rowClickCall.callWithArgs(args);
                }, null, this);
            }
            this.yuiTable.subscribe("rowDblclickEvent", this.handleRowDblClick, null, this);

            this.yuiTable.subscribe("rowSelectEvent", this.handleSelectionChange, null, this);
            this.yuiTable.subscribe("columnReorderEvent", this.handleColumnReorder, null, this);
            this.yuiTable.subscribe("columnResizeEvent", this.handleColumnResize, null, this);
            var t = this;
            this.yuiTable.doBeforeSortColumn = function() {
                t.tableBeforeSortColumn.apply(t, arguments);
            } /*this.tableBeforeSortColumn.bind(this);*/

            this.yuiTable.getColumn = function(column) {
                var oColumn = this._oColumnSet.getColumn(column);

                if(!oColumn) {
                    // Validate TD element
                    var elCell = this.getThEl(column);
                    if(elCell) {
                            // Find by TH el ID
                            var allColumns = this._oColumnSet.flat;
                            for(var i=0, len=allColumns.length; i<len; i++) {
                                if(allColumns[i].getThEl().id === elCell.id) {
                                    oColumn = allColumns[i];
                                }
                            }
                    }
                    // Validate TH element
                    else {
                        elCell = this.getTdEl(column);
                        if (elCell) oColumn = this._oColumnSet.getColumn(elCell.cellIndex);
                    }
                }
                if(!oColumn) {
                }
                return oColumn;
            };


            if (this.selectedIndice) this.selectRows();

            if (this.toggleableColumns instanceof Array && this.toggleableColumns.length) {
                this.buildContextMenu();
            }

            this.notifyMessageQueueEnd();

            //this.yuiTable.subscribe("rowUnselectEvent", this.handleSelectionChange, null, this);
        } else {
        }
    },

    notifyMessageQueueEnd: function() {
        if (this._hasToRender) {
            this._hasToRender = false;
            this.yuiTable.render();
        }
    },

    handleColumnToggle: function(key, menuItem) {
        this.sendMessage('columnToggle', key, !menuItem.cfg.getProperty('checked')? 1 : 0);
    },
    
    setDataForColumn: function(key, colData) {
        var rs = this.yuiTable.getRecordSet();
        var l = rs.getLength();
        for (var i = 0; i < l; i++) {
            var rec = rs.getRecord(i);
            var aeUid = rec.getData('__aeUid');
            if (colData[aeUid] != undefined) {
                this.yuiTable.updateCell(rec, key, colData[aeUid]);
            }
        }
    },

    toggleColumn: function(key, visibility, colData) {
        var col = this.yuiTable.getColumn(key);
        if (col) {
            if (visibility) this.yuiTable.showColumn(col);
                else  this.yuiTable.hideColumn(col);
            if (colData) this.setDataForColumn(key, colData);
        }
        if (this.contextMenu) {
            var id = this.id + '_toggle_' + key;
            var items = this.contextMenu.getItems();
            for (var i = 0; i < items.length; i++) {
                if (items[i].id == id) {
                    items[i].cfg.setProperty('checked', !!visibility);
                }
            }
        }
    },
    
    setLabelColumn: function(key, label) {
    	var col = this.yuiTable.getColumn(key);
        if (col) {
    		var e = col.getThLinerEl(); 
    		if (e) e.innerHTML = label;
        }
    },

    buildContextMenu: function() {
        var tc = this.toggleableColumns;
        var items = [];
        for (var i = 0; i < tc.length; i++) {
            var key = tc[i].key;
            var label = tc[i].label;
            var visible = this.yuiTable.getColumn(key) && !this.yuiTable.getColumn(key).hidden;
            var clickHandler = function(k, t) {
                return function() {
                    t.handleColumnToggle(k, this);
                }
            } (key, this);
            
            items[items.length] = {
                'id': this.id + '_toggle_' + key,
                'checked': visible,
                'text': label,
                'onclick': {
                    'fn': clickHandler
                }
            }
        }
        this.contextMenu = new YAHOO.widget.ContextMenu(this.id + "ContextMenu",
            {
                trigger: this.yuiTable.getTheadEl(),
                itemdata: items,
                lazyload: true
            }
        );
        Pwg_Yui_Util.fixMenuDisplay(this.contextMenu);

    },

    tableBeforeSortColumn: function(column, dir) {
        if (!this.yuiTable.get('dynamicData')) {
            return true;
        } else {
            this.sendMessage('columnSortRequest', column.key, dir == YAHOO.widget.DataTable.CLASS_ASC? 'asc' : 'desc');
        }
    },

    /**
     * @param key column key
     * @param dir 'asc' | 'desc' whether direction is ascending
     */
    setColumnSort: function(key, dir) {
        if (!key) {
            thus.yuiTable.set('sortedBy', null);
        } else {
            var col = this.yuiTable.getColumn(key);
            if (col) {
                var foo =
                    YAHOO.widget.DataTable[dir == 'asc'? 'CLASS_ASC' : 'CLASS_DESC'];
                this.yuiTable.set('sortedBy', {'key' : key, 'dir': foo});
            }
        }
    },

    handleColumnResize: function(oArgs) {
        this.sendMessage('columnResize', oArgs.column.key, oArgs.width);
    },

    resizeColumn: function(key, width) {
        this.lockMessages();
        this.yuiTable.setColumnWidth(key, width);
        this.unlockMessages();
    },

    handleColumnReorder: function(oArgs) {
        this.sendMessage('columnReorder', oArgs.column.key, oArgs.column.getTreeIndex());
    },

    reorderColumn: function(key, newIndex) {
        this.lockMessages();
        this.yuiTable.reorderColumn(key, newIndex);
        this.unlockMessages();
    },

    handleSelectionChange: function(args) {
        if (!this.msgLock) {
            var sr = this.yuiTable.getSelectedRows();
            var rowIndice = [];
            for (var i = 0; i < sr.length; i++) {
                rowIndice[rowIndice.length] = this.yuiTable.getRecord(sr[i]).getData('__aeUid');
            }
            this.selectedIndice = rowIndice;
            if (this.transport) this.transport.pushMessage(this.id, 'selectionChange', [this.selectedIndice]);
        }
    },
    
    deref: function(arg) {
        var res = arg instanceof Pwg_Ref? arg.deref(this) : arg;
        return res;
    },
    
    derefAll: function(args) {
        var res = [];
        for (var i = 0; i < args.length; i++) {
            res[i] = this.deref(args[i]);
        }
        return res;
    },
    
    bindAggregate: function(aggregateId, eventName, outArgs) {
        var aggregate = this.getAggregateById(aggregateId), handler, res = false;
        if (aggregate) {
            if (typeof(outArgs) == 'array' && outArgs.length) {
                handler = function(event) {
                    var args = {};
                    for (var i = 0; i < outArgs.length; i++) args[outArgs[i]] = event[outArgs[i]];
                    this.transport.pushMessage(this.id, eventName + '@' + aggregateId, args);
                }
            } else {
                handler = function(event) { this.transport.pushMessage(this.id, eventName + '@' + aggregateId, {}); }
            }
            aggregate.subscribe (event, handler, null, this);
            rets = true;
        }
        return res;
    },
    
    frontendObserve: function(eventName, outArgs) {
        var aggregateEvent = eventName.split('@');
        if (aggregateEvent.length == 2) {
            var event = aggregateEvent[0];
            var aggregate = aggregateEvent[1];
            this.bindAggregate(aggregateId, eventName, outArgs);
        } else {
            Pwg_Element.prototype.frontendObserve.call(this, eventName, outArgs);
        }
    },
    
    handleServerMessage: function(methodName, params) {
        var aggregateMethod = methodName.split('@');
        if (aggregateMethod.length == 2) {
            var method = aggregateMethod[0];
            var aggregate = this.getAggregateById(aggregateMethod[1]);
            if (aggregate) {
                if (typeof(aggregate[method]) == 'function') aggregate[method].apply(aggregate, params);
            } else {
                throw "Aggregate not found: " + aggregateMethod[1];
            }
        } else {
            Pwg_Element.prototype.handleServerMessage.call(this, methodName, params);
        }
    },

    refresh: function() {        
    },

    setRows: function(rows, immediateRender) {
        if (rows != undefined) this.rows = rows;
        this.yuiTable.getRecordSet().replaceRecords(rows);
        if (immediateRender) this.yuiTable.render();
            else this._hasToRender = true;
    },

    selectRows: function(selectedIndice) {
        this.lockMessages();
        if (selectedIndice instanceof Array) this.selectedIndice = selectedIndice;
        var rs = this.yuiTable.getRecordSet();
        var l = rs.getLength();
        for (var i = 0; i < l; i++) {
            var rec = rs.getRecord(i);
            var aeUid = rec.getData('__aeUid');
            var sel = false;
            for (var j = 0; j < this.selectedIndice.length; j++) {
                if (aeUid == this.selectedIndice[j]) {
                    sel = true;
                    break;
                }
            }
            if (sel) this.yuiTable.selectRow(rec);
                else this.yuiTable.unselectRow(rec);
        }
        this.unlockMessages();
    },
    
    doOnDelete: function() {
 	   if (this.yuiTable) {
 		   this.yuiTable.destroy();
 		   delete this.yuiTable;
 	   }
       if (this.contextMenu) {
           this.contextMenu.destroy();
           delete this.contextMenu;
       }
 	   delete this.dataSource;
    },

    handleRowDblClick: function(args) {
        var event = args.event, tr = args.target;
        if (this._rowClickCall) this._rowClickCall.cancel();
        if (tr) {
            var record = this.yuiTable.getRecord(tr);
            if (record) {
                var uid = record.getData('__aeUid');
                if (uid) {
                    this.sendMessage('rowDblClick', uid);
                    //if (event) YAHOO.util.Event.stopEvent(event);
                }
            }
        }
    }


};

Pwg_Util.extend(Pwg_Table, Pwg_Element);