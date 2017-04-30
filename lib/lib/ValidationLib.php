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
class ValidationLib {
	var $nodeconfiguration;
	var $msg_manager;
	function ValidationLib($nodeconfiguration, $msg_manager) {
		$this->nodeconfiguration = $nodeconfiguration;
		$this->msg_manager = $msg_manager;
		return;
	}
	function email($params) {
		if (count ( $params ) != 1)
			throw new Exception ( 'Email validation: must have one parameter that specifies email to validate. ' );
		
		$email = ( string ) $params [0];
		
		$valid = preg_match ( "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/", $email );
		
		if (! $valid)
			$this->msg_manager->add ( \Kuink\Core\MessageType::ERROR, kuink_get_string ( 'invalid_email' ) );
		
		return ( int ) $valid;
	}
	
	/**
	 * Validates a portuguese postal code
	 * 
	 * @param Array $params
	 *        	Parameters array. The postal code to validate is in this array
	 * @throws Exception Invalid parameters number
	 * @return Boolean True if postal code is valid, False otherwise
	 * @author Joao Patricio
	 */
	function pt_postal_code($params) {
		if (count ( $params ) != 1) {
			throw new Exception ( "PT Postal Code validation: must have one parameter that specifies postal code to validate" );
		}
		$postal_code = ( string ) $params [0];
		// $valid = eregi("^[1-9]{1}[0-9]{3}-[0-9]{3}$", $postal_code);
		$valid = preg_match ( "/^[1-9]{1}[0-9]{3}-[0-9]{3}$/", $postal_code );
		if (! $valid)
			$this->msg_manager->add ( \Kuink\Core\MessageType::ERROR, kuink_get_string ( 'invalid_pt_postal_code' ) );
		
		return ( int ) $valid;
	}
	
	/**
	 * Validates a string as a number
	 * 
	 * @param Array $params
	 *        	Parameters array. The string to validate is in this array
	 * @throws Exception Invalid parameters number
	 * @return Boolean True if string is a number, False otherwise
	 * @author Joao Patricio
	 */
	function number($params) {
		if (count ( $params ) != 2) {
			throw new Exception ( "Number validation: must have one parameter that specifies the number" );
		}
		$number = ( string ) $params [0];
		// $valid = eregi("^[0-9]*$", $number);
		$valid = preg_match ( "/^[0-9]*$/", $number );
		
		if (! $valid) {
			$error_string = ( string ) $params [1];
			$this->msg_manager->add ( \Kuink\Core\MessageType::ERROR, kuink_get_string ( $error_string ) );
		}
		
		return ( int ) $valid;
	}
	
	/**
	 * Validates a portuguese tax number
	 * 
	 * @param Array $params
	 *        	Parameters Array. The tax number to validate is in this array
	 * @return Boolean True is is a valid pt tax number, False otherwise
	 */
	function pt_tax_number($params) {
		if (count ( $params ) != 1)
			throw new Exception ( "PT Tax Number validation: must have one parameter that specifies the tax number" );
		
		$tax_number = ( string ) $params [0];
		$is_valid = false;
		$regex = '/^[125689]\d{8}$/';
		
		if (! preg_match ( $regex, $tax_number )) {
			$is_valid = false;
		} else {
			$checkDigit = $tax_number [0] * 9;
			
			for($i = 1; $i < 8; $i ++) {
				$checkDigit += $tax_number [$i] * (9 - $i);
			}
			
			$checkDigit = 11 - ($checkDigit % 11);
			
			// Se o digito de controle é maior que dez, coloca-o a zero
			if ($checkDigit >= 10)
				$checkDigit = 0;
				
				// Compara o digito de controle com o último numero do NIF
				// Se igual, o NIF é válido.
			if ($checkDigit == $tax_number [8])
				$is_valid = true;
		}
		if (! $is_valid) {
			$this->msg_manager->add ( \Kuink\Core\MessageType::ERROR, kuink_get_string ( "invalid_pt_tax_number" ) );
		}
		return ( int ) $is_valid;
	}
	
	/**
	 * Validates portuguese identity number
	 * 
	 * @param Array $params
	 *        	Parameters array. The identity number to validate is in this array
	 * @throws Exception If the number of parameter isn't exactly one
	 * @return True if is valid, False otherwise
	 */
	function pt_civil_number($params) {
		if (count ( $params ) != 1)
			throw new Exception ( "PT Identity Number validation: must have one parameter that specifies the identity number" );
		
		$identity_num = ( string ) $params [0];
		$is_valid = false;
		
		$bi = str_replace ( ' ', '', $identity_num );
		
		$regex = '/^[0-9]{5,9}/';
		
		if (! (preg_match ( $regex, $bi ) and is_numeric ( $bi ) and $bi < 1000000000)) {
			$is_valid = false;
		} else {
			$is_valid = true;
		}
		
		if (! $is_valid) {
			$this->msg_manager->add ( \Kuink\Core\MessageType::ERROR, kuink_get_string ( "invalid_pt_civil_number" ) );
		} else {
			$is_valid = true;
		}
		return ( int ) $is_valid;
	}
	
