<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Null_Migration {

    protected $_migration_enabled = FALSE;
    protected $_migration_type = 'sequential';
    protected $_migration_path = NULL;
    protected $_migration_version = 0;
    protected $_migration_table = 'migrations';
    protected $_migration_auto_latest = FALSE;
    protected $_migration_regex = NULL;
    protected $_error_string = '';

    public function __construct($config = array()) {
        // Only run this constructor on main library load
        if (!in_array(get_class($this), array('Null_Migration', config_item('subclass_prefix') . 'Migration'), TRUE)) {
            return;
        }

        foreach ($config as $key => $val) {
            $this->{'_' . $key} = $val;
        }

        log_message('info', 'Migrations Class Initialized');

        // Are they trying to use migrations while it is disabled?
        if ($this->_migration_enabled !== TRUE) {
            show_error('Migrations has been loaded but is disabled or set up incorrectly.');
        }

        // If not set, set it
        $this->_migration_path !== '' OR $this->_migration_path = APPPATH . 'migrations/';

        // Add trailing slash if not set
        $this->_migration_path = rtrim($this->_migration_path, '/') . '/';

        // Load migration language
        $this->lang->load('migration');

        // They'll probably be using dbforge
        $this->load->dbforge();

        // Make sure the migration table name was set.
        if (empty($this->_migration_table)) {
            show_error('Migrations configuration file (migration.php) must have "migration_table" set.');
        }

        // Migration basename regex
        $this->_migration_regex = ($this->_migration_type === 'timestamp') ? '/^\d{14}_(\w+)$/' : '/^\d{3}_(\w+)$/';

        // Make sure a valid migration numbering type was set.
        if (!in_array($this->_migration_type, array('sequential', 'timestamp'))) {
            show_error('An invalid migration numbering type was specified: ' . $this->_migration_type);
        }

        // If the migrations table is missing, make it
        if (!$this->db->table_exists($this->_migration_table)) {
            $this->dbforge->add_field(array(
                'version' => array('type' => 'BIGINT', 'constraint' => 20),
            ));

            $this->dbforge->create_table($this->_migration_table, TRUE);

            $this->db->insert($this->_migration_table, array('version' => 0));
        }

        // Do we auto migrate to the latest migration?
        if ($this->_migration_auto_latest === TRUE && !$this->latest()) {
            show_error($this->error_string());
        }
    }

    // --------------------------------------------------------------------

    public function version($target_version) {
        // Note: We use strings, so that timestamp versions work on 32-bit systems
        $current_version = $this->_get_version();

        if ($this->_migration_type === 'sequential') {
            $target_version = sprintf('%03d', $target_version);
        } else {
            $target_version = (string) $target_version;
        }

        $migrations = $this->find_migrations();

        if ($target_version > 0 && !isset($migrations[$target_version])) {
            $this->_error_string = sprintf($this->lang->line('migration_not_found'), $target_version);
            return FALSE;
        }

        if ($target_version > $current_version) {
            // Moving Up
            $method = 'up';
        } else {
            // Moving Down, apply in reverse order
            $method = 'down';
            krsort($migrations);
        }

        if (empty($migrations)) {
            return TRUE;
        }

        $previous = FALSE;

        // Validate all available migrations, and run the ones within our target range
        foreach ($migrations as $number => $file) {
            // Check for sequence gaps
            if ($this->_migration_type === 'sequential' && $previous !== FALSE && abs($number - $previous) > 1) {
                $this->_error_string = sprintf($this->lang->line('migration_sequence_gap'), $number);
                return FALSE;
            }

            include_once($file);
            $class = 'Migration_' . ucfirst(strtolower($this->_get_migration_name(basename($file, '.php'))));

            // Validate the migration file structure
            if (!class_exists($class, FALSE)) {
                $this->_error_string = sprintf($this->lang->line('migration_class_doesnt_exist'), $class);
                return FALSE;
            }

            $previous = $number;

            // Run migrations that are inside the target range
            if (
                    ($method === 'up' && $number > $current_version && $number <= $target_version) OR ( $method === 'down' && $number <= $current_version && $number > $target_version)
            ) {
                $instance = new $class();
                if (!is_callable(array($instance, $method))) {
                    $this->_error_string = sprintf($this->lang->line('migration_missing_' . $method . '_method'), $class);
                    return FALSE;
                }

                log_message('debug', 'Migrating ' . $method . ' from version ' . $current_version . ' to version ' . $number);
                call_user_func(array($instance, $method));
                $current_version = $number;
                $this->_update_version($current_version);
            }
        }

        // This is necessary when moving down, since the the last migration applied
        // will be the down() method for the next migration up from the target
        if ($current_version <> $target_version) {
            $current_version = $target_version;
            $this->_update_version($current_version);
        }

        log_message('debug', 'Finished migrating to ' . $current_version);

        return $current_version;
    }

    // --------------------------------------------------------------------

    public function latest() {
        $migrations = $this->find_migrations();

        if (empty($migrations)) {
            $this->_error_string = $this->lang->line('migration_none_found');
            return FALSE;
        }

        $last_migration = basename(end($migrations));

        // Calculate the last migration step from existing migration
        // filenames and proceed to the standard version migration
        return $this->version($this->_get_migration_number($last_migration));
    }

    // --------------------------------------------------------------------

    public function current() {
        return $this->version($this->_migration_version);
    }

    // --------------------------------------------------------------------

    public function error_string() {
        return $this->_error_string;
    }

    // --------------------------------------------------------------------

    public function find_migrations() {
        $migrations = array();

        // Load all *_*.php files in the migrations path
        foreach (glob($this->_migration_path . '*_*.php') as $file) {
            $name = basename($file, '.php');

            // Filter out non-migration files
            if (preg_match($this->_migration_regex, $name)) {
                $number = $this->_get_migration_number($name);

                // There cannot be duplicate migration numbers
                if (isset($migrations[$number])) {
                    $this->_error_string = sprintf($this->lang->line('migration_multiple_version'), $number);
                    show_error($this->_error_string);
                }

                $migrations[$number] = $file;
            }
        }

        ksort($migrations);
        return $migrations;
    }

    // --------------------------------------------------------------------

    protected function _get_migration_number($migration) {
        return sscanf($migration, '%[0-9]+', $number) ? $number : '0';
    }

    // --------------------------------------------------------------------

    protected function _get_migration_name($migration) {
        $parts = explode('_', $migration);
        array_shift($parts);
        return implode('_', $parts);
    }

    // --------------------------------------------------------------------

    protected function _get_version() {
        $row = $this->db->select('version')->get($this->_migration_table)->row();
        return $row ? $row->version : '0';
    }

    // --------------------------------------------------------------------

    protected function _update_version($migration) {
        $this->db->update($this->_migration_table, array(
            'version' => $migration
        ));
    }

    // --------------------------------------------------------------------

    public function __get($var) {
        return get_instance()->$var;
    }

}
