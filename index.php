<?php

define('ENVIRONMENT', isset($_SERVER['Null_ENV']) ? $_SERVER['Null_ENV'] : 'development');

switch (ENVIRONMENT) {
    case 'development':
        error_reporting(-1);
        ini_set('display_errors', 1);
        break;

    case 'production':
        ini_set('display_errors', 0);
        (version_compare(PHP_VERSION, '5.3', '>=')) ? error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED) : error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
        break;

    default:
        header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
        echo 'The application environment is not set correctly.';
        exit(1); // EXIT_ERROR
}

$system_path = 'core';
$apps_folder = 'apps';
$view_folder = '';

// Set the current directory correctly for CLI requests
if (defined('STDIN')) {
    chdir(dirname(__FILE__));
}

(($_temp = realpath($system_path)) !== FALSE) ? $system_path = $_temp . '/' : $system_path = rtrim($system_path, '/') . '/'; // Ensure there's a trailing slash
// Is the system path correct?
if (!is_dir($system_path)) {
    header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
    echo 'Your system folder path does not appear to be set correctly. Please open the following file and correct this: ' . pathinfo(__FILE__, PATHINFO_BASENAME);
    exit(3); // EXIT_CONFIG
}

define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
define('BASEPATH', str_replace('\\', '/', $system_path));
define('FCPATH', dirname(__FILE__) . '/');
define('SYSDIR', trim(strrchr(trim(BASEPATH, '/'), '/'), '/'));

// The path to the "apps" folder
if (is_dir($apps_folder)) {
    if (($_temp = realpath($apps_folder)) !== FALSE) {
        $apps_folder = $_temp;
    }

    define('APPPATH', $apps_folder . DIRECTORY_SEPARATOR);
} else {
    if (!is_dir(BASEPATH . $apps_folder . DIRECTORY_SEPARATOR)) {
        header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
        echo 'Your application folder path does not appear to be set correctly. Please open the following file and correct this: ' . SELF;
        exit(3); // EXIT_CONFIG
    }

    define('APPPATH', BASEPATH . $apps_folder . DIRECTORY_SEPARATOR);
}

// The path to the "views" folder
if (!is_dir($view_folder)) {
    if (!empty($view_folder) && is_dir(APPPATH . $view_folder . DIRECTORY_SEPARATOR)) {
        $view_folder = APPPATH . $view_folder;
    } elseif (!is_dir(APPPATH . 'views' . DIRECTORY_SEPARATOR)) {
        header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
        echo 'Your view folder path does not appear to be set correctly. Please open the following file and correct this: ' . SELF;
        exit(3); // EXIT_CONFIG
    } else {
        $view_folder = APPPATH . 'views';
    }
}

(($_temp = realpath($view_folder)) !== FALSE) ? $view_folder = $_temp . DIRECTORY_SEPARATOR : $view_folder = rtrim($view_folder, '/\\') . DIRECTORY_SEPARATOR;

define('VIEWPATH', $view_folder);
require_once BASEPATH . 'core/NullComputindo.php';
