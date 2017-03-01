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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Kuink Application Framework.  If not, see <http://www.gnu.org/licenses/>.

namespace Kuink\UI\Formatter;

class DateTime extends Formatter {

    /** ISO 8601 **/
    //WEEK FORMATS
    const WEEK_COMPLETE_BASIC = "Y\WWN"; //2013W293 | ISO8601:2004 4.1.4.2
    const WEEK_COMPLETE_EXTENDED = "Y-\WW-N"; //2013-W29-3 | ISO8601:2004 4.1.4.2
    const WEEK_REDUCED_BASIC = "Y\WW"; //2013W29 | ISO8601:2004 4.1.4.3
    const WEEK_REDUCED_EXTENDED = "Y-\WW"; //2013-W29 | ISO8601:2004 4.1.4.3
    const WEEK_DAY_STR = "l"; //Saturday, Sunday, etc

    const DATE_BASIC_1 = "Y-m-d";
    const DATE_BASIC_2 = "Ymd";

    //TIME FORMATS
    const TIME_COMPLETE_BASIC = "His"; //161132 | ISO8601:2004 4.2.2.2
    const TIME_COMPLETE_EXTENDED = "H:i:s"; //16:11:32 | ISO8601:2004 4.2.2.2
    const TIME_REDUCED_BASIC = "Hi"; //1611 | ISO8601:2004 4.2.2.3
    const TIME_REDUCED_EXTENDED = "H:i"; //16:11 | ISO8601:2004 4.2.2.3
    const TIME_HOUR = "H"; //16 | ISO8601:2004 4.2.2.3

    //TIME UTC FORMATS
    const TIME_UTC_COMPLETE_BASIC = "His\Z"; //161132 | ISO8601:2004 4.2.4
    const TIME_UTC_COMPLETE_EXTENDED = "H:i:s\Z"; //16:11:32 | ISO8601:2004 4.2.4
    const TIME_UTC_REDUCED_BASIC = "Hi\Z"; //1611 | ISO8601:2004 4.2.4
    const TIME_UTC_REDUCED_EXTENDED = "H:i\Z"; //16:11 | ISO8601:2004 4.2.4
    const TIME_UTC_HOUR = "H\Z"; //16Z | ISO8601:2004 4.2.4

    //TIME UTC WITH DIFF
    const TIME_UTC_DIFF_BASIC = "HisO"; //163628+0200 | ISO8601:2004 4.2.5.2
    const TIME_UTC_DIFF_EXTENDED = "H:i:sP"; //16:36:41+02:00 | ISO8601:2004 4.2.5.2

    //DATE TIME FORMATS
    const DATETIME_COMPLETE_BASIC_1 = "Ymd\THis"; //20130717T163658 | ISO8601:2004 4.3.2
    const DATETIME_COMPLETE_BASIC_2 = "Ymd\THis\Z"; //20130717T163743Z | ISO8601:2004 4.3.2
    const DATETIME_COMPLETE_BASIC_3 = "Ymd\THisO"; //20130717T163841+0200 | ISO8601:2004 4.3.2
    const DATETIME_COMPLETE_EXTENDED_1 = "Y-m-d\TH:i:s"; //2013-07-17T16:40:27 | ISO8601:2004 4.3.2
    const DATETIME_COMPLETE_EXTENDED_2 = "Y-m-d\TH:i:s\Z"; //2013-07-17T16:40:35Z | ISO8601:2004 4.3.2
    const DATETIME_COMPLETE_EXTENDED_3 = "Y-m-d\TH:i:sP"; //2013-07-17T16:40:47+02:00 | ISO8601:2004 4.3.2
    const DATETIME_CUSTOM_1 = "Y-m-d\ H:i";
    const DATETIME_CUSTOM_2 = "Y-m-d\ H:i:s";

