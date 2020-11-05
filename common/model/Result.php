<?php
/**
 * Created by PhpStorm.
 * User: lenvji
 * Date: 2018/9/26
 * Time: 9:29
 */

namespace common\model;


class Result
{
    const DATATYPE_STRING = 'string';
    const DATATYPE_INT = 'int';
    const DATATYPE_HTML = 'html';
    const DATATYPE_JSON = 'json';
    const DATATYPE_ARRAY = 'array';
    const DATATYPE_LIST = 'list';
    const DATATYPE_URL = 'url';
    const DATATYPE_OBJECT = 'object';

    public $msg;
    public $success;
    public $error;
    public $description;
    public $data;
    public $url;
    public $dataType;
    public $needLogin;
    public $needPermission;
    public $totalPages;
    public $page;
    public $limit;
    public $time;

    public function __construct()
    {
        $this->msg = '';
        $this->success = false;
        $this->error = 0;
        $this->data = '';
        $this->url = '';
        $this->description = '';
        $this->dataType = self::DATATYPE_STRING;
        $this->needLogin = false;
        $this->needPermission = false;

        $this->totalPages = 0;
        $this->page = 1;
        $this->limit = 10;

        $this->time = 0;
    }
}