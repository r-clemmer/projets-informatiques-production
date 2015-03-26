<?php
	class OutilsController extends Zend_Controller_Action {

		public function init(){
			$this->view->headLink()->appendStylesheet($this->_request->getBaseUrl().'/js/jqueryFileTree/jqueryFileTree.css');
			$this->view->headLink()->appendStylesheet($this->_request->getBaseUrl().'/js/jqueryFileTree/jquery.contextMenu/jquery.contextMenu.css');
			$this->view->headLink()->appendStylesheet($this->_request->getBaseUrl().'/css/tree.css');
			
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/jqueryFileTree/jquery.livequery.js');
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/jqueryFileTree/jqueryFileTree.js');
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/jqueryFileTree/jquery.contextMenu/jquery.contextMenu.js');
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/outils.js');
			
		}
		
		public function indexAction(){
			
			$this->view->title = 'Outils de mise en oeuvre';		
	    }
	    
	    public function uploadAction(){
	    	
		    $this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			
			$destinationDir = $_POST['dir_pathUp'];
			$sizeMax = $_POST['MAX_FILE_SIZE'];
			$destinationDir = substr($destinationDir,1,strlen($destinationDir));
			
			$chemin ='/var/www/public'.$destinationDir;
			if($chemin == "/var/www/public"){
				$chemin = '/var/www/public/documents/ressources/';
			}

			$name = $this->textFormat($_FILES['uploadedfile']['name']);

			if( !file_exists($chemin) ){
				mkdir($chemin, 0777);
			}


			if(move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $chemin.$name)){
			chmod ($chemin.$name, 0755);  
				if( isset( $_POST['redirect'] ) && $_POST['redirect'] != '' ){
					$this->_redirect($_POST['redirect']);
				}else{
					$this->_redirect('/outils/index/');
				}
			}
	    	
	    }
	    public function renamedirAction(){
	    	$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			
			$cheminDossier = $_POST['dir_pathRename'];

			$cheminDossier = substr($cheminDossier,0,strlen($cheminDossier)-1);

			$cheminDossier = substr($cheminDossier,0,strrpos($cheminDossier,'/'));
			
			$name = $this->textFormat($_POST['newName']);
		
			$NewNomFichier = $cheminDossier.'/'.$name;
			
			$oldFichier = $_POST['dir_pathRename'];

			rename($oldFichier, $NewNomFichier);
			$this->_redirect('/outils/index/');
			
	    }
		public function renamefileAction(){
	    	$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			
			$cheminDossier = $_POST['fileDirRename'];

			$cheminDossier = substr($cheminDossier,0,strrpos($cheminDossier,'/'));

			$name = $this->textFormat($_POST['newName']);
			
			$NewNomFichier = $cheminDossier.'/'.$name;
			
			$oldFichier = $_POST['fileDirRename'];
			$ext = substr($oldFichier,strrpos($oldFichier,'.'),strlen($oldFichier));
			
			$NewNomFichier = $NewNomFichier.$ext;
			
			rename($oldFichier, $NewNomFichier);
				
			$this->_redirect('/outils/index/');
			
	    }
	    public function deletedirAction(){
	    	$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			
			$cheminDossier = $_POST['dir_pathDelete'];
			
			$this->delete_directory($cheminDossier);
			$this->_redirect('/outils/index/');
			
	    }
	    
	    public function delete_directory($dirname) {
			if (is_dir($dirname)) $dir_handle = opendir($dirname);
	   		if (!$dir_handle) return false;
	    	while($file = readdir($dir_handle)) {
	       		if ($file != "." && $file != "..") {
	          		if (!is_dir($dirname."/".$file)) unlink($dirname."/".$file);
	          		else $this->delete_directory($dirname.'/'.$file);    
	       		}
	    	}
	    	closedir($dir_handle);
	    	rmdir($dirname);
		} 
		
		public function deletefileAction(){
	    	$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			
			$cheminDossier = $_POST['fileDelete'];
			
			if(unlink($cheminDossier)){
				$this->_redirect('/outils/index/');
			}
			
	    }
	    public function filetreeAction(){
	    	$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			
			$arrayFile= array();
			$tree = "";
			
	  		$_POST['dir'] = urldecode($_POST['dir']);

			if( file_exists($_POST['dir']) ) {
				$files = scandir($_POST['dir']);
				natcasesort($files);
				if( count($files) > 2 ) { /* The 2 accounts for . and .. */
					echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
					// All dirs
					foreach( $files as $file ) {
						if( file_exists($_POST['dir'] . $file) && $file != '.' && $file != '..' && is_dir($_POST['dir'] . $file) ) {
							echo  "<li class=\"directory collapsed\"><a class='link_dir' href=\"#\" rel=\"" . htmlentities($_POST['dir'] . $file) . "/\">" . htmlentities($file) . "</a></li>";
						}
					}
					// All files
					foreach( $files as $file ) {
						$classFile = $_POST['dir'];
						$classFile = substr($classFile,0,strlen($classFile)-1);
						$classFile = strrchr($classFile, '/'); 
						$classFile = substr($classFile,1,strlen($classFile));
						
						if( file_exists($_POST['dir'] . $file) && $file != '.' && $file != '..' && !is_dir($_POST['dir'] . $file) ) {
							$ext = preg_replace('/^.*\./', '', $file);
							echo "<li class=\"file ext_$ext ".$classFile." \"><a href=\"#\" rel=\"" . htmlentities($_POST['dir'] . $file) . "\">" . htmlentities($file) . "</a></li>";
						}
					}
					echo "</ul>";	
					
				}
			}
			$this->view->dircontent = $arrayFile;
	    }
	    
	public function createdirectoryAction(){
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		$name = $this->textFormat($_POST['dir_new']);
		$dirName = './documents/ressources/'.$name;
		
		if(isset($_POST['dir_newDir']) && $_POST['dir_newDir']!=""){
			$name = $this->textFormat($_POST['dir_new']);
			$dirName = $_POST['dir_newDir'].'/'.$name;
		}

		if(mkdir($dirName,0777)){
			$this->_redirect('/outils/index/');
		}else {
			echo "Erreur de crÃ©ation du dossier";
		}
	}
	
	public function downloadAction(){
        	$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);

			$file = substr($_GET['dir'],1,strlen($_GET['dir']));
            $name = substr($file,strrpos($file,'/'),strlen($file));
            $name = substr($name,1,strlen($name));
            $file = ".".$file;

               header("Pragma: public" );
               header("Expires: 0" );
               header("Cache-Control: must-revalidate, post-check=0, pre-check=0" );
               header("Cache-Control: public" );
               header("Content-Description: File Transfer" );
               header("Content-Type: application/force-download" );
               $header="Content-Disposition: attachment; filename=".$name.";";
               header($header );
               header("Content-Transfer-Encoding: binary" );
               echo file_get_contents($file);
               exit;
        }
	
        public function textFormat($string){
        	
        	$a = 'Ã€ÃÃ‚ÃƒÃ„Ã…Ã†Ã‡ÃˆÃ‰ÃŠÃ‹ÃŒÃÃŽÃÃÃ‘Ã’Ã“Ã”Ã•Ã–Ã˜Ã™ÃšÃ›ÃœÃÃžÃŸÃ Ã¡Ã¢Ã£Ã¤Ã¥Ã¦Ã§Ã¨Ã©ÃªÃ«Ã¬Ã­Ã®Ã¯Ã°Ã±Ã²Ã³Ã´ÃµÃ¶Ã¸Ã¹ÃºÃ»Ã½Ã½Ã¾Ã¿Å”Å• ';
    		$b = 'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr_';
		    $string = utf8_decode($string);    
		    $string = strtr($string, utf8_decode($a), $b);
		    $string = strtolower($string);
		    return utf8_encode($string); 
        }
        
        public function getfileAction(){
        	$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			$files = "";
			
			$dir = $_POST['dir'];
			
			if (is_dir($dir)) {
				if ($dh = opendir($dir)) {
					while (($file = readdir($dh)) !== false) {
						if($file != "." && $file != ".." && !is_dir($dir.$file)){
							$files .= $dir.$file.'*';
						}
					}
					closedir($dh);
				}
			}
			echo $files;
        }

	    
}