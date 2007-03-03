#!/usr/bin/php
<?php
$mails = array('lividecay@gmail.com', 'v2ex.livid@gmail.com');
$today = time();
shell_exec('cd /www/babel/htdocs/img/ && tar czvf /bak/babel.img.' . $today . '.tgz p');
foreach ($mails as $m) {
	shell_exec('/usr/bin/mutt -a /bak/babel.img.' . $today . '.tgz -s "BABEL IMG/BAK: ' . date('Y-n-j', $today) . '" ' . $m . '</dev/null');
}
?>