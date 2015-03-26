<?php
	class ImageController extends Zend_Controller_Action {

		public function stringtoimageAction(){

			Zend_Layout::getMvcInstance()->disableLayout();

			header('Content-type: image/png');

			$string = $this->_request->getParam('string');
			$width = $this->_request->getParam('width');
			$height = $this->_request->getParam('height');
			$rotation = $this->_request->getParam('rotation');

			$string = str_replace('+', ' ', $string);
			$string = utf8_decode($string);

			$front = Zend_Controller_Front::getInstance();

			$image = imagecreate($width,$height);

			$blanc = imagecolorallocate($image, 255, 255, 255);
			$noir = imagecolorallocate($image, 0, 0, 0);

//			$largeur = imagefontwidth(1) * strlen($string);

			imagestring($image, 1, 10, 10, $string, $noir);

			$image = imagerotate($image, $rotation, $blanc);

			imagepng($image);
		}

	}