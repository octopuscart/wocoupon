<?php

date_default_timezone_set('Asia/Hong_Kong');
require("configdbconnect.php");
$configuration = $globleConnectDB;
defined('BASEPATH') OR exit('No direct script access allowed');
define('PANELVERSION', "V0.9.5.18");

//Project inforamtion
define('AUTOSKU', True);
define('SKU_PREFIX', 'JL');
define('DEFAULT_IMAGE', 'image/default_image.jpg');
define('GLOBAL_CURRENCY', $configuration['currency']);
define('SITE_NAME', $configuration['site_name']);
define('SITE_LOGO', $configuration['site_logo']);
define('SITE_URL', $configuration['site_url']);
define('PRODUCT_IMAGE_BASE', $configuration['product_images_url']);
define('PRODUCT_FOLDER', $configuration['product_folders']);
define('product_folders', $configuration['product_folders']);

define('product_image_base', $configuration['product_images_url']);

//Email Setting
define('EMAIL_SENDER', $configuration['email_sender']);
define('EMAIL_SENDER_NAME', $configuration['email_sender_name']);
define('EMAIL_BCC', $configuration['email_bcc']);





//reporting
define('PDF_HEADER', $globleConnectReport['pdf_report_header']);
define('EMAIL_HEADER', $globleConnectReport['email_header']);
define('EMAIL_FOOTER', $globleConnectReport['email_footer']);
define('MESSAGE_HEADER', $globleConnectReport['message_header']);
define('REPORT_MODE', $globleConnectReport['report_mode']);


define('SEO_TITLE', $configuration['seo_title']);
define('SEO_DESC', $configuration['seo_desc']);


define('DEFAULT_PAYMENT', $globleConnectCheckout['default_payment_mode']);

define('PRODUCT_PATH_PRE', $globleConnectCheckout['product_path_pre']);
define('PRODUCT_PATH_POST', $globleConnectCheckout['product_path_post']);

define('HEADERCSS', $globleConnectTheme['style_css']);







$baselink = 'http://' . $_SERVER['SERVER_NAME'];

if (strpos($baselink, '192.168')) {
    $islocal = true;
     $baselinkmainsite = 'http://' . $_SERVER['SERVER_NAME'] . $configuration['localpath']."/index.php/";;
} elseif (strpos($baselink, 'localhost')) {
    $islocal = true;
    $baselinkmainsite = 'http://' . $_SERVER['SERVER_NAME'] . $configuration['localpath']."/index.php/";
} else {
    $baselinkmainsite = $configuration['site_url'];
}
define('MAIN_WEBSITE', $baselinkmainsite);



/*
  |--------------------------------------------------------------------------
  | Display Debug backtrace
  |--------------------------------------------------------------------------
  |
  | If set to TRUE, a backtrace will be displayed along with php errors. If
  | error_reporting is disabled, the backtrace will not display, regardless
  | of this setting
  |
 */
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
  |--------------------------------------------------------------------------
  | File and Directory Modes
  |--------------------------------------------------------------------------
  |
  | These prefs are used when checking and setting modes when working
  | with the file system.  The defaults are fine on servers with proper
  | security, but you may wish (or even need) to change the values in
  | certain environments (Apache running a separate process for each
  | user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
  | always be used to set the mode correctly.
  |
 */
defined('FILE_READ_MODE') OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE') OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE') OR define('DIR_WRITE_MODE', 0755);

/*
  |--------------------------------------------------------------------------
  | File Stream Modes
  |--------------------------------------------------------------------------
  |
  | These modes are used when working with fopen()/popen()
  |
 */
defined('FOPEN_READ') OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE') OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE') OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE') OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE') OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE') OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT') OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT') OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
  |--------------------------------------------------------------------------
  | Exit Status Codes
  |--------------------------------------------------------------------------
  |
  | Used to indicate the conditions under which the script is exit()ing.
  | While there is no universal standard for error codes, there are some
  | broad conventions.  Three such conventions are mentioned below, for
  | those who wish to make use of them.  The CodeIgniter defaults were
  | chosen for the least overlap with these conventions, while still
  | leaving room for others to be defined in future versions and user
  | applications.
  |
  | The three main conventions used for determining exit status codes
  | are as follows:
  |
  |    Standard C/C++ Library (stdlibc):
  |       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
  |       (This link also contains other GNU-specific conventions)
  |    BSD sysexits.h:
  |       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
  |    Bash scripting:
  |       http://tldp.org/LDP/abs/html/exitcodes.html
  |
 */
defined('EXIT_SUCCESS') OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR') OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG') OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE') OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS') OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT') OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE') OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN') OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX') OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code
