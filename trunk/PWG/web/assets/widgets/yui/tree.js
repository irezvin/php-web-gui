Pmt_Tree_Node = function(options) {
    this.children = new Array();
    this.initialize(options);
};

Pmt_Tree_Node.placeNode = function(parentYuiTreeNode, yuiTreeNode, displayOrder) {
    var isFirst = true;
    var firstChild = null;
    var found = null;
    for (var i = 0; i < parentYuiTreeNode.children.length; i++) {
        var child = parentYuiTreeNode.children[i];
        if (!firstChild) firstChild = child;
        if (child.pmtTreeNodeId) {
            var varId = 'v_' + child.pmtTreeNodeId;
            if (window[varId] && window[varId].displayOrder !== undefined) {
                if (window[varId].displayOrder < displayOrder) {
                    isFirst = false;
                    found = child;
                }
            }
        }
    }
    if (isFirst && firstChild) yuiTreeNode.insertBefore(firstChild);
    else {
        if (found) yuiTreeNode.insertAfter(found);
        else yuiTreeNode.appendTo(parentYuiTreeNode);
    }
    if (yuiTreeNode.nextSibling == yuiTreeNode) {
        // TODO: Find out why this happens!
        //console.log("Fuck");
        yuiTreeNode.nextSibling = yuiTreeNode.previousSibling = null;
    }

};

