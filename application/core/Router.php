<?php

class Router{
    private $routes;
    private $routed = false;
    private $instance;
    
    public function __construct() 
    {
        $routersPath = APP.'/components/Routes.php';
        $this->routes = include $routersPath;
    }
    
    private function getURI()
    {
        if(!empty($_SERVER['REQUEST_URI']))
        {
            return trim($_SERVER['REQUEST_URI'], '/');
        }
    }

    public static function to404()
    {
        header('HTTP/1.1 404 Not Found'); //This may be put inside err.php instead
        $_GET['e'] = 404; //Set the variable for the error code (you cannot have a query string in an include directive)
        include APP . '/views/404.php';
        exit; //Do not do any more work in this script.
    }

    public static function toHomePage()
    {
       header('Location: /');
    }

    public function run() 
    {
         // Get query string
        $uri = $this->getUri();


        if($uri == '')
        {
            $controllerObject = new IndexController('index');
            $controllerObject->actionIndex();
        }
        else
        {
            //Check for such request in routes.php
            foreach ($this->routes as $uriPattern => $path){
                if(preg_match("~$uriPattern~", $uri)){
                    $internalRoute = preg_replace("~$uriPattern~", $path, $uri);
                    // Determinate which controller
                    // AND action will process the request
                    $segments = explode('/', $internalRoute);

                    $this->instance = array_shift($segments);
                    $controllerName = ucfirst($this->instance).'Controller';
                    $actionName = 'action'.ucfirst(array_shift($segments));
                    $params = $segments;

                    $controllerFile = APP.'/controllers/'.$controllerName.'.php';


                    if(file_exists($controllerFile)){
                        include_once($controllerFile);

                        $controllerObject = new $controllerName($this->instance);

                        $return = call_user_func_array(array($controllerObject, $actionName), $params);

                        if($return !== false)
                        {
                            $this->routed = true;
                            break;
                        }
                    }
                }
            }
            if($this->getUri() !== '' && !$this->routed)
            {
                self::to404();
            }
        }
   }
}