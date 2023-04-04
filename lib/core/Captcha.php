<?php

// This file is part of Kuink Application Framework
//
// Kuink Application Framework is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Kuink Application Framework is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Kuink Application Framework. If not, see <http://www.gnu.org/licenses/>.
namespace Kuink\Core;

// Requires neonDatasource to load a datasource

/**
 * This class is the base class for captcha generation and validation.
 * 
 * @author ptavares
 *        
 */
 class Captcha {

	/**
	 * Generates an image
	 * From: https://code.tutsplus.com/tutorials/build-your-own-captcha-and-contact-form-in-php--net-5362
	 */
	static function getImage() {
		global $KUINK_CFG;
		$permittedChars = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
			
		function secure_generate_string($input, $strength = 5, $secure = true) {
			$input_length = strlen($input);
			$random_string = '';
			for($i = 0; $i < $strength; $i++) {
					if($secure) {
							$random_character = $input[random_int(0, $input_length - 1)];
					} else {
							$random_character = $input[mt_rand(0, $input_length - 1)];
					}
					$random_string .= $random_character;
			}

			return $random_string;
		}
			
		$image = imagecreatetruecolor(200, 50);
			
		imageantialias($image, true);
			
		$colors = [];
			
		$red = rand(125, 150);
		$green = rand(125, 150);
		$blue = rand(125, 150);
			
		for($i = 0; $i < 5; $i++) {
			$colors[] = imagecolorallocate($image, $red - 20*$i, $green - 20*$i, $blue - 20*$i);
		}
			
		imagefill($image, 0, 0, $colors[0]);
			
		for($i = 0; $i < 10; $i++) {
			imagesetthickness($image, rand(2, 10));
			$lineColor = $colors[rand(1, 4)];
			imagerectangle($image, rand(-10, 190), rand(-10, 10), rand(-10, 190), rand(40, 60), $lineColor);
		}
			
		$black = imagecolorallocate($image, 0, 0, 0);
		$white = imagecolorallocate($image, 255, 255, 255);
		$textcolors = [$black, $white];
		$gdFontPath = realpath(dirname(__FILE__).'/../tools/KuinkFonts');
		putenv('GDFONTPATH=' . $gdFontPath);
		$fonts = ['Acme', 'Merriweather', 'DancingScript', 'Ubuntu', 'PlayfairDisplay'];
			
		$stringLength = 6;
		$captchaString = secure_generate_string($permittedChars, $stringLength, true);

		$_SESSION['kuink_captcha_text'] = $captchaString;

		for($i = 0; $i < $stringLength; $i++) {
			$letterSpace = 170/$stringLength;
			$initial = 15;

			imagettftext($image, 24, rand(-15, 15), $initial + $i*$letterSpace, rand(25, 45), $textcolors[rand(0, 1)], $fonts[array_rand($fonts)], $captchaString[$i]);
		}
		return $image;
	}

	/**
	 * Validates a captcha value
	 */
	static function isValid($value) {
		$captchaString = $_SESSION['kuink_captcha_text'];

		return (strtoupper($value) == strtoupper($captchaString));
	}
	
}