Pmt_Tree_Node.prototype = {
    jsClassName: "Pmt_Tree_Node",
    
    className: null,
    content: null,
    href: null,
    expanded: null,
    selected: null,
    id: null,
    contentProperty: 'label',
    type: 'text', // can also be 'html', 'menu', 'date'
    displayOrder: null,

    autoEvents: [],

    yuiTreeNode: false,
    children: null,
    parentId: false,

    hasToScrollIntoView: false,

    _lockDestroy: 0,

    getYuiTreeView: function() {
        var res = false;
        if (this.yuiTreeNode && this.yuiTreeNode.tree && this.yuiTreeNode.tree._pmtId) {
            var varName = "v_" + this.yuiTreeNode.tree._pmtId;
            if (window[varName]) res = window[varName];
        }
        return res;
    },

    isInsetNode: function() {
        var tv = this.getYuiTreeView(), res = false;
        if (tv) res = (this.id === tv.insetPanelNode);
        return res;
    },

    addChild: function(treeNode) {
        for (var i = this.children.length - 1; i >= 0; i--) {
            if (this.children[i] === treeNode) this.children.splice(i, 1);
        }
        if (this.yuiTreeNode && treeNode.yuiTreeNode && this.yuiTreeNode !== treeNode.yuiTreeNode.parent) {
            Pmt_Tree_Node.placeNode(treeNode.yuiTreeNode, this.yuiTreeNode, this.displayOrder);
            var tv = this.getYuiTreeView();if (tv) tv.refresh(treeNode.hasToScrollIntoView? treeNode: null);
        } else {
            Pmt_Util.pushWithOrder(this.children, treeNode, 'displayOrder');
        }
    },

    getParent: function() {
        var res = null;
        if (this.parentId) {
            if (typeof(window[this.parentId]) === 'object' && typeof(window[this.parentId].addChild) === 'function')
                res = window[this.parentId];
        }
        return res;
    },

    getTreeNodeConfig: function() {
        var treeNodeConfig = {};
        if (this.className !== null) treeNodeConfig.className = this.className;
        if (this.content !== null) treeNodeConfig[this.contentProperty] = this.content;
        	else treeNodeConfig[this.contentProperty] = '';
        if (this.href !== null) treeNodeConfig.href = this.href;
        if (this.expanded !== null) treeNodeConfig.expanded = this.expanded;
        if (this.selected !== null) treeNodeConfig.highlightState = 1;
        return treeNodeConfig;
    },

    createTreeNode: function(c) {
        if (this.type == 'html') this.contentProperty = 'html';
        if (!c) c = YAHOO.widget.TextNode;
        var treeNodeConfig = this.getTreeNodeConfig();
        var parent = this.getParent();
        var parentNode, isOfRoot = false;
        if (parent && parent.yuiTreeNode) parentNode = parent.yuiTreeNode; 
        else {
            if (parent && parent.yuiTreeView) {
                parentNode = parent.yuiTreeView.getRoot();
                isOfRoot = true;
            }
            else parentNode = null;
        }
        var res = new c (treeNodeConfig, parentNode);
        if (parentNode) Pmt_Tree_Node.placeNode(parentNode, res, this.displayOrder);
        if (parentNode) {
            //parentNode.refresh();
            if (parentNode && parentNode.tree) {
                res.tree = parentNode.tree;
                if (res.tree._pmtId) {
                    var v = "v_" + res.tree._pmtId;
                    if (window[v] && window[v].refresh) window[v].refresh();
                }
//              if (this.selected) {
//                  res.tree.render();
//                  var focusRes = res.focus();
//                  console.log(this.id + " is selected; ", focusRes);
//              }
            }
        }
        if (!isOfRoot && parentNode.expanded) parentNode.tree.render();
        res.pmtTreeNodeId = this.id;

        res.refresh = function() {
            console.log('refreshing', this.pmtTreeNodeId);
            c.prototype.refresh.apply(this, arguments);
            if (this.pmtTreeNodeId && window['v_' + this.pmtTreeNodeId]) {
                var pmtNode = window['v_' + this.pmtTreeNodeId];
                if (pmtNode.isInsetNode && pmtNode.isInsetNode()) {
                    var tv = pmtNode.getYuiTreeView();
                    tv.showInsetPanelAt(pmtNode);
                }
            }
        }
        return res;
    },

    handleTreeNodeEvent: function() {
        this.sendMessage.apply(this, arguments);
    },

    getConstructor: function() {
        var res = null;
        switch (this.type) {
            case 'text':
                res = YAHOO.widget.TextNode;
                break;
            case 'menu':
                res = YAHOO.widget.MenuNode;
                break;
            case 'html':
                res = YAHOO.widget.HTMLNode;
                break;
            case 'date':
                res = YAHOO.widget.DateNode;
                break;
            default:
                throw 'Unsupported TreeNode type: "' + this.type + '"';
                break;
        }
        return res;
    },

    renderElement: function() {
        if (!this.yuiTreeNode) {
            var p = this.getParent();
            var c = this.getConstructor();
            this.yuiTreeNode = this.createTreeNode(c, parent && parent.yuiTreeNode? parent.yuiTreeNode : null);
            if (p) {
                p.addChild(this);
            }
            if (this.children instanceof Array)
                for (var i = this.children.length - 1; i >= 0; i--) this.children[i].renderElement();
            //var tmp = [].concat(this.children);
            //for (var i = 0; i < tmp.length; i++) if (tmp[i]) tmp[i].renderElement();
        }
    },

    getInsetEl: function() {
        var res = false;
        if (this.yuiTreeNode && (res = this.yuiTreeNode.getContentEl())) {
            while (res.nextSibling) res = res.nextSibling;
        }
        return res;
    },

    refresh: function() {
    },

    doOnDelete: function() {
        if (this._lockDestroy) {
            return;
        }
        this._lockDestroy++;
        var tree;
    	if (this.yuiTreeNode) {
            if (this.yuiTreeNode && (tree = this.yuiTreeNode.tree)) {
            	var parent = this.yuiTreeNode.parent;
            	while (this.yuiTreeNode.tree)
                    this.yuiTreeNode.tree.removeNode(this.yuiTreeNode, true);
                var v = "v_" + tree._pmtId;
                if (window[v] && window[v].showInsetPanelAt) window[v].showInsetPanelAt(null);
                if (window[v] && window[v].refresh) window[v].refresh();
                var v1 = "v_" + this.id;
                if (window[v1] && window[v1].yuiTreeNode && window[v1].yuiTreeNode.tree) {
                    window[v1].yuiTreeNode.tree.removeNode(window[v1].yuiTreeNode);
                }
            	//if (parent) parent.refresh();
            	//console.log("Removed!");
            	
            }
        }
        for (var i = this.children.length - 1; i >= 0; i--) {
            var tmp = this.children[i];
            if (tmp) tmp.destroy();
            this.children.splice(i, 1);
        }
        this._lockDestroy--;
    },

    setContent: function(content) {
        if (content !== undefined) this.content = content;
        if (this.content === null) this.content = '';
        if (this.yuiTreeNode) {
            this.yuiTreeNode[this.contentProperty] = this.content;
            this.yuiTreeNode.refresh();
        }
    },

    setClassName: function(className) {
        if (className !== undefined) this.className = className;
        if (this.yuiTreeNode) {
            //console.log("Setting class name of ", this.id, " to ", className)
            this.yuiTreeNode.className = this.className;
            if (this.yuiTreeNode.tree && this.yuiTreeNode.tree._pmtId) {
            	var v = "v_" + this.yuiTreeNode.tree._pmtId;
            	if (window[v] && window[v].refresh) {
            		window[v].refresh();
            	}
            } else {
            	this.yuiTreeNode.refresh();
            }
        }
    },

    setExpanded: function(expanded) {
        if (expanded !== undefined) this.expanded = expanded;
        if (this.yuiTreeNode) {
            this.lockMessages();
            if (expanded) this.yuiTreeNode.expand();
                else this.yuiTreeNode.collapse();
            this.unlockMessages();
        }
    },

    setSelected: function(selected) {
        if (selected !== undefined) this.selected = selected;
        if (this.yuiTreeNode) {
            this.lockMessages();
            if (selected) this.yuiTreeNode.highlight();
                else this.yuiTreeNode.unhighlight();
            this.unlockMessages();
        }
    },

    canImmediatelyScrollIntoView: function() {
        return !!(this.yuiTreeNode && this.yuiTreeNode.getEl());
    },

    scrollIntoView: function() {
    	var el;
        if (this.canImmediatelyScrollIntoView()) {
            var el = this.yuiTreeNode.getEl();
        	if (!this.yuiTreeNode.focus()) el.scrollIntoView();
            this.hasToScrollIntoView = false;
        } else {
            this.hasToScrollIntoView = true;
            var tv = this.getYuiTreeView();
            if (tv) tv.refresh(this);
        }
    },

    changeParent: function(newParentId) {
        var np;
        this.parentId = newParentId;
        if (this.yuiTreeNode && (np = this.getParent()) && np.addChild) {
            np.addChild(this);
        }
    }

}

