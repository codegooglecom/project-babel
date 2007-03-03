var z2DeleteProject = function(project_id, area) {
	if (confirm("此操作不可恢复，你确认要删除此项目？")) {
		url = "/api/zen2/delete/project/" + parseInt(project_id) + "/from/" + area;
		$("div#zen2_projects_" + area).load(url);
	} else {
		return false;
	}
}

var z2DoneProject = function(project_id, seed_r) {
	url = "/api/zen2/done/project/" + parseInt(project_id) + "." + seed_r;
	$("div#zen2_projects_active").load(url);
	url = "/api/zen2/load/projects/done";
	$("div#zen2_projects_done").load(url);
}

var z2UndoneProject = function(project_id, seed_r) {
	url = "/api/zen2/undone/project/" + parseInt(project_id) + "." + seed_r;
	$("div#zen2_projects_done").load(url);
	url = "/api/zen2/load/projects/active";
	$("div#zen2_projects_active").load(url);
}

var z2LoadProjectsActive = function(user_id) {
	url = "/api/zen2/load/projects/active/" + parseInt(user_id);
	$("div#zen2_projects_active").load(url);
}

var z2LoadProjectsDone = function(user_id) {
	url = "/api/zen2/load/projects/done/" + parseInt(user_id);
	$("div#zen2_projects_done").load(url);
}

var z2SwitchProjectTaskToolbar = function(project_id) {
	var tb = getObj("project_task_toolbar");
	tb.style.backgroundColor = "#F9F9F9";
	tb.innerHTML = '<form style="margin: 0px; padding: 0px; display: inline;"><input type="text" maxlength="60" name="task" id="task_new" class="sll" /> <input type="submit" value="添加" class="zen2_btn" /> <input type="button" value="取消" class="zen2_btn" onclick="z2RevertProjectTaskToolbar(' + project_id + ')" /></form>';
	var tn = getObj("task_new");
	tn.focus();
}

var z2RevertProjectTaskToolbar = function(project_id) {
	var tb = getObj("project_task_toolbar");
	tb.style.backgroundColor = "#FFF";
	tb.innerHTML = ' &nbsp; <img src="/img/icons/silk/add.png" align="absmiddle" />&nbsp; <a href="#;" class="t" onclick="z2SwitchProjectTaskToolbar(' + project_id + ');">添加新任务</a>';
}
