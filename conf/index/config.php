<?php
use think\Request;

$request = Request::instance();
$base    = $request->root();
$root    = strpos($base, '.') ? ltrim(dirname($base), DS) : $base;
if ('' != $root) {
    $root = '/' . ltrim($root, '/');
}
$templatefile = 'yadotemp/'.config('__TEMPLATE__').'/';
$staticStatic = $root . '/' . $templatefile . 'static';
return [
    'view_replace_str'       => [
        '__TEMPLATE_FILE__' => './' . $templatefile,
        '__STATIC__' => $staticStatic,
        '__CSS__'    => $staticStatic . '/css',
        '__JS__'     => $staticStatic . '/js',
        '__IMG__'    => $staticStatic . '/img',
    ],
];
