<?php
/******************************************************************************************************
/  Class SDTO - Simple DateTime Object
/               A simple way to handle times and dates and to convert between
/               System and SQL Formats
/
/  (C) 2001-2004 Christian Hansel, CVH, chris@cpi-service.com
/
/  Distributed under GPL, may be distributed, modified and utilized if Copyright
/  notice is maintained and manual is provided and author is notified of modifications
/
/  Please support our development by sending remarks or comments or suggestions
/
/********************************************************************************************************/

class SimpleDateTimeObject {
     var $basetime;            // @private - holds UniX Timestamp
     var $calctime;            // @private - holds UniX Timestamp Calculations
     var $stdParamDateList;     // @private - holds StandardParameters for Dropdown-lists
     var $stdParamAdd;        // @private - holds StandardParameters for Adding/Substracting Periods

    /* Constructor */
    function SimpleDateTimeObject ($timestamp = -1) {
        $this->basetime = ($timestamp == -1) ? mktime() : $timestamp;
        $this->calctime = $this->basetime;
        $this->stdParamDateList = array( "start"     => 0,
                                         "run"         => 5,
                                         "steps"     => 1,
                                         "running"    =>"days",
                                         "format"    =>"d.M.Y H:i",
                                         "keyformat" => "" ,
                                         "style" => "");

        $this->stdParamAdd         = array( "years"=>0,
                                         "months"=>0,
                                         "days"=>0,
                                         "hours"=>0,
                                         "minutes"=>0,
                                         "seconds"=>0);
    }



    /****************************************************************************************************
    /   @public Time2Array -
    /                  Helper Function which Transforms a Time-String
    /                in SQL Format into an Array of Periods
    /    Parameters    $timestring - e.g. "-01:00:00)
    /
    /********************************************************************************************************/


    function Time2Array($timestring) {
        $timestring = preg_replace("#\s#","",$timestring);
        $timestring = preg_replace("#\:#","",$timestring);
        $timestring = preg_replace("#\.#","",$timestring);
        $timestring = preg_replace("#\-#","",$timestring);
        $timestring = preg_replace("#\/#","",$timestring);
        $fac = ("-" == substr($timestring,0,1)) ? -1 : 1;

        $timestring = ("-" == substr($timestring,0,1) || "+" == substr($timestring,0,1)) ? substr($timestring,1,strlen($timestring)-1) : $timestring;

        $array['hours']     = $fac * substr($timestring, 0,2);
        $array['minutes']     = $fac * substr($timestring, 2,2) ;
        $array['seconds']     = (4 < strlen($timestring)) ? $fac * substr($timestring, 4,2) : 0;
        return $array;
    }


    /****************************************************************************************************
    /   @public DateTime2Array -
    /                  Helper Function which Transforms a DateTime-String
    /                in SQL Format into an Array of Periods
    /    Parameters    $timestring - e.g. "12.01.2004 23:00:00)
    /
    /********************************************************************************************************/
    function DateTime2Array($timestring) {
        $timestring = preg_replace("#\s#","",$timestring);
        $timestring = preg_replace("#\:#","",$timestring);
        $timestring = preg_replace("#\.#","",$timestring);
        $timestring = preg_replace("#\/#","",$timestring);
        $timestring = preg_replace("#\-#","",$timestring);
        $fac = ("-" == substr($timestring,0,1)) ? -1 : 1;
        $timestring = ("-" == substr($timestring,0,1)) ? substr($timestring,1,strlen($timestring)-1) : $timestring;

        $array['years']     = (6==strlen($timestamp)) ? $fac * substr($timestamp,0,2): $fac * substr($timestamp,0,4);
        $array['months']     = (6==strlen($timestamp)) ? $fac * substr($timestamp,2,2): $fac * substr($timestamp,4,2);
        $array['days']        = (6==strlen($timestamp)) ? $fac * substr($timestamp,4,2): $fac * substr($timestamp,6,2);
        $array['hours']     = (12 > strlen($timestamp)) ? 0 : $fac * substr($timestamp, 8,2);
        $array['minutes']     = (12 > strlen($timestamp)) ? 0 : $fac * substr($timestamp, 10,2);
        $array['seconds']     = (14 > strlen($timestamp)) ? 0 : $fac * substr($timestamp, 12,2);
        return $array;
    }



