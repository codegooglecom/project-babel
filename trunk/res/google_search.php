<?php
if (isset($keyword)) {
	$kw = make_single_return($keyword);
} else {
	$kw = '';
}
?>
<!-- SiteSearch Google --><form method="get" action="http://www.google.com/custom" target="google_window"><a href="http://www.google.com/" class="var"><img src="/img/favicons/google/google.png" border="0" alt="Google" align="absmiddle"></img></a> <input type="hidden" name="domains" value="v2ex.com;livid.cn"></input><?php
echo ('<input type="text" id="txt_google_search" class="google_search" name="q" size="17" maxlength="255" value=""></input><br />');
?><label><input type="radio" name="sitesearch" value=""></input>Web&nbsp;&nbsp;</label><label><input type="radio" name="sitesearch" value="v2ex.com" checked="checked"></input>V2EX&nbsp;&nbsp;</label><br /><input type="image" name="sa" src="/img/graphite/go.gif" align="absmiddle"></input><input type="hidden" name="client" value="pub-9823529788289591"></input><input type="hidden" name="forid" value="1"></input><input type="hidden" name="ie" value="UTF-8"></input><input type="hidden" name="oe" value="UTF-8"></input><input type="hidden" name="flav" value="0000"></input><input type="hidden" name="sig" value="sPOKErd-NdJXfEEx"></input><input type="hidden" name="cof" value="GALT:#008000;GL:1;DIV:#FFFFFF;VLC:663399;AH:center;BGC:FFFFFF;LBGC:FFFFFF;ALC:0000FF;LC:0000FF;T:000000;GFNT:0000FF;GIMP:0000FF;LH:50;LW:167;L:http://www.v2ex.com/img/v2ex_site_search.png;S:http://www.v2ex.com;FORID:1;"></input><input type="hidden" name="hl" value="zh-CN"></input></form><!-- SiteSearch Google -->