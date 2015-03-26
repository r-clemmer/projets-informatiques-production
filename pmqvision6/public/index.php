<?php
/*

	define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
	
	define('LIBRARY_PATH', realpath(dirname(__FILE__) . '/../library'));	
	
	define('ZEND_PATH', realpath(dirname(__FILE__) . '/../library/Zend'));
	
	define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));
	
	// On modifie l'include path de PHP
	set_include_path(
		implode(PATH_SEPARATOR,
			array(
				//realpath(ZEND_PATH),
				realpath(LIBRARY_PATH),
		   		get_include_path()
		   	)
		)
	);
	
	//Autoload
	require_once 'Zend/Application.php';
	require_once 'Zend/Loader/Autoloader.php';
	$autoloader = Zend_Loader_Autoloader::getInstance();
	$autoloader->setFallbackAutoloader(true);

    // On lance la session
	//Zend_Session::start();
	
	// On créé l'application, on lance le bootstrap et on lance l'application !
	$application = new Zend_Application(
	    APPLICATION_ENV,
	    APPLICATION_PATH . '/configs/application.ini'
	);
	
	$application->bootstrap()->run();
	*/
	
	define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
	define('LIBRARY_PATH', realpath(dirname(__FILE__) . '/../library'));
	define('ZEND_PATH', realpath(dirname(__FILE__) . '/../library'));
	define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));
	// On modifie l'include path de PHP
	set_include_path(
		implode(PATH_SEPARATOR,
			array(
				realpath(ZEND_PATH),
		   		get_include_path()
		   	)
		)
	);

	//Autoload
	require_once 'Zend/Application.php';
	/*require_once 'Zend/Loader/Autoloader.php';
	$autoloader = Zend_Loader_Autoloader::getInstance();
	$autoloader->setFallbackAutoloader(true);*/
    // On lance la session
	//Zend_Session::start();
	// On crÃ©Ã© l'application, on lance le bootstrap et on lance l'application !
	$application = new Zend_Application(
	    APPLICATION_ENV,
	    APPLICATION_PATH . '/configs/application.ini'
	);

	$application->bootstrap()->run();
	
			