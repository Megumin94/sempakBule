<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Null_Cache_wincache extends Null_Driver {

    public function get($id) {
        $success = FALSE;
        $data = wincache_ucache_get($id, $success);

        // Success returned by reference from wincache_ucache_get()
        return ($success) ? $data : FALSE;
    }

    // ------------------------------------------------------------------------

    public function save($id, $data, $ttl = 60, $raw = FALSE) {
        return wincache_ucache_set($id, $data, $ttl);
    }

    // ------------------------------------------------------------------------

    public function delete($id) {
        return wincache_ucache_delete($id);
    }

    // ------------------------------------------------------------------------

    public function increment($id, $offset = 1) {
        $success = FALSE;
        $value = wincache_ucache_inc($id, $offset, $success);

        return ($success === TRUE) ? $value : FALSE;
    }

    // ------------------------------------------------------------------------

    public function decrement($id, $offset = 1) {
        $success = FALSE;
        $value = wincache_ucache_dec($id, $offset, $success);

        return ($success === TRUE) ? $value : FALSE;
    }

    // ------------------------------------------------------------------------

    public function clean() {
        return wincache_ucache_clear();
    }

    // ------------------------------------------------------------------------

    public function cache_info() {
        return wincache_ucache_info(TRUE);
    }

    // ------------------------------------------------------------------------

    public function get_metadata($id) {
        if ($stored = wincache_ucache_info(FALSE, $id)) {
            $age = $stored['ucache_entries'][1]['age_seconds'];
            $ttl = $stored['ucache_entries'][1]['ttl_seconds'];
            $hitcount = $stored['ucache_entries'][1]['hitcount'];

            return array(
                'expire' => $ttl - $age,
                'hitcount' => $hitcount,
                'age' => $age,
                'ttl' => $ttl
            );
        }

        return FALSE;
    }

    // ------------------------------------------------------------------------

    public function is_supported() {
        if (!extension_loaded('wincache') OR ! ini_get('wincache.ucenabled')) {
            log_message('debug', 'The Wincache PHP extension must be loaded to use Wincache Cache.');
            return FALSE;
        }

        return TRUE;
    }

}
