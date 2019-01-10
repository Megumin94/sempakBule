<?php

defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('nl2br_except_pre')) {

    function nl2br_except_pre($str) {
        $Null = & get_instance();
        $Null->load->library('typography');
        return $Null->typography->nl2br_except_pre($str);
    }

}

// ------------------------------------------------------------------------

if (!function_exists('auto_typography')) {

    function auto_typography($str, $reduce_linebreaks = FALSE) {
        $Null = & get_instance();
        $Null->load->library('typography');
        return $Null->typography->auto_typography($str, $reduce_linebreaks);
    }

}

// --------------------------------------------------------------------

if (!function_exists('entity_decode')) {

    function entity_decode($str, $charset = NULL) {
        return get_instance()->security->entity_decode($str, $charset);
    }

}
