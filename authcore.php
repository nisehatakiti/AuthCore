<?php
/**
 * Plugin Name: AuthCore
 * Description: nisehatakiti製品群向けの共通認証エンジン／認証基盤。
 * Version: 0.1.0
 * Author: nisehatakiti
 * Author URI: https://github.com/nisehatakiti
 * License: GPL-2.0-or-later
 * Requires at least: 6.0
 * Requires PHP: 8.1
 * Text Domain: authcore
 */

defined( 'ABSPATH' ) || exit;

define( 'AUTHCORE_VERSION', '0.1.0' );
define( 'AUTHCORE_FILE', __FILE__ );
define( 'AUTHCORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'AUTHCORE_URL', plugin_dir_url( __FILE__ ) );

require_once AUTHCORE_DIR . 'src/Autoloader.php';

\AuthCore\Autoloader::register( AUTHCORE_DIR . 'src' );
\AuthCore\Plugin::boot();
