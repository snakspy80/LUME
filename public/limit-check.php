<?php
header('Content-Type: text/plain');
echo 'sapi=' . php_sapi_name() . PHP_EOL;
echo 'post_max_size=' . ini_get('post_max_size') . PHP_EOL;
echo 'upload_max_filesize=' . ini_get('upload_max_filesize') . PHP_EOL;
echo 'user_ini.filename=' . ini_get('user_ini.filename') . PHP_EOL;
