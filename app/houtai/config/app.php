<?php
/**
 * Created by PhpStorm.
 * User: lenvji
 * Date: 2018/10/6
 * Time: 20:36
 */
return [
    // 默认跳转页面对应的模板文件
    'dispatch_success_tmpl'  => APP_PATH . 'houtai/tpl/dispatch_jump.tpl',
    'dispatch_error_tmpl'    => APP_PATH . 'houtai/tpl/dispatch_jump.tpl',

    // 异常页面的模板文件
    'exception_tmpl'         => Env::get('think_path') . 'tpl/think_exception.tpl',

    // 错误显示信息,非调试模式有效
    'error_message'          => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg'         => false,
    // 异常处理handle类 留空使用 \think\exception\Handle
    'exception_handle'       => '',
];