<?php

/**
 * Created by PhpStorm.
 * User: Hamlet
 * Date: 27.04.2017
 * Time: 11:56
 */
class Model
{
    protected $db;
    protected $settings;

    public function __construct()
    {
        $this->settings = include_once APP . '/components/DBConfig.php';
        $this->db = new SafeMySQL($this->settings);
    }

}