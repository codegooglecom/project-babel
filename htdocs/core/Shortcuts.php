<?php

function _v_m_s() { echo('<div id="main">'); }

function _v_b_l_s() { echo('<div class="blank" align="left">'); }

function _v_b_c_s() { echo('<div class="blank" align="center">'); }

function _v_d_e() { echo('</div>'); }

function _v_h1_i($text) { return '<h1 class="ititle">' . $text . '</h1>'; }

function _v_hr() { echo('<hr size="1" color="#EEE" style="color: #EEE; background-color: #EEE; height: 1px; border: 0;" />'); }

function _vo_hr() { return '<hr size="1" color="#EEE" style="color: #EEE; background-color: #EEE; height: 1px; border: 0;" />'; }

function _v_btn_l($label, $link) {
	echo('<div class="btn_o" align="left"><div class="btn_i" align="center"><a href="' . $link . '">' . $label . '</a></div></div>');
}

function _v_ico_map() {
	echo('<img src="' . CDN_UI . 'img/icons/silk/map.png" align="absmiddle" alt="You are here" class="map" />');
}

function _vo_ico_map() {
	return '<img src="' . CDN_UI . 'img/icons/silk/map.png" align="absmiddle" alt="You are here" class="map" />';
}

function _v_btn_f($label, $form) {
	$form_md5 = md5($form);
	$container = 'btn_' . strval(rand(1111, 9999));
	echo('<script type="text/javascript">');
	echo('var i = new Image(15,15); i.src="' . CDN_UI . 'img/loading.gif";');
	echo('var f_' . $form_md5 . "_do = function() {\n");
	echo('var c = getObj("' . $container . '"); c.innerHTML = "<img src=' . CDN_UI . 'img/loading.gif align=absmiddle /> <span class=tip_i>正在发送请求</span>";');
	echo('var o = getObj("' . $form . '"); return o.submit();');
	echo('}');
	echo('</script>');
	echo('<div id="' . $container . '"><div class="btn_o" align="left" onclick="f_' . $form_md5 . '_do();"><div class="btn_i" align="center"><a href="#;" onclick="f_' . $form_md5 . '_do();" onmousedown="f_' . $form_md5 . '_do();">' . $label . '</a></div></div></div>');
}

function _v_ico_silk($icon, $align = 'absmiddle') {
	echo('<img src="' . CDN_UI . 'img/icons/silk/' . $icon . '.png" align="' . $align . '" />');
}

function _vo_ico_silk($icon, $align = 'absmiddle') {
	return '<img src="' . CDN_UI . 'img/icons/silk/' . $icon . '.png" align="' . $align . '" border="0" />';
}

function _v_ing_style_personal() {
	echo('<style type="text/css">');
	echo('.entry_odd { padding: 10px 5px 10px 5px; }');
	echo("\n");
	echo('.entry_even { padding: 10px 5px 10px 5px; background-color: #F5F5F5; -webkit-border-radius: 7px; -moz-border-radius: 7px; }');
	echo('</style>');
}

function _v_ing_style_public() {
	echo('<style type="text/css">');
	echo('.entry_odd { padding: 5px 5px 5px 5px; }');
	echo("\n");
	echo('.entry_even { padding: 5px 5px 5px 5px; background-color: #F5F5F5; -webkit-border-radius: 7px; -moz-border-radius: 7px; }');
	echo('</style>');
}
?>
