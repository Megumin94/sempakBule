<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Null_Form_validation {

    protected $Null;
    protected $_field_data = array();
    protected $_config_rules = array();
    protected $_error_array = array();
    protected $_error_messages = array();
    protected $_error_prefix = '<p>';
    protected $_error_suffix = '</p>';
    protected $error_string = '';
    protected $_safe_form_data = FALSE;
    public $validation_data = array();

    public function __construct($rules = array()) {
        $this->Null = & get_instance();

        // applies delimiters set in config file.
        if (isset($rules['error_prefix'])) {
            $this->_error_prefix = $rules['error_prefix'];
            unset($rules['error_prefix']);
        }
        if (isset($rules['error_suffix'])) {
            $this->_error_suffix = $rules['error_suffix'];
            unset($rules['error_suffix']);
        }

        // Validation rules can be stored in a config file.
        $this->_config_rules = $rules;

        // Automatically load the form helper
        $this->Null->load->helper('form');

        log_message('info', 'Form Validation Class Initialized');
    }

    // --------------------------------------------------------------------

    public function set_rules($field, $label = '', $rules = array(), $errors = array()) {
        // No reason to set rules if we have no POST data
        // or a validation array has not been specified
        if ($this->Null->input->method() !== 'post' && empty($this->validation_data)) {
            return $this;
        }

        // If an array was passed via the first parameter instead of individual string
        // values we cycle through it and recursively call this function.
        if (is_array($field)) {
            foreach ($field as $row) {
                // Houston, we have a problem...
                if (!isset($row['field'], $row['rules'])) {
                    continue;
                }

                // If the field label wasn't passed we use the field name
                $label = isset($row['label']) ? $row['label'] : $row['field'];

                // Add the custom error message array
                $errors = (isset($row['errors']) && is_array($row['errors'])) ? $row['errors'] : array();

                // Here we go!
                $this->set_rules($row['field'], $label, $row['rules'], $errors);
            }

            return $this;
        }

        // No fields or no rules? Nothing to do...
        if (!is_string($field) OR $field === '' OR empty($rules)) {
            return $this;
        } elseif (!is_array($rules)) {
            // BC: Convert pipe-separated rules string to an array
            if (!is_string($rules)) {
                return $this;
            }

            $rules = preg_split('/\|(?![^\[]*\])/', $rules);
        }

        // If the field label wasn't passed we use the field name
        $label = ($label === '') ? $field : $label;

        $indexes = array();

        // Is the field name an array? If it is an array, we break it apart
        // into its components so that we can fetch the corresponding POST data later
        if (($is_array = (bool) preg_match_all('/\[(.*?)\]/', $field, $matches)) === TRUE) {
            sscanf($field, '%[^[][', $indexes[0]);

            for ($i = 0, $c = count($matches[0]); $i < $c; $i++) {
                if ($matches[1][$i] !== '') {
                    $indexes[] = $matches[1][$i];
                }
            }
        }

        // Build our master array
        $this->_field_data[$field] = array(
            'field' => $field,
            'label' => $label,
            'rules' => $rules,
            'errors' => $errors,
            'is_array' => $is_array,
            'keys' => $indexes,
            'postdata' => NULL,
            'error' => ''
        );

        return $this;
    }

    // --------------------------------------------------------------------

    public function set_data(array $data) {
        if (!empty($data)) {
            $this->validation_data = $data;
        }

        return $this;
    }

    // --------------------------------------------------------------------

    public function set_message($lang, $val = '') {
        if (!is_array($lang)) {
            $lang = array($lang => $val);
        }

        $this->_error_messages = array_merge($this->_error_messages, $lang);
        return $this;
    }

    // --------------------------------------------------------------------

    public function set_error_delimiters($prefix = '<p>', $suffix = '</p>') {
        $this->_error_prefix = $prefix;
        $this->_error_suffix = $suffix;
        return $this;
    }

    // --------------------------------------------------------------------

    public function error($field, $prefix = '', $suffix = '') {
        if (empty($this->_field_data[$field]['error'])) {
            return '';
        }

        if ($prefix === '') {
            $prefix = $this->_error_prefix;
        }

        if ($suffix === '') {
            $suffix = $this->_error_suffix;
        }

        return $prefix . $this->_field_data[$field]['error'] . $suffix;
    }

    // --------------------------------------------------------------------

    public function error_array() {
        return $this->_error_array;
    }

    // --------------------------------------------------------------------

    public function error_string($prefix = '', $suffix = '') {
        // No errors, validation passes!
        if (count($this->_error_array) === 0) {
            return '';
        }

        if ($prefix === '') {
            $prefix = $this->_error_prefix;
        }

        if ($suffix === '') {
            $suffix = $this->_error_suffix;
        }

        // Generate the error string
        $str = '';
        foreach ($this->_error_array as $val) {
            if ($val !== '') {
                $str .= $prefix . $val . $suffix . "\n";
            }
        }

        return $str;
    }

    // --------------------------------------------------------------------

    public function run($group = '') {
        // Do we even have any data to process?  Mm?
        $validation_array = empty($this->validation_data) ? $_POST : $this->validation_data;
        if (count($validation_array) === 0) {
            return FALSE;
        }

        // Does the _field_data array containing the validation rules exist?
        // If not, we look to see if they were assigned via a config file
        if (count($this->_field_data) === 0) {
            // No validation rules?  We're done...
            if (count($this->_config_rules) === 0) {
                return FALSE;
            }

            if (empty($group)) {
                // Is there a validation rule for the particular URI being accessed?
                $group = trim($this->Null->uri->ruri_string(), '/');
                isset($this->_config_rules[$group]) OR $group = $this->Null->router->class . '/' . $this->Null->router->method;
            }

            $this->set_rules(isset($this->_config_rules[$group]) ? $this->_config_rules[$group] : $this->_config_rules);

            // Were we able to set the rules correctly?
            if (count($this->_field_data) === 0) {
                log_message('debug', 'Unable to find validation rules');
                return FALSE;
            }
        }

        // Load the language file containing error messages
        $this->Null->lang->load('form_validation');

        // Cycle through the rules for each field and match the corresponding $validation_data item
        foreach ($this->_field_data as $field => $row) {
            // Fetch the data from the validation_data array item and cache it in the _field_data array.
            // Depending on whether the field name is an array or a string will determine where we get it from.
            if ($row['is_array'] === TRUE) {
                $this->_field_data[$field]['postdata'] = $this->_reduce_array($validation_array, $row['keys']);
            } elseif (isset($validation_array[$field])) {
                $this->_field_data[$field]['postdata'] = $validation_array[$field];
            }
        }

        foreach ($this->_field_data as $field => $row) {
            // Don't try to validate if we have no rules set
            if (empty($row['rules'])) {
                continue;
            }

            $this->_execute($row, $row['rules'], $this->_field_data[$field]['postdata']);
        }

        // Did we end up with any errors?
        $total_errors = count($this->_error_array);
        if ($total_errors > 0) {
            $this->_safe_form_data = TRUE;
        }

        // Now we need to re-set the POST data with the new, processed data
        $this->_reset_post_array();

        return ($total_errors === 0);
    }

    // --------------------------------------------------------------------

    protected function _reduce_array($array, $keys, $i = 0) {
        if (is_array($array) && isset($keys[$i])) {
            return isset($array[$keys[$i]]) ? $this->_reduce_array($array[$keys[$i]], $keys, ($i + 1)) : NULL;
        }

        // NULL must be returned for empty fields
        return ($array === '') ? NULL : $array;
    }

    // --------------------------------------------------------------------

    protected function _reset_post_array() {
        foreach ($this->_field_data as $field => $row) {
            if ($row['postdata'] !== NULL) {
                if ($row['is_array'] === FALSE) {
                    if (isset($_POST[$row['field']])) {
                        $_POST[$row['field']] = $row['postdata'];
                    }
                } else {
                    // start with a reference
                    $post_ref = & $_POST;

                    // before we assign values, make a reference to the right POST key
                    if (count($row['keys']) === 1) {
                        $post_ref = & $post_ref[current($row['keys'])];
                    } else {
                        foreach ($row['keys'] as $val) {
                            $post_ref = & $post_ref[$val];
                        }
                    }

                    if (is_array($row['postdata'])) {
                        $array = array();
                        foreach ($row['postdata'] as $k => $v) {
                            $array[$k] = $v;
                        }

                        $post_ref = $array;
                    } else {
                        $post_ref = $row['postdata'];
                    }
                }
            }
        }
    }

    // --------------------------------------------------------------------

    protected function _execute($row, $rules, $postdata = NULL, $cycles = 0) {
        // If the $_POST data is an array we will run a recursive call
        if (is_array($postdata)) {
            foreach ($postdata as $key => $val) {
                $this->_execute($row, $rules, $val, $key);
            }

            return;
        }

        // If the field is blank, but NOT required, no further tests are necessary
        $callback = FALSE;
        if (!in_array('required', $rules) && ($postdata === NULL OR $postdata === '')) {
            // Before we bail out, does the rule contain a callback?
            foreach ($rules as &$rule) {
                if (is_string($rule)) {
                    if (strncmp($rule, 'callback_', 9) === 0) {
                        $callback = TRUE;
                        $rules = array(1 => $rule);
                        break;
                    }
                } elseif (is_callable($rule)) {
                    $callback = TRUE;
                    $rules = array(1 => $rule);
                    break;
                } elseif (is_array($rule) && isset($rule[0], $rule[1]) && is_callable($rule[1])) {
                    $callback = TRUE;
                    $rules = array(array($rule[0], $rule[1]));
                    break;
                }
            }

            if (!$callback) {
                return;
            }
        }

        // Isset Test. Typically this rule will only apply to checkboxes.
        if (($postdata === NULL OR $postdata === '') && !$callback) {
            if (in_array('isset', $rules, TRUE) OR in_array('required', $rules)) {
                // Set the message type
                $type = in_array('required', $rules) ? 'required' : 'isset';

                // Check if a custom message is defined
                if (isset($this->_field_data[$row['field']]['errors'][$type])) {
                    $line = $this->_field_data[$row['field']]['errors'][$type];
                } elseif (isset($this->_error_messages[$type])) {
                    $line = $this->_error_messages[$type];
                } elseif (FALSE === ($line = $this->Null->lang->line('form_validation_' . $type))
                        // DEPRECATED support for non-prefixed keys
                        && FALSE === ($line = $this->Null->lang->line($type, FALSE))) {
                    $line = 'The field was not set';
                }

                // Build the error message
                $message = $this->_build_error_msg($line, $this->_translate_fieldname($row['label']));

                // Save the error message
                $this->_field_data[$row['field']]['error'] = $message;

                if (!isset($this->_error_array[$row['field']])) {
                    $this->_error_array[$row['field']] = $message;
                }
            }

            return;
        }

        // --------------------------------------------------------------------
        // Cycle through each rule and run it
        foreach ($rules as $rule) {
            $_in_array = FALSE;

            // We set the $postdata variable with the current data in our master array so that
            // each cycle of the loop is dealing with the processed data from the last cycle
            if ($row['is_array'] === TRUE && is_array($this->_field_data[$row['field']]['postdata'])) {
                // We shouldn't need this safety, but just in case there isn't an array index
                // associated with this cycle we'll bail out
                if (!isset($this->_field_data[$row['field']]['postdata'][$cycles])) {
                    continue;
                }

                $postdata = $this->_field_data[$row['field']]['postdata'][$cycles];
                $_in_array = TRUE;
            } else {
                // If we get an array field, but it's not expected - then it is most likely
                // somebody messing with the form on the client side, so we'll just consider
                // it an empty field
                $postdata = is_array($this->_field_data[$row['field']]['postdata']) ? NULL : $this->_field_data[$row['field']]['postdata'];
            }

            // Is the rule a callback?
            $callback = $callable = FALSE;
            if (is_string($rule)) {
                if (strpos($rule, 'callback_') === 0) {
                    $rule = substr($rule, 9);
                    $callback = TRUE;
                }
            } elseif (is_callable($rule)) {
                $callable = TRUE;
            } elseif (is_array($rule) && isset($rule[0], $rule[1]) && is_callable($rule[1])) {
                // We have a "named" callable, so save the name
                $callable = $rule[0];
                $rule = $rule[1];
            }

            // Strip the parameter (if exists) from the rule
            // Rules can contain a parameter: max_length[5]
            $param = FALSE;
            if (!$callable && preg_match('/(.*?)\[(.*)\]/', $rule, $match)) {
                $rule = $match[1];
                $param = $match[2];
            }

            // Call the function that corresponds to the rule
            if ($callback OR $callable !== FALSE) {
                if ($callback) {
                    if (!method_exists($this->Null, $rule)) {
                        log_message('debug', 'Unable to find callback validation rule: ' . $rule);
                        $result = FALSE;
                    } else {
                        // Run the function and grab the result
                        $result = $this->Null->$rule($postdata, $param);
                    }
                } else {
                    $result = is_array($rule) ? $rule[0]->{$rule[1]}($postdata) : $rule($postdata);

                    // Is $callable set to a rule name?
                    if ($callable !== FALSE) {
                        $rule = $callable;
                    }
                }

                // Re-assign the result to the master data array
                if ($_in_array === TRUE) {
                    $this->_field_data[$row['field']]['postdata'][$cycles] = is_bool($result) ? $postdata : $result;
                } else {
                    $this->_field_data[$row['field']]['postdata'] = is_bool($result) ? $postdata : $result;
                }

                // If the field isn't required and we just processed a callback we'll move on...
                if (!in_array('required', $rules, TRUE) && $result !== FALSE) {
                    continue;
                }
            } elseif (!method_exists($this, $rule)) {
                // If our own wrapper function doesn't exist we see if a native PHP function does.
                // Users can use any native PHP function call that has one param.
                if (function_exists($rule)) {
                    // Native PHP functions issue warnings if you pass them more parameters than they use
                    $result = ($param !== FALSE) ? $rule($postdata, $param) : $rule($postdata);

                    if ($_in_array === TRUE) {
                        $this->_field_data[$row['field']]['postdata'][$cycles] = is_bool($result) ? $postdata : $result;
                    } else {
                        $this->_field_data[$row['field']]['postdata'] = is_bool($result) ? $postdata : $result;
                    }
                } else {
                    log_message('debug', 'Unable to find validation rule: ' . $rule);
                    $result = FALSE;
                }
            } else {
                $result = $this->$rule($postdata, $param);

                if ($_in_array === TRUE) {
                    $this->_field_data[$row['field']]['postdata'][$cycles] = is_bool($result) ? $postdata : $result;
                } else {
                    $this->_field_data[$row['field']]['postdata'] = is_bool($result) ? $postdata : $result;
                }
            }

            // Did the rule test negatively? If so, grab the error.
            if ($result === FALSE) {
                // Callable rules might not have named error messages
                if (!is_string($rule)) {
                    $line = $this->Null->lang->line('form_validation_error_message_not_set') . '(Anonymous function)';
                }
                // Check if a custom message is defined
                elseif (isset($this->_field_data[$row['field']]['errors'][$rule])) {
                    $line = $this->_field_data[$row['field']]['errors'][$rule];
                } elseif (!isset($this->_error_messages[$rule])) {
                    if (FALSE === ($line = $this->Null->lang->line('form_validation_' . $rule))
                            // DEPRECATED support for non-prefixed keys
                            && FALSE === ($line = $this->Null->lang->line($rule, FALSE))) {
                        $line = $this->Null->lang->line('form_validation_error_message_not_set') . '(' . $rule . ')';
                    }
                } else {
                    $line = $this->_error_messages[$rule];
                }

                // Is the parameter we are inserting into the error message the name
                // of another field? If so we need to grab its "field label"
                if (isset($this->_field_data[$param], $this->_field_data[$param]['label'])) {
                    $param = $this->_translate_fieldname($this->_field_data[$param]['label']);
                }

                // Build the error message
                $message = $this->_build_error_msg($line, $this->_translate_fieldname($row['label']), $param);

                // Save the error message
                $this->_field_data[$row['field']]['error'] = $message;

                if (!isset($this->_error_array[$row['field']])) {
                    $this->_error_array[$row['field']] = $message;
                }

                return;
            }
        }
    }

    // --------------------------------------------------------------------

    protected function _translate_fieldname($fieldname) {
        // Do we need to translate the field name? We look for the prefix 'lang:' to determine this
        // If we find one, but there's no translation for the string - just return it
        if (sscanf($fieldname, 'lang:%s', $line) === 1 && FALSE === ($fieldname = $this->Null->lang->line($line, FALSE))) {
            return $line;
        }

        return $fieldname;
    }

    // --------------------------------------------------------------------

    protected function _build_error_msg($line, $field = '', $param = '') {
        // Check for %s in the string for legacy support.
        if (strpos($line, '%s') !== FALSE) {
            return sprintf($line, $field, $param);
        }

        return str_replace(array('{field}', '{param}'), array($field, $param), $line);
    }

    // --------------------------------------------------------------------

    public function has_rule($field) {
        return isset($this->_field_data[$field]);
    }

    // --------------------------------------------------------------------

    public function set_value($field = '', $default = '') {
        if (!isset($this->_field_data[$field], $this->_field_data[$field]['postdata'])) {
            return $default;
        }

        if (is_array($this->_field_data[$field]['postdata'])) {
            return array_shift($this->_field_data[$field]['postdata']);
        }

        return $this->_field_data[$field]['postdata'];
    }

    // --------------------------------------------------------------------

    public function set_select($field = '', $value = '', $default = FALSE) {
        if (!isset($this->_field_data[$field], $this->_field_data[$field]['postdata'])) {
            return ($default === TRUE && count($this->_field_data) === 0) ? ' selected="selected"' : '';
        }

        $field = $this->_field_data[$field]['postdata'];
        $value = (string) $value;
        if (is_array($field)) {
            // Note: in_array('', array(0)) returns TRUE, do not use it
            foreach ($field as &$v) {
                if ($value === $v) {
                    return ' selected="selected"';
                }
            }

            return '';
        } elseif (($field === '' OR $value === '') OR ( $field !== $value)) {
            return '';
        }

        return ' selected="selected"';
    }

    // --------------------------------------------------------------------

    public function set_radio($field = '', $value = '', $default = FALSE) {
        if (!isset($this->_field_data[$field], $this->_field_data[$field]['postdata'])) {
            return ($default === TRUE && count($this->_field_data) === 0) ? ' checked="checked"' : '';
        }

        $field = $this->_field_data[$field]['postdata'];
        $value = (string) $value;
        if (is_array($field)) {
            // Note: in_array('', array(0)) returns TRUE, do not use it
            foreach ($field as &$v) {
                if ($value === $v) {
                    return ' checked="checked"';
                }
            }

            return '';
        } elseif (($field === '' OR $value === '') OR ( $field !== $value)) {
            return '';
        }

        return ' checked="checked"';
    }

    // --------------------------------------------------------------------

    public function set_checkbox($field = '', $value = '', $default = FALSE) {
        // Logic is exactly the same as for radio fields
        return $this->set_radio($field, $value, $default);
    }

    // --------------------------------------------------------------------

    public function required($str) {
        return is_array($str) ? (bool) count($str) : (trim($str) !== '');
    }

    // --------------------------------------------------------------------

    public function regex_match($str, $regex) {
        return (bool) preg_match($regex, $str);
    }

    // --------------------------------------------------------------------

    public function matches($str, $field) {
        return isset($this->_field_data[$field], $this->_field_data[$field]['postdata']) ? ($str === $this->_field_data[$field]['postdata']) : FALSE;
    }

    // --------------------------------------------------------------------

    public function differs($str, $field) {
        return !(isset($this->_field_data[$field]) && $this->_field_data[$field]['postdata'] === $str);
    }

    // --------------------------------------------------------------------

    public function is_unique($str, $field) {
        sscanf($field, '%[^.].%[^.]', $table, $field);
        return isset($this->Null->db) ? ($this->Null->db->limit(1)->get_where($table, array($field => $str))->num_rows() === 0) : FALSE;
    }

    // --------------------------------------------------------------------

    public function min_length($str, $val) {
        if (!is_numeric($val)) {
            return FALSE;
        }

        return ($val <= mb_strlen($str));
    }

    // --------------------------------------------------------------------

    public function max_length($str, $val) {
        if (!is_numeric($val)) {
            return FALSE;
        }

        return ($val >= mb_strlen($str));
    }

    // --------------------------------------------------------------------

    public function exact_length($str, $val) {
        if (!is_numeric($val)) {
            return FALSE;
        }

        return (mb_strlen($str) === (int) $val);
    }

    // --------------------------------------------------------------------

    public function valid_url($str) {
        if (empty($str)) {
            return FALSE;
        } elseif (preg_match('/^(?:([^:]*)\:)?\/\/(.+)$/', $str, $matches)) {
            if (empty($matches[2])) {
                return FALSE;
            } elseif (!in_array($matches[1], array('http', 'https'), TRUE)) {
                return FALSE;
            }

            $str = $matches[2];
        }

        $str = 'http://' . $str;

        if (version_compare(PHP_VERSION, '5.2.13', '==') OR version_compare(PHP_VERSION, '5.3.2', '==')) {
            sscanf($str, 'http://%[^/]', $host);
            $str = substr_replace($str, strtr($host, array('_' => '-', '-' => '_')), 7, strlen($host));
        }

        return (filter_var($str, FILTER_VALIDATE_URL) !== FALSE);
    }

    // --------------------------------------------------------------------

    public function valid_email($str) {
        if (function_exists('idn_to_ascii') && $atpos = strpos($str, '@')) {
            $str = substr($str, 0, ++$atpos) . idn_to_ascii(substr($str, $atpos));
        }

        return (bool) filter_var($str, FILTER_VALIDATE_EMAIL);
    }

    // --------------------------------------------------------------------

    public function valid_emails($str) {
        if (strpos($str, ',') === FALSE) {
            return $this->valid_email(trim($str));
        }

        foreach (explode(',', $str) as $email) {
            if (trim($email) !== '' && $this->valid_email(trim($email)) === FALSE) {
                return FALSE;
            }
        }

        return TRUE;
    }

    // --------------------------------------------------------------------

    public function valid_ip($ip, $which = '') {
        return $this->Null->input->valid_ip($ip, $which);
    }

    // --------------------------------------------------------------------

    public function alpha($str) {
        return ctype_alpha($str);
    }

    // --------------------------------------------------------------------

    public function alpha_numeric($str) {
        return ctype_alnum((string) $str);
    }

    // --------------------------------------------------------------------

    public function alpha_numeric_spaces($str) {
        return (bool) preg_match('/^[A-Z0-9 ]+$/i', $str);
    }

    // --------------------------------------------------------------------

    public function alpha_dash($str) {
        return (bool) preg_match('/^[a-z0-9_-]+$/i', $str);
    }

    // --------------------------------------------------------------------

    public function numeric($str) {
        return (bool) preg_match('/^[\-+]?[0-9]*\.?[0-9]+$/', $str);
    }

    // --------------------------------------------------------------------

    public function integer($str) {
        return (bool) preg_match('/^[\-+]?[0-9]+$/', $str);
    }

    // --------------------------------------------------------------------

    public function decimal($str) {
        return (bool) preg_match('/^[\-+]?[0-9]+\.[0-9]+$/', $str);
    }

    // --------------------------------------------------------------------

    public function greater_than($str, $min) {
        return is_numeric($str) ? ($str > $min) : FALSE;
    }

    // --------------------------------------------------------------------

    public function greater_than_equal_to($str, $min) {
        return is_numeric($str) ? ($str >= $min) : FALSE;
    }

    // --------------------------------------------------------------------

    public function less_than($str, $max) {
        return is_numeric($str) ? ($str < $max) : FALSE;
    }

    // --------------------------------------------------------------------

    public function less_than_equal_to($str, $max) {
        return is_numeric($str) ? ($str <= $max) : FALSE;
    }

    // --------------------------------------------------------------------

    public function in_list($value, $list) {
        return in_array($value, explode(',', $list), TRUE);
    }

    // --------------------------------------------------------------------

    public function is_natural($str) {
        return ctype_digit((string) $str);
    }

    // --------------------------------------------------------------------

    public function is_natural_no_zero($str) {
        return ($str != 0 && ctype_digit((string) $str));
    }

    // --------------------------------------------------------------------

    public function valid_base64($str) {
        return (base64_encode(base64_decode($str)) === $str);
    }

    // --------------------------------------------------------------------

    public function prep_for_form($data = '') {
        if ($this->_safe_form_data === FALSE OR empty($data)) {
            return $data;
        }

        if (is_array($data)) {
            foreach ($data as $key => $val) {
                $data[$key] = $this->prep_for_form($val);
            }

            return $data;
        }

        return str_replace(array("'", '"', '<', '>'), array('&#39;', '&quot;', '&lt;', '&gt;'), stripslashes($data));
    }

    // --------------------------------------------------------------------

    public function prep_url($str = '') {
        if ($str === 'http://' OR $str === '') {
            return '';
        }

        if (strpos($str, 'http://') !== 0 && strpos($str, 'https://') !== 0) {
            return 'http://' . $str;
        }

        return $str;
    }

    // --------------------------------------------------------------------

    public function strip_image_tags($str) {
        return $this->Null->security->strip_image_tags($str);
    }

    // --------------------------------------------------------------------

    public function encode_php_tags($str) {
        return str_replace(array('<?', '?>'), array('&lt;?', '?&gt;'), $str);
    }

    // --------------------------------------------------------------------

    public function reset_validation() {
        $this->_field_data = array();
        $this->_error_array = array();
        $this->_error_messages = array();
        $this->error_string = '';
        return $this;
    }

}
