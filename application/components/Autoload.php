<?php
function __autoload($className)
{
	$searchArray = array(
			'/models/',
			'/components/',
            '/controllers/',
			'/core/'
		);



	foreach($searchArray as $search)
	{		
		$search = APP . $search . $className . '.php';
		if(is_file($search))
		{
			include_once $search;
		}
	}
}