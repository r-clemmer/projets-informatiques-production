<?php
class Zend_View_Helper_StringToImage{

	public function setView(Zend_View_Interface $view){
        $this->view = $view;
    }

    public function StringToImage( $string = '', $width = 0, $height = 0, $rotation = 0){

		$front = Zend_Controller_Front::getInstance();

		require $front->getBaseUrl()."/image/stringtoimage/?string=$string&width=$width&height=$height&rotation=$rotation";

	}

}