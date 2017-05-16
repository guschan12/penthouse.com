<?php

class View
{
    private $data = array();
    private $view;

    public function __construct($view)
    {
        $this->view = $view;
    }

    public function set($key,$value,$overwrite = false)
    {
        if(isset ($this->data[$key]) && !$overwrite)
        {
            $err = 'Unable to set var `' . $key . '`. Already set, and overwrite not allowed<br>';
            $err .= "Error initiated in " . System::caller(__CLASS__) . ", thrown";
            trigger_error($err, E_USER_NOTICE);
            return false;
        }
        $this->data[$key] = $value;
    }

    public function show()
    {
        $data = $this->data;
        $view = $this->view;
        include APP . "/template/index.php";
    }

    public function show_admin($tpl)
    {
        $data = $this->data;
        include APP . "/template/admin/index.php";
    }

    public static function showLogin()
    {
        include APP . '/template/admin/login.php';
    }

    public function adminLogin()
    {
        include APP . '/template/admin/login.php';
    }

}