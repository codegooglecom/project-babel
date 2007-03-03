#!/usr/bin/php
<?php
$today = time();
shell_exec('/usr/bin/mysqldump -uplanescape -pAgonyIsMyName planescape>/bak/planescape.' . $today . '.sql');
shell_exec('cd /bak/ && tar czf planescape.' . $today . '.tgz planescape.' . $today . '.sql');
shell_exec('rm /bak/v2ex.latest.tgz');
shell_exec('ln -s /bak/planescape.' . $today . '.tgz /bak/v2ex.latest.tgz');
shell_exec('/usr/bin/mutt -a /bak/planescape.' . $today . '.tgz -s "V2EX DB/BAK: ' . date('Y-n-j', $today) . '" v2ex.livid@gmail.com</dev/null');
shell_exec('/usr/bin/mutt -a /bak/planescape.' . $today . '.tgz -s "V2EX DB/BAK: ' . date('Y-n-j', $today) . '" lividecay@gmail.com</dev/null');
?>
