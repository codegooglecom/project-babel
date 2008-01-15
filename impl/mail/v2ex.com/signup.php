<?php
$mail['subject'] = "{$this->User->usr_nick} 你好，欢迎来到 " . Vocabulary::site_name;
$mail['body'] = "{$this->User->usr_nick}，你好！\n\n" . Vocabulary::site_name . " 欢迎你的到来，你或许会对 " . Vocabulary::site_name . " 这个名字感到好奇吧？\n\n" . Vocabulary::site_name . " 是两个短句的缩写，way too extreme 和 way to explore，前者关于一种生活的态度，后者关于我们每天都会产生然后又失去的好奇心。So is V2EX，希望你喜欢。\n\n目前看来，V2EX 是一个普普通通不足为奇的社区（或者说论坛），不过，我们正在修建一个有着透明玻璃的怪物博物馆，不久的将来，每天都会有各种怪物可以玩，也是相当开心的事情吧。\n\nEnjoy!" . BABEL_AM_SIGNATURE;
?>