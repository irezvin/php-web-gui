Pwg_Yui_Tree_Action = function(options) {
    Pwg_Util.override(this, options);
    if (this.immediate) this.run();
}


Pwg_Yui_Tree_Action.prototype = {

    treeId: null,
    nodeId: null,
    immediate: true,

    run: function() {

    }

}