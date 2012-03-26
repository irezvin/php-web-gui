Pmt_Yui_Tree_New = function (options) {
    this.items = new Array;
    this.queue = new Array;
    this.autoEvents = new Array;
    this.toRefresh = {};
    this.initialize(options);
}

Pmt_Yui_Tree_New.prototype = {

    jsClassName: "Pmt_Yui_Tree_New",
    container: false,
    id: false,

    nodePrototypes: null,

    yuiTreeView: false,

    insetPanelContainerId: false,
    insetPanelContainer: null,
    insetPanelOuterContainer: null,
    
    insetPanelNode: null,
    selectedNode: null,

    rendered: false,
    currentNodeId: false,

    /**
     * Actions that tree will perform after render()
     * @type Array
     */
    queue: null,

    toRefresh: null,
    toScroll: null,
    refreshCall: null,

    isRefreshing: false,

    _refresh: function(what) {
        this.toRefresh._flag = true;
        var key = (what.data && what.data.uid)? what.data.uid : 0;
        this.toRefresh[key] = what;
        this.refreshCall.call();
    },

    _immediateRefresh: function() {
        if (this.isRefreshing) return;
        this.isRefreshing = true;
        var tmp = this.insetPanelNode;
        this.setInsetPanelNode(null);
        var tr = this.toRefresh;
        this.toRefresh = {};
        if (tr[0]) tr[0].render();
        else {
            for (var i in tr) {
                var t = tr[i];
                if (t.refresh) {
                    t.refresh();
                }
            }
        }
        this.isRefreshing = false;
        this.setInsetPanelNode(tmp);
        this.setSelectedNode();
        if (this.toRefresh._flag) this.refreshCall.call();
        else {
            if (this.toScroll && this.toScroll.getEl && this.toScroll.getEl()) {
                if (this.toScroll.blur) this.toScroll.blur();
                var p = this.toScroll.getEl().parentNode;
                var d = document.createElement('a');
                d.href='#';
                //d.innerHTML = '&nbsp;';
                this.toScroll.getEl().parentNode.insertBefore(d, this.toScroll.getEl());
                d.focus();
                d.blur();
            	if (!this.toScroll.focus()) this.toScroll.getEl().scrollIntoView();
                d.parentNode.removeChild(d);
                this.toScroll = null;
            }
        }
    },

    _arrangeInsetContainer: function() {
        this.insetPanelContainerId = this.containerId + '_insetPanel';
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

                //this.setInsetPanelNode();
            }
        }
    },

    _reappend: function(node, children) {
        for (var i = 0, l = children.length; i < l; i++) {
            children[i].appendTo(node);
            if (children[i].children) {
                var tmp = children[i].children;
                children[i].children = [];
                this._reappend(children[i], tmp);
            }
        }
    },

    renderElement: function() {
        if (!this.yuiTreeView) {
            this.refreshCall = new Pmt_Util.DelayedCall(this._immediateRefresh, null, this, [], this.getDefault('renderDelay', 200), false);
            this._arrangeInsetContainer();

            this.yuiTreeView = new YAHOO.widget.TreeView(this.container, []);
            
            var root = this.yuiTreeView.root;

            this.yuiTreeView._onClickEvent = this._onClickEvent;
            this.yuiTreeView.createEvent("checkClick", this.tree);

            this.yuiTreeView.render();

            this._reappend(root, this.nodePrototypes);
            
            var events = ['clickEvent', 'dblClickEvent', 'expand', 'collapse', 'highlightEvent', 'checkClick'];
            for (var i = 0; i < events.length; i++) {
                this.yuiTreeView.subscribe(events[i], this.handleYuiEvents, events[i], this);
            }
        }
    },

    getNodePath: function(node) {
        var res = [];
        while (node.parent) {
            res.splice(0, 0, Pmt_Util.indexOf(node, node.parent.children));
            node = node.parent;
        }
        return res;
    },

    getNodeByPath: function(path) {
        var res = null;
        if (this.yuiTreeView) {
            res = this.yuiTreeView.root;
            for (var i = 0; res && (i < path.length); i++) res = res.children[path[i]];
            if (!res) res = null;
        }
        return res;
    },

    msgRemoveNode: function(path) {
        var n = this.getNodeByPath(path);
        if (n) {
            var p = n.parent;
            if (this.hasInsetPanelInside(n)) this._showInsetPanelAt(null);
            if (p) this._refresh(p);
                else this._refresh(this.yuiTreeView);
        }
    },

    msgRemoveChild: function(path, index) {
        var n = this.getNodeByPath(path);
        if (n && n.children[index]) {
            this.yuiTree.popNode(n.children[index]);
            if (this.toRefresh[n.children[index].data.uid]) delete this.toRefresh[n.children[index].data.uid];
            this._refresh(n);
        }
    },

    msgDeleteNode: function(path) {
        var n = this.getNodeByPath(path);
        if (n) {
            if (this.toRefresh[n.data.uid]) delete this.toRefresh[n.data.uid];
            this.yuiTreeView.removeNode(n);
            var p = n.parent;
            if (this.hasInsetPanelInside(n)) this._showInsetPanelAt(null);
            if (p) this._refresh(p);
                else this._refresh(this.yuiTreeView);
        }
    },

    msgScrollNodeIntoView: function(path) {
        var n = this.getNodeByPath(path);
        if (n) {
            this.toScroll = n;
            this._refresh(n);
        }
    },

    handleYuiEvents: function(oArgs, eventType) {
//        console.log('handleYuiEvents triggered', arguments);

        switch (eventType) {
//            case 'focusChanged':
//                var oldNode = this.getPmtNode(oArgs.oldNode), newNode = this.getPmtNode(oArgs.newNode);
//                if (oldNode) oldNode.handleTreeNodeEvent('exit');
//                if (newNode) newNode.handleTreeNodeEvent('enter');
//                break;

            case 'expand':
                this.sendMessage('nodeEvent', 'childExpand', oArgs.data.uid);
                break;

            case 'collapse':
                this.sendMessage('nodeEvent', 'childCollapse', oArgs.data.uid);
                break;

            case 'clickEvent':
                this.sendMessage('nodeEvent', 'childClick', oArgs.node.data.uid);
                break;

            case 'dblClickEvent':
                this.sendMessage('nodeEvent', 'childDblClick', oArgs.node.data.uid);
                break;

            case 'checkClick':
                this.sendMessage('nodeEvent', 'childCheckedChange', oArgs.data.uid, oArgs.checked? 1 : 0);
                break;

        }
    },

    msgSetNodeProperty: function(idPath, propName, propValue) {
        return this.setNodeProperty(idPath, propName, propValue);
    },

    setNodeProperty: function(idPath, propName, propValue) {
        var n = this.getNodeByPath(idPath);
        if (n) {
            var setterName = 'set' + Pmt_Util.ucFirst(propName);
            if (typeof n[setterName] == 'function') n[setterName] (propValue);
                else n[propName] = propValue;
            this._refresh(n);
        } else {
        }
    },
    
    msgExecuteNodeMethod: function(idPath, method, args) {
        return this.executeNodeMethod(idPath, method, args);
    },

    executeNodeMethod: function(idPath, method, args) {
        if (!args) args = [];
        var n = this.getNodeByPath(idPath);
        if (n) {
            n[method].apply(n, args);
        }
    },

    msgAddNode: function(node, parentPath, index) {
        var p = this.getNodeByPath(parentPath);
        if (p) {
            //p.children.splice(index, 0, node);
            if (p.children[index]) node.insertBefore(p.children[index]);
                else node.appendTo(p);
            if (p.parentNode) this._refresh(p.parentNode);
                else this._refresh(this.yuiTreeView);
        }
    },

    moveNode: function(nodePath, index) {
        var n = this.getNodeByPath(nodePath);
        if (n && n.parent) {
            var p = n.parent;
            this.yuiTreeView.popNode(n);
            if (p.children[index]) n.insertBefore(p.children[index]);
                else n.appendTo(p);
            this._refresh(p);
        }
    },

    rebuildTree: function() {
        if (!this.yuiTreeView) this.renderElement();
            else this.yuiTreeView.render();
    },

    refresh: function() {
        this._refresh(this.yuiTreeView);
        //this.setSelectedNode();
        //this.setInsetPanelNode();
    },

    setVisible: function(visible) {
        if (visible === undefined) visible = this.visible;
        return Pmt_Element.prototype.setVisible.call(this, visible);
    },

    findNode: function(nodeOrPath) {
        var res = null;
        if (nodeOrPath instanceof Array) res = this.getNodeByPath(nodeOrPath);
        else {
            if (((typeof nodeOrPath) === 'object') && nodeOrPath && nodeOrPath.tree) res = nodeOrPath;
        }
        return res;
    },

    setSelectedNode: function(selectedNode) {
        var oldSelectedNode = this.findNode(this.selectedNode);
        if (selectedNode === undefined) selectedNode = this.selectedNode;
        selectedNode = this.findNode(selectedNode);
        if (oldSelectedNode && (oldSelectedNode !== selectedNode)) {
            this._unselectNode(oldSelectedNode);
        }
        if (selectedNode) this._selectNode(selectedNode);
        this.selectedNode = selectedNode;
    },

    _selectNode: function(node) {
        for (var p = node.parent; p; p = p.parent) p.expand();
        var te = node.getToggleEl();
        if (te) YAHOO.util.Dom.addClass(te, 'selectedToggle');
        YAHOO.util.Dom.addClass(node.contentElId, 'selected');
    },

    _unselectNode: function(node) {
        var te = node.getToggleEl();
        if (te) YAHOO.util.Dom.removeClass(te, 'selectedToggle');
        YAHOO.util.Dom.removeClass(node.contentElId, 'selected');
    },

    setInsetPanelNode: function(insetPanelNode) {
        var oldInsetPanelNode = this.findNode(this.insetPanelNode);
        if (insetPanelNode === undefined) insetPanelNode = this.insetPanelNode;
        insetPanelNode = this.findNode(insetPanelNode);
        this._showInsetPanelAt(insetPanelNode);
        this.insetPanelNode = insetPanelNode;
    },

    _showInsetPanelAt: function(node) {
        if (this.insetPanelContainer) {
            var el;
            if (node) {
                el = document.getElementById(node.contentElId);
            } else {
                el = this.insetPanelOuterContainer;
            }
            if (el && this.insetPanelContainer.parentNode !== el) {
                if (this.insetPanelContainer.parentNode) this.insetPanelContainer.parentNode.removeChild(this.insetPanelContainer);
                    el.appendChild(this.insetPanelContainer);
            }
        }
    },

    clear: function() {
        this._showInsetPanelAt(null);
        this.insetPanelNode = this.selectedNode = null;
        if (this.yuiTreeView) {
            this.lockMessages();
            this.yuiTreeView.removeChildren(this.yuiTreeView.root);
            this.yuiTreeView.render();
            this.unlockMessages();
        }        
    },

    hasInsetPanelInside: function(node) {
        var ipn = this.findNode(this.insetPanelNode);
        for (var n = ipn; n && n !== node; n = n.parent) {};
        return !!n;
    },

    _onClickEvent: function (ev) {
        var self = this,
            Event = YAHOO.util.Event,
            Dom = YAHOO.util.Dom,
            td = this._getEventTargetTdEl(ev),
            node,
            target,
            toggle = function (force) {
                node.focus();
				if (force || !node.href) {
					node.toggle();
					try {
						Event.preventDefault(ev);
					} catch (e) {
	                    // @TODO
	                    // For some reason IE8 is providing an event object with
	                    // most of the fields missing, but only when clicking on
	                    // the node's label, and only when working with inline
	                    // editing.  This generates a "Member not found" error
	                    // in that browser.  Determine if this is a browser
	                    // bug, or a problem with this code.  Already checked to
	                    // see if the problem has to do with access the event
	                    // in the outer scope, and that isn't the problem.
	                    // Maybe the markup for inline editing is broken.
					}
                }
            };

        if (!td) {
            return;
        }

        node = this.getNodeByElement(td);
        if (!node) {
            return;
        }

        // exception to handle deprecated event labelClick
        // @TODO take another look at this deprecation.  It is common for people to
        // only be interested in the label click, so why make them have to test
        // the node type to figure out whether the click was on the label?
        
        target = Event.getTarget(ev);
        
        if (Dom.hasClass(target, node.labelStyle) || Dom.getAncestorByClassName(target, node.labelStyle)) {
            this.fireEvent('labelClick',node);
        }

        if (node.check && !node.disabled && YAHOO.util.Dom.hasClass(target,'ygtvcheck')) {
            if (!node.checked) {
                node.check(node.recursiveCheck);
            } else {
                node.uncheck(node.recursiveCheck);
            }
            node.onCheckClick(node);
            this.fireEvent("checkClick", node);
            YAHOO.util.Event.stopEvent(ev);
            return false;
        }


        //  If it is a toggle cell, toggle
        if (/\bygtv[tl][mp]h?h?/.test(td.className)) {
            toggle(true);
        } else {
            if (this._dblClickTimer) {
                window.clearTimeout(this._dblClickTimer);
                this._dblClickTimer = null;
            } else {
                if (this._hasDblClickSubscriber) {
                    this._dblClickTimer = window.setTimeout(function () {
                        self._dblClickTimer = null;
                        if (self.fireEvent('clickEvent', {event:ev,node:node}) !== false) {
                            toggle();
                        }
                    }, 200);
                } else {
                    if (self.fireEvent('clickEvent', {event:ev,node:node}) !== false) {
                        toggle();
                    }
                }
            }
        }
    }

}

