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

class Tools {
	static public function getPageUrl() {
		$server_https = isset ( $_SERVER ["HTTPS"] ) ? ( string ) $_SERVER ["HTTPS"] : '';
		$server_port = isset ( $_SERVER ["SERVER_PORT"] ) ? ( string ) $_SERVER ["SERVER_PORT"] : '';
		$server_name = isset ( $_SERVER ["SERVER_NAME"] ) ? ( string ) $_SERVER ["SERVER_NAME"] : '';
		$server_request_uri = isset ( $_SERVER ["REQUEST_URI"] ) ? ( string ) $_SERVER ["REQUEST_URI"] : '';
		
		$pageURL = 'http';
		if ($server_https == "on") {
			$pageURL .= "s";
		}
		$pageURL .= "://";
		if ($server_port != "80") {
			$pageURL .= $server_name . ":" . $server_port . $server_request_uri;
		} else {
			$pageURL .= $server_name . $server_request_uri;
		}
		
		return $pageURL;
	}
	static public function getWWWRoot() {
		global $KUINK_CFG;
		
		return $KUINK_CFG->wwwRoot;
	}
	static public function setUrlParams($baseurl, $params=array()) {
		$url_parsed = parse_url ( htmlspecialchars_decode($baseurl) );
		//print_object($url_parsed);
		$query = ( string ) $url_parsed ['query'];
		parse_str ( $query, $query_parsed );
		// var_dump($query);
		
		foreach ( $params as $key => $value )
			if (trim($value!=''))
				$query_parsed [$key] = $value;
			else
				unset($query_parsed[$key]);
		
		$query =  htmlspecialchars_decode(http_build_query ( $query_parsed ));
		$url_parsed ['query'] = $query;
		$url = explode ( '?', $baseurl );
		$server = ( string ) $url [0];
		$location = $server . '?' . $query;
	  //rint_object($location);		

		return $location;
	}
}

?>