    /****************************************************************************************************
    /   @public add -
    /                  Adds a Period to the BaseTime of the Object
    /                Returns a Unix Timestamp
    /    Parameters    $param [Array]- e.g.array("days"=>2, "months"=> 1, "hours"=>4)
    /                optional $overwrite [Bool] = true - If true the BaseTime of the
    /                                                    Object will be overwritten with
    /                                                    or adjusted to the calculated result
    /
    /********************************************************************************************************/
    function add($param = array(), $overwrite = true) {
        $base_day = date ("d",$this->basetime);
        $base_month = date ("m",$this->basetime);
        $base_year = date ("Y",$this->basetime);
        $base_hour = date ("H",$this->basetime);
        $base_minute = date ("i",$this->basetime);
        $base_second = date ("s",$this->basetime);

        $hours = (isset($param['hours']) && ! empty($param['hours'])) ? $param['hours'] : $this->stdParamAdd['hours'];
        $minutes = (isset($param['minutes']) && ! empty($param['minutes'])) ? $param['minutes'] : $this->stdParamAdd['minutes'];
        $seconds = (isset($param['seconds']) && ! empty($param['seconds'])) ? $param['seconds'] : $this->stdParamAdd['seconds'];
        $days = (isset($param['days']) && ! empty($param['days'])) ? $param['days'] : $this->stdParamAdd['days'];
        $months = (isset($param['months']) && ! empty($param['months'])) ? $param['months'] : $this->stdParamAdd['months'];
        $years = (isset($param['years']) && ! empty($param['years'])) ? $param['years'] : $this->stdParamAdd['years'];


        $this->calctime = mktime ( $base_hour      +     $hours,
                        $base_minute +     $minutes,
                        $base_second +     $seconds,
                        $base_month  +     $months,
                        $base_day      +     $days,
                        $base_year      +     $years);
        if ($overwrite) {
            $this->basetime = $this->calctime;
        }




        return $this->calctime;

    }


    /****************************************************************************************************
    /   @public sub -
    /                  Subtracts a Period from the BaseTime of the Object
    /                Returns a Unix Timestamp
    /    Parameters    $param [Array]- e.g.array("days"=>2, "months"=> 1, "hours"=>4)
    /                optional $overwrite [Bool] = true - If true the BaseTime of the
    /                                                    Object will be overwritten with
    /                                                    or adjusted to the calculated result
    /
    /********************************************************************************************************/
    function sub($param = array(), $overwrite = true) {
        $base_day = date ("d",$this->basetime);
        $base_month = date ("m",$this->basetime);
        $base_year = date ("Y",$this->basetime);
        $base_hour = date ("H",$this->basetime);
        $base_minute = date ("i",$this->basetime);
        $base_second = date ("s",$this->basetime);

        $hours = (isset($param['hours']) && ! empty($param['hours'])) ? $param['hours'] : $this->stdParamAdd['hours'];
        $minutes = (isset($param['minutes']) && ! empty($param['minutes'])) ? $param['minutes'] : $this->stdParamAdd['minutes'];
        $seconds = (isset($param['seconds']) && ! empty($param['seconds'])) ? $param['seconds'] : $this->stdParamAdd['seconds'];
        $days = (isset($param['days']) && ! empty($param['days'])) ? $param['days'] : $this->stdParamAdd['days'];
        $months = (isset($param['months']) && ! empty($param['months'])) ? $param['months'] : $this->stdParamAdd['months'];
        $years = (isset($param['years']) && ! empty($param['years'])) ? $param['years'] : $this->stdParamAdd['years'];


        $this->calctime = mktime ( $base_hour     -     $hours,
                        $base_minute -     $minutes,
                        $base_second -     $seconds,
                        $base_month  -     $months,
                        $base_day      -     $days,
                        $base_year      -     $years);
        if ($overwrite) {
            $this->basetime = $this->calctime;
        }




        return $this->calctime;

    }



