<?php

# phpmig.php

use Phpmig\Adapter;
use Pimple\Container;

$container = new Container();

$container['db'] = function () {
    $dbh = new PDO('mysql:dbname=app_db;host=application-mysql;port=3306', 'admin', '111111');
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
};

$container['phpmig.adapter'] = function ($c) {
    return new Adapter\PDO\Sql($c['db'], 'migrations');
};

$container['phpmig.migrations_path'] = __DIR__ . DIRECTORY_SEPARATOR . 'migrations';

return $container;
