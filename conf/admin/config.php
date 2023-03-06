<?php
use think\Request;

$request = Request::instance();
$base    = $request->root();
$root    = strpos($base, '.') ? ltrim(dirname($base), DS) : $base;
if ('' != $root) {
    $root = '/' . ltrim($root, '/');
}

return [
    'view_replace_str'       => [
        '__ROOT__'   => $root,
        '__STATIC__' => $root . '/adminstatic',
        '__CSS__'    => $root . '/adminstatic/css',
        '__JS__'     => $root . '/adminstatic/js',
        '__IMG__'    => $root . '/adminstatic/img',
        '__EDITOR__' => $root . '/editor/kindeditor',
    ],
    'template'               => [
        'layout_on' => true,
        'layout_name' => 'yado',
    ],
];