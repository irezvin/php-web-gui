function showSelectFile(url, id, fileChangeFn) {
    if (id) url = url + id;
    var foo = window.open(url, 'selectFileWin', 'left=100,top=100,width=500,height=500');
    if (fileChangeFn) foo.fileChangeFn = fileChangeFn;
}