    const DATETIME_REDUCED_BASIC_1 = "Ymd\THi"; //20130717T1643 | ISO8601:2004 4.3.3
    const DATETIME_REDUCED_BASIC_2 = "Ymd\THi\Z"; //20130717T1643Z | ISO8601:2004 4.3.3
    const DATETIME_REDUCED_BASIC_3 = "Ymd\THiZ"; //20130717T1643+0200 | ISO8601:2004 4.3.3
    const DATETIME_REDUCED_EXTENDED_1 = "Y-m-d\TH:i"; //2013-07-17T16:43 | ISO8601:2004 4.3.3
    const DATETIME_REDUCED_EXTENDED_2 = "Y-m-d\TH:i\Z"; //2013-07-17T16:44Z | ISO8601:2004 4.3.3
    const DATETIME_REDUCED_EXTENDED_3 = "Y-m-d\TH:iP"; //2013-07-17T16:44+02:00 | ISO8601:2004 4.3.3



    function format($value, $params = null) {

        return $this->shortDate($value, $params);
    }

    function longDateTime($value, $params = null) {
        if ($value == '0' || $value == '-' || $value == '')
            return '-';
        return $this->weekDayStringTranslated($value).', '.$this->shortDateTime($value);
    }

    function longDateTimeSec($value, $params = null) {
        if ($value == '0' || $value == '-' || $value == '')
            return '-';
        return $this->weekDayStringTranslated($value).', '.$this->shortDateTimeSec($value);
    }

    function shortDateTime($value, $params = null) {
        if ($value == '0' || $value == '-' || $value == '')
            return '-';
        return $this->shortDate($value). " ".$this->shortTime($value);
        ;
    }
    function shortDateTimeSec($value, $params = null) {
        if ($value == '0' || $value == '-' || $value == '')
            return '-';
        return $this->shortDate($value, $params). " ".$this->shortTimeSec($value, $params);
        ;
    }

    function shortDateTimeStamp($value, $params = null) {
        if ($value == '0' || $value == '-' || $value == '')
            return '-';
        return $this->iso8601_datetime6($value, $params);
        ;
    }

    function longDate($value, $params = null) {
        if ($value == '0' || $value == '-' || $value == '')
            return '-';
        return $this->weekDayStringTranslated($value).', '.$this->iso8601_date_basic1($value, $params);
    }

    function shortDate($value, $params = null) {
        if ($value == '0' || $value == '-' || $value == '')
            return '-';
        return $this->iso8601_date_basic1($value,$params);
    }

    function shortDate2($value, $params = null) {
        if ($value == '0' || $value == '-' || $value == '')
            return '-';
        return $this->iso8601_date_basic2($value,$params);
    }

    function shortTime($value, $params = null) {
        if ($value == '0' || $value == '-' || $value == '')
            return '-';

        return $this->iso8601_time4($value, $params);
    }

    function shortTimeSec($value, $params = null) {
        if ($value == '0' || $value == '-' || $value == '')
            return '-';

        return $this->iso8601_time2($value, $params);
    }

    function shortWeekDay($value, $params = null) {
        if ($value == '0' || $value == '-' || $value == '')
            return '-';
        return $this->neon_userdate($value, '%a');
    }

    function longWeekDay($value, $params = null) {
        if ($value == '0' || $value == '-' || $value == '')
            return '-';
        return $this->neon_userdate($value, '%A');
    }

    function weekDayString($value, $params = null) {
        if ($value == '0' || $value == '-' || $value == '')
            return '-';
        $value = "weekday_" . $value;
        return kuink_get_string($value, $this->nodeconfiguration['customappname']);
    }
    function weekDayStringTranslated($value, $params = null) {
        if ($value == '0' || $value == '-' || $value == '')
            return '-';
        $value = $this->iso8601_format($value, self::WEEK_DAY_STR);
        return kuink_get_string(strtolower($value), $this->nodeconfiguration['customappname']);
    }

