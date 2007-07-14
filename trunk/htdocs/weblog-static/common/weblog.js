var getObj = function(objId) {
	return document.all ? document.all[objId] : document.getElementById(objId);
}

var openComment = function(entryId) {
	newWin = window.open("http://www.v2ex.com/blog/comment?entry_id=" + entryId, "winComment", "width=580,height=450,scrollbars=yes");
	newWin.moveTo(40, 40);
	newWin.focus();
}