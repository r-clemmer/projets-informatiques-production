<?php
 class Fichiers{
 		
	function getDirectoryTree( $outerDir, $filters = array() ){ 
	    $dirs = array_diff( scandir( $outerDir ), array_merge( Array( ".", ".." ), $filters ) ); 
	    $dir_array = Array(); 
	    foreach( $dirs as $d ){
	        $dir_array[ $d ] = is_dir($outerDir."/".$d) ? Fichiers::getDirectoryTree( $outerDir."/".$d, $filters ) : $dir_array[ $d ] = $d;
	    }
	    return $dir_array; 
	}
	
 	public function afficherArbre($arbre){
 		
    	$string = "";
    	
	    foreach($arbre as $key => $item){

			if(is_array($item)){
				$string .= '
					<li class="closed"><span class="folder">'.$key.'</span>
						<ul>';
				$string .= Fichiers::afficherArbre($item);
				$string .= '	
						</ul>
					</li>
				';
			}
			
		}
		
		foreach($arbre as $key => $item){

			if(!is_array($item)){
				$string .= '
					<li><span class="file">'.$item.'</span></li>
				';
			}
			
		}
		
		return $string;
    }
	
}