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
class UserControl {
	var $iduser;
	var $type;
	function UserControl($nodeconfiguration, $msg_manager) {
		return;
	}
	function load($nodeconfiguration, $nodexml, $ctrlname) {
		// print( $ctrlname );
		$ctrl_nodexml = $nodexml->xpath ( '//User[@name="' . $ctrlname . '"]' );
		if ($ctrl_nodexml == null)
			throw new Exception ( "UserControl: $ctrlname does not exist. Cannot load control." );
			
			// Type is the method to display the user control
			// Can be: small,...
		$this->type = ( string ) $ctrl_nodexml [0] ['type'];
		
		return $this;
	}
	function display($file) {
		if (empty ( $this->iduser ))
			print ('User not bound...') ;
		else {
			$this->displaySmall ();
		}
		
		return;
	}
	function addDataSource($params) {
	}
	function bind($params) {
		if (count ( $params ) != 2)
			throw new Exception ( "UserControl bind needs 1 param" );
			
			// var_dump($params);
		
		$ctrl = $params [0];
		
		if ($ctrl == null)
			throw new Exception ( 'UserControl bind: object not found.' );
		$this->iduser = $params [1];
	}
	function displaySmall() {
		global $OUTPUT, $DB;
		
		// kuink_mydebug ('USER',$this->iduser);
		$loaded_user = $DB->get_record ( 'user', array (
				"idnumber" => $this->iduser 
		) );
		
		// Getting user data from fw_user
		$iduser = $loaded_user->idnumber;
		
		$datasource = new Kuink\Core\DataSource ( null, 'framework.user,user,user.search', 'framework.user', 'user' );
		$params = array (
				'id_user' => $iduser 
		);
		// var_dump($params);
		$user_data = $datasource->execute ( $params );
		
		// var_dump($user_data);
		
		$output = '';
		$user_data = array_pop ( $user_data ['records'] );
		if ($user_data) {
			$user = new stdClass ();
			$user->id = $loaded_user->id;
			// var_dump($user);
			$picture = $OUTPUT->user_picture ( $user, array (
					'size' => 55 
			) );
			$nome = ( string ) $user_data->name;
			$email = ( string ) $user_data->email;
			$output = '<table width="100%" border="1"><tr><td><table border="0"><tr><td>' . $picture . '</td><td>' . $nome . '<br/><a href="mailto:' . $email . '">' . $email . '</a><br/>' . $tipo . '</td></tr></table></legend></fieldset></td></tr></table>';
		}
		print $output;
	}
}

?>