	/**
	 * Validates portuguese identity expiry date
	 * 
	 * @param Array $params
	 *        	Parameters array. The identity date to validate is in this array and the current date
	 * @throws Exception If the number of parameter isn't exactly two
	 * @return True if is valid, False otherwise
	 */
	function pt_doc_date($params) {
		if (count ( $params ) != 2)
			throw new Exception ( "PT Document Expiry Date validation: must have two parameter that specifies the identity expiry date" );
		
		$identity_date = $params [0];
		$date_now = $params [1];
		$is_valid = false;
		
		if ($identity_date > $date_now)
			$is_valid = true;
		
		if (! $is_valid)
			$this->msg_manager->add ( \Kuink\Core\MessageType::ERROR, kuink_get_string ( "invalid_pt_doc_date" ) );
		return ( int ) $is_valid;
	}
	
	/**
	 * Validates a portuguese civil card number.
	 * This implementation follows the algorithm published in http://www.cartaodecidadao.pt/
	 * 
	 * @param Array $params
	 *        	Parameters array. The civil card number to validate is in this array
	 * @throws Exception If the number of parameter isn't exactly one
	 * @return True if is valid, False otherwise
	 */
	function pt_civil_card_number($params) {
		if (count ( $params ) != 1)
			throw new Exception ( "Civil Card Number validation: must have one parameter that specifies the civil card number to validate" );
		
		$civil_card_num = ( string ) $params [0];
		$is_valid = false;
		$regex = '/^[0-9A-Z]{12}$/';
		$numeroDocumento = str_replace ( ' ', '', strtoupper ( $civil_card_num ) );
		
		if (! preg_match ( $regex, $numeroDocumento )) {
			$is_valid = false;
		} else {
			
			$sum = 0;
			$secondDigit = false;
			
			// if(strlen($numeroDocumento) != 12)
			// $is_valid=false;
			// else{
			for($i = strlen ( $numeroDocumento ) - 1; $i >= 0; -- $i) {
				$valor = $this->getNumberFromChar ( $numeroDocumento [$i] );
				if ($secondDigit) {
					$valor *= 2;
					if ($valor > 9)
						$valor -= 9;
				}
				
				$sum += $valor;
				$secondDigit = ! $secondDigit;
			}
			$is_valid = (($sum % 10) == 0) ? true : false;
			// }
		}
		if (! $is_valid) {
			$this->msg_manager->add ( \Neon\Core\MessageType::ERROR, neon_get_string ( "invalid_pt_civil_card_number" ) );
		}
		return ( int ) $is_valid;
	}
	
	/**
	 * This is an auxiliar function to validate pt civil card number
	 * 
	 * @param unknown_type $letter        	
	 */
	private function getNumberFromChar($letter) {
		switch ($letter) {
			case '0' :
				return 0;
			case '1' :
				return 1;
			case '2' :
				return 2;
			case '3' :
				return 3;
			case '4' :
				return 4;
			case '5' :
				return 5;
			case '6' :
				return 6;
			case '7' :
				return 7;
			case '8' :
				return 8;
			case '9' :
				return 9;
			case 'A' :
				return 10;
			case 'B' :
				return 11;
			case 'C' :
				return 12;
			case 'D' :
				return 13;
			case 'E' :
				return 14;
			case 'F' :
				return 15;
			case 'G' :
				return 16;
			case 'H' :
				return 17;
			case 'I' :
				return 18;
			case 'J' :
				return 19;
			case 'K' :
				return 20;
			case 'L' :
				return 21;
			case 'M' :
				return 22;
			case 'N' :
				return 23;
			case 'O' :
				return 24;
			case 'P' :
				return 25;
			case 'Q' :
				return 26;
			case 'R' :
				return 27;
			case 'S' :
				return 28;
			case 'T' :
				return 29;
			case 'U' :
				return 30;
			case 'V' :
				return 31;
			case 'W' :
				return 32;
			case 'X' :
				return 33;
			case 'Y' :
				return 34;
			case 'Z' :
				return 35;
		}
		return false;
	}
	
