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
namespace Kuink\UI\Formatter;

class User extends Formatter {
	function format($value, $params = null) {
		return $this->small ( $value, $params );
	}
	function small($value) {
		global $OUTPUT, $USER, $DB;
		
		$loaded_user = $DB->get_record ( 'user', array (
				"idnumber" => $value 
		) );
		
		// Getting user data from fw_user
		$datasource = new Kuink\Core\DataSource ( null, 'framework.user,user,user.search', 'framework.user', 'user' );
		$params = array (
				'id_user' => $value 
		);
		$user_data = $datasource->execute ( $params );
		
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
			$output = '<div style="border: 0px; float: left; margin: 0px 10px 3px 0px;">' . $picture . '</div><div width="100%" style="border: 0px">' . $nome . '<br/><a href="mailto:' . $email . '">' . $email . '</a><br/>' . $tipo . '</div>';
		}
		return $output;
	}
}

?>