Pmt_Util.extend(Pmt_Tree_Node, Pmt_Element);
Pmt_Util.augment(Pmt_Tree_Node.prototype, Pmt_Control_Parent_Functions);

Pmt_Tree_View = function(options) {
    this.initialize(options);
}

Pmt_Tree_View.prototype = {
    jsClassName: "Pmt_Tree_View",
    container: false,
    id: false,
    
    children:[],
    
    autoEvents: [],
     
    yuiTreeView: false,
    
    delayedRefreshCall: false,

    insetPanelContainerId: false,

    insetPanelContainer: null,

    insetPanelOuterContainer: null,

    insetPanelNode: null,
    
    rendered: false,

    currentNodeId: false,

    addChild: function(treeNode) {
        var root;
        if (this.yuiTreeView && (root = this.yuiTreeView.getRoot()) && treeNode.yuiTreeNode) {
            if (treeNode.yuiTreeNode.parent !== this.yuiTreeView.root) {
                //treeNode.yuiTreeNode.appendTo(root);
                Pmt_Tree_Node.placeNode(root, treeNode.yuiTreeNode, treeNode.displayOrder);
            }
            for (var i = this.children.length - 1; i >= 0; i--) {
                if (this.children[i] === treeNode) this.children.splice(i, 1);
            }
            //root.refresh();
            this.yuiTreeView._pmtId = this.id;
            this.refresh(treeNode.hasToScrollIntoView? treeNode : null);
            //this.yuiTreeView.render();
        } else {
            //this.children.push[treeNode];
        }
    },
    
    renderElement: function() {
        if (!this.yuiTreeView) {
            if (this.insetPanelContainerId) {
                this.insetPanelContainer = document.getElementById(this.insetPanelContainerId);
                if (this.insetPanelContainer) {
                    var stopper = function(event) {
                        YAHOO.util.Event.stopEvent(event);
                    }
                    YAHOO.util.Event.addListener(this.insetPanelContainer, 'click', stopper);
                    YAHOO.util.Event.addListener(this.insetPanelContainer, 'dblclick', stopper);
                    YAHOO.util.Event.addListener(this.insetPanelContainer, 'focus', stopper);
                    YAHOO.util.Event.addListener(this.insetPanelContainer, 'blur', stopper);
                    if (this.insetPanelContainer.parentNode)
                        this.insetPanelContainer.parentNode.removeChild(this.insetPanelContainer);
                    
                    var c = this.getContainer();
                    this.insetPanelOuterContainer = document.createElement('div');
                    this.insetPanelOuterContainer.style.display="none";
                    this.insetPanelOuterContainer.appendChild(this.insetPanelContainer);
                    if (c) c.parentNode.insertBefore(this.insetPanelOuterContainer, this.getContainer().nextSibling);

                    this.setInsetPanelNode();
                }
            }
            var config = [];
            this.yuiTreeView = new YAHOO.widget.TreeView(this.container, config);
            for (var i = 0; i < this.children.length; i++) this.children[i].renderElement();
            var events = ['clickEvent', 'dblClickEvent', 'expand', 'collapse', 'highlightEvent', 'focusChanged'];
            for (var i = 0; i < events.length; i++) {
                this.yuiTreeView.subscribe(events[i], this.handleYuiEvents, events[i], this);
            }
            this.yuiTreeView.removeChildren(this.yuiTreeView.getRoot());
            this.yuiTreeView.render();
            this.delayedRefreshCall = new Pmt_Util.DelayedCall(this.immediateRefresh, undefined, this, [], 0.1, false);
        }
    },

    setInsetPanelNode: function(insetPanelNode) {
        var old = this.insetPanelNode;
        if (insetPanelNode !== undefined)
            this.insetPanelNode = insetPanelNode;
        else insetPanelNode = this.insetPanelNode;
        console.log('insetPanelNode is ', insetPanelNode);
        if (insetPanelNode) {
            var newNode = this.getNodeByResponderId(insetPanelNode);
            console.log('newNode is ', newNode);
            if (newNode && newNode.yuiTreeNode) newNode.yuiTreeNode.refresh();
        } else this.showInsetPanelAt(null);
    },

    showInsetPanelAt: function(pmtNode) {
        if (this.insetPanelContainer) {
            var el;
            if (pmtNode && pmtNode.yuiTreeNode) {
                el = pmtNode.getInsetEl();
            } else {
                el = this.insetPanelOuterContainer;
            }
            if (el && this.insetPanelContainer.parentNode !== el) {
                if (this.insetPanelContainer.parentNode) this.insetPanelContainer.parentNode.removeChild(this.insetPanelContainer);
                    el.appendChild(this.insetPanelContainer);
            }
        }
    },

    getNodeByResponderId: function(responderId) {
        var res = null;
        if (window['v_' + responderId]) res = window['v_' + responderId];
        return res;
    },
    
    immediateRefresh: function(nodeToScrollIntoView) {
    	if (!this.rendered) {
    		if (this.yuiTreeView && this.yuiTreeView.render) this.yuiTreeView.render();
            if (this.currentNodeId && window['v_' + this.currentNodeId]) window['v_' + this.currentNodeId].scrollIntoView();
            this.rendered = true;
    	} else {
            if (this.yuiTreeView && this.yuiTreeView.render) this.yuiTreeView.render();
    	}
        if (nodeToScrollIntoView) {
            if (nodeToScrollIntoView.canImmediatelyScrollIntoView())
                nodeToScrollIntoView.scrollIntoView();
            else {
                nodeToScrollIntoView.hasToScrollIntoView = false;
            }
        }
        this.setInsetPanelNode();
    },
    
    refresh: function(nodeToScrollIntoView) {
    	this.delayedRefreshCall.callWithArgs(nodeToScrollIntoView);
    },
    
    getPmtNode: function(yuiTreeNode) {
        var res = null;
        if (yuiTreeNode && (typeof(yuiTreeNode) === 'object') && (typeof(yuiTreeNode.pmtTreeNodeId) === 'string')) {
            var varId = 'v_' + yuiTreeNode.pmtTreeNodeId;
            if (window[varId] && window[varId].yuiTreeNode) res = window[varId];
        }
        return res;
    },
    
    handleYuiEvents: function(oArgs, eventType) {
        //console.log('handleYuiEvents triggered', arguments);
        
        switch (eventType) {
//            case 'focusChanged':
//                var oldNode = this.getPmtNode(oArgs.oldNode), newNode = this.getPmtNode(oArgs.newNode);
//                if (oldNode) oldNode.handleTreeNodeEvent('exit');
//                if (newNode) newNode.handleTreeNodeEvent('enter');
//                break;
                
            case 'expand':
            case 'collapse':
                var node = this.getPmtNode(oArgs);
                if (node) node.handleTreeNodeEvent(eventType);
                break;
                
            case 'clickEvent':
                var node = this.getPmtNode(oArgs.node);
                if (node) node.handleTreeNodeEvent('click');
                break;
                
            case 'dblClickEvent':
                var node = this.getPmtNode(oArgs.node);
                if (node) node.handleTreeNodeEvent('dblClick');
                break;
        }
    },
    
    doOnDelete: function() {
        if (this.delayedRefreshCall) this.delayedRefreshCall.cancel();
        if (this.yuiTreeView) {
        	this.yuiTreeView.destroy();
            delete this.yuiTreeView;
        }
    },
    
    setClassName: function(className) {
        if (className !== undefined) this.className = className;
        var el;
        if (this.yuiTreeView && (el = this.yuiTreeView.getEl())) {
            el.className = this.className;
        }
    }   
}

Pmt_Util.extend(Pmt_Tree_View, Pmt_Element);
Pmt_Util.augment(Pmt_Tree_View.prototype, Pmt_Control_Parent_Functions);