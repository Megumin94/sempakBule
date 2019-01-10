<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// ------------------------------------------------------------------------

if (!function_exists('byte_format')) {

    function byte_format($num, $precision = 1) {
        $Null = & get_instance();
        $Null->lang->load('number');

        if ($num >= 1000000000000) {
            $num = round($num / 1099511627776, $precision);
            $unit = $Null->lang->line('terabyte_abbr');
        } elseif ($num >= 1000000000) {
            $num = round($num / 1073741824, $precision);
            $unit = $Null->lang->line('gigabyte_abbr');
        } elseif ($num >= 1000000) {
            $num = round($num / 1048576, $precision);
            $unit = $Null->lang->line('megabyte_abbr');
        } elseif ($num >= 1000) {
            $num = round($num / 1024, $precision);
            $unit = $Null->lang->line('kilobyte_abbr');
        } else {
            $unit = $Null->lang->line('bytes');
            return number_format($num) . ' ' . $unit;
        }

        return number_format($num, $precision) . ' ' . $unit;
    }

}