    /**
     * Returns a formatted string that represents a date in user time
     *
     * Returns a formatted string that represents a date in user time
     * <b>WARNING: note that the format is for strftime(), not date().</b>
     * Because of a bug in most Windows time libraries, we can't use
     * the nicer %e, so we have to use %d which has leading zeroes.
     * A lot of the fuss in the function is just getting rid of these leading
     * zeroes as efficiently as possible.
     *
     * If parameter fixday = true (default), then take off leading
     * zero from %d, else maintain it.
     *
     * @package core
     * @category time
     * @param int $date the timestamp in UTC, as obtained from the database.
     * @param string $format strftime format. You should probably get this using
     *        get_string('strftime...', 'langconfig');
     * @param int|float|string  $timezone by default, uses the user's time zone. if numeric and
     *        not 99 then daylight saving will not be added.
     *        {@link http://docs.moodle.org/dev/Time_API#Timezone}
     * @param bool $fixday If true (default) then the leading zero from %d is removed.
     *        If false then the leading zero is maintained.
     * @param bool $fixhour If true (default) then the leading zero from %I is removed.
     * @return string the formatted date/time.
     */
    private function neon_userdate($date, $format = '', $timezone = 99, $fixday = true, $fixhour = true) {
        if (empty($format)) {
            $format = get_string('strftimedaydatetime', 'langconfig');
        }

        if ($fixday) {
            $formatnoday = str_replace('%d', 'DD', $format);
            $fixday = ($formatnoday != $format);
            $format = $formatnoday;
        }

        // Note: This logic about fixing 12-hour time to remove unnecessary leading
        // zero is required because on Windows, PHP strftime function does not
        // support the correct 'hour without leading zero' parameter (%l).
        if ($fixhour) {
            $formatnohour = str_replace('%I', 'HH', $format);
            $fixhour = ($formatnohour != $format);
            $format = $formatnohour;
        }

        //add daylight saving offset for string timezones only, as we can't get dst for
        //float values. if timezone is 99 (user default timezone), then try update dst.
        if ((99 == $timezone) || !is_numeric($timezone)) {
            $date += dst_offset_on($date, $timezone);
        }

        //@TODO STI: joao.patricio - fix this function to handle timezones...
        $timezone = 0; //get_user_timezone_offset($timezone);
        // If we are running under Windows convert to windows encoding and then back to UTF-8
        // (because it's impossible to specify UTF-8 to fetch locale info in Win32)

        if (abs($timezone) > 13) {   /// Server time
            $datestring = date_format_string($date, $format, $timezone);
            if ($fixday) {
                $daystring = ltrim(str_replace(array(' 0', ' '), '', strftime(' %d', $date)));
                $datestring = str_replace('DD', $daystring, $datestring);
            }
            if ($fixhour) {
                $hourstring = ltrim(str_replace(array(' 0', ' '), '', strftime(' %I', $date)));
                $datestring = str_replace('HH', $hourstring, $datestring);
            }
        } else {
            $date += (int) ($timezone * 3600);
            $datestring = date_format_string($date, $format, $timezone);
            if ($fixday) {
                $daystring = ltrim(str_replace(array(' 0', ' '), '', gmstrftime(' %d', $date)));
                $datestring = str_replace('DD', $daystring, $datestring);
            }
            if ($fixhour) {
                $hourstring = ltrim(str_replace(array(' 0', ' '), '', gmstrftime(' %I', $date)));
                $datestring = str_replace('HH', $hourstring, $datestring);
            }
        }

        return $datestring;
    }

    function iso8601_format($timestamp, $format, $as_localtime=false, $timezone='Europe/Lisbon') {
        date_default_timezone_set($timezone);
        $obj = new \DateTime();
        $obj->setTimestamp($timestamp);

        if (!$as_localtime)
            $obj->setTimezone(new \DateTimeZone('UTC'));

        $result = $obj->format($format);
        //replace time diff utc by user time diff.
        //Reason: php do not implement iso8601
        $result = str_replace((string) $obj->format("O"), date("O"), $result);
        $result = str_replace((string) $obj->format("P"), date("P"), $result);
        return $result;
    }

    function iso8601_week1($value,$params) {
        return $this->iso8601_format($value, self::WEEK_COMPLETE_BASIC);
    }
    function iso8601_week2($value,$params) {
        return $this->iso8601_format($value, self::WEEK_COMPLETE_EXTENDED);
    }
    function iso8601_week3($value,$params) {
        return $this->iso8601_format($value, self::WEEK_REDUCED_BASIC);
    }
    function iso8601_week4($value,$params) {
        return $this->iso8601_format($value, self::WEEK_REDUCED_EXTENDED);
    }

