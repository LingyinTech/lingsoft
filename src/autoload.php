<?php
/**
 * Created by PhpStorm.
 * User: huanjin
 * Date: 2017/12/7
 * Time: 19:56
 */

spl_autoload_register(function ($class) {
    $prefix = 'lingyin\\profile\\';
    $base_dir = __DIR__ . '/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relativeClass = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});