    /****************************************************************************************************
    /   @public  diff_MySQL -
    /                  Calculates the Difference between a MYSQL Timestamp
    /                and the Objects Basetime which is useful for SQL commands
    /                like "SELECT DAT_ADD(mydate, INTERVAL '10 02' DAY_HOUR)
    /                or to be used in PHP/HTML : print "The time you have to do this job is ".$result['days'];
    /            returns an array like Array {     "years" => 1 ,
    /                                            "weeks" => 5 ,
    /                                            "days"  => 3 ,
    /                                            "hours" => 0,
    /                                            "minutes"  => 35 ,
    /                                            "days"  => 0 }
    /            when the Parameter $timestring lies 1 year and 5 weeks and 3 days and 35 minutes after
    /            the Basetime of the Object                                        }
    /    Parameters    $timestring - A MysQL Dat/DateTime String : e.g. "20040711132712"
    /                 $allow_negative    -  if true negative Differences will be returned as such
    /                                    otherwise differences are always positive
    /
    /********************************************************************************************************/

    function diff_MySQL($timestring, $allow_negative = false) {
        $basetime = $this->basetime;        // 2BRemembered
        $target = $this->setMySQLDateTime($timestring);
        if ($target > $basetime) {
            $diff = $target - $basetime ;
            $fac = 1;
        } else {
            $diff = $basetime-$target;
            $fac = ($allow_negative)? -1 : 1;
        }
        $diffarr['years'] = $fac * $this->extract_from_seconds($diff,"years");
        $diffarr['weeks'] =  $fac * $this->extract_from_seconds($diff,"weeks");
        $diffarr['days'] =  $fac * $this->extract_from_seconds($diff,"days");
        $diffarr['hours'] =  $fac * $this->extract_from_seconds($diff,"hours");
        $diffarr['minutes'] =  $fac * $this->extract_from_seconds($diff,"minutes");
        $diffarr['seconds'] =  $fac * $this->extract_from_seconds($diff,"seconds");
        $this->basetime = $basetime;

        return $diffarr;
    }


    /****************************************************************************************************
    /   @private / public   extract_from_seconds -
    /                  extracts full years / days / weeks / hours / or minutes from a Unix Timestamp a
    /                any period given seconds
    /            returns an integer of the time unit provided as parameter 2 (e.g. 'hours')
    /    Parameters    $seconds !!!ByRef!!!     - An integer of seconds
    /                $what (String)            - What shall be extracted ("hours"/"minutes"/"days"/"weeks"/"years")
    /
    /
    /********************************************************************************************************/

    function extract_from_seconds(&$seconds,$what) {
    switch ($what) {
        case "minutes":    case "minutes": case "minutes": case "i":
            $value = bcdiv($seconds,60);
            $seconds =  bcmod($seconds,60);
            break;
        case "hours": case "H": case "h": case "hour":
            $value = bcdiv($seconds,3600);
            $seconds =  bcmod($seconds,3600);
            break;
        case "days": case "D": case "d": case "day":
            $value         = bcdiv($seconds,3600*24);
            $seconds     = bcmod($seconds,3600*24);
            break;
        case "weeks": case "W": case "w": case "week":
            $value         = bcdiv($seconds,3600*24*7);
            $seconds     = bcmod($seconds,3600*24*7);
            break;
        case "years": case "year": case "y": case "Y":
            $value         = bcdiv($seconds,3600*24*365);
            $seconds     = bcmod($seconds,3600*24*365);
            break;
        default:
            $value = $seconds;
    }
    return $value;

    }



    /****************************************************************************************************
    /   @private / public   reset -
    /                  resets the Object's basetime to the current Systemtime
    /
    /
    /********************************************************************************************************/

    function reset() {
        $this->DateTimeObject(mktime());
    }



    /****************************************************************************************************
    /   @public   setMySQLDateTime($timestamp) -
    /                  Sets the Object's base time to the time provided by Timestring
    /                return Unix timestamp equivilant
    /    Parameters : $timestring =     String of MySQL DateTime (e.g ("20010911091200") or "2001-09-11 09:12:00")
    /
    /
    /********************************************************************************************************/

    function setMySQLDateTime($timestring) {
        $timestring .="";
        $timestring = preg_replace("#\s#","",$timestring);
        $timestring = preg_replace("#\:#","",$timestring);
        $timestring = preg_replace("#\.#","",$timestring);
        $timestring = preg_replace("#\-#","",$timestring);
        $timestring = preg_replace("#\/#","",$timestring);
        $year     = (6==strlen($timestring)) ? substr($timestring,0,2): substr($timestring,0,4);
        $month     = (6==strlen($timestring)) ? substr($timestring,2,2): substr($timestring,4,2);
        $day     = (6==strlen($timestring)) ? substr($timestring,4,2): substr($timestring,6,2);
        $hour     = (12 > strlen($timestring)) ? 0 : substr($timestring, 8,2);
        $minute = (12 > strlen($timestring)) ? 0 : substr($timestring, 10,2);
        $second = (14 > strlen($timestring)) ? 0 : substr($timestring, 12,2);

        $this->SimpleDateTimeObject(mktime($hour,$minute,$second,$month, $day,$year));
        return $this->getTimestamp();
    }



