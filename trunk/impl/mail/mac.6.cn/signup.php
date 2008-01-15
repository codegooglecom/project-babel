<?php
$mail['subject'] = "{$this->User->usr_nick} 你好，欢迎来到 " . Vocabulary::site_name;
$mail['body'] = "{$this->User->usr_nick}，你好！\n\n" . Vocabulary::site_name . " 欢迎你的到来，这是一个关于 Mac 的分享和交流的地方，希望你喜欢！\n\nEnjoy!" . BABEL_AM_SIGNATURE;
?>