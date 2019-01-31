<?php

use Core\Container;

require __DIR__ . '/vendor/autoload.php';

// 应用初始化
$app_path = __DIR__ . '/app/';

Container::get('app')->path($app_path)->run();
