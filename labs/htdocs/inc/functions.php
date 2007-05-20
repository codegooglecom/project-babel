<?php

function _v_hr() { echo('<hr size="1" color="#DDD" style="color: #DDD; background-color: #DDD; height: 1px; border: 0;" />'); }

function _v_btn_l($label, $link) {
	echo('<div class="btn_o" align="left"><div class="btn_i" align="center"><a href="' . $link . '">' . $label . '</a></div></div>');
}

function _v_btn_f($label, $form) {
	$form_md5 = md5($form);
	$container = 'btn_' . strval(rand(1111, 9999));
	echo('<script type="text/javascript">');
	echo('var f_' . $form_md5 . "_do = function() {\n");
	echo('var c = getObj("' . $container . '"); c.innerHTML = "<img src=/img/loading.gif align=absmiddle /> <span class=tip_i>正在发送请求</span>";');
	echo('var o = getObj("' . $form . '"); return o.submit();');
	echo('}');
	echo('</script>');
	echo('<div id="' . $container . '"><div class="btn_o" align="left" onclick="f_' . $form_md5 . '_do();"><div class="btn_i" align="center"><a href="#;" onclick="f_' . $form_md5 . '_do();" onmousedown="f_' . $form_md5 . '_do();">' . $label . '</a></div></div></div>');
}

function _v_ico_silk($icon, $align = 'absmiddle') {
	echo('<img src="' . CDN_UI . 'icons/silk/' . $icon . '.png" align="' . $align . '" />');
}

function _v_nav($page = 'index') {
	if ($page == 'index') {
		echo('Overview');
	} else {
		echo('<a href="/">Overview</a>');
	}
	echo(' | ');
	if ($page == 'downloads') {
		echo('Downloads');
	} else {
		echo('<a href="/downloads.php">Downloads</a>');
	}
	echo(' | ');
	if ($page == 'installation') {
		echo('Installation');
	} else {
		echo('<a href="/installation.php">Installation</a>');
	}
	echo(' | ');
	if ($page == 'support') {
		echo('Support');
	} else {
		echo('<a href="/support.php">Support</a>');
	}
	echo(' | ');
	if ($page == 'faq') {
		echo('FAQ');
	} else {
		echo('<a href="/faq.php">FAQ</a>');
	}
	echo(' | ');
	if ($page == 'team') {
		echo('Team');
	} else {
		echo('<a href="/team.php">Team</a>');
	}
	echo(' | ');
	if ($page == 'contribute') {
		echo('Contribute');
	} else {
		echo('<a href="/contribute.php">Contribute</a>');
	}
}
?>
