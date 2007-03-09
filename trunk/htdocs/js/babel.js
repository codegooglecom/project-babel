/* Project Babel
*  Author: Livid Torvalds
*  File: /htdocs/js/babel.js
*  Usage: Client side functions for Project Babel
*  Format: 1 tab ident(4 spaces), LF, UTF-8, no-BOM
*
*  Subversion Keywords:
*
*  $Id: babel.js 295 2007-01-04 01:21:37Z livid $
*  $LastChangedDate: 2007-01-04 09:21:37 +0800 (Thu, 04 Jan 2007) $
*  $LastChangedRevision: 295 $
*  $LastChangedBy: livid $
*/

var getObj = function(objId) {
	return document.all ? document.all[objId] : document.getElementById(objId);
}

var switchDisplay = function(objId) {
	obj = getObj(objId);
	if (obj.style.display != "block") {
		obj.style.display = "block";
	} else {
		obj.style.display = "none";
	}
}

var changeBlockStyle = function(objId, strBgColor) {
	obj = getObj(objId);
	obj.bgColor = strBgColor;
}

var showReply = function(replyId) {
	if (replyId == "vxReplyA" ) {
		vertScroll = 0;
	} else {
		if (document.all) {
			vertScroll = document.body.offsetHeight + 400;
		} else {
			vertScroll = window.pageYOffset + 400;
		}
	}
	
	action = "window.scroll(0, " + vertScroll + ")";
	
	objReply = getObj(replyId);
	if (objReply.style.display != "block") {
		objReply.style.display = "block";
		setTimeout(action, 10);
	}
	
	if (replyId == "vxReplyB") {
		objReplyTip = getObj("vxReplyTip");
		if (objReplyTip.style.display != "none") {
			objReplyTip.style.display = "none";
		}
	}	
}

var jumpReply = function() {
	objQuick = getObj("taQuick");
	setTimeout("objQuick.focus()", 200);
}

var req;

function loadXML(url, cb) {
	req = false;
	// branch for native XMLHttpRequest object
	if(window.XMLHttpRequest) {
		try {
			req = new XMLHttpRequest();
		} catch(e) {
			req = false;
		}
	// branch for IE/Windows ActiveX version
	} else if(window.ActiveXObject) {
		try {
			req = new ActiveXObject("Msxml2.XMLHTTP");
		} catch(e) {
			try {
				req = new ActiveXObject("Microsoft.XMLHTTP");
			} catch(e) {
				req = false;
			}
		}
	}
	if(req) {
		req.onreadystatechange = cb;
		req.open("GET", url, true);
		req.send("");
	}
}

var addFavoriteTopic = function(topicId) {
	objFav = getObj("tpcFav");
	objFav.innerHTML = '<span class="tip_i">&nbsp; <img src="/img/loading.gif" align="absmiddle" />&nbsp;正在发送请求 ...</span>';
	url = "/fav/topic/add/" + topicId + ".vx";
	setTimeout("loadXML(url, addFavoriteTopicCallback)", 300);
}

var addFavoriteTopicCallback = function() {
	if (req.readyState == 4) {
		if (req.status == 200) {
			objFav = getObj("tpcFav");
			objFav.innerHTML = '<span class="tip_i">&nbsp; 本主题已加入收藏</span>';
		} else {
			objFav = getObj("tpcFav");
			objFav.innerHTML = '<a href="#" class="h">oops</a>';
		}
	}
}

var removeFavoriteTopic = function(topicId) {
	objFav = getObj("tpcFav");
	objFav.innerHTML = '<span class="tip_i">&nbsp; <img src="/img/loading.gif" align="absmiddle" />&nbsp;正在发送请求 ...</span>';
	url = "/fav/remove/" + topicId + ".vx";
	setTimeout("loadXML(url, removeFavoriteTopicCallback)", 700);
}

var removeFavoriteTopicCallback = function() {
	if (req.readyState == 4) {
		if (req.status == 200) {
			objFav = getObj("tpcFav");
			objFav.innerHTML = '<span class="tip_i">&nbsp; 本主题已从收藏中移出</span>';
		} else {
			objFav = getObj("tpcFav");
			objFav.innerHTML = '<a href="#" class="h">oops</a>';
		}
	}
}

var addFavoriteNode = function(nodeId) {
	objFav = getObj("nodFav");
	objFav.innerHTML = '<span class="tip_i"><img src="/img/loading.gif" align="absmiddle" />&nbsp;正在发送请求 ...</span>';
	url = "/fav/node/add/" + nodeId + ".vx";
	loadXML(url, addFavoriteNodeCallback);
}

var addFavoriteNodeCallback = function() {
	if (req.readyState == 4) {
		if (req.status == 200) {
			objFav = getObj("nodFav");
			objFav.innerHTML = '<img src="/img/pico_ok.gif" align="absmiddle" />&nbsp;本讨论板已加入收藏！';
		} else {
			objFav = getObj("nodFav");
			objFav.innerHTML = '<img src="/img/pico_error.gif" align="absmiddle" />&nbsp;oops';
		}
	}
}

var removeFavoriteNode = function(nodeId) {
	objFav = getObj("nodFav");
	objFav.innerHTML = '<span class="tip_i"><img src="/img/loading.gif" align="absmiddle" />&nbsp;正在发送请求 ...</span>';
	url = "/fav/remove/" + nodeId + ".vx";
	loadXML(url, removeFavoriteNodeCallback);
}