    //time
    function iso8601_time1($value,$params) {
        return $this->iso8601_format($value, self::TIME_COMPLETE_BASIC,true);
    }
    function iso8601_time2($value,$params) {
        if(isset($params['timezone']))
          return $this->iso8601_format($value, self::TIME_COMPLETE_EXTENDED,true, $params['timezone']);
        else
          return $this->iso8601_format($value, self::TIME_COMPLETE_EXTENDED,true);
    }
    function iso8601_time3($value,$params) {
        return $this->iso8601_format($value, self::TIME_REDUCED_BASIC,true);
    }
    function iso8601_time4($value,$params) {
        return $this->iso8601_format($value, self::TIME_REDUCED_EXTENDED,true);
    }
    function iso8601_time5($value,$params) {
        return $this->iso8601_format($value, self::TIME_HOUR,true);
    }

    //time UTC
    function iso8601_time_utc1($value,$params) {
        return $this->iso8601_format($value, self::TIME_UTC_COMPLETE_BASIC);
    }
    function iso8601_time_utc2($value,$params) {
        return $this->iso8601_format($value, self::TIME_UTC_COMPLETE_EXTENDED);
    }
    function iso8601_time_utc3($value,$params) {
        return $this->iso8601_format($value, self::TIME_UTC_REDUCED_BASIC);
    }
    function iso8601_time_utc4($value,$params) {
        return $this->iso8601_format($value, self::TIME_UTC_REDUCED_EXTENDED);
    }
    function iso8601_time_utc5($value,$params) {
        return $this->iso8601_format($value, self::TIME_UTC_HOUR);
    }

     //time UTC Diff
    function iso8601_time_utc_diff1($value,$params) {
        return $this->iso8601_format($value, self::TIME_UTC_DIFF_BASIC);
    }
    function iso8601_time_utc_diff2($value,$params) {
        return $this->iso8601_format($value, self::TIME_UTC_DIFF_EXTENDED);
    }


    function iso8601_datetime1($value,$params) {
        return $this->iso8601_format($value, self::DATETIME_COMPLETE_BASIC_1,true);
    }
    function iso8601_datetime2($value,$params) {
        return $this->iso8601_format($value, self::DATETIME_COMPLETE_BASIC_2);
    }
    function iso8601_datetime3($value,$params) {
        return $this->iso8601_format($value, self::DATETIME_COMPLETE_BASIC_3);
    }
    function iso8601_datetime4($value,$params) {
        return $this->iso8601_format($value, self::DATETIME_COMPLETE_EXTENDED_1,true);
    }
    function iso8601_datetime5($value,$params) {
        return $this->iso8601_format($value, self::DATETIME_COMPLETE_EXTENDED_2);
    }
    function iso8601_datetime6($value,$params) {
        return $this->iso8601_format($value, self::DATETIME_COMPLETE_EXTENDED_3);
    }

    function iso8601_datetime_reduced1($value,$params) {
        return $this->iso8601_format($value, self::DATETIME_REDUCED_BASIC_1,true);
    }
    function iso8601_datetime_reduced2($value,$params) {
        return $this->iso8601_format($value, self::DATETIME_REDUCED_BASIC_2);
    }
    function iso8601_datetime_reduced3($value,$params) {
        return $this->iso8601_format($value, self::DATETIME_REDUCED_BASIC_3);
    }
    function iso8601_datetime_reduced4($value,$params) {
        return $this->iso8601_format($value, self::DATETIME_REDUCED_EXTENDED_1,true);
    }
    function iso8601_datetime_reduced5($value,$params) {
        return $this->iso8601_format($value, self::DATETIME_REDUCED_EXTENDED_2);
    }
    function iso8601_datetime_reduced6($value,$params) {
        return $this->iso8601_format($value, self::DATETIME_REDUCED_EXTENDED_3);
    }

    function iso8601_date_basic1($value, $params){
        if(isset($params['timezone']))
          return $this->iso8601_format($value, self::DATE_BASIC_1,false, $params['timezone']);
        else
          return $this->iso8601_format($value, self::DATE_BASIC_1);
    }

    function iso8601_date_basic2($value, $params){
        return $this->iso8601_format($value, self::DATE_BASIC_2);
    }

    function dayOfWeekSeparated($value, $params){
        $days = explode(',',$value);
        $out = array();
        foreach ($days as $day){
            $out[] = kuink_get_string('dow_'.$day, $this->nodeconfiguration['customappname']);
        }
        return implode(',',$out);
    }

}

?>
