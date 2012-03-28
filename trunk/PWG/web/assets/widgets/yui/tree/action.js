Pmt_Yui_Tree_Action = function(options) {
    Pmt_Util.override(this, options);
    if (this.immediate) this.run();
}


Pmt_Yui_Tree_Action.prototype = {

    treeId: null,
    nodeId: null,
    immediate: true,

    run: function() {

    }

}