    /****************************************************************************************************
    /   @public   getMySQLDateTime() -
    /                  Returns the Object's base time in preset MySQLFormat "YmdHis"
    /
    /
    /
    /********************************************************************************************************/
    function getMySQLDateTime(){
        return date("YmdHis",$this->basetime);
    }


    /****************************************************************************************************
    /    @public   getTimestamp() -
    /                  Returns the Object's base time as Unix TimeStamp
    /
    /
    /
    /********************************************************************************************************/
    function getTimestamp() {
        return $this->basetime;
    }


    /****************************************************************************************************
    /   @public   day() -
    /                  Returns the Day of the Object's base time
    /    Paramters optional $format - specify the format of the returned value
    /                                 according to the allowed Format Strings of
    /                                 PHP 's own date command
    /             optional $timestamp - specify a time of which a part shall be returned
    /                                   if not specified the Objects BaseTime will be used
    /
    /
    /********************************************************************************************************/

    function day( $format = "d", $timestamp = 0) {
        if ($timestamp == 0) { return date ($format,$this->basetime);
        } else {return date ($format,$timestamp);}
    }
    /****************************************************************************************************
    /   @public   month() -
    /                  Returns the Month of the Object's base time
    /    Paramters optional $format - specify the format of the returned value
    /                                 according to the allowed Format Strings of
    /                                 PHP 's own date command
    /             optional $timestamp - specify a time of which a part shall be returned
    /                                   if not specified the Objects BaseTime will be used
    /
    /
    /********************************************************************************************************/
    function month ( $format = "m", $timestamp = 0) {
        if ($timestamp == 0) { return date ($format,$this->basetime);
        } else {return date ($format,$timestamp);}
    }
    /****************************************************************************************************
    /   @public  year() -
    /                  Returns the Year of the Object's base time
    /    Paramters optional $format - specify the format of the returned value
    /                                 according to the allowed Format Strings of
    /                                 PHP 's own date command
    /             optional $timestamp - specify a time of which a part shall be returned
    /                                   if not specified the Objects BaseTime will be used
    /
    /
    /********************************************************************************************************/
    function year ( $format = "Y", $timestamp = 0) {
        if ($timestamp == 0) { return date ($format,$this->basetime);
        } else {return date ($format,$timestamp);}
    }
    /****************************************************************************************************
    /   @public  hour() -
    /                  Returns the hour of the Object's base time
    /    Paramters optional $format - specify the format of the returned value
    /                                 according to the allowed Format Strings of
    /                                 PHP 's own date command
    /             optional $timestamp - specify a time of which a part shall be returned
    /                                   if not specified the Objects BaseTime will be used
    /
    /
    /********************************************************************************************************/
    function hour ( $format = "H", $timestamp = 0) {
        if ($timestamp == 0) { return date ($format,$this->basetime);
        } else {return date ($format,$timestamp);}
    }
    /****************************************************************************************************
    /   @public  minute() -
    /                  Returns the Minutes of the Object's base time
    /    Paramters optional $format - specify the format of the returned value
    /                                 according to the allowed Format Strings of
    /                                 PHP 's own date command
    /             optional $timestamp - specify a time of which a part shall be returned
    /                                   if not specified the Objects BaseTime will be used
    /
    /
    /********************************************************************************************************/
    function minute ( $format = "i", $timestamp = 0) {
        if ($timestamp == 0) { return date ($format,$this->basetime);
        } else {return date ($format,$timestamp);}
    }
    /****************************************************************************************************
    /   @public  second() -
    /                  Returns the Seconds of the Object's base time
    /    Paramters optional $format - specify the format of the returned value
    /                                 according to the allowed Format Strings of
    /                                 PHP 's own date command
    /             optional $timestamp - specify a time of which a part shall be returned
    /                                   if not specified the Objects BaseTime will be used
    /
    /
    /********************************************************************************************************/
    function second ( $format = "s", $timestamp = 0) {
        if ($timestamp == 0) { return date ($format,$this->basetime);
        } else {return date ($format,$timestamp);}
    }
    /****************************************************************************************************
    /  @public  getString -
    /                  Returns the Formated Time of the Object's base time
    /    Paramters optional $format - specify the format of the returned value
    /                                 according to the allowed Format Strings of
    /                                 PHP 's own date command
    /             optional $timestamp - specify a time of which a part shall be returned
    /                                   if not specified the Objects BaseTime will be used
    /
    /
    /********************************************************************************************************/

