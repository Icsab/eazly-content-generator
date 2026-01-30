<?php

namespace Eazly\Admin\Framework;

if (! class_exists(__NAMESPACE__ . '\Admin_Menu')) {

    class Admin_Menu
    {

        const PARENT_SLUG = 'eazly-dashboard';
        const CAPABILITY  = 'manage_options';

        protected static $textdomain = 'default';

        /**
         * Function register_plugin.
         *
         * @param array $args Description for $args.
         */
        public static function register_plugin(array $args)
        {
            if (! empty($args['textdomain'])) {
                self::$textdomain = $args['textdomain'];
            }

            add_action('admin_menu', function () use ($args) {

                if (! defined('EAZLY_ADMIN_MENU_REGISTERED')) {
                    define('EAZLY_ADMIN_MENU_REGISTERED', true);

                    add_menu_page(
                        __('Eazly Dashboard', self::$textdomain),
                        __('Eazly Plugins', self::$textdomain),
                        self::CAPABILITY,
                        self::PARENT_SLUG,
                        [__CLASS__, 'render_dashboard'],
                        self::get_menu_icon(),
                        58
                    );
                }

                add_submenu_page(
                    self::PARENT_SLUG,
                    $args['page_title'],
                    $args['menu_title'],
                    self::CAPABILITY,
                    $args['menu_slug'],
                    $args['callback']
                );

               /* remove_submenu_page(
                    self::PARENT_SLUG,
                    self::PARENT_SLUG
                );*/
            }, 99);
        }

        /**
         * Function render_dashboard.
         */
        public static function render_dashboard() {

    $plugins = self::get_eazly_plugins();

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__( 'Eazly Plugins', self::$textdomain ) . '</h1>';
    echo '<div class="eazly-plugin-grid">';

    foreach ( $plugins as $plugin ) {

        $plugin_file = $plugin['plugin_file'];
        $is_installed = file_exists( WP_PLUGIN_DIR . '/' . $plugin_file );
        $is_active = $is_installed && is_plugin_active( $plugin_file );

        if ( $is_active ) {
            $url   = admin_url( 'admin.php?page=' . $plugin['menu_slug'] );
            $label = __( 'Open', self::$textdomain );
        } elseif ( $is_installed ) {
            $url   = wp_nonce_url(
                admin_url( 'plugins.php?action=activate&plugin=' . $plugin_file ),
                'activate-plugin_' . $plugin_file
            );
            $label = __( 'Activate', self::$textdomain );
        } else {
            $url   = wp_nonce_url(
                admin_url( 'update.php?action=install-plugin&plugin=' . $plugin['wp_slug'] ),
                'install-plugin_' . $plugin['wp_slug']
            );
            $label = __( 'Install', self::$textdomain );
        }

        echo '<div class="eazly-plugin-card">';
        echo '<h2>' . esc_html( $plugin['name'] ) . '</h2>';
        echo '<p>' . esc_html( $plugin['description'] ) . '</p>';
        echo '<a class="button button-primary" href="' . esc_url( $url ) . '">'
            . esc_html( $label ) . '</a>';
        echo '</div>';
    }

    echo '</div></div>';
}


        /**
         * Retrieves the SVG icon for the admin menu.
         *
         * @return string Base64 encoded SVG icon.
         */
        private static function get_menu_icon()
        {
            $svg = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none"
        xmlns="http://www.w3.org/2000/svg">
        <path fill="currentColor"
            d="M12 2L2 7l10 5 10-5-10-5zm0 7l-10 5 10 5 10-5-10-5z"/>
    </svg>';

            return 'data:image/svg+xml;base64,' . base64_encode($svg);
        }

        /**
         * Retrieves a list of Eazly plugins with their details.
         *
         * @return array List of Eazly plugins with their properties such as slug, plugin file, name, description, menu slug, and WordPress slug.
         */
        private static function get_eazly_plugins()
        {
            return [
                [
                    'slug'        => 'eazly-content-generator',
                    'plugin_file' => 'eazly-content-generator/eazly-content-generator.php',
                    'name'        => 'Content Generator',
                    'description' => 'Quick dummy content for theme development.',
                    'menu_slug'   => 'eazly-content-generator',
                    'wp_slug'     => 'eazly-content-generator',
                ],
                [
                    'slug'        => 'eazly-weather-widget',
                    'plugin_file' => 'eazly-weather-widget/eazly-weather-widget.php',
                    'name'        => 'Weather Widget',
                    'description' => 'Display weather information in a widget.',
                    'menu_slug'   => 'eazly-weather-widget',
                    'wp_slug'     => 'eazly-weather-widget',
                ],
                // future plugins here
            ];
        }
    }
}
