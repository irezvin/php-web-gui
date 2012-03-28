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


YAHOO.widget.ToggleNode.checkClick = function(oArgs) {
        console.log("Check click");
		var node = oArgs.node;
		var target = YAHOO.util.Event.getTarget(oArgs.event);
        if (node.check && !node.disabled && YAHOO.util.Dom.hasClass(target,'ygtvspacer')) {
            if (!node.checked) {
                node.check(node.recursiveCheck);
            } else {
                node.uncheck(node.recursiveCheck);
            }

            node.onCheckClick(node);
            this.fireEvent("checkClick", node);
            return false;
        }
    };

YAHOO.widget.ToggleNode.checkDblClick = function(oArgs) {
		var node = oArgs.node;
		var target = YAHOO.util.Event.getTarget(oArgs.event);
		if (node.check && !node.disabled && YAHOO.util.Dom.hasClass(target,'ygtvspacer')) {
            node.refreshCheckState();
	        if (node.recursiveToggleOnDblClick) {
                if (node.checkState !== 5 && node.checkState !== 1) node.check(true);
                    else node.uncheck(true);
	        }

	        node.onCheckDblClick(node);
	        this.fireEvent("checkDblClick", node);
		    return false;
		}
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

        if (this.tree && !this.tree.hasEvent("checkClick")) {
            this.tree.createEvent("checkClick", this.tree);
            this.tree.subscribe('clickEvent',YAHOO.widget.ToggleNode.checkClick);
        }
        
        if (this.tree && !this.tree.hasEvent("checkDblClick")) {
            this.tree.createEvent("checkDblClick", this.tree);
            this.tree.subscribe('dblClickEvent',YAHOO.widget.ToggleNode.checkDblClick);
        }

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

    /**
     * Override to get the check doubleclick event
     */
    onCheckDblClick: function() {
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
        console.log("refreshing check state for ", this.label, "allChecked is ", allChecked, "someChecked is ", someChecked, "checked is ", this.checked );
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
        console.log("Changing checked value from ", oldChecked, "to", checked);
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
        sb[sb.length] = '<td';
        sb[sb.length] = ' id="' + this.getCheckElId() + '"';
        sb[sb.length] = ' class="' + this.getCheckStyle() + '"';
        sb[sb.length] = '>';
        sb[sb.length] = '<div class="ygtvspacer"></div></td>';

        sb[sb.length] = '<td><span';
        sb[sb.length] = ' id="' + this.labelElId + '"';
        if (this.title) {
            sb[sb.length] = ' title="' + this.title + '"';
        }
        sb[sb.length] = ' class="' + this.labelStyle  + '"';
        sb[sb.length] = ' >';
        sb[sb.length] = this.label;
        sb[sb.length] = '</span></td>';
        return sb.join("");
    }
});

Pwg_Tree_Node_Toggle = function(options) {
    Pwg_Tree_Node.call(this, options);
}

Pwg_Tree_Node_Toggle.prototype = {
    jsClassName: "Pwg_Tree_Node_Toggle",
    disabled: false,
    recursiveCheck: false,
    recursiveToggleOnDblClick: false,
    checked: false,

    getConstructor: function() {
        return YAHOO.widget.ToggleNode;
    },

    getTreeNodeConfig: function() {
        var res = Pwg_Tree_Node.prototype.getTreeNodeConfig.call(this);
        res.disabled = this.disabled;
        res.recursiveCheck = this.recursiveCheck;
        res.recursiveToggleOnDblClick = this.recursiveToggleOnDblClick;
        res.checked = this.checked;
        return res;
    },

    createTreeNode: function(c) {
        var res = Pwg_Tree_Node.prototype.createTreeNode.call(this, c);
        res.onCheckedChange = function(a) {
            return function(node) { a.onNodeCheckedChange(node); };
        } (this);
        res.onBranchToggle = function(a) {
            return function(node) { a.onNodeBranchToggle(node); };
        } (this);
        
        return res;
    },

    onNodeCheckedChange: function(node) {
        this.sendMessage('checkedChange', node.checked? 1 : 0);
    },

    onNodeBranchToggle: function(node) {
        this.sendMessage('branchToggle', node.checked? 1 : 0);
    },

    setDisabled: function(value) {
        if (value === undefined) value = this.disabled;
            else this.disabled = value;
        if (this.yuiTreeNode) this.yuiTreeNode.disabled = value;
    },

    setRecursiveCheck: function(value) {
        if (value === undefined) value = this.recursiveCheck;
            else this.recursiveCheck = value;
        if (this.yuiTreeNode) this.yuiTreeNode.recursiveCheck = value;
    },

    setRecursiveToggleOnDblClick: function(value) {
        if (value === undefined) value = this.recursiveToggleOnDblClick;
            else this.recursiveToggleOnDblClick = value;
        if (this.yuiTreeNode) this.yuiTreeNode.recursiveToggleOnDblClick = value;
    },

    setChecked: function(value) {
        this.lockMessages();
        if (value === undefined) value = this.checked;
            else this.checked = value;
        if (this.yuiTreeNode) this.yuiTreeNode.setChecked(value);
        this.unlockMessages();
    }

}

Pwg_Util.extend(Pwg_Tree_Node_Toggle, Pwg_Tree_Node);