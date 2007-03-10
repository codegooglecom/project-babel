/* Project Babel
 *
 * Author: Livid Torvalds
 * File: /htdocs/js/babel.js
 * Usage: Client side functions for Project Babel
 * Format: 1 tab ident(4 spaces), LF, UTF-8, no-BOM
 *
 * Subversion Keywords:
 *
 * $Id: babel.js 40 2006-10-30 10:12:28Z livid $
 * $LastChangedDate: 2006-10-30 18:12:28 +0800 (Mon, 30 Oct 2006) $
 * $LastChangedRevision: 40 $
 * $LastChangedBy: livid $
 */

sections = new Array("2", "3", "4", "71", "220");

var noop = function() {
	return 1;
}

var switchHomeTab = function(tab, res, name) {
	if (tab == "latest") {
		_latest = getObj("home_tab_latest");
		_latest.className = "current";
		for (i in sections) {
			_section = getObj("home_tab_section_" + sections[i]);
			if (_section) {
				_section.className = "normal";
			}
		}
		switchHomeTabContentLoading();
		getHomeTabLatest();
	} else {
		if (tab == "section") {
			_latest = getObj("home_tab_latest");
			_latest.className = "normal";
			if (parseInt(res) > 0) {
				_tab = "home_tab_section_" + res;
				_section = getObj(_tab);
				_section.className = "current";
				for (i in sections) {
					if (parseInt(sections[i]) != parseInt(res)) {
						_tab = "home_tab_section_" + sections[i];
						_section = getObj(_tab);
						if (_section) {
							_section.className = "normal";
						}
					}
				}
				switchHomeTabContentLoading();
				getHomeTabSection(res, name);
			} else {
			}
		}
	}
}

var switchHomeTabContentLoading = function() {
	_t = getObj("home_tab_top");
	_t.style.padding = "5px";
	_t.innerHTML = '<span class="tip_i"><img src="/img/loading.gif" align="absmiddle" /> 正在读取二级导航 ...</span>';
	_c = getObj("home_tab_content");
	_c.style.padding = "5px";
	_c.innerHTML = '<span class="tip_i"><img src="/img/loading.gif" align="absmiddle" /> 正在读取最新主题 ...</span>';
}

var getHomeTabLatest = function() {
	url = "/json/home/tab/latest";
	loadXML(url, cbGetHomeTabLatest);
}

var cbGetHomeTabLatest = function() {
	if (req.readyState == 4) {
		if (req.status == 200) {
			data = eval('(' + req.responseText + ')');
			_o = '<span class="tip_i">';
			_t = getObj("home_tab_top");
			_t.style.padding = "5px";
			i = 0;
			for (var id in data.boards) {
				i++;
				_o = _o + '<a href="/go/' + data.boards[i].nod_name + '" style="color: ' + data.boards[i].color + ';" class="var">' + data.boards[id].nod_title_plain + '</a><small> ' + data.boards[id].nod_topics + '</small>';
				_o = _o + '<small>&nbsp;..&nbsp;</small>';
			}
			_o = _o + '<a href="/feed/v2ex.rss" class="var"><img src="/img/icons/silk/feed.png" align="absmiddle" border="0" alt="RSS 2.0" /></a>';
			_o = _o + '</div>';
			_t.innerHTML = _o;
			_o = "";
			for (var id in data.topics) {
				_o = _o + '<div style="padding: 5px;"><img src="' + data.topics[id].usr_portrait_img + '" align="absmiddle" alt="' + data.topics[id].usr_nick_plain + '" class="portrait" /> <a href="/u/' + data.topics[id].usr_nick_plain + '" style="color: ' + data.topics[id].color + ';" class="var">' + data.topics[id].usr_nick_plain + '</a> <span class="tip_i">...</span> <a href="/go/' + data.topics[id].nod_name + '">' + data.topics[id].nod_title_plain + '</a> <span class="tip_i">... [ <a href="/topic/view/' + data.topics[id].tpc_id + '.html" style="color: ' + data.topics[id].color + ';" class="var">' + data.topics[id].tpc_title_plain + '</a> ] ... ' + data.topics[id].tpc_posts + ' 篇回复，' + data.topics[id].tpc_lasttouched_relative + '</span> </div>';
			}
			_c = getObj("home_tab_content");
			_c.innerHTML = _o;
		} else {
			_o = '<span class="tip_i">数据读取失败 ...</span>';
			_c = getObj("home_tab_content");
			_c.innerHTML = _o;
		}
	}
}

var getHomeTabSection = function(section_id, section_name) {
	url = "/json/home/tab/section/" + section_id;
	loadXML(url, cbGetHomeTabSection);
}

var cbGetHomeTabSection = function() {
	if (req.readyState == 4) {
		if (req.status == 200) {
			data = eval('(' + req.responseText + ')');
			_o = '<span class="tip_i">';
			_t = getObj("home_tab_top");
			_t.style.padding = "5px";
			i = 0;
			for (var id in data.boards) {
				i++;
				_o = _o + '<a href="/go/' + data.boards[i].nod_name + '" style="color: ' + data.boards[i].color + ';" class="var">' + data.boards[id].nod_title_plain + '</a><small> ' + data.boards[id].nod_topics + '</small>';
				_o = _o + '<small>&nbsp;..&nbsp;</small>';
			}
			_o = _o + '<a href="/go/' + data.node.nod_name + '" class="t var"><small>more</small></a><small> .. </small>';
			_o = _o + '<a href="/feed/board/' + data.node.nod_name + '.rss" class="var"><img src="/img/icons/silk/feed.png" align="absmiddle" border="0" alt="RSS 2.0" /></a>';
			_o = _o + '</div>';
			_t.innerHTML = _o;
			_o = "";
			for (var id in data.topics) {
				_o = _o + '<div style="padding: 5px;"><img src="' + data.topics[id].usr_portrait_img + '" align="absmiddle" alt="' + data.topics[id].usr_nick_plain + '" class="portrait" /> <a href="/u/' + data.topics[id].usr_nick_plain + '" style="color: ' + data.topics[id].color + ';" class="var">' + data.topics[id].usr_nick_plain + '</a> <span class="tip_i">...</span> <a href="/go/' + data.topics[id].nod_name + '">' + data.topics[id].nod_title_plain + '</a> <span class="tip_i">... [ <a href="/topic/view/' + data.topics[id].tpc_id + '.html" style="color: ' + data.topics[id].color + ';" class="var">' + data.topics[id].tpc_title_plain + '</a> ] ... ' + data.topics[id].tpc_posts + ' 篇回复，' + data.topics[id].tpc_lasttouched_relative + '</span> </div>';
			}
			_c = getObj("home_tab_content");
			_c.innerHTML = _o;
		} else {
			_o = '<span class="tip_i">数据读取失败 ...</span>';
			_c = getObj("home_tab_content");
			_c.innerHTML = _o;
		}
	}
}

var initHomeTabs = function(command) {
	switchHomeTabContentLoading();
	if (command == "latest") {
		switchHomeTab("latest", "", "");
	} else {
		var _section = command.split(":");
		switchHomeTab("section", _section[1], _section[2]);
	}
}
