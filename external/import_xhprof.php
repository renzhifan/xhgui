<?php

use XHGui\Application;

require __DIR__ . '/../vendor/autoload.php';

$options = getopt('f:');

if (!isset($options['f'])) {
    throw new InvalidArgumentException('You should define a file to be loaded');
}
$file = $options['f'];

if (!is_readable($file)) {
    throw new InvalidArgumentException($file . ' isn\'t readable');
}

$fp = fopen($file, 'r');
if (!$fp) {
    throw new RuntimeException('Can\'t open ' . $file);
}

$app = new Application();
$saver = $app->getSaver();

while (!feof($fp)) {
    $line = fgets($fp);

    $time = time();
    $requestTs = array('sec' => $time, 'usec' => 0);
    $requestTsMicro = array('sec' => 0, 'usec' => 0);
    $xhprofData['meta'] = array(
        'url' => basename($file),
        'get' => $_GET,
        'env' => $_ENV,
        'SERVER' => $_SERVER,
        'simple_url' => "",
        'request_ts_micro' => $requestTsMicro,
        'request_ts' => $requestTs,
        'request_date' => date('Y-m-d', $time),
    );
    $xhprofData['profile'] = unserialize($line);

    if ($xhprofData) {
        try {
            $saver->save($xhprofData);
        } catch (Throwable $e) {
            error_log($e);
        }
    }
}
fclose($fp);
