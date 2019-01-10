<?php

defined('BASEPATH') OR exit('No direct script access allowed');

abstract class Null_DB_utility {

    protected $db;
    protected $_list_databases = FALSE;
    protected $_optimize_table = FALSE;
    protected $_repair_table = FALSE;

    // --------------------------------------------------------------------
    public function __construct(&$db) {
        $this->db = & $db;
        log_message('info', 'Database Utility Class Initialized');
    }

    // --------------------------------------------------------------------

    public function list_databases() {
        // Is there a cached result?
        if (isset($this->db->data_cache['db_names'])) {
            return $this->db->data_cache['db_names'];
        } elseif ($this->_list_databases === FALSE) {
            return ($this->db->db_debug) ? $this->db->display_error('db_unsupported_feature') : FALSE;
        }

        $this->db->data_cache['db_names'] = array();

        $query = $this->db->query($this->_list_databases);
        if ($query === FALSE) {
            return $this->db->data_cache['db_names'];
        }

        for ($i = 0, $query = $query->result_array(), $c = count($query); $i < $c; $i++) {
            $this->db->data_cache['db_names'][] = current($query[$i]);
        }

        return $this->db->data_cache['db_names'];
    }

    // --------------------------------------------------------------------

    public function database_exists($database_name) {
        return in_array($database_name, $this->list_databases());
    }

    // --------------------------------------------------------------------

    public function optimize_table($table_name) {
        if ($this->_optimize_table === FALSE) {
            return ($this->db->db_debug) ? $this->db->display_error('db_unsupported_feature') : FALSE;
        }

        $query = $this->db->query(sprintf($this->_optimize_table, $this->db->escape_identifiers($table_name)));
        if ($query !== FALSE) {
            $query = $query->result_array();
            return current($query);
        }

        return FALSE;
    }

    // --------------------------------------------------------------------

    public function optimize_database() {
        if ($this->_optimize_table === FALSE) {
            return ($this->db->db_debug) ? $this->db->display_error('db_unsupported_feature') : FALSE;
        }

        $result = array();
        foreach ($this->db->list_tables() as $table_name) {
            $res = $this->db->query(sprintf($this->_optimize_table, $this->db->escape_identifiers($table_name)));
            if (is_bool($res)) {
                return $res;
            }

            // Build the result array...
            $res = $res->result_array();
            $res = current($res);
            $key = str_replace($this->db->database . '.', '', current($res));
            $keys = array_keys($res);
            unset($res[$keys[0]]);

            $result[$key] = $res;
        }

        return $result;
    }

    // --------------------------------------------------------------------

    public function repair_table($table_name) {
        if ($this->_repair_table === FALSE) {
            return ($this->db->db_debug) ? $this->db->display_error('db_unsupported_feature') : FALSE;
        }

        $query = $this->db->query(sprintf($this->_repair_table, $this->db->escape_identifiers($table_name)));
        if (is_bool($query)) {
            return $query;
        }

        $query = $query->result_array();
        return current($query);
    }

    // --------------------------------------------------------------------

    public function csv_from_result($query, $delim = ',', $newline = "\n", $enclosure = '"') {
        if (!is_object($query) OR ! method_exists($query, 'list_fields')) {
            show_error('You must submit a valid result object');
        }

        $out = '';
        // First generate the headings from the table column names
        foreach ($query->list_fields() as $name) {
            $out .= $enclosure . str_replace($enclosure, $enclosure . $enclosure, $name) . $enclosure . $delim;
        }

        $out = substr($out, 0, -strlen($delim)) . $newline;

        // Next blast through the result array and build out the rows
        while ($row = $query->unbuffered_row('array')) {
            $line = array();
            foreach ($row as $item) {
                $line[] = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $item) . $enclosure;
            }
            $out .= implode($delim, $line) . $newline;
        }

        return $out;
    }

    // --------------------------------------------------------------------

    public function xml_from_result($query, $params = array()) {
        if (!is_object($query) OR ! method_exists($query, 'list_fields')) {
            show_error('You must submit a valid result object');
        }

        // Set our default values
        foreach (array('root' => 'root', 'element' => 'element', 'newline' => "\n", 'tab' => "\t") as $key => $val) {
            if (!isset($params[$key])) {
                $params[$key] = $val;
            }
        }

        // Create variables for convenience
        extract($params);

        // Load the xml helper
        get_instance()->load->helper('xml');

        // Generate the result
        $xml = '<' . $root . '>' . $newline;
        while ($row = $query->unbuffered_row()) {
            $xml .= $tab . '<' . $element . '>' . $newline;
            foreach ($row as $key => $val) {
                $xml .= $tab . $tab . '<' . $key . '>' . xml_convert($val) . '</' . $key . '>' . $newline;
            }
            $xml .= $tab . '</' . $element . '>' . $newline;
        }

        return $xml . '</' . $root . '>' . $newline;
    }

    // --------------------------------------------------------------------

    public function backup($params = array()) {
        if (is_string($params)) {
            $params = array('tables' => $params);
        }

        // Set up our default preferences
        $prefs = array(
            'tables' => array(),
            'ignore' => array(),
            'filename' => '',
            'format' => 'gzip', // gzip, zip, txt
            'add_drop' => TRUE,
            'add_insert' => TRUE,
            'newline' => "\n",
            'foreign_key_checks' => TRUE
        );

        // Did the user submit any preferences? If so set them....
        if (count($params) > 0) {
            foreach ($prefs as $key => $val) {
                if (isset($params[$key])) {
                    $prefs[$key] = $params[$key];
                }
            }
        }

        // Are we backing up a complete database or individual tables?
        // If no table names were submitted we'll fetch the entire table list
        if (count($prefs['tables']) === 0) {
            $prefs['tables'] = $this->db->list_tables();
        }

        // Validate the format
        if (!in_array($prefs['format'], array('gzip', 'zip', 'txt'), TRUE)) {
            $prefs['format'] = 'txt';
        }

        // Is the encoder supported? If not, we'll either issue an
        // error or use plain text depending on the debug settings
        if (($prefs['format'] === 'gzip' && !function_exists('gzencode'))
                OR ( $prefs['format'] === 'zip' && !function_exists('gzcompress'))) {
            if ($this->db->db_debug) {
                return $this->db->display_error('db_unsupported_compression');
            }

            $prefs['format'] = 'txt';
        }

        // Was a Zip file requested?
        if ($prefs['format'] === 'zip') {
            // Set the filename if not provided (only needed with Zip files)
            if ($prefs['filename'] === '') {
                $prefs['filename'] = (count($prefs['tables']) === 1 ? $prefs['tables'] : $this->db->database)
                        . date('Y-m-d_H-i', time()) . '.sql';
            } else {
                // If they included the .zip file extension we'll remove it
                if (preg_match('|.+?\.zip$|', $prefs['filename'])) {
                    $prefs['filename'] = str_replace('.zip', '', $prefs['filename']);
                }

                // Tack on the ".sql" file extension if needed
                if (!preg_match('|.+?\.sql$|', $prefs['filename'])) {
                    $prefs['filename'] .= '.sql';
                }
            }

            // Load the Zip class and output it
            $Null = & get_instance();
            $Null->load->library('zip');
            $Null->zip->add_data($prefs['filename'], $this->_backup($prefs));
            return $Null->zip->get_zip();
        } elseif ($prefs['format'] === 'txt') { // Was a text file requested?
            return $this->_backup($prefs);
        } elseif ($prefs['format'] === 'gzip') { // Was a Gzip file requested?
            return gzencode($this->_backup($prefs));
        }

        return;
    }

}
