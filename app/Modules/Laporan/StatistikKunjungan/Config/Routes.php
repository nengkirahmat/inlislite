<?php if (!isset($routes)) {
    $routes = \Config\Services::routes(true);
}

$routes->group('report', ['namespace' => 'StatistikKunjungan\Controllers'], function ($subroutes) {
    $subroutes->add('statistik-kunjungan', 'StatistikKunjungan::index');
    $subroutes->add('statistik-kunjungan/index', 'StatistikKunjungan::index');
});
