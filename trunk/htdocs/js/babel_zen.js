/* Project Babel
*  Author: Livid Torvalds
*  File: /htdocs/js/babel_zen.js
*  Usage: Client side functions for Project Zen
*  Format: 1 tab ident(4 spaces), LF, UTF-8, no-BOM
*
*  Subversion Keywords:
*
*  $Id: babel_zen.js 3 2006-10-12 18:19:29Z livid $
*  $LastChangedDate: 2006-10-13 02:19:29 +0800 (Fri, 13 Oct 2006) $
*  $LastChangedRevision: 3 $
*  $LastChangedBy: livid $
*/

var ZENDoneTask = function (taskId) {
	location.href = '/change/zen/task/done/' + taskId + '.vx';
	return true;
}

var ZENSwitchProjectForm = function (projectId) {
	pf = getObj("pf_" + projectId);
	pf.innerHTML = '<form class="zen" action="/recv/zen/task/' + projectId + '.vx" method="post"><input type="text" class="sl" name="zta_title" maxlength="80" /> <input type="submit" class="zen_btn" value="添加" /> <input type="button" value="取消" onclick="ZENResetProjectForm(' + projectId + ');" class="zen_btn" /></form>';
}

var ZENResetProjectForm = function (projectId) {
	pf = getObj("pf_" + projectId);
	pf.innerHTML = '<img src="/img/plus_green.gif" align="absmiddle" alt="+" /> <a href="#;" onclick="ZENSwitchProjectForm(' + projectId + ');" class="t">添加新任务</a>';
}