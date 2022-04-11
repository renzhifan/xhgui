<?php

use XHGui\Application;

require __DIR__ . '/../vendor/autoload.php';

$app = new Application();
$saver = $app->getSaver();
if(!isset($argv[1])){
    throw new RuntimeException('请输入文件路径');
}
$path = $argv[1];
$files = scandir($path);
foreach ($files as $file) {
    $file = $path . DIRECTORY_SEPARATOR . $file;
    $extension = pathinfo($file, PATHINFO_EXTENSION);
    if ($extension == 'xhprof') {

        $fp = fopen($file, 'r');
        if (!$fp) {
            throw new RuntimeException('Can\'t open ' . $file);
        }

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

    }
}