    function getString ( $format = "M, dS Y h:i a", $timestamp = 0) {
        if ($timestamp == 0) { return date ($format,$this->basetime);
        } else {return date ($format,$timestamp);}
    }



    /****************************************************************************************************
    / @public   dropdown
    /                   - returns a string containing a HTML DropDown Field (SELECT) based on the current basetime
    /           Parameters :
    /                   $name - Name of the HTML-Select Tag
    /                   optional $param - Array of Paramaters configuring the Select Field
                                    ['formatshow']        -     Specify the way the entries will be showed
    /                               ['format']          -   Specify the kind of Field to be produced (months / years / days ..
                                                            What will be run?
                                    ['start']           -   Specify a start value
                                                            From where will be run?
    /                               ['run']             -   Specify the number of entries to be produced
    /                                                       How far will be run?/
    /                               ['style']           -   Specify a CSS - Class to be used
    /                               ['selectsize']      -   Specify the Size of the Select Tag ( 1 Row or multirow)
    /                               ['selecteddate']    -   preselected Date
    /
    /***************************************************************************************************/
    function dropdown ($name, $param=array()) {
        $start = (isset($param['start']) && ! empty($param['start'])) ? $param['start'] : $this->stdParamAdd['start'];
        $run = (isset($param['run']) && ! empty($param['run'])) ? $param['run'] : $this->stdParamAdd['run'];
        $format = (isset($param['format']) && ! empty($param['format'])) ? $param['format'] : "d";
        $style = (isset($param['style']) && ! empty($param['style'])) ? $param['style'] : "";
        $selectsize  = (isset($param['selectsize']) && ! empty($param['selectsize'])) ? $param['selectsize'] : 1;

        $selecteddate = (isset($param['selecteddate']) && ! empty($param['selecteddate'])) ? $param['selecteddate'] : $this->basetime;

        $base_day = date ("d",$this->basetime);
        $base_month = date ("m",$this->basetime);
        $base_year = date ("Y",$this->basetime);
        $base_hour = date ("H",$this->basetime);
        $base_minute = date ("i",$this->basetime);
        $base_second = date ("s",$this->basetime);


        switch ($format) {
            case "months": case "month": case "m":
                $frm = "m"; $field = &$base_month; $base_day=1;  // As there are months which don't have 29th,30th,31st
                break;
            case "years": case "year": case "y": $base_day=1;  // As there are months which don't have 29th,30th,31st
                $frm = "Y"; $field = &$base_year;
                break;
            case "hours": case "hour": case "h":
                $frm = "H"; $field = &$base_hour;
                break;
            case "minutes": case "minute": case "i":
                $frm = "i"; $field = &$base_minute;
                break;
            case "seconds": case "second": case "s":
                $frm = "s"; $field = &$base_second;
                break;
            case "days": case "day": case "d": default:
                $frm = "d"; $field = &$base_day;
                break;

        }

        $formatshow = (isset($param['formatshow']) && ! empty($param['formatshow'])) ? $param['formatshow'] : $frm;

        $start = date($frm, $this->basetime) + $start;
        $selected = date($frm, $selecteddate);

        for($field=$start; $field<=$start+$run; $field++)
        {
             $key = date ($frm,mktime ( $base_hour, $base_minute, $base_second, $base_month, $base_day, $base_year));
            $value = date ($formatshow,mktime ( $base_hour, $base_minute, $base_second, $base_month, $base_day, $base_year));
            $outputString .= ($key == $selected) ? '  <option value="'.$key.'" selected="selected">'.$value."</option>\n" : '  <option value="'.$key.'">'.$value."</option>\n";
        }
        return  "<select name=\"".$name."\" class=\"$style\" size=\"$selectsize\">\n".$outputString."</select>\n";


    }
    /****************************************************************************************************
    / @public   datelist
    /                   - returns a string containing a HTML DropDown Field (SELECT) based on the current basetime
    /           Parameters :
    /                   $name - Name of the HTML-Select Tag
    /                   optional $param - Array of Paramaters configuring the Select Field
    /                               ['format']          -   Specify the format of the Labels in the Selectbox
                                    ['keyformat']       -   Specify the format of the value in the option tags
                                    ['running']         -   Specify iteration / What will be run ? (e.g. "hours", "years")
                                    ['start']           -   Specify a start value
                                                            From where will be run?
    /                               ['run']             -   Specify the number of entries to be produced
    /                                                       How far will be run?/
                                    ['steps']           -   Specify the distance between the entries in X format
    /                               ['style']           -   Specify a CSS - Class to be used
    /                               ['selectsize']      -   Specify the Size of the Select Tag ( 1 Row or multirow)
    /                               ['selecteddate']    -   preselected Date
    /
    /***************************************************************************************************/
    function datelist (    $name, $param = array()) {
        $base_day = date ("d",$this->basetime);
        $base_month = date ("m",$this->basetime);
        $base_year = date ("Y",$this->basetime);
        $base_hour = date ("H",$this->basetime);
        $base_minute = date ("i",$this->basetime);
        $base_second = date ("s",$this->basetime);

        $start = (isset($param['start']) && ! empty($param['start'])) ? $param['start'] : $this->stdParamAdd['start'];
        $run = (isset($param['run']) && ! empty($param['run'])) ? $param['run'] : $this->stdParamAdd['run'];
        $steps = (isset($param['steps']) && ! empty($param['steps'])) ? $param['steps'] : $this->stdParamAdd['steps'];
        $running = (isset($param['running']) && ! empty($param['running'])) ? $param['running'] : $this->stdParamAdd['running'];
        $format = (isset($param['format']) && ! empty($param['format'])) ? $param['format'] : $this->stdParamAdd['format'];
        $keyformat = (isset($param['keyformat']) && ! empty($param['keyformat'])) ? $param['keyformat'] : $this->stdParamAdd['keyformat'];
        $style = (isset($param['style']) && ! empty($param['style'])) ? $param['style'] : "";
        $selectsize  = (isset($param['selectsize']) && ! empty($param['selectsize'])) ? $param['selectsize'] : 1;
        $selecteddate = (isset($param['selecteddate']) && ! empty($param['selecteddate'])) ? $param['selecteddate'] : $this->basetime;

        $steps = ($steps<1) ? 1 : $steps;
        switch ($running) {
            case "days": case "day": case "d":
                $field = &$base_day; $frm = "d";
                break;
            case "months": case "month": case "m":
                $field = &$base_month; $frm = "m";
                break;
            case "years": case "year": case "y":
                $field = &$base_year; $frm = "Y";
                break;
            case "hours": case "hour": case "h":
                $field = &$base_hour; $frm = "H";
                break;
            case "minutes": case "minute": case "i":
                $field = &$base_minute; $frm = "i";
                break;
            case "seconds": case "second": case "s":
                $field = &$base_second; $frm = "s";
                break;
        }
        $start = date($frm, $this->basetime) + $start;
        $selected = date($keyformat, $selecteddate);

        for($field=$start; $field<=$start+$run; $field=$field+$steps)
        {
            if ("" == $keyformat) {
                $key = mktime ( $base_hour, $base_minute, $base_second, $base_month, $base_day, $base_year);
            } else { $key = date($keyformat,mktime ( $base_hour, $base_minute, $base_second, $base_month, $base_day, $base_year));     }
            $value = date($format,mktime ( $base_hour, $base_minute, $base_second, $base_month, $base_day, $base_year));

            $outputString .= ($key==$selected)? '  <option value="'.$key.'" selected="selected">'.$value."</option>\n" : '  <option value="'.$key.'">'.$value."</option>\n";

        }
        return  "<select name=\"".$name."\" class=\"$style\" size=\"$selectsize\">\n".$outputString."</select>\n";


    }
}

if (! function_exists('bcdiv')) {
    function bcdiv($divident, $divisor)  // Ganzzahldivision - ERsatz für bcdiv
    {
        return floor($divident/$divisor);
    }
}
if (! function_exists('bcmod')) {
    function bcmod($divident, $divisor) // Ganzzahldivisionsmodulo - Eratz für bcmod
    {
        return $divident - ($divisor*bcdiv($divident,$divisor));
    }
}
?>