var removeFavoriteNodeCallback = function() {
	if (req.readyState == 4) {
		if (req.status == 200) {
			objFav = getObj("nodFav");
			objFav.innerHTML = '<img src="/img/pico_ok.gif" align="absmiddle" />&nbsp;本讨论板已从收藏中移出！';
		} else {
			objFav = getObj("nodFav");
			objFav.innerHTML = '<img src="/img/pico_error.gif" align="absmiddle" />&nbsp;oops';
		}
	}
}

var addFavoriteChannel = function(channelId) {
	objFav = getObj("chlFav");
	objFav.innerHTML = '<span class="tip_i"><img src="/img/loading.gif" align="absmiddle" />&nbsp;正在发送请求 ...</span>';
	url = "/fav/channel/add/" + channelId + ".vx";
	loadXML(url, addFavoriteChannelCallback);
}

var addFavoriteChannelCallback = function() {
	if (req.readyState == 4) {
		if (req.status == 200) {
			objFav = getObj("chlFav");
			objFav.innerHTML = '<img src="/img/pico_ok.gif" align="absmiddle" />&nbsp;本频道已加入收藏！';
		} else {
			objFav = getObj("chlFav");
			objFav.innerHTML = '<img src="/img/pico_error.gif" align="absmiddle" />&nbsp;oops';
		}
	}
}

var removeFavoriteChannel = function(favId) {
	objFav = getObj("chlFav");
	objFav.innerHTML = '<span class="tip_i"><img src="/img/loading.gif" align="absmiddle" />&nbsp;正在发送请求 ...</span>';
	url = "/fav/remove/" + favId + ".vx";
	loadXML(url, removeFavoriteChannelCallback);
}

var removeFavoriteChannelCallback = function() {
	if (req.readyState == 4) {
		if (req.status == 200) {
			objFav = getObj("chlFav");
			objFav.innerHTML = '<img src="/img/pico_ok.gif" align="absmiddle" />&nbsp;本频道已从收藏中移出！';
		} else {
			objFav = getObj("chlFav");
			objFav.innerHTML = '<img src="/img/pico_error.gif" align="absmiddle" />&nbsp;oops';
		}
	}
}

function sendMessage(userId) {
	newWin = window.open("/message/compose/" + userId + ".vx", "winMessage", "width=400,height=300");
	newWin.moveTo(100,100);
	newWin.focus();
}

function openMessage() {
	newWin = window.open("/message/home.vx", "winMessage", "width=400,height=300");
	newWin.moveTo(100,100);
	newWin.focus();
}


function openTopWealth() {
	newWin = window.open("/top/wealth.html", "winTopWealth", "width=502,height=400");
	newWin.moveTo(100,100);
	newWin.focus();
}

var closeLogin = function() {
	var oLogin = getObj("login");
	oLogin.style.display = "none";
}

var swLogin = function() {
	var oLogin = getObj("login");
	if (oLogin.style.display == "block") {
		oLogin.style.display = "none";
	} else {
		oLogin.style.display = "block";
		var oLoginBox = getObj("boxLogin");
		oLoginBox.focus();
	}
	return false;
}

var focusGo = function() {
	var oGo = getObj("boxGo");
	if (oGo) {
		oGo.focus();
	}
}

var brightBox = function(o) {
	if (o) {
		if (document.all) {
			o.style.backgroundColor = "#FFF";
			o.style.borderColor = "#0C0";
		}
	}
}

var dimBox = function(o) {
	if (o) {
		if (document.all) {
			o.style.backgroundColor = "#F7F7F7";
			o.style.borderColor = "#CCC";
		}
	}
}

var V2EXShell = function() {
	var oGo = getObj("boxGo");
	if (oGo) {
		_c = oGo.value;
		if (_c == "") {
			return false;
		} else {
			if (_c == "/") {
				location.href = "/";
				return false;
			}
			if (_c == "profile" || _c == "settings" || _c == "c") {
				location.href = "/user/modify.vx";
				return false;
			}
			if (_c == "login" || _c == "l") {
				return swLogin();
			}
			if (_c == "logout" || _c == "exit") {
				location.href = "/exit";
				return false;
			}
			if (_c == "inbox") {
				oGo.value = "";
				openMessage();
				return false;
			}
			if (_c == "close") {
				window.close();
				return false;
			}
			if (_c == "online" || _c == "o") {
				location.href = "/online/view.vx";
				return false;
			}
			if (_c == "me" || _c == "i") {
				location.href = "/me";
				return false;
			}
			if (_c == "?" || _c == "help") {
				location.href = "/topic/view/3038.html";
				return false;
			}
		}
	}
}

sfHover = function() {
	var sfEls = document.getElementById("nav_menu").getElementsByTagName("LI");
	for (var i=0; i<sfEls.length; i++) {
		sfEls[i].onmouseover=function() {
			this.className+=" sfhover";
		}
		sfEls[i].onmouseout=function() {
			this.className=this.className.replace(new RegExp(" sfhover\\b"), "");
		}
	}
}

if (window.attachEvent) window.attachEvent("onload", sfHover);
