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
class DateTimeLib {
	private $nodeconfiguration;
	
	private function setDefaultTimezone() {
		date_default_timezone_set ( 'UTC' );		
	}
	
	function __construct($nodeconfiguration, $msg_manager) {
		$this->nodeconfiguration = $nodeconfiguration;
		return;
	}
	function Execute($functionname, $params) {
		$this->$functionname ( $params );
	}
	function PreviousWeekStart($params) {
		return strtotime ( "last sunday -7 days" );
	}
	function PreviousWeekEnd($params) {
		$date = getdate ( strtotime ( "last sunday" ) );
		return mktime ( 23, 59, 59, $date ['mon'], $date ['mday'], $date ['year'] );
	}
	function ThisWeekStart($params) {
		return strtotime ( "last sunday" );
	}
	function ThisWeekEnd($params) {
		$today = getdate ( strtotime ( "now" ) );
		if ($today ['wday'] == 0)
			$date = getdate ( strtotime ( "now +7 days" ) );
		else
			$date = getdate ( strtotime ( "last sunday +7 days" ) );
		return mktime ( 23, 59, 59, $date ['mon'], $date ['mday'], $date ['year'] );
	}
	function NextWeekStart($params) {
		return strtotime ( "next sunday" );
	}
	function NextWeekEnd($params) {
		return strtotime ( "next sunday +7 days" );
	}
	function PreviousMonthStart($params) {
		return mktime ( 0, 0, 0, $this->month ( null ) - 1, 1, $this->year ( null ) );
	}
	function PreviousMonthEnd($params) {
		return mktime ( 23, 59, 59, $this->month ( null ), 0, $this->year ( null ) );
	}
	function ThisMonthStart($params) {
		return mktime ( 0, 0, 0, $this->month ( null ), 1, $this->year ( null ) );
	}
	function ThisMonthEnd($params) {
		return mktime ( 23, 59, 59, $this->month ( null ) + 1, 0, $this->year ( null ) );
	}
	function NextMonthStart($params) {
		return mktime ( 0, 0, 0, $this->month ( null ) + 1, 1, $this->year ( null ) );
	}
	function NextMonthEnd($params) {
		return mktime ( 23, 59, 59, $this->month ( null ) + 2, 0, $this->year ( null ) );
	}
	function getRelative($params) {
		$str_relative = ( string ) $params [0];
		date_default_timezone_set ( 'UTC' );
		return strtotime ( $str_relative );
	}
	function Today($params) {
		return mktime ( 12, 0, 0, $this->month ( null ), $this->day ( null ), $this->year ( null ) );
	}
	function TodayStart($params) {
		return mktime ( 0, 0, 0, $this->month ( null ), $this->day ( null ), $this->year ( null ) );
	}
	function TodayEnd($params) {
		return mktime ( 23, 59, 59, $this->month ( null ), $this->day ( null ), $this->year ( null ) );
	}
	
	/*
	 * OLD
	 * function Now($params)
	 * {
	 * if (isset($params[0]))
	 * return date($params[0]);
	 * return time();
	 * }
	 */
	function Now($params=null) {
		$dateTime = new \DateTime ( 'NOW', new \DateTimeZone ( 'UTC' ) );
		
		// compatibility mode
		if (isset ( $params [0] ))
			date ( $params [0] );
		
		return $dateTime->getTimestamp ();
	}
	function month($params) {
		$dateTime = new \DateTime ( 'NOW', new \DateTimeZone ( 'UTC' ) );
		if (isset ( $params [0] )) {
			$dateTime->setTimestamp ( $params [0] );
		}
		return $dateTime->format ( "m" );
	}
	function day($params) {
		$dateTime = new \DateTime ( 'NOW', new \DateTimeZone ( 'UTC' ) );
		if (isset ( $params [0] )) {
			$dateTime->setTimestamp ( $params [0] );
		}
		return $dateTime->format ( "d" );
	}
	function year($params) {
		$dateTime = new \DateTime ( 'NOW', new \DateTimeZone ( 'UTC' ) );
		if (isset ( $params [0] )) {
			$dateTime->setTimestamp ( $params [0] );
		}
		return $dateTime->format ( "y" );
	}
	function microtime($params) {
		return microtime ( true );
	}
	function AddWorkingDays($params) {
		$date = $params [0];
		$add = ( int ) $params [1];
		
		if ($add == 0)
			return $date;
		
		$sign = $this->getNumberSign ( $add );
		
		$max = $add * $sign;
		
		$count = 1;
		$newdate = $date;
		
		while ( $count <= $max ) {
			$newdate = $newdate + 86400 * $sign;
			$checkdate = getdate ( $newdate );
			$wday = $checkdate ['wday'];
			if ($wday >= 1 && $wday <= 5)
				$count ++;
		}
		return $newdate;
	}
	function inTimeValidation($params) {
		$oldDate = $params [0];
		$interval = $params [1];
		$now = time ();
		return ( int ) (($now - $oldDate) <= $interval);
	}
	private function getNumberSign($number) {
		$sign = $number * (- 1);
		if ($sign < 0)
			return 1;
		if ($sign > 0)
			return - 1;
		
		return 0;
	}
	function getWeekDay($params) {
  	$this->setDefaultTimezone();		
		$value = isset ( $params [0] ) ? $params [0] : false;
		return ($value) ? date ( "w", $value ) : date ( "w" );
	}
	function getWeekNumber($params) {
		$value = isset ( $params [0] ) ? $params [0] : false;
		return ($value) ? date ( "W", strtotime ( $value ) ) : date ( "W" );
	}
	function getWeekNumberByTimestamp($params) {
		$value = isset ( $params [0] ) ? $params [0] : false;
		return ($value) ? date ( "W", $value ) : date ( "W" );
	}
	
