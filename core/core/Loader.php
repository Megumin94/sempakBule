<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Null_Loader {

    protected $_null_ob_level;
    protected $_null_view_paths = array(VIEWPATH => TRUE);
    protected $_null_library_paths = array(APPPATH, BASEPATH);
    protected $_null_model_paths = array(APPPATH);
    protected $_null_helper_paths = array(APPPATH, BASEPATH);
    protected $_null_cached_vars = array();
    protected $_null_classes = array();
    protected $_null_models = array();
    protected $_null_helpers = array();
    protected $_null_varmap = array(
        'unit_test' => 'unit',
        'user_agent' => 'agent'
    );

    // --------------------------------------------------------------------

    public function __construct() {
        $this->_null_ob_level = ob_get_level();
        $this->_null_classes = & is_loaded();

        log_message('info', 'Loader Class Initialized');
    }

    // --------------------------------------------------------------------

    public function initialize() {
        $this->_null_autoloader();
    }

    // --------------------------------------------------------------------

    public function is_loaded($class) {
        return array_search(ucfirst($class), $this->_null_classes, TRUE);
    }

    // --------------------------------------------------------------------

    public function library($library, $params = NULL, $object_name = NULL) {
        if (empty($library)) {
            return $this;
        } elseif (is_array($library)) {
            foreach ($library as $key => $value) {
                if (is_int($key)) {
                    $this->library($value, $params);
                } else {
                    $this->library($key, $params, $value);
                }
            }

            return $this;
        }

        if ($params !== NULL && !is_array($params)) {
            $params = NULL;
        }

        $this->_null_load_library($library, $params, $object_name);
        return $this;
    }

    // --------------------------------------------------------------------

    public function model($model, $name = '', $db_conn = FALSE) {
        if (empty($model)) {
            return $this;
        } elseif (is_array($model)) {
            foreach ($model as $key => $value) {
                is_int($key) ? $this->model($value, '', $db_conn) : $this->model($key, $value, $db_conn);
            }

            return $this;
        }

        $path = '';

        // Is the model in a sub-folder? If so, parse out the filename and path.
        if (($last_slash = strrpos($model, '/')) !== FALSE) {
            // The path is in front of the last slash
            $path = substr($model, 0, ++$last_slash);

            // And the model name behind it
            $model = substr($model, $last_slash);
        }

        if (empty($name)) {
            $name = $model;
        }

        if (in_array($name, $this->_null_models, TRUE)) {
            return $this;
        }

        $Null = & get_instance();
        if (isset($Null->$name)) {
            throw new RuntimeException('The model name you are loading is the name of a resource that is already being used: ' . $name);
        }

        if ($db_conn !== FALSE && !class_exists('Null_DB', FALSE)) {
            if ($db_conn === TRUE) {
                $db_conn = '';
            }

            $this->database($db_conn, FALSE, TRUE);
        }

        if (!class_exists('Null_Model', FALSE)) {
            load_class('Model', 'core');
        }

        $model = ucfirst($model);
        if (!class_exists($model)) {
            foreach ($this->_null_model_paths as $mod_path) {
                if (!file_exists($mod_path . 'models/' . $path . $model . '.php')) {
                    continue;
                }

                require_once($mod_path . 'models/' . $path . $model . '.php');
                if (!class_exists($model, FALSE)) {
                    throw new RuntimeException($mod_path . "models/" . $path . $model . ".php exists, but doesn't declare class " . $model);
                }

                break;
            }

            if (!class_exists($model, FALSE)) {
                throw new RuntimeException('Unable to locate the model you have specified: ' . $model);
            }
        } elseif (!is_subclass_of($model, 'Null_Model')) {
            throw new RuntimeException("Class " . $model . " already exists and doesn't extend Null_Model");
        }

        $this->_null_models[] = $name;
        $Null->$name = new $model();
        return $this;
    }

    // --------------------------------------------------------------------

    public function database($params = '', $return = FALSE, $query_builder = NULL) {
        // Grab the super object
        $Null = & get_instance();

        // Do we even need to load the database class?
        if ($return === FALSE && $query_builder === NULL && isset($Null->db) && is_object($Null->db) && !empty($Null->db->conn_id)) {
            return FALSE;
        }

        require_once(BASEPATH . 'database/DB.php');

        if ($return === TRUE) {
            return DB($params, $query_builder);
        }

        // Initialize the db variable. Needed to prevent
        // reference errors with some configurations
        $Null->db = '';

        // Load the DB class
        $Null->db = & DB($params, $query_builder);
        return $this;
    }

    // --------------------------------------------------------------------

    public function dbutil($db = NULL, $return = FALSE) {
        $Null = & get_instance();

        if (!is_object($db) OR ! ($db instanceof Null_DB)) {
            class_exists('Null_DB', FALSE) OR $this->database();
            $db = & $Null->db;
        }

        require_once(BASEPATH . 'database/DB_utility.php');
        require_once(BASEPATH . 'database/drivers/' . $db->dbdriver . '/' . $db->dbdriver . '_utility.php');
        $class = 'Null_DB_' . $db->dbdriver . '_utility';

        if ($return === TRUE) {
            return new $class($db);
        }

        $Null->dbutil = new $class($db);
        return $this;
    }

    // --------------------------------------------------------------------

    public function dbforge($db = NULL, $return = FALSE) {
        $Null = & get_instance();
        if (!is_object($db) OR ! ($db instanceof Null_DB)) {
            class_exists('Null_DB', FALSE) OR $this->database();
            $db = & $Null->db;
        }

        require_once(BASEPATH . 'database/DB_forge.php');
        require_once(BASEPATH . 'database/drivers/' . $db->dbdriver . '/' . $db->dbdriver . '_forge.php');

        if (!empty($db->subdriver)) {
            $driver_path = BASEPATH . 'database/drivers/' . $db->dbdriver . '/subdrivers/' . $db->dbdriver . '_' . $db->subdriver . '_forge.php';
            if (file_exists($driver_path)) {
                require_once($driver_path);
                $class = 'Null_DB_' . $db->dbdriver . '_' . $db->subdriver . '_forge';
            }
        } else {
            $class = 'Null_DB_' . $db->dbdriver . '_forge';
        }

        if ($return === TRUE) {
            return new $class($db);
        }

        $Null->dbforge = new $class($db);
        return $this;
    }

    // --------------------------------------------------------------------

    public function view($view, $vars = array(), $return = FALSE) {
        return $this->_null_load(array('_null_view' => $view, '_null_vars' => $this->_null_object_to_array($vars), '_null_return' => $return));
    }

    // --------------------------------------------------------------------

    public function file($path, $return = FALSE) {
        return $this->_null_load(array('_null_path' => $path, '_null_return' => $return));
    }

    // --------------------------------------------------------------------

    public function vars($vars, $val = '') {
        if (is_string($vars)) {
            $vars = array($vars => $val);
        }

        $vars = $this->_null_object_to_array($vars);

        if (is_array($vars) && count($vars) > 0) {
            foreach ($vars as $key => $val) {
                $this->_null_cached_vars[$key] = $val;
            }
        }

        return $this;
    }

    // --------------------------------------------------------------------

    public function clear_vars() {
        $this->_null_cached_vars = array();
        return $this;
    }

    // --------------------------------------------------------------------

    public function get_var($key) {
        return isset($this->_null_cached_vars[$key]) ? $this->_null_cached_vars[$key] : NULL;
    }

    // --------------------------------------------------------------------

    public function get_vars() {
        return $this->_null_cached_vars;
    }

    // --------------------------------------------------------------------

    public function helper($helpers = array()) {
        foreach ($this->_null_prep_filename($helpers, '_helper') as $helper) {
            if (isset($this->_null_helpers[$helper])) {
                continue;
            }

            // Is this a helper extension request?
            $ext_helper = config_item('subclass_prefix') . $helper;
            $ext_loaded = FALSE;
            foreach ($this->_null_helper_paths as $path) {
                if (file_exists($path . 'helpers/' . $ext_helper . '.php')) {
                    include_once($path . 'helpers/' . $ext_helper . '.php');
                    $ext_loaded = TRUE;
                }
            }

            // If we have loaded extensions - check if the base one is here
            if ($ext_loaded === TRUE) {
                $base_helper = BASEPATH . 'helpers/' . $helper . '.php';
                if (!file_exists($base_helper)) {
                    show_error('Unable to load the requested file: helpers/' . $helper . '.php');
                }

                include_once($base_helper);
                $this->_null_helpers[$helper] = TRUE;
                log_message('info', 'Helper loaded: ' . $helper);
                continue;
            }

            // No extensions found ... try loading regular helpers and/or overrides
            foreach ($this->_null_helper_paths as $path) {
                if (file_exists($path . 'helpers/' . $helper . '.php')) {
                    include_once($path . 'helpers/' . $helper . '.php');

                    $this->_null_helpers[$helper] = TRUE;
                    log_message('info', 'Helper loaded: ' . $helper);
                    break;
                }
            }

            // unable to load the helper
            if (!isset($this->_null_helpers[$helper])) {
                show_error('Unable to load the requested file: helpers/' . $helper . '.php');
            }
        }

        return $this;
    }

    // --------------------------------------------------------------------

    public function helpers($helpers = array()) {
        return $this->helper($helpers);
    }

    // --------------------------------------------------------------------

    public function language($files, $lang = '') {
        get_instance()->lang->load($files, $lang);
        return $this;
    }

    // --------------------------------------------------------------------

    public function config($file, $use_sections = FALSE, $fail_gracefully = FALSE) {
        return get_instance()->config->load($file, $use_sections, $fail_gracefully);
    }

    // --------------------------------------------------------------------

    public function driver($library, $params = NULL, $object_name = NULL) {
        if (is_array($library)) {
            foreach ($library as $driver) {
                $this->driver($driver);
            }

            return $this;
        } elseif (empty($library)) {
            return FALSE;
        }

        if (!class_exists('Null_Driver_Library', FALSE)) {
            // We aren't instantiating an object here, just making the base class available
            require BASEPATH . 'libraries/Driver.php';
        }

        // We can save the loader some time since Drivers will *always* be in a subfolder,
        // and typically identically named to the library
        if (!strpos($library, '/')) {
            $library = ucfirst($library) . '/' . $library;
        }

        return $this->library($library, $params, $object_name);
    }

    // --------------------------------------------------------------------

    public function add_package_path($path, $view_cascade = TRUE) {
        $path = rtrim($path, '/') . '/';

        array_unshift($this->_null_library_paths, $path);
        array_unshift($this->_null_model_paths, $path);
        array_unshift($this->_null_helper_paths, $path);

        $this->_null_view_paths = array($path . 'views/' => $view_cascade) + $this->_null_view_paths;

        // Add config file path
        $config = & $this->_null_get_component('config');
        $config->_config_paths[] = $path;

        return $this;
    }

    // --------------------------------------------------------------------

    public function get_package_paths($include_base = FALSE) {
        return ($include_base === TRUE) ? $this->_null_library_paths : $this->_null_model_paths;
    }

    // --------------------------------------------------------------------

    public function remove_package_path($path = '') {
        $config = & $this->_null_get_component('config');

        if ($path === '') {
            array_shift($this->_null_library_paths);
            array_shift($this->_null_model_paths);
            array_shift($this->_null_helper_paths);
            array_shift($this->_null_view_paths);
            array_pop($config->_config_paths);
        } else {
            $path = rtrim($path, '/') . '/';
            foreach (array('_null_library_paths', '_null_model_paths', '_null_helper_paths') as $var) {
                if (($key = array_search($path, $this->{$var})) !== FALSE) {
                    unset($this->{$var}[$key]);
                }
            }

            if (isset($this->_null_view_paths[$path . 'views/'])) {
                unset($this->_null_view_paths[$path . 'views/']);
            }

            if (($key = array_search($path, $config->_config_paths)) !== FALSE) {
                unset($config->_config_paths[$key]);
            }
        }

        // make sure the application default paths are still in the array
        $this->_null_library_paths = array_unique(array_merge($this->_null_library_paths, array(APPPATH, BASEPATH)));
        $this->_null_helper_paths = array_unique(array_merge($this->_null_helper_paths, array(APPPATH, BASEPATH)));
        $this->_null_model_paths = array_unique(array_merge($this->_null_model_paths, array(APPPATH)));
        $this->_null_view_paths = array_merge($this->_null_view_paths, array(APPPATH . 'views/' => TRUE));
        $config->_config_paths = array_unique(array_merge($config->_config_paths, array(APPPATH)));

        return $this;
    }

    // --------------------------------------------------------------------

    protected function _null_load($_null_data) {
        // Set the default data variables
        foreach (array('_null_view', '_null_vars', '_null_path', '_null_return') as $_null_val) {
            $$_null_val = isset($_null_data[$_null_val]) ? $_null_data[$_null_val] : FALSE;
        }

        $file_exists = FALSE;

        // Set the path to the requested file
        if (is_string($_null_path) && $_null_path !== '') {
            $_null_x = explode('/', $_null_path);
            $_null_file = end($_null_x);
        } else {
            $_null_ext = pathinfo($_null_view, PATHINFO_EXTENSION);
            $_null_file = ($_null_ext === '') ? $_null_view . '.php' : $_null_view;

            foreach ($this->_null_view_paths as $_null_view_file => $cascade) {
                if (file_exists($_null_view_file . $_null_file)) {
                    $_null_path = $_null_view_file . $_null_file;
                    $file_exists = TRUE;
                    break;
                }

                if (!$cascade) {
                    break;
                }
            }
        }

        if (!$file_exists && !file_exists($_null_path)) {
            show_error('Unable to load the requested file: ' . $_null_file);
        }

        // This allows anything loaded using $this->load (views, files, etc.)
        // to become accessible from within the Controller and Model functions.
        $_null_Null = & get_instance();
        foreach (get_object_vars($_null_Null) as $_null_key => $_null_var) {
            if (!isset($this->$_null_key)) {
                $this->$_null_key = & $_null_Null->$_null_key;
            }
        }

        if (is_array($_null_vars)) {
            $this->_null_cached_vars = array_merge($this->_null_cached_vars, $_null_vars);
        }
        extract($this->_null_cached_vars);

        ob_start();

        // If the PHP installation does not support short tags we'll
        // do a little string replacement, changing the short tags
        // to standard PHP echo statements.
        if (!is_php('5.4') && !ini_get('short_open_tag') && config_item('rewrite_short_tags') === TRUE) {
            echo eval('?>' . preg_replace('/;*\s*\?>/', '; ?>', str_replace('<?=', '<?php echo ', file_get_contents($_null_path))));
        } else {
            include($_null_path); // include() vs include_once() allows for multiple views with the same name
        }

        log_message('info', 'File loaded: ' . $_null_path);

        // Return the file data if requested
        if ($_null_return === TRUE) {
            $buffer = ob_get_contents();
            @ob_end_clean();
            return $buffer;
        }

        if (ob_get_level() > $this->_null_ob_level + 1) {
            ob_end_flush();
        } else {
            $_null_Null->output->append_output(ob_get_contents());
            @ob_end_clean();
        }

        return $this;
    }

    // --------------------------------------------------------------------

    protected function _null_load_library($class, $params = NULL, $object_name = NULL) {
        // Get the class name, and while we're at it trim any slashes.
        // The directory path can be included as part of the class name,
        // but we don't want a leading slash
        $class = str_replace('.php', '', trim($class, '/'));

        // Was the path included with the class name?
        // We look for a slash to determine this
        if (($last_slash = strrpos($class, '/')) !== FALSE) {
            // Extract the path
            $subdir = substr($class, 0, ++$last_slash);

            // Get the filename from the path
            $class = substr($class, $last_slash);
        } else {
            $subdir = '';
        }

        $class = ucfirst($class);

        // Is this a stock library? There are a few special conditions if so ...
        if (file_exists(BASEPATH . 'libraries/' . $subdir . $class . '.php')) {
            return $this->_null_load_stock_library($class, $subdir, $params, $object_name);
        }

        // Let's search for the requested library file and load it.
        foreach ($this->_null_library_paths as $path) {
            // BASEPATH has already been checked for
            if ($path === BASEPATH) {
                continue;
            }

            $filepath = $path . 'libraries/' . $subdir . $class . '.php';

            // Safety: Was the class already loaded by a previous call?
            if (class_exists($class, FALSE)) {
                // Before we deem this to be a duplicate request, let's see
                // if a custom object name is being supplied. If so, we'll
                // return a new instance of the object
                if ($object_name !== NULL) {
                    $Null = & get_instance();
                    if (!isset($Null->$object_name)) {
                        return $this->_null_init_library($class, '', $params, $object_name);
                    }
                }

                log_message('debug', $class . ' class already loaded. Second attempt ignored.');
                return;
            }
            // Does the file exist? No? Bummer...
            elseif (!file_exists($filepath)) {
                continue;
            }

            include_once($filepath);
            return $this->_null_init_library($class, '', $params, $object_name);
        }

        // One last attempt. Maybe the library is in a subdirectory, but it wasn't specified?
        if ($subdir === '') {
            return $this->_null_load_library($class . '/' . $class, $params, $object_name);
        }

        // If we got this far we were unable to find the requested class.
        log_message('error', 'Unable to load the requested class: ' . $class);
        show_error('Unable to load the requested class: ' . $class);
    }

    // --------------------------------------------------------------------

    protected function _null_load_stock_library($library_name, $file_path, $params, $object_name) {
        $prefix = 'Null_';

        if (class_exists($prefix . $library_name, FALSE)) {
            if (class_exists(config_item('subclass_prefix') . $library_name, FALSE)) {
                $prefix = config_item('subclass_prefix');
            }

            // Before we deem this to be a duplicate request, let's see
            // if a custom object name is being supplied. If so, we'll
            // return a new instance of the object
            if ($object_name !== NULL) {
                $Null = & get_instance();
                if (!isset($Null->$object_name)) {
                    return $this->_null_init_library($library_name, $prefix, $params, $object_name);
                }
            }

            log_message('debug', $library_name . ' class already loaded. Second attempt ignored.');
            return;
        }

        $paths = $this->_null_library_paths;
        array_pop($paths); // BASEPATH
        array_pop($paths); // APPPATH (needs to be the first path checked)
        array_unshift($paths, APPPATH);

        foreach ($paths as $path) {
            if (file_exists($path = $path . 'libraries/' . $file_path . $library_name . '.php')) {
                // Override
                include_once($path);
                if (class_exists($prefix . $library_name, FALSE)) {
                    return $this->_null_init_library($library_name, $prefix, $params, $object_name);
                } else {
                    log_message('debug', $path . ' exists, but does not declare ' . $prefix . $library_name);
                }
            }
        }

        include_once(BASEPATH . 'libraries/' . $file_path . $library_name . '.php');

        // Check for extensions
        $subclass = config_item('subclass_prefix') . $library_name;
        foreach ($paths as $path) {
            if (file_exists($path = $path . 'libraries/' . $file_path . $subclass . '.php')) {
                include_once($path);
                if (class_exists($subclass, FALSE)) {
                    $prefix = config_item('subclass_prefix');
                    break;
                } else {
                    log_message('debug', $path . ' exists, but does not declare ' . $subclass);
                }
            }
        }

        return $this->_null_init_library($library_name, $prefix, $params, $object_name);
    }

    // --------------------------------------------------------------------

    protected function _null_init_library($class, $prefix, $config = FALSE, $object_name = NULL) {
        // Is there an associated config file for this class? Note: these should always be lowercase
        if ($config === NULL) {
            // Fetch the config paths containing any package paths
            $config_component = $this->_null_get_component('config');

            if (is_array($config_component->_config_paths)) {
                $found = FALSE;
                foreach ($config_component->_config_paths as $path) {
                    // We test for both uppercase and lowercase, for servers that
                    // are case-sensitive with regard to file names. Load global first,
                    // override with environment next
                    if (file_exists($path . 'config/' . strtolower($class) . '.php')) {
                        include($path . 'config/' . strtolower($class) . '.php');
                        $found = TRUE;
                    } elseif (file_exists($path . 'config/' . ucfirst(strtolower($class)) . '.php')) {
                        include($path . 'config/' . ucfirst(strtolower($class)) . '.php');
                        $found = TRUE;
                    }

                    if (file_exists($path . 'config/' . ENVIRONMENT . '/' . strtolower($class) . '.php')) {
                        include($path . 'config/' . ENVIRONMENT . '/' . strtolower($class) . '.php');
                        $found = TRUE;
                    } elseif (file_exists($path . 'config/' . ENVIRONMENT . '/' . ucfirst(strtolower($class)) . '.php')) {
                        include($path . 'config/' . ENVIRONMENT . '/' . ucfirst(strtolower($class)) . '.php');
                        $found = TRUE;
                    }

                    // Break on the first found configuration, thus package
                    // files are not overridden by default paths
                    if ($found === TRUE) {
                        break;
                    }
                }
            }
        }

        $class_name = $prefix . $class;

        // Is the class name valid?
        if (!class_exists($class_name, FALSE)) {
            log_message('error', 'Non-existent class: ' . $class_name);
            show_error('Non-existent class: ' . $class_name);
        }

        // Set the variable name we will assign the class to
        // Was a custom class name supplied? If so we'll use it
        if (empty($object_name)) {
            $object_name = strtolower($class);
            if (isset($this->_null_varmap[$object_name])) {
                $object_name = $this->_null_varmap[$object_name];
            }
        }

        // Don't overwrite existing properties
        $Null = & get_instance();
        if (isset($Null->$object_name)) {
            if ($Null->$object_name instanceof $class_name) {
                log_message('debug', $class_name . " has already been instantiated as '" . $object_name . "'. Second attempt aborted.");
                return;
            }

            show_error("Resource '" . $object_name . "' already exists and is not a " . $class_name . " instance.");
        }

        // Save the class name and object name
        $this->_null_classes[$object_name] = $class;

        // Instantiate the class
        $Null->$object_name = isset($config) ? new $class_name($config) : new $class_name();
    }

    // --------------------------------------------------------------------

    protected function _null_autoloader() {
        if (file_exists(APPPATH . 'config/autoload.php')) {
            include(APPPATH . 'config/autoload.php');
        }

        if (file_exists(APPPATH . 'config/' . ENVIRONMENT . '/autoload.php')) {
            include(APPPATH . 'config/' . ENVIRONMENT . '/autoload.php');
        }

        if (!isset($autoload)) {
            return;
        }

        // Autoload packages
        if (isset($autoload['packages'])) {
            foreach ($autoload['packages'] as $package_path) {
                $this->add_package_path($package_path);
            }
        }

        // Load any custom config file
        if (count($autoload['config']) > 0) {
            foreach ($autoload['config'] as $val) {
                $this->config($val);
            }
        }

        // Autoload helpers and languages
        foreach (array('helper', 'language') as $type) {
            if (isset($autoload[$type]) && count($autoload[$type]) > 0) {
                $this->$type($autoload[$type]);
            }
        }

        // Autoload drivers
        if (isset($autoload['drivers'])) {
            foreach ($autoload['drivers'] as $item) {
                $this->driver($item);
            }
        }

        // Load libraries
        if (isset($autoload['libraries']) && count($autoload['libraries']) > 0) {
            // Load the database driver.
            if (in_array('database', $autoload['libraries'])) {
                $this->database();
                $autoload['libraries'] = array_diff($autoload['libraries'], array('database'));
            }

            // Load all other libraries
            $this->library($autoload['libraries']);
        }

        // Autoload models
        if (isset($autoload['model'])) {
            $this->model($autoload['model']);
        }
    }

    // --------------------------------------------------------------------

    protected function _null_object_to_array($object) {
        return is_object($object) ? get_object_vars($object) : $object;
    }

    // --------------------------------------------------------------------

    protected function &_null_get_component($component) {
        $Null = & get_instance();
        return $Null->$component;
    }

    // --------------------------------------------------------------------

    protected function _null_prep_filename($filename, $extension) {
        if (!is_array($filename)) {
            return array(strtolower(str_replace(array($extension, '.php'), '', $filename) . $extension));
        } else {
            foreach ($filename as $key => $val) {
                $filename[$key] = strtolower(str_replace(array($extension, '.php'), '', $val) . $extension);
            }

            return $filename;
        }
    }

}