	/**
	 * Validates a identification document number
	 * 
	 * @param Array $params
	 *        	Array with the [0]document number and [1]the document type. The document type must be an integer.
	 *        	Supported document types:
	 *        	1 - Portuguese identification number
	 *        	2 - Residence authorization title number
	 *        	3 - Personal ballot number
	 *        	4 - Birth Certificate number
	 *        	5 - Passport number
	 *        	6 - ID Card number for foreigners
	 *        	7 - Portuguese civil card number
	 *        	
	 *        	
	 * @throws Exception If the params has a incorrect length
	 * @return Ambigous <True, number>|number
	 * @author Joao Patricio
	 *         TODO STI: Joao Patricio - Add another identification document validators
	 */
	function iddoc_number($params) {
		if (count ( $params ) != 2)
			throw new Exception ( "PT ID Doc validation: must have exactly two parameters: [0] Document number, [1] Document Type" );
		
		$doc_number = $params [0];
		$doc_type = $params [1];
		
		switch ($doc_type) {
			case 1 : // portuguese identification number
				return ( int ) $this->pt_civil_number ( array (
						$doc_number 
				) );
				break;
			case 2 : // residence authorization title
				break;
			case 3 : // personal ballot
				break;
			case 4 : // birth certificate
				break;
			case 5 : // passport
				break;
			case 6 : // ID Card for foreigners
				break;
			case 7 : // Portuguese civil card
				return ( int ) $this->pt_civil_card_number ( array (
						$doc_number 
				) );
				break;
		}
		
		return ( int ) true;
	}
	
	/**
	 * Validates a identification document expiry date
	 * 
	 * @param Array $params
	 *        	Array with the [0]document expiry date, [1]the document type and [2] current date. The document type must be an integer.
	 *        	Supported document types:
	 *        	1 - Portuguese identification number
	 *        	2 - Residence authorization title number
	 *        	3 - Personal ballot number
	 *        	4 - Birth Certificate number
	 *        	5 - Passport number
	 *        	6 - ID Card number for foreigners
	 *        	7 - Portuguese civil card number
	 *        	
	 *        	
	 * @throws Exception If the params has a incorrect length
	 * @return Ambigous <True, number>|number
	 * @author André Bittencourt
	 */
	function iddoc_date($params) {
		if (count ( $params ) < 2 && count ( $params ) > 3)
			throw new Exception ( "PT ID Doc validation: must have three parameters: [0] Document Expiry Date, [1] Document Type, [2] Actual Date" );
		
		$doc_date = $params [0];
		$doc_type = $params [1];
		$date_now = $params [2];
		if ($doc_type != 99)
			return ( int ) $this->pt_doc_date ( array (
					$doc_date,
					$date_now 
			) );
		
		return ( int ) true;
	}
	
	// TODO STI: Joao Patricio - Add generic phone number validation
	/**
	 * function phone_number( $params ){
	 * if (count($params)!=2)
	 * throw new Exception("Phone Number Validation: must have two parameters - [0] phone number to validate, [1] error message ");
	 *
	 * $phone_number = (string)$params[0];
	 * $error_msg = (string)$params[1];
	 * $valid = preg_match("([+(\d]{1})(([\d+() -.]){5,16})([+(\d]{1})", $phone_number);
	 * if (!$valid){
	 * $this->msg_manager->add(\Kuink\Core\MessageType::ERROR, kuink_get_string($error_msg));
	 * }
	 * die(var_dump($phone_number). " |||| Result -> ".(int)$valid);
	 * return (int)$valid;
	 * }
	 */
	
	/**
	 * Validates a birthday date
	 * 
	 * @param
	 *        	Array Array with
	 *        	[0] Birthday,
	 *        	[1] String to be evaluated,
	 *        	[2] Lang key of error message
	 * @author Joao Patricio
	 *        
	 */
	function birthday($params) {
		if (count ( $params ) != 1)
			throw new Exception ( "Birthday Validation only support one parameter: the timestamp in UTC to validate" );
		
		$timestamp = $params [0];
		
		$initialTs = - 2177452800; // Tue, 01 Jan 1901 00:00:00 GMT
		
		$valid = $timestamp > $initialTs;
		
		$error_msg = 'invalidBirthdayDate';
		
		if (! $valid)
			$this->msg_manager->add ( \Kuink\Core\MessageType::ERROR, kuink_get_string ( $error_msg ) );
		
		return ( int ) $valid;
	}
	
	/**
	 * Validates the length of a string
	 * 
	 * @param
	 *        	Array Array with
	 *        	[0] Max length allowed,
	 *        	[1] String to be evaluated,
	 *        	[2] Lang key of error message
	 * @author Joao Patricio
	 *        
	 */
	function string_max_length($params) {
		if (count ( $params ) != 3)
			throw new Exception ( "String max length: must have three parameters - [0] max length, [1] string, [2] error lang key" );
		
		$length = ( string ) $params [0];
		$string = ( string ) $params [1];
		$error_msg = ( string ) $params [2];
		
		$valid = strlen ( $string ) <= $length;
		if (! $valid)
			$this->msg_manager->add ( \Kuink\Core\MessageType::ERROR, kuink_get_string ( $error_msg ) );
		
		return ( int ) $valid;
	}
}

?>