	/**
	 * Get user offset from utc time
	 * 
	 * @param String $params
	 *        	0 : Timezone string
	 * @return int Offset in seconds
	 */
	function getTzOffset($params) {
		$tz = (isset ( $params [0] )) ? $params [0] : $this->nodeconfiguration ['USER'] ['timezone'];
		$timezone = new \DateTimeZone ( $tz );
		return $timezone->getOffset ( new \DateTime ( 'NOW', new \DateTimeZone ( 'UTC' ) ) );
	}
	
	/**
	 * Get timestamp
	 */
	function toTimestamp($params) {
		global $KUINK_BRIDGE_CFG;
		$year = ( string ) $params ['year'];
		$month = ( string ) $params ['month'];
		$day = ( string ) $params ['day'];
		$hour = ( string ) $params ['hour'];
		$minute = ( string ) $params ['minute'];
		$second = ( string ) $params ['second'];
		$applyOffset = (isset ( $params ['applyOffset'] ) && $params ['applyOffset'] == 1) ? 1 : 0;
		$useLocalTimezone = (isset($params['useLocalTimezone']) && $params['useLocalTimezone'] == 1) ? 1 : 0;
        $timezone = (string)$params['timezone'];

        if ($timezone != '') {
            //The user supplies a timezone
            $datetime = $year.'-'.$month.'-'.$day.' '.$hour.':'.$minute.':'.$second;
            $given = new \DateTime($datetime, new \DateTimeZone($timezone));
            //Convert it to UTC
            $given->setTimezone(new \DateTimeZone('UTC'));
            $returnValue = $given->getTimestamp();
        }
        else if ($useLocalTimezone) {
            //date_default_timezone_set($NEON_CFG->serverTimezone);
            $datetime = $year.'-'.$month.'-'.$day.' '.$hour.':'.$minute.':'.$second;
            //Get hierarchy timezones: USER | TODO: COMPANY | SERVER
            $kuinkUser = new \Kuink\Core\User();
            $kuink_user = $kuinkUser->getUser();
			$localTimeZone = (($kuink_user['timezone'] != null) && ($kuink_user['timezone'] != '')) ? $kuink_user['timezone'] : $KUINK_BRIDGE_CFG->serverTimezone;
            $given = new \DateTime($datetime, new \DateTimeZone($localTimeZone));
            $given->setTimezone(new \DateTimeZone('UTC'));
            $returnValue = $given->getTimestamp();
        }
        else {
            //Compatibility
            date_default_timezone_set('UTC');
            $returnValue = mktime ($hour, $minute, $second, $month, $day, $year);
        }

        if ($applyOffset == 1)
            //Compatibility
           	return $returnValue - $this->getTzOffset();
        else
			return $returnValue;	
	}
	
	/**
	 * Given a timestamp, get the day
	 */
	function getDay($params) {
		$timestamp = isset($params [0]) ? $params [0] : null;
		$dateTime = new \DateTime ( 'NOW', new \DateTimeZone ( 'UTC' ) );
		$dateTime->setTimestamp ( $timestamp );
		if (isset ( $params [0] )) {
			$dateTime->setTimestamp ( $params [0] );
		}
		return $dateTime->format ( "d" );
	}
	
	/**
	 * Given a timestamp, get the month
	 */
	function getMonth($params) {
		$timestamp = isset($params [0]) ? $params [0] : null;
		$dateTime = new \DateTime ( 'NOW', new \DateTimeZone ( 'UTC' ) );
		$dateTime->setTimestamp ( $timestamp );
		if (isset ( $params [0] )) {
			$dateTime->setTimestamp ( $params [0] );
		}
		return $dateTime->format ( "m" );
	}
	
	/**
	 * Given a timestamp, get the year
	 */
	function getYear($params) {
		$timestamp = isset($params [0]) ? $params [0] : null;
		$format = isset($params [1]) ? $params [1] : 'Y';
		$dateTime = new \DateTime ( 'NOW', new \DateTimeZone ( 'UTC' ) );
		$dateTime->setTimestamp ( $timestamp );
		if (isset ( $params [0] )) {
			$dateTime->setTimestamp ( $params [0] );
		}
		return $dateTime->format ( $format );
	}
	
