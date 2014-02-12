<?php
namespace App\Helper;

/**
 * API Helper
 */
class Api
{
    /**
     * Produce format-independent error response
     */
    public function errorResponse(array $errors, $status = 400)
    {
        $app = app();

        // Ensure errors are contined in 'errors' key
        if(!isset($errors['errors'])) {
            $message = array('errors' => $errors);
        } else {
            $message = $errors;
        }

        return $app->response($status, $message);
    }

    /**
     * Format given string to valid URL string
     *
     * @param string $url
     * @return string URL-safe string
     */
    public function formatUrl($string)
    {
        // Allow only alphanumerics, underscores and dashes
        $string = preg_replace('/([^a-zA-Z0-9_\-]+)/', '-', strtolower($string));
        // Replace extra spaces and dashes with single dash
        $string = preg_replace('/\s+/', '-', $string);
        $string = preg_replace('|-+|', '-', $string);
        // Trim extra dashes from beginning and end
        $string = trim($string, '-');

        return $string;
    }

    /**
     * Converts underscores to spaces and capitalizes first letter of each word
     *
     * @param string $word
     * @return string
     */
    public function formatUnderscoreWord($word)
    {
        return ucwords(str_replace('_', ' ', $word));
    }

    /**
     * Return number of pages for a given amount of records
     *
     * @param integer $records Number of total records matching query
     * @return integer Number of total pages to display all records
     */
    public function pages($records)
    {
        return ceil($records / 20);
    }

    /**
     * Group label formatting closure for view templates
     */
    public function groupLabelFormatter()
    {
        return function(array $groups) {
            $str = '';
            foreach($groups as $group) {
                $str .= '<span class="label">' . $group['name'] . '</span> ';
            }
            return $str;
        };
    }

    /**
     * Filesize Calculating function
     * Retuns the size of a file in a "human" format
     *
     * @param int $size Filesize in bytes
     * @return string Calculated filesize with units (ex. "4.58 MB")
     */
    public function formatFilesize($size)
    {
        $kb = 1024;
        $mb = 1048576;
        $gb = 1073741824;
        $tb = 1099511627776;

        if($size < $kb) {
            return $size." B";
        } else if($size < $mb) {
            return round($size/$kb,2)." KB";
        } else if($size < $gb) {
            return round($size/$mb,2)." MB";
        } else if($size < $tb) {
            return round($size/$gb,2)." GB";
        } else {
            return round($size/$tb,2)." TB";
        }
    }

    /**
     * Generate random string
     *
     * @param int $length Character length of returned random string
     * @return string Random string generated
     */
    public function randomString($length = 32, $type = 'all')
    {
        $string = "";
        if($type === 'alpha') {
            $possible = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        } else {
            $possible = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789`~!@#$%^&*()-_+=";
        }
        for($i=0;$i < $length;$i++) {
            $char = $possible[mt_rand(0, strlen($possible)-1)];
            $string .= $char;
        }
        return $string;
    }
}
