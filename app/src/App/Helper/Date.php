<?php
namespace App\Helper;

/**
 * Date Helper
 */
class Date
{
    /**
     * Formats date into human-readable string
     *
     * @return string
     */
    public function format($date, $format = 'm/d/y h:ia')
    {
        if(empty($date)) { return $date; }
        if($date instanceof \DateTime) {
            $hasTime = $date->format('H:i:s') !== '00:00:00';
            if(!$hasTime) {
                $format = explode(' ', $format)[0];
            }
            return $date->format($format);
        }
        return date($format, strtotime($date));
    }

    /**
     * Checks is given string is a date
     *
     * @return boolean
     */
    public function isDate($date) {
        if($date instanceof \DateTime) {
            return true;
        }

        // Check strtotime + checkdate
        $ts = strtotime($date);
        if($ts !== false) {
            $month = date('m', $ts);
            $day   = date('d', $ts);
            $year  = date('Y', $ts);
            return checkdate($month, $day, $year);
        }
        return false;
    }
}