	/**
	 * Given a timestamp, get the hour
	 */
	function getHour($params) {
		$timestamp = isset($params [0]) ? $params [0] : null;
		$dateTime = new \DateTime ( 'NOW', new \DateTimeZone ( 'UTC' ) );
		$dateTime->setTimestamp ( $timestamp );
		if (isset ( $params [0] )) {
			$dateTime->setTimestamp ( $params [0] );
		}
		if (isset ( $params [1] ))
			return $dateTime->format ( $params [1] );
		else
			return $dateTime->format ( "h" );
	}
	
	/**
	 * Given a timestamp, get the minutes
	 */
	function getMinutes($params) {
		$timestamp = isset($params [0]) ? $params [0] : null;
		$dateTime = new \DateTime ( 'NOW', new \DateTimeZone ( 'UTC' ) );
		$dateTime->setTimestamp ( $timestamp );
		if (isset ( $params [0] )) {
			$dateTime->setTimestamp ( $params [0] );
		}
		return $dateTime->format ( "i" );
	}
	
	/**
	 * Given a timestamp, get the seconds
	 */
	function getSeconds($params) {
		$timestamp = isset($params [0]) ? $params [0] : null;
		$dateTime = new \DateTime ( 'NOW', new \DateTimeZone ( 'UTC' ) );
		$dateTime->setTimestamp ( $timestamp );
		if (isset ( $params [0] )) {
			$dateTime->setTimestamp ( $params [0] );
		}
		return $dateTime->format ( "s" );
	}
	
	/**
	 * Given a timestamp, get the date components in an array (day, month, year, hour, minutes, seconds)
	 */
	function getComponents($params) {
		$timestamp = isset($params [0]) ? $params [0] : null;
		if (isset ( $params [1] )) {
			$format = $params [1];
			$hourArray = array (
					$timestamp,
					$format 
			);
		} else {
			$hourArray = array (
					$timestamp 
			);
		}
		$out ['day'] = $this->getDay ( array (
				$timestamp 
		) );
		$out ['month'] = $this->getMonth ( array (
				$timestamp 
		) );
		$out ['year'] = $this->getYear ( array (
				$timestamp 
		) );
		$out ['hour'] = $this->getHour ( $hourArray );
		$out ['minute'] = $this->getMinutes ( array (
				$timestamp 
		) );
		$out ['second'] = $this->getSeconds ( array (
				$timestamp 
		) );
		return $out;
	}
	function addDays($params) {
		$timestamp = isset($params [0]) ? $params [0] : null;
		$numberOfDays = (isset ( $params [1] )) ? $params [1] : 0;
		return (( int ) $timestamp + (86400 * $numberOfDays));
	}
	
	/**
	 * Splut hh:mm:ss to an array("hour", "minute", "second")
	 * 
	 * @param Array $params        	
	 */
	function splitDateTime($params) {
		$str = (isset ( $params [0] )) ? ( string ) $params [0] : "00:00:00";
		$parts = explode ( ':', $str );
		$parts [2] = (isset ( $parts [2] )) ? $parts [2] : "00";
		return array (
				"hour" => $parts [0],
				"minute" => $parts [1],
				"second" => $parts [2] 
		);
	}
	
	/**
	 * Get the last day of a given month
	 */
	function getLastDayOfMonth($params = null) {
		$timestamp = $this->Now ( array () );
		$components = $this->getComponents ( array (
				$timestamp 
		) );
		
		$day = (isset ( $params ['day'] )) ? $params ['day'] : 1; // any day of month. 1 occours in every month
		$month = (isset ( $params ['month'] )) ? $params ['month'] : $components ['month'];
		$year = (isset ( $params ['year'] )) ? $params ['year'] : $components ['year'];
		
		$timestamp = $this->toTimestamp ( array (
				'day' => $day,
				'month' => $month,
				'year' => $year,
				'hour' => 0,
				'minute' => 0,
				'second' => 0 
		) );
		
		$lastday = date ( 't', $timestamp );
		
		return $this->toTimestamp ( array (
				'day' => $lastday,
				'month' => $month,
				'year' => $year,
				'hour' => 22,
				'minute' => 59,
				'second' => 59 
		) );
	}
	function convertFromISO8601Format($params) {
		// TODO:: hotfix for access control
		$src = $params [0];
		
		// temporary fix for sportsLisbon
		$sportsLisbonFix = (isset ( $params [3] )) ? $params [3] : 0;
		//
		
		$format = (isset ( $params [1] )) ? $params [1] : 'Y-m-d\TH:i:s.u';
		$timezone = (isset ( $params [2] )) ? $params [2] : 'Europe/Lisbon';
		$dateTimeSrc = date_create ( $src, timezone_open ( $timezone ) );
		$offset = timezone_offset_get ( timezone_open ( $timezone ), $dateTimeSrc );
		if (strpos ( $src, '.' ) === false) {
			$src .= '.001';
		}
		$date = \DateTime::createFromFormat ( $format, $src, new DateTimeZone ( $timezone ) );
		
		// temporary fix for sportsLisbon
		if ($sportsLisbonFix == 1)
			$offset -= 3600;
			//
		
		if ($date)
			return ($date->getTimestamp () + $offset);
		else
			return $src;
	}
    
	function convertFromString($params = null){
        $src = (string)$params[0];
        
        return strtotime($src);
    }	
}

?>
