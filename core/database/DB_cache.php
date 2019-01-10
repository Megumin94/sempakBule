<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Null_DB_Cache {

    public $Null;
    public $db;

    // --------------------------------------------------------------------

    public function __construct(&$db) {
        // Assign the main Null object to $this->Null and load the file helper since we use it a lot
        $this->Null = & get_instance();
        $this->db = & $db;
        $this->Null->load->helper('file');

        $this->check_path();
    }

    // --------------------------------------------------------------------

    public function check_path($path = '') {
        if ($path === '') {
            if ($this->db->cachedir === '') {
                return $this->db->cache_off();
            }

            $path = $this->db->cachedir;
        }

        // Add a trailing slash to the path if needed
        $path = realpath($path) ? rtrim(realpath($path), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR : rtrim($path, '/') . '/';

        if (!is_dir($path)) {
            log_message('debug', 'DB cache path error: ' . $path);

            // If the path is wrong we'll turn off caching
            return $this->db->cache_off();
        }

        if (!is_really_writable($path)) {
            log_message('debug', 'DB cache dir not writable: ' . $path);

            // If the path is not really writable we'll turn off caching
            return $this->db->cache_off();
        }

        $this->db->cachedir = $path;
        return TRUE;
    }

    // --------------------------------------------------------------------

    public function read($sql) {
        $segment_one = ($this->Null->uri->segment(1) == FALSE) ? 'default' : $this->Null->uri->segment(1);
        $segment_two = ($this->Null->uri->segment(2) == FALSE) ? 'index' : $this->Null->uri->segment(2);
        $filepath = $this->db->cachedir . $segment_one . '+' . $segment_two . '/' . md5($sql);

        if (FALSE === ($cachedata = @file_get_contents($filepath))) {
            return FALSE;
        }

        return unserialize($cachedata);
    }

    // --------------------------------------------------------------------

    public function write($sql, $object) {
        $segment_one = ($this->Null->uri->segment(1) == FALSE) ? 'default' : $this->Null->uri->segment(1);
        $segment_two = ($this->Null->uri->segment(2) == FALSE) ? 'index' : $this->Null->uri->segment(2);
        $dir_path = $this->db->cachedir . $segment_one . '+' . $segment_two . '/';
        $filename = md5($sql);

        if (!is_dir($dir_path) && !@mkdir($dir_path, 0750)) {
            return FALSE;
        }

        if (write_file($dir_path . $filename, serialize($object)) === FALSE) {
            return FALSE;
        }

        chmod($dir_path . $filename, 0640);
        return TRUE;
    }

    // --------------------------------------------------------------------

    public function delete($segment_one = '', $segment_two = '') {
        if ($segment_one === '') {
            $segment_one = ($this->Null->uri->segment(1) == FALSE) ? 'default' : $this->Null->uri->segment(1);
        }

        if ($segment_two === '') {
            $segment_two = ($this->Null->uri->segment(2) == FALSE) ? 'index' : $this->Null->uri->segment(2);
        }

        $dir_path = $this->db->cachedir . $segment_one . '+' . $segment_two . '/';
        delete_files($dir_path, TRUE);
    }

    // --------------------------------------------------------------------

    public function delete_all() {
        delete_files($this->db->cachedir, TRUE, TRUE);
    }

}