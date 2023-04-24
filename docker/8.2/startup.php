#!/usr/bin/php
<?php

// 定时任务
$cron_file   = '/var/spool/cron/crontabs/www-data';
$cron_shells = [
    "chown -R root:crontab {$cron_file}",
    "chmod 600 {$cron_file}"
];
if (file_exists($cron_file)) {
    echo '配置定时任务' . PHP_EOL;
    foreach ($cron_shells as $shell) {
        echo $shell . PHP_EOL;
        exec($shell);
    }
}

$xdebug  = $_ENV['XDEBUG_MODE'];
$version = $_ENV['PHP_VERSION'];
if ($xdebug !== 'off') {
    echo 'set clear_env to no', "\n";
    $target  = "/etc/php/{$version}/fpm/pool.d/www.conf";
    $content = file_get_contents($target);
    $after   = str_replace(';clear_env = no', 'clear_env = no', $content);
    file_put_contents($target, $after);
}

$file = $_ENV['TEMPLATE_FILE'] ?? false;
if (empty($file)) {
    echo "无Nginx模板文件\n";
    return;
}
if (file_exists("/etc/nginx/conf.d/{$file}.conf")) {
    echo "已经完成初始化\n";
    return;
}

$https  = $_ENV['HTTPS'] ?? '0';
$target = "/data/www/{$file}.conf";
if (!empty((int)($https))) {
    $target = "/data/www/{$file}.https.conf";
}

if (!file_exists($target)) {
    echo "没有配置文件:{$target}\n";
    return;
}
exec("envsubst < ${target} > /etc/nginx/conf.d/${file}.conf");

echo "{$target}生成配置文件完成\n";
