<?php
	class Bootstrap extends Zend_Application_Bootstrap_Bootstrap{

		public function run(){
			
			// Cela permet d'avoir la configuration disponible de partout dans notre application
	        Zend_Registry::set('config', new Zend_Config($this->getOptions()));	        		
			$config = new Zend_Config($this->getOptions());
			$connect = mysql_connect($config->resources->db->params->host,$config->resources->db->params->username,$config->resources->db->params->password) or die("erreur de connexion au serveur $host");
			mysql_select_db($config->resources->db->params->dbname) or die("erreur de connexion a la base de donnees");
	        
	        parent::run();
	        
	    
		
		}
	    
	
	    protected function _initAutoload(){
	    	
			// On enregistre les modules (les parties de notre application), souvenez-vous : Backend et Frontend
	        $loader = new Zend_Application_Module_Autoloader(array(
	            'namespace' => '',
	            'basePath'  => APPLICATION_PATH));
	
	        return $loader;
	    }
	
	    protected function _initSession(){
			// On initialise la session
			Zend_Session::start();
	        $session = new Zend_Session_Namespace('PMQVision', true);
			return $session;
	    }

	
	    protected function _initView(){
	    	
			// Initialisation de la vue et des helpers de vue
	        $view = new Zend_View();
	        $view->doctype('XHTML1_STRICT');
	        $view->headTitle('PMQVision Version 6');
	        $view->headMeta()->appendHttpEquiv('Content-Type', 'text/html; charset=utf-8');
	        // On ajoute le dossier des helpers
	        $view->addHelperPath(APPLICATION_PATH . '/views/helpers');
	        // On charge l'helper qui va se charger de la vue
	        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
	        $viewRenderer->setView($view);
	
	        return $view;
	    }
	    
	    /**
	    * Initialize data bases
	    * 
	    * @return Zend_Db::factory
	    */
	    protected function _initDb(){
	    	
			//on charge notre fichier de configuration
	    	$config = new Zend_Config($this->getOptions());
	        //On essaye de faire une connection a la base de donnee.
	    	try{  
	            $db = Zend_Db::factory($config->resources->db);
	            //PROFILER
	            $profiler = new Zend_Db_Profiler_Firebug('profiler');
	            $profiler->setEnabled(true);
	            $db->setProfiler($profiler);
	            //
	            //on precise l'adapatateur au cas o� il y a plusieurs bases de donnees et donc plusieurs adaptateurs
	            Zend_Db_Table::setDefaultAdapter($db);
	            //on test si la connection se fait
	            $db->getConnection();
	        }catch ( Exception $e ) {
	            exit( $e -> getMessage() );  
	        }
	        // on stock notre dbAdapter dans le registre      
	        Zend_Registry::set( 'db', $db );
			$db->query('SET NAMES UTF8');
			
	        return $db;
	    }
	    
		/**
		 * @return Zend_Navigation
		 */
		protected function _initNavigation(){
			
			$view = $this->bootstrap('layout')->getResource('layout')->getView();
			$config = new Zend_Config_Xml(APPLICATION_PATH . '/configs/menu.xml', 'nav');
			//$view->navigation(new Zend_Navigation($config));
			$nav = new Zend_Navigation($config);
			if( isset( $_SERVER['HTTP_REFERER'] ) ){
				$nav->addPage(array(
					'label'		=>	'<<',
					'uri'		=>	$_SERVER['HTTP_REFERER'],
					'title'		=>	'Revenir à la page précédente'
				));
			}
			$view->navigation($nav);
			
		}
		
		/**
		 * 
		 * @return unknown_type
		 */
		/*protected function _initPaginator(){
			
			Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
			
		}*/
	
	}