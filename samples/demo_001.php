<?php
/**
 * Created by PhpStorm.
 * User: huanjin
 * Date: 2017/4/30
 * Time: 21:58
 */

require __DIR__ . '/../src/autoload.php';
function bar() {
    return 1;
}
function foo($x) {
    $sum = 0;
    for ($idx = 0; $idx < 2; $idx++) {
        $sum += bar();
    }
    return strlen("hello: {$x}");
}
$_GET['lingyin-profile'] = 1;
define('PHP_PROFILE_OUTPUT','./');
\lingyin\profile\Profile::start(['adapter'=>'Xhprof']);
foo(10);
\lingyin\profile\Profile::stop();