<?php


class Controller
{
    protected $model;
    protected $view;

    function __construct($instance)
    {
        $this->view = new View($instance);
        $modelName = ucfirst($instance);
        $this->model = new $modelName;
    }
}