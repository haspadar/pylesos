<?php
function loadAutoload(): string {
    $vendorPaths = [
        dirname(dirname(__FILE__)) . '/vendor',
        dirname(dirname(dirname(dirname(__FILE__))))
    ];
    foreach ($vendorPaths as $vendorPath) {
        if (file_exists($vendorPath . '/autoload.php')) {
            require_once $vendorPath . '/autoload.php';

            return $vendorPath;
        }
    }

    return '';
}