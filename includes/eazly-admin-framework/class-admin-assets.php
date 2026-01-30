<?php

namespace Eazly\Admin\Framework;

class Admin_Assets {


 private static string $base_url;
    private static string $base_path;

    public static function init( string $base_path, string $base_url ): void
    {
        self::$base_path = rtrim( $base_path, '/' ) . '/';
        self::$base_url  = rtrim( $base_url, '/' ) . '/';
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue' ] );
    }

    /**
     * Function enqueue.
     */
    public static function enqueue(): void {
        if ( empty( self::$base_url ) ) {
            return; // safety check
        }

        wp_enqueue_style(
            'eazly-admin',
            self::$base_url . 'assets/admin.css',
            [],
            EAZLY_ADMIN_FRAMEWORK_VERSION
        );

      /*  
      wp_enqueue_script(
            'eazly-admin',
            self::$base_url . 'assets/admin.js',
            [ 'jquery' ],
            EAZLY_ADMIN_FRAMEWORK_VERSION,
            true
        );
        */
    }
}
