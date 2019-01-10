<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Null_Session_database_driver extends Null_Session_driver implements SessionHandlerInterface {

    protected $_db;
    protected $_row_exists = FALSE;
    protected $_platform;

    // ------------------------------------------------------------------------

    public function __construct(&$params) {
        parent::__construct($params);

        $Null = & get_instance();
        isset($Null->db) OR $Null->load->database();
        $this->_db = $Null->db;

        if (!$this->_db instanceof Null_DB_query_builder) {
            throw new Exception('Query Builder not enabled for the configured database. Aborting.');
        } elseif ($this->_db->pconnect) {
            throw new Exception('Configured database connection is persistent. Aborting.');
        } elseif ($this->_db->cache_on) {
            throw new Exception('Configured database connection has cache enabled. Aborting.');
        }

        $db_driver = $this->_db->dbdriver . (empty($this->_db->subdriver) ? '' : '_' . $this->_db->subdriver);
        if (strpos($db_driver, 'mysql') !== FALSE) {
            $this->_platform = 'mysql';
        } elseif (in_array($db_driver, array('postgre', 'pdo_pgsql'), TRUE)) {
            $this->_platform = 'postgre';
        }

        // Note: BC work-around for the old 'sess_table_name' setting, should be removed in the future.
        isset($this->_config['save_path']) OR $this->_config['save_path'] = config_item('sess_table_name');
    }

    // ------------------------------------------------------------------------

    public function open($save_path, $name) {
        return empty($this->_db->conn_id) ? (bool) $this->_db->db_connect() : TRUE;
    }

    // ------------------------------------------------------------------------

    public function read($session_id) {
        if ($this->_get_lock($session_id) !== FALSE) {
            // Needed by write() to detect session_regenerate_id() calls
            $this->_session_id = $session_id;

            $this->_db
                    ->select('data')
                    ->from($this->_config['save_path'])
                    ->where('id', $session_id);

            if ($this->_config['match_ip']) {
                $this->_db->where('ip_address', $_SERVER['REMOTE_ADDR']);
            }

            if (($result = $this->_db->get()->row()) === NULL) {
                // PHP7 will reuse the same SessionHandler object after
                // ID regeneration, so we need to explicitly set this to
                // FALSE instead of relying on the default ...
                $this->_row_exists = FALSE;
                $this->_fingerprint = md5('');
                return '';
            }

            // PostgreSQL's variant of a BLOB datatype is Bytea, which is a
            // PITA to work with, so we use base64-encoded data in a TEXT
            // field instead.
            $result = ($this->_platform === 'postgre') ? base64_decode(rtrim($result->data)) : $result->data;

            $this->_fingerprint = md5($result);
            $this->_row_exists = TRUE;
            return $result;
        }

        $this->_fingerprint = md5('');
        return '';
    }

    // ------------------------------------------------------------------------

    public function write($session_id, $session_data) {
        // Was the ID regenerated?
        if ($session_id !== $this->_session_id) {
            if (!$this->_release_lock() OR ! $this->_get_lock($session_id)) {
                return FALSE;
            }

            $this->_row_exists = FALSE;
            $this->_session_id = $session_id;
        } elseif ($this->_lock === FALSE) {
            return FALSE;
        }

        if ($this->_row_exists === FALSE) {
            $insert_data = array(
                'id' => $session_id,
                'ip_address' => $_SERVER['REMOTE_ADDR'],
                'timestamp' => time(),
                'data' => ($this->_platform === 'postgre' ? base64_encode($session_data) : $session_data)
            );

            if ($this->_db->insert($this->_config['save_path'], $insert_data)) {
                $this->_fingerprint = md5($session_data);
                return $this->_row_exists = TRUE;
            }

            return FALSE;
        }

        $this->_db->where('id', $session_id);
        if ($this->_config['match_ip']) {
            $this->_db->where('ip_address', $_SERVER['REMOTE_ADDR']);
        }

        $update_data = array('timestamp' => time());
        if ($this->_fingerprint !== md5($session_data)) {
            $update_data['data'] = ($this->_platform === 'postgre') ? base64_encode($session_data) : $session_data;
        }

        if ($this->_db->update($this->_config['save_path'], $update_data)) {
            $this->_fingerprint = md5($session_data);
            return TRUE;
        }

        return FALSE;
    }

    // ------------------------------------------------------------------------

    public function close() {
        return ($this->_lock) ? $this->_release_lock() : TRUE;
    }

    // ------------------------------------------------------------------------

    public function destroy($session_id) {
        if ($this->_lock) {
            $this->_db->where('id', $session_id);
            if ($this->_config['match_ip']) {
                $this->_db->where('ip_address', $_SERVER['REMOTE_ADDR']);
            }

            return $this->_db->delete($this->_config['save_path']) ? ($this->close() && $this->_cookie_destroy()) : FALSE;
        }

        return ($this->close() && $this->_cookie_destroy());
    }

    // ------------------------------------------------------------------------

    public function gc($maxlifetime) {
        return $this->_db->delete($this->_config['save_path'], 'timestamp < ' . (time() - $maxlifetime));
    }

    // ------------------------------------------------------------------------

    protected function _get_lock($session_id) {
        if ($this->_platform === 'mysql') {
            $arg = $session_id . ($this->_config['match_ip'] ? '_' . $_SERVER['REMOTE_ADDR'] : '');
            if ($this->_db->query("SELECT GET_LOCK('" . $arg . "', 300) AS null_session_lock")->row()->null_session_lock) {
                $this->_lock = $arg;
                return TRUE;
            }

            return FALSE;
        } elseif ($this->_platform === 'postgre') {
            $arg = "hashtext('" . $session_id . "')" . ($this->_config['match_ip'] ? ", hashtext('" . $_SERVER['REMOTE_ADDR'] . "')" : '');
            if ($this->_db->simple_query('SELECT pg_advisory_lock(' . $arg . ')')) {
                $this->_lock = $arg;
                return TRUE;
            }

            return FALSE;
        }

        return parent::_get_lock($session_id);
    }

    // ------------------------------------------------------------------------

    protected function _release_lock() {
        if (!$this->_lock) {
            return TRUE;
        }

        if ($this->_platform === 'mysql') {
            if ($this->_db->query("SELECT RELEASE_LOCK('" . $this->_lock . "') AS null_session_lock")->row()->null_session_lock) {
                $this->_lock = FALSE;
                return TRUE;
            }

            return FALSE;
        } elseif ($this->_platform === 'postgre') {
            if ($this->_db->simple_query('SELECT pg_advisory_unlock(' . $this->_lock . ')')) {
                $this->_lock = FALSE;
                return TRUE;
            }

            return FALSE;
        }

        return parent::_release_lock();
    }

}
