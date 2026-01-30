<?php
/**
 * Eazly Admin Framework
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( defined( 'EAZLY_ADMIN_FRAMEWORK_VERSION' ) ) {
    return;
}

define( 'EAZLY_ADMIN_FRAMEWORK_VERSION', '1.0.0' );
define( 'EAZLY_ADMIN_FRAMEWORK_PATH', __DIR__ );

require_once EAZLY_ADMIN_FRAMEWORK_PATH . '/class-admin-menu.php';
require_once EAZLY_ADMIN_FRAMEWORK_PATH . '/class-admin-assets.php';


