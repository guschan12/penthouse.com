<?php

/**
 * Created by PhpStorm.
 * User: Hamlet
 * Date: 27.04.2017
 * Time: 11:17
 */
class IndexController extends Controller
{
    public function actionIndex()
    {
    	$userName = $this->model->getUserName();
    	$this->view->set('user_name',$userName);
        $this->view->show();
    }
}