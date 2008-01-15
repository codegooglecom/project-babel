<?php

function _v_m_s() { echo('<div id="main">'); }

function _v_b_l_s() { echo('<div class="blank" align="left">'); }

function _v_b_c_s() { echo('<div class="blank" align="center">'); }

function _v_d_e() { echo('</div>'); }

function _v_d_tr_s() {
	echo('<div style="float: right; padding: 3px 10px 3px 10px; font-size: 10px; background-color: #F0F0F0; -moz-border-radius: 5px; color: #999;">');
}

function _v_h1_i($text) { return '<h1 class="ititle">' . $text . '</h1>'; }

function _v_hr() { echo('<hr size="1" color="#EEE" style="color: #EEE; background-color: #EEE; height: 1px; border: 0;" />'); }

function _vo_hr() { return '<hr size="1" color="#EEE" style="color: #EEE; background-color: #EEE; height: 1px; border: 0;" />'; }

function _v_btn_l_1($label, $link) {
	echo('<div class="btn_o" align="left"><div class="btn_i" align="center"><a href="' . $link . '">' . $label . '</a></div></div>');
}

function _v_btn_l($label, $link) {
	echo('<div class="btn_2_o" align="left"><div class="btn_2_i" align="center"><a href="' . $link . '">' . $label . '</a></div></div>');
}

function _v_btn_f_1($label, $form) {
	$form_md5 = md5($form);
	$container = 'btn_' . strval(rand(1111, 9999));
	echo('<script type="text/javascript">');
	echo('var i = new Image(15,15); i.src="/img/loading.gif";');
	echo('var f_' . $form_md5 . "_do = function() {\n");
	echo('var c = getObj("' . $container . '"); c.innerHTML = "<img src=/img/loading.gif align=absmiddle /> <span class=tip_i>');
	switch (BABEL_LANG) {
		case 'zh_cn':
			echo('正在发送请求');
			break;
		default:
		case 'en_us':
			echo('Requesting');
			break;
		case 'de_de':
			echo('Anfragen');
			break;
	}
	echo('</span>";');
	echo('var o = getObj("' . $form . '"); return o.submit();');
	echo('}');
	echo('</script>');
	echo('<div id="' . $container . '"><div class="btn_o" align="left" onclick="f_' . $form_md5 . '_do();"><div class="btn_i" align="center"><a href="#;" onclick="f_' . $form_md5 . '_do();" onmousedown="f_' . $form_md5 . '_do();">' . $label . '</a></div></div></div>');
}

function _v_btn_f($label, $form) {
	$form_md5 = md5($form);
	$container = 'btn_' . strval(rand(1111, 9999));
	echo('<script type="text/javascript">');
	echo('var i = new Image(15,15); i.src="/img/loading.gif";');
	echo('var f_' . $form_md5 . "_do = function() {\n");
	echo('var c = getObj("' . $container . '"); c.innerHTML = "<img src=/img/loading.gif align=absmiddle /> <span class=tip_i>');
	switch (BABEL_LANG) {
		case 'zh_cn':
			echo('正在发送请求');
			break;
		default:
		case 'en_us':
			echo('Requesting');
			break;
		case 'de_de':
			echo('Anfragen');
			break;
	}
	echo('</span>";');
	echo('var o = getObj("' . $form . '"); return o.submit();');
	echo('}');
	echo('</script>');
	echo('<div id="' . $container . '" align="center" style="width: 120px;"><div class="btn_2_o" align="left" onclick="f_' . $form_md5 . '_do();"><div class="btn_2_i" align="center"><a href="#;" onclick="f_' . $form_md5 . '_do();" onmousedown="f_' . $form_md5 . '_do();">' . $label . '</a></div></div></div>');
}

function _v_ico_map() {
	switch (BABEL_DNS_DOMAIN) {
		case 'v2ex.com':
		default:
			echo('<img src="' . CDN_UI . 'img/icons/silk/map.png" align="absmiddle" alt="' . Vocabulary::SITE_NAME . '" class="map" />');
			break;
			
		case 'mac.6.cn':
			echo('<img src="/img/m6_icon.gif" align="absmiddle" alt="You are here" class="map" />');
			break;
	}
}

function _vo_ico_map() {
	switch (BABEL_DNS_DOMAIN) {
		case 'v2ex.com':
		default:
			return '<img src="' . CDN_UI . 'img/icons/silk/map.png" align="absmiddle" alt="' . Vocabulary::SITE_NAME . '" class="map" />';
			break;
		
		case 'mac.6.cn':
			return '<img src="/img/m6_icon.gif" align="absmiddle" alt="M6" class="map" />';
			break;
	}
}

function _v_ico_silk($icon, $align = 'absmiddle') {
	echo('<img src="' . CDN_UI . 'img/icons/silk/' . $icon . '.png" align="' . $align . '" border="0" />');
}

function _vo_ico_silk($icon, $align = 'absmiddle') {
	return '<img src="' . CDN_UI . 'img/icons/silk/' . $icon . '.png" align="' . $align . '" border="0" />';
}

function _v_ico_buuf($icon, $align = 'absmiddle') {
	echo('<img src="/img/icons/buuf/' . $icon . '.png" align="' . $align . '" border="0" />');
}

function _vo_ico_buuf($icon, $align = 'absmiddle') {
	return '<img src="/img/icons/buuf/' . $icon . '.png" align="' . $align . '" border="0" />';
}

function _v_ico_tango_16($icon, $align = 'absmiddle') {
	echo '<img src="' . CDN_UI . 'img/icons/tango/16x16/' . $icon . '.png" align="' . $align . '" border="0" />';
}

function _vo_ico_tango_16($icon, $align = 'absmiddle') {
	return '<img src="' . CDN_UI . 'img/icons/tango/16x16/' . $icon . '.png" align="' . $align . '" border="0" />';
}

function _v_ico_tango_22($icon, $align = 'absmiddle') {
	echo '<img src="' . CDN_UI . 'img/icons/tango/22x22/' . $icon . '.png" align="' . $align . '" border="0" />';
}

function _vo_ico_tango_22($icon, $align = 'absmiddle') {
	return '<img src="' . CDN_UI . 'img/icons/tango/22x22/' . $icon . '.png" align="' . $align . '" border="0" />';
}

function _v_ico_tango_32($icon, $align = 'absmiddle', $class = '') {
	echo '<img src="' . CDN_UI . 'img/icons/tango/32x32/' . $icon . '.png" align="' . $align . '" border="0" class="' . $class . '" />';
}

function _vo_ico_tango_32($icon, $align = 'absmiddle', $class = '') {
	return '<img src="' . CDN_UI . 'img/icons/tango/32x32/' . $icon . '.png" align="' . $align . '" border="0" class="' . $class . '" />';
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