Pmt_Util.extend(Pmt_Yui_Tree_New, Pmt_Element);

/**
 * @extends YAHOO.widget.TextNode
 * @constructor
 * @param oData    {object}  A string or object containing the data that will
 *                           be used to render this node.
 * @param oParent  {Node}    This node's parent node
 * @param expanded {boolean} The initial expanded/collapsed state
 * @param checked  {boolean} The initial checked/unchecked state
 */
YAHOO.widget.ToggleNode = function(oData, oParent, expanded, checked) {
	YAHOO.widget.ToggleNode.superclass.constructor.call(this,oData,oParent,expanded);
    this.setUpCheck(checked || oData.checked);

};

YAHOO.extend(YAHOO.widget.ToggleNode, YAHOO.widget.TextNode, {

    /**
     * @type boolean
     */
    checked: false,

    /**
     * @type boolean
     */
    disabled: false,

    /**
     * checkState
     * 0=unchecked and none of children are checked (even if don't have children),
     * 1=checked but have children and none of children checked
     * 2=unchecked but some of children are checked
     * 3=checked and some of children are checked
     * 4=unchecked and all of children are checked
     * 5=checked and all of children are checked
     * @type int
     */
    checkState: 0,

    /**
     * check/uncheck will always be recursive
     * @type bool
     */
    recursiveCheck: false,

    /**
     * Double click will cause recursive check/uncheck.
     * @type bool
     */
    recursiveToggleOnDblClick: false,

    /**
     * ID of group to simulate "radio toggle" - only one node within the group
     * can be checked (if groupId is non-zero).
     * @type int
     */
    groupId: 0,

	/**
     * The node type
     * @property _type
     * @private
     * @type string
     * @default "TextNode"
     */
    _type: "ToggleNode",

	ToggleNodeParentChange: function() {
        //this.updateParent();
    },

    setUpCheck: function(checked) {
        // if this node is checked by default, run the check code to update
        // the parent's display state
        if (checked && checked === true) {
            this.check();
        }

        // set up the custom event on the tree for checkClick
        /**
         * Custom event that is fired when the check box is clicked.  The
         * custom event is defined on the tree instance, so there is a single
         * event that handles all nodes in the tree.  The node clicked is
         * provided as an argument.  Note, your custom node implentation can
         * implement its own node specific events this way.
         *
         * @event checkClick
         * @for YAHOO.widget.TreeView
         * @param {YAHOO.widget.Node} node the node clicked
         */

        this.subscribe("parentChange", this.ToggleNodeParentChange);

    },

    /**
     * The id of the check element
     * @for YAHOO.widget.ToggleNode
     * @type string
     */
    getCheckElId: function() {
        return "ygtvcheck" + this.index;
    },

    /**
     * Returns the check box element
     * @return the check html element (img)
     */
    getCheckEl: function() {
        return document.getElementById(this.getCheckElId());
    },

    /**
     * The style of the check element, derived from its current state
     * @return {string} the css style for the current check state
     */
    getCheckStyle: function() {
        return "ygtvcheck" + this.checkState;
    },


    /**
     * Override to get the check click event
     */
    onCheckClick: function() {
    },

    refreshCheckState: function(nonRecursive, dontRefresh, dontUpdateParentIfStateChanged) {
        var oldCheckState = this.checkState;
        var allChecked = this.children.length > 0;
        var someChecked = false;
        for (var i = 0, l = this.children.length; i < l; i++) {
            var n = this.children[i];
            if (n.refreshCheckState) {
                if (!nonRecursive) n.refreshCheckState(false, dontRefresh, true);
                if (! (n.checkState == 5 || !n.children.length && n.checkState == 1)) allChecked = false;
                if (n.checkState !== 0) someChecked = true;
            }
        }
        if (allChecked) {

            this.checkState = this.checked? (this.children.length? 5 : 1) : 4;
        } else {
            if (someChecked) {
                this.checkState = this.checked? 3 : 2;
            } else {
                this.checkState = this.checked? 1 : 0;
            }
        }

        if (this.checkState !== oldCheckState) {
            if (!dontRefresh) this.updateCheckHtml();
            if (!dontUpdateParentIfStateChanged && this.updateParent) this.updateParent(dontRefresh);
        }
        //if (!dontRefresh && this.yuiTreeNode) this.yuiTreeNode.refresh();
    },

    /**
     * Refresh the state of this node's parent, and cascade up.
     */
    updateParent: function(dontRefresh) {
        var p = this.parent;

        if (!p || !p.refreshCheckState) {
            return;
        }

        p.refreshCheckState(true, dontRefresh);
    },

    /**
     * If the node has been rendered, update the html to reflect the current
     * state of the node.
     */
    updateCheckHtml: function() {
        if (this.parent && this.parent.childrenRendered) {
            this.getCheckEl().className = this.getCheckStyle();
        }
    },

    setChecked: function(checked, recursive, silent) {
        if (recursive === undefined) recursive = this.recursiveCheck;
        var oldChecked = this.checked;
        this.checked = checked;
        if (!silent && (oldChecked !== this.checked)) {
            if (recursive) this.onBranchToggle(this);
            else this.onCheckedChange(this);
        }
        if (recursive)
            for (var i = 0, l = this.children.length; i < l; i++) {
                var n = this.children[i];
                if (n.setChecked) n.setChecked(checked, recursive);
            }

        this.refreshCheckState();
        this.updateCheckHtml();
    },

    onCheckedChange: function(node) {
    },

    onBranchToggle: function(node) {
    },

    /**
     * Check this node
     */
    check: function(recursive, silent) {
        this.setChecked(true, recursive, silent);
    },

    /**
     * Uncheck this node
     */
    uncheck: function(recursive, silent) {
        this.setChecked(false, recursive, silent);
    },
    // Overrides YAHOO.widget.TextNode

    getContentHtml: function() {
        var sb = [];
        sb[sb.length] = '<span';
        sb[sb.length] = ' id="' + this.getCheckElId() + '"';
        sb[sb.length] = ' class="' + this.getCheckStyle() + '"';
        sb[sb.length] = '>';
        sb[sb.length] = '<span class="ygtvcheck"></span></span>';

        sb[sb.length] = '<span';
        sb[sb.length] = ' id="' + this.labelElId + '"';
        if (this.title) {
            sb[sb.length] = ' title="' + this.title + '"';
        }
        sb[sb.length] = ' class="' + this.labelStyle  + '"';
        sb[sb.length] = ' >';
        sb[sb.length] = this.label;
        sb[sb.length] = '</span>';
        return sb.join("");
    }
});
