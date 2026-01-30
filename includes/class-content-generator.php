<?php
if (! defined('ABSPATH')) {
    exit;
}


use Eazly\Admin\Framework\Admin_Menu;


class Eazly_Content_Generator
{


    /**
     * Constructor
     */
    public function __construct()
    {
        Admin_Menu::register_plugin([
            'page_title' => __('Eazly Content Generator', 'eazly-content-generator'),
            'menu_title' =>  __('Eazly Content', 'eazly-content-generator'),
            'menu_slug'  => 'eazly-content-generator',
            'callback'   =>  array($this, 'render_admin_page'),
        ]);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_eazly_submit_feature', array($this, 'ajax_submit_feature'));


        add_action('wp_ajax_eazly_load_step_two', array($this, 'ajax_load_step_two'));
        add_action('wp_ajax_eazly_check_title', array($this, 'ajax_check_title'));
        add_action('wp_ajax_eazly_generate_posts', array($this, 'ajax_generate_posts'));
        add_filter('display_post_states', array($this, 'add_eazly_content_label'), 10, 2);
    }


    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook)
    {

        if ($hook !== 'eazly-plugins_page_eazly-content-generator') {
            return;
        }
        add_thickbox();
        wp_enqueue_style(
            'select2',
            EAZLY_CONTENT_GENERATOR_PLUGIN_URL . 'assets/select2.min.css',
            array(),
            '4.1.0'
        );

        wp_enqueue_style(
            'eazly-content-generator-admin',
            EAZLY_CONTENT_GENERATOR_PLUGIN_URL . 'assets/admin.css',
            array(),
            EAZLY_CONTENT_GENERATOR_VERSION
        );

        wp_enqueue_script(
            'select2',
            EAZLY_CONTENT_GENERATOR_PLUGIN_URL . 'assets/select2.min.js',
            array('jquery', 'wp-i18n'),
            '4.1.0',
            true
        );


        wp_enqueue_script(
            'eazly-content-generator-admin',
            EAZLY_CONTENT_GENERATOR_PLUGIN_URL . 'assets/admin.js',
            array('jquery', 'wp-i18n', 'select2', 'thickbox'),
            EAZLY_CONTENT_GENERATOR_VERSION,
            true
        );

        wp_localize_script(
            'eazly-content-generator-admin',
            'eazlyContent',
            array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('eazly_content_nonce'),
            )
        );

        wp_set_script_translations(
            'eazly-content-generator-admin',
            'eazly-content-generator',
            EAZLY_CONTENT_GENERATOR_PLUGIN_DIR . 'languages'
        );
    }

    /**
     * Render admin page
     */
    public function render_admin_page()
    {
        $current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'generate_content';

        $tabs = [
            'general'          => __('General', 'eazly-content-generator'),
            'custom_post_types' => __('Custom Post Types', 'eazly-content-generator'),
            'page_templates'    => __('Page Templates', 'eazly-content-generator'),
            'generate_content'  => __('Generate Content', 'eazly-content-generator'),
        ];
?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <h2 class="nav-tab-wrapper">
                <?php foreach ($tabs as $slug => $label): ?>
                    <a href="<?php echo esc_url(add_query_arg('tab', $slug)); ?>"
                        class="nav-tab <?php echo $current_tab === $slug ? 'nav-tab-active' : ''; ?>">
                        <?php echo esc_html($label); ?>
                    </a>
                <?php endforeach; ?>
            </h2>

            <div class="tab-content">
                <?php
                switch ($current_tab) {
                    case 'general':
                        $this->render_tab_general();
                        break;

                    case 'custom_post_types':
                        $this->render_tab_custom_post_types();
                        break;

                    case 'page_templates':
                        $this->render_tab_page_templates();
                        break;

                    case 'generate_content':
                    default:
                        $this->render_tab_generate_content();
                        break;
                }
                ?>
            </div>
        </div>
    <?php
    }

    protected function render_tab_general()
    {
    ?>
        <h2><?php esc_html_e('General Settings', 'eazly-content-generator'); ?></h2>


        <div class="eazly-feature-request">
            <h2><?php esc_html_e('Request a Feature', 'eazly-content-generator'); ?></h2>
            <p><?php esc_html_e('Do you have an idea to improve this plugin? Let us know!', 'eazly-content-generator'); ?></p>

            <a href="#TB_inline?width=600&height=400&inlineId=eazly-feature-form"
                class="button button-secondary thickbox">
                <?php esc_html_e('Submit Feature Request', 'eazly-content-generator'); ?>
            </a>
        </div>

        <!-- Hidden form for ThickBox -->
        <div id="eazly-feature-form" style="display:none;">
            <form id="eazly-feature-request-form">
                <?php wp_nonce_field('eazly_content_nonce', 'eazly_nonce'); ?>

                <table class="form-table">
                    <tr>
                        <th><label for="feature_name"><?php esc_html_e('Feature Name', 'eazly-content-generator'); ?></label></th>
                        <td><input type="text" id="feature_name" name="feature_name" required class="regular-text"></td>
                    </tr>

                    <tr>
                        <th><label for="feature_description"><?php esc_html_e('Description', 'eazly-content-generator'); ?></label></th>
                        <td><textarea id="feature_description" name="feature_description" rows="5" class="large-text" required></textarea></td>
                    </tr>

                    <tr>
                        <th><label for="user_email"><?php esc_html_e('Email (optional)', 'eazly-content-generator'); ?></label></th>
                        <td><input type="email" id="user_email" name="user_email" class="regular-text"></td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e('Submit Feature Request', 'eazly-content-generator'); ?>
                    </button>
                </p>

                <div id="eazly-feature-notice" style="display:none;"></div>
            </form>
        </div>

    <?php
    }
    protected function render_tab_custom_post_types()
    {
    ?>
        <h2><?php esc_html_e('Custom Post Types', 'eazly-content-generator'); ?></h2>

        <div class="eazly-tab-coming-soon">
            <p><?php esc_html_e('Coming soon! You’ll be able to manage Custom Post Types here in a future release.', 'eazly-content-generator'); ?></p>
        </div>
    <?php
    }
    protected function render_tab_page_templates()
    {
    ?>
        <h2><?php esc_html_e('Page Templates', 'eazly-content-generator'); ?></h2>
        <div class="eazly-tab-coming-soon">
            <p><?php esc_html_e('Coming soon! Page Templates management will be available in a future update.', 'eazly-content-generator'); ?></p>
        </div>
    <?php
    }

    protected function render_tab_generate_content()
    {
    ?>
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

        <!-- Notification area for AJAX messages -->
        <div id="eazly-notification" class="notice" style="display: none;">
            <p id="eazly-notification-message"></p>
        </div>

        <div id="eazly-step-one" class="eazly-step">
            <h2><?php esc_html_e('Step 1: Content Settings', 'eazly-content-generator'); ?></h2>
            <form id="eazly-form-step-one">
                <?php wp_nonce_field('eazly_content_nonce', 'eazly_nonce'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label
                                for="num_posts"><?php esc_html_e('Number of Posts', 'eazly-content-generator'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="num_posts" name="num_posts" value="3" min="1" max="100"
                                class="small-text" required>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="post_types"><?php esc_html_e('Post Types', 'eazly-content-generator'); ?></label>
                        </th>
                        <td>
                            <select id="post_types" name="post_types[]" multiple size="5" required>
                                <?php
                                $post_types = get_post_types(array('public' => true), 'objects');
                                unset($post_types['attachment']);
                                foreach ($post_types as $post_type) {
                                    printf(
                                        '<option value="%s">%s</option>',
                                        esc_attr($post_type->name),
                                        esc_html($post_type->label)
                                    );
                                }
                                ?>
                            </select>
                            <p class="description">
                                <?php esc_html_e('Hold Ctrl (or Cmd) to select multiple post types', 'eazly-content-generator'); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label
                                for="title_generation"><?php esc_html_e('Title Generation', 'eazly-content-generator'); ?></label>
                        </th>
                        <td>
                            <select id="title_generation" name="title_generation" required>
                                <option value="sequential">
                                    <?php esc_html_e('Sequential (Post1, Post2, ...)', 'eazly-content-generator'); ?>
                                </option>
                                <option value="generic">
                                    <?php esc_html_e('Generic Page Titles', 'eazly-content-generator'); ?>
                                </option>
                                <option value="lorem"><?php esc_html_e('Lorem Ipsum Title', 'eazly-content-generator'); ?>
                                </option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label
                                for="content_elements"><?php esc_html_e('Content Elements', 'eazly-content-generator'); ?></label>
                        </th>
                        <td>
                            <select id="content_elements" name="content_elements[]" multiple size="12" required>
                                <?php
                                $html_tags = array(
                                    'h1' => __('Heading 1', 'eazly-content-generator'),
                                    'h2' => __('Heading 2', 'eazly-content-generator'),
                                    'h3' => __('Heading 3', 'eazly-content-generator'),
                                    'h4' => __('Heading 4', 'eazly-content-generator'),
                                    'h5' => __('Heading 5', 'eazly-content-generator'),
                                    'h6' => __('Heading 6', 'eazly-content-generator'),
                                    'p' => __('Paragraph', 'eazly-content-generator'),
                                    'blockquote' => __('Blockquote', 'eazly-content-generator'),
                                    'ul' => __('Unordered List', 'eazly-content-generator'),
                                    'ol' => __('Ordered List', 'eazly-content-generator'),
                                    'img' => __('Featured Image', 'eazly-content-generator'),
                                    'hr' => __('Horizontal Rule', 'eazly-content-generator'),
                                );
                                foreach ($html_tags as $tag => $label) {
                                    printf(
                                        '<option value="%s">%s</option>',
                                        esc_attr($tag),
                                        esc_html($label)
                                    );
                                }
                                ?>
                            </select>
                            <p class="description">
                                <?php esc_html_e('Hold Ctrl (or Cmd) to select multiple elements', 'eazly-content-generator'); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit"
                        class="button button-primary"><?php esc_html_e('Continue to Step 2', 'eazly-content-generator'); ?></button>
                </p>
            </form>
        </div>

        <div id="eazly-step-two" class="eazly-step" style="display: none;">
            <h2><?php esc_html_e('Step 2: Configure Posts', 'eazly-content-generator'); ?></h2>
            <div id="eazly-step-two-content"></div>
        </div>


    <?php
    }


    public function ajax_submit_feature()
    {
        check_ajax_referer('eazly_content_nonce', 'eazly_nonce');

        $feature_name = sanitize_text_field($_POST['feature_name'] ?? '');
        $feature_desc = sanitize_textarea_field($_POST['feature_description'] ?? '');
        $email        = sanitize_email($_POST['user_email'] ?? '');

        $from_email = $email;

        if (empty($from_email)) {
            $site_url = home_url();
            $host = parse_url($site_url, PHP_URL_HOST);
            $from_email = 'no-reply@' . $host;
        }

        // Example: send an email to plugin author
        wp_mail('attila.gyorbiro@gmail.com', "New feature request: $feature_name", $feature_desc, [
            'From' => $from_email
        ]);

        wp_send_json_success(['message' => __('Feature request submitted. Thank you!', 'eazly-content-generator')]);
    }

    /**
     * AJAX: Load step two
     */
    public function ajax_load_step_two()
    {
        check_ajax_referer('eazly_content_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'eazly-content-generator'));
        }
        $num_posts = isset($_POST['num_posts']) ? absint($_POST['num_posts']) : 5;
        $post_types = isset($_POST['post_types']) ? array_map('sanitize_key', (array)$_POST['post_types']) : ['post'];
        $title_generation = isset($_POST['title_generation']) ? sanitize_key($_POST['title_generation']) : '';
        $content_elements = isset($_POST['content_elements']) ? array_map('sanitize_key', (array)$_POST['content_elements']) : ['h1', 'p'];

        $existing_titles = $this->get_all_existing_titles($post_types);

        ob_start();
    ?>
        <form id="eazly-form-step-two">
            <?php wp_nonce_field('eazly_content_nonce', 'eazly_nonce'); ?>
            <input type="hidden" name="title_generation" value="<?php echo esc_attr($title_generation); ?>">
            <input type="hidden" name="content_elements" value="<?php echo esc_attr(implode(',', $content_elements)); ?>">

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 40%;"><?php esc_html_e('Post Title', 'eazly-content-generator'); ?></th>
                        <th style="width: 20%;"><?php esc_html_e('Post Type', 'eazly-content-generator'); ?></th>
                        <th style="width: 20%;"><?php esc_html_e('Paragraph Count', 'eazly-content-generator'); ?></th>
                        <th style="width: 20%;"><?php esc_html_e('Status', 'eazly-content-generator'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($i = 0; $i < $num_posts; $i++): ?>
                        <?php
                        $random_post_type = $post_types[array_rand($post_types)];
                        $generated_title = $this->generate_title($title_generation, $random_post_type, $existing_titles, $i);
                        $existing_titles[] = $generated_title;
                        ?>
                        <tr>
                            <td>
                                <input type="text" name="posts[<?php echo esc_attr($i); ?>][title]" class="eazly-post-title widefat"
                                    data-index="<?php echo esc_attr($i); ?>" value="<?php echo esc_attr($generated_title); ?>"
                                    required>
                            </td>
                            <td>
                                <select name="posts[<?php echo esc_attr($i); ?>][post_type]" class="eazly-post-type"
                                    data-index="<?php echo esc_attr($i); ?>" required>
                                    <?php
                                    $all_post_types = get_post_types(array('public' => true), 'objects');
                                    unset($all_post_types['attachment']);
                                    foreach ($post_types as $pt):
                                        if (isset($all_post_types[$pt])):
                                    ?>
                                            <option value="<?php echo esc_attr($pt); ?>" <?php selected($pt, $random_post_type); ?>>
                                                <?php echo esc_html($all_post_types[$pt]->label); ?>
                                            </option>
                                    <?php
                                        endif;
                                    endforeach;
                                    ?>
                                </select>
                            </td>
                            <td>
                                <input type="number" name="posts[<?php echo esc_attr($i); ?>][paragraph_count]" value="5" min="1"
                                    max="50" class="small-text" required>
                            </td>
                            <td>
                                <span class="eazly-status dashicons dashicons-yes" data-index="<?php echo esc_attr($i); ?>"
                                    style="color: #46b450;"></span>
                            </td>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>

            <p class="submit">
                <button type="button" id="eazly-back-button"
                    class="button"><?php esc_html_e('Back to Step 1', 'eazly-content-generator'); ?></button>
                <button type="submit" id="eazly-generate-button"
                    class="button button-primary"><?php esc_html_e('Generate Posts', 'eazly-content-generator'); ?></button>
            </p>
        </form>
<?php
        $html = ob_get_clean();

        wp_send_json_success(array('html' => $html));
    }

    /**
     * AJAX: Check title availability
     */
    public function ajax_check_title()
    {
        check_ajax_referer('eazly_content_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized', 403);
        }

        $title = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '';
        $post_type = isset($_POST['post_type']) ? sanitize_text_field(wp_unslash($_POST['post_type'])) : 'post';

        if (empty($title)) {
            wp_send_json_success(['available' => false]);
        }

        // Check across all public post types
        $all_post_types = get_post_types(['public' => true]);
        $query = new WP_Query([
            'post_type' => $all_post_types,
            'title' => $title,
            'posts_per_page' => 1,
            'fields' => 'ids'
        ]);

        if ($query->have_posts()) {
            $existing_id = $query->posts[0];
            $existing_post = get_post($existing_id);

            wp_send_json_success([
                'available' => false,
                'same_type' => ($existing_post->post_type === $post_type),
                'existing_type' => $existing_post->post_type,
                'existing_id' => $existing_id,
                'edit_link' => get_edit_post_link($existing_id, ''),
                'permalink' => get_permalink($existing_id)
            ]);
        }

        wp_send_json_success(['available' => true]);
    }

    /**
     * AJAX: Generate posts
     */
    public function ajax_generate_posts()
    {
        check_ajax_referer('eazly_content_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'eazly-content-generator'));
        }
        $posts = [];

        if (isset($_POST['posts']) && is_array($_POST['posts'])) {
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized individually below.
            $posts_raw = wp_unslash($_POST['posts']);

            foreach ($posts_raw as $post) {

                if (! is_array($post)) {
                    continue;
                }

                $posts[] = [
                    'title'           => isset($post['title']) ? sanitize_text_field($post['title']) : '',
                    'post_type'       => isset($post['post_type']) ? sanitize_key($post['post_type']) : '',
                    'paragraph_count' => isset($post['paragraph_count']) ? absint($post['paragraph_count']) : 0,
                ];
            }
        }

        if (isset($_POST['content_elements'])) {
            $content_elements_raw = sanitize_text_field(wp_unslash($_POST['content_elements']));
            $content_elements = array_map('sanitize_key', explode(',', $content_elements_raw));
        } else {
            $content_elements = [];
        }
        $created_posts = array();
        $errors = array();

        foreach ($posts as $post_data) {
            $title = sanitize_text_field($post_data['title']);
            $post_type = sanitize_key($post_data['post_type']);
            $paragraph_count = absint($post_data['paragraph_count']);

            // Check if featured image is requested
            $set_featured_image = in_array('img', $content_elements, true);

            $content = $this->generate_content($content_elements, $paragraph_count);

            $post_id = wp_insert_post(
                array(
                    'post_title' => $title,
                    'post_content' => $content,
                    'post_status' => 'publish',
                    'post_type' => $post_type,
                )
            );

            if (!is_wp_error($post_id)) {
                // Mark this post as dummy content
                update_post_meta($post_id, '_eazly_dummy_content', '1');

                // Set featured image if requested
                if ($set_featured_image) {
                    $this->set_featured_image_for_post($post_id);
                }

                $created_posts[] = array(
                    'id' => $post_id,
                    'title' => $title,
                    'url' => get_edit_post_link($post_id, 'raw'),
                );
            } else {
                $errors[] = array(
                    'title' => $title,
                    'error' => $post_id->get_error_message(),
                );
            }
        }

        wp_send_json_success(
            array(
                'message' => sprintf(
                    /* translators: %d: number of posts created */
                    _n('%d post created successfully!', '%d posts created successfully!', count($created_posts), 'eazly-content-generator'),
                    count($created_posts)
                ),
                'posts' => $created_posts,
            )
        );
    }

    /**
     * Get all existing titles for given post types
     */
    private function get_all_existing_titles($post_types)
    {
        if (empty($post_types) || ! is_array($post_types)) {
            return [];
        }

        $post_types = array_map('sanitize_key', $post_types);

        $query = new WP_Query([
            'post_type'      => $post_types,
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ]);

        if (empty($query->posts)) {
            return [];
        }

        $titles = [];

        foreach ($query->posts as $post_id) {
            $titles[] = get_the_title($post_id);
        }

        return $titles;
    }

    /**
     * Generate title based on method
     */
    private function generate_title($method, $post_type, $existing_titles, $index)
    {
        $generic_titles = array(
            __('Home', 'eazly-content-generator'),
            __('About', 'eazly-content-generator'),
            __('Services', 'eazly-content-generator'),
            __('Contact', 'eazly-content-generator'),
            __('Blog', 'eazly-content-generator'),
            __('Shop', 'eazly-content-generator'),
            __('Products', 'eazly-content-generator'),
            __('FAQ', 'eazly-content-generator'),
            __('Team', 'eazly-content-generator'),
            __('Portfolio', 'eazly-content-generator'),
        );

        $lorem_words = array(
            'Lorem',
            'Ipsum',
            'Dolor',
            'Sit',
            'Amet',
            'Consectetur',
            'Adipiscing',
            'Elit',
            'Sed',
            'Eiusmod',
            'Tempor',
            'Incididunt',
        );

        switch ($method) {
            case 'sequential':
                $post_type_obj = get_post_type_object($post_type);
                $base_title = $post_type_obj ? $post_type_obj->labels->singular_name : ucfirst($post_type);
                $counter = $index + 1;

                $title = $base_title . ' ' . $counter;
                while (in_array($title, $existing_titles, true)) {
                    ++$counter;
                    $title = $base_title . ' ' . $counter;
                }
                return $title;

            case 'generic':
                $base_title = $generic_titles[array_rand($generic_titles)];
                $counter = 1;

                $title = $base_title;
                while (in_array($title, $existing_titles, true)) {
                    ++$counter;
                    $title = $base_title . ' ' . $counter;
                }
                return $title;

            case 'lorem':
                $word_count = wp_rand(3, 4);
                $words = array();

                for ($i = 0; $i < $word_count; $i++) {
                    $words[] = $lorem_words[array_rand($lorem_words)];
                }

                $title = implode(' ', $words);

                $counter = 1;
                $original_title = $title;
                while (in_array($title, $existing_titles, true)) {
                    ++$counter;
                    $title = $original_title . ' ' . $counter;
                }
                return $title;

            default:
                return 'Untitled ' . ($index + 1);
        }
    }

    /**
     * Generate content based on selected elements
     */
    private function generate_content($elements, $paragraph_count)
    {
        $content = '';

        foreach ($elements as $element) {
            switch ($element) {
                case 'h1':
                case 'h2':
                case 'h3':
                case 'h4':
                case 'h5':
                case 'h6':
                    // Generate a short heading (3-6 words)
                    $heading_words = array();
                    $word_count = wp_rand(3, 6);
                    for ($i = 0; $i < $word_count; $i++) {
                        $heading_words[] = Eazly_Lorem_Ipsum_Generator::generate_paragraphs(1);
                    }
                    // Get first few words from generated paragraph
                    $heading_text = explode(' ', Eazly_Lorem_Ipsum_Generator::generate_paragraphs(1));
                    $heading_text = array_slice($heading_text, 0, $word_count);
                    $heading_text = ucwords(implode(' ', $heading_text));
                    // Remove any punctuation from heading
                    $heading_text = preg_replace('/[^\w\s]/', '', $heading_text);

                    $content .= '<' . $element . '>' . $heading_text . '</' . $element . '>' . "\n\n";
                    break;

                case 'blockquote':
                    $quote_text = Eazly_Lorem_Ipsum_Generator::generate_paragraphs(1);
                    $content .= '<blockquote>' . $quote_text . '</blockquote>' . "\n\n";
                    break;

                case 'ul':
                    $content .= '<ul>' . "\n";
                    for ($i = 0; $i < 5; $i++) {
                        // Generate a short sentence for list item
                        $list_text = explode('.', Eazly_Lorem_Ipsum_Generator::generate_paragraphs(1));
                        $list_item = trim($list_text[0]);
                        $content .= '  <li>' . $list_item . '</li>' . "\n";
                    }
                    $content .= '</ul>' . "\n\n";
                    break;

                case 'ol':
                    $content .= '<ol>' . "\n";
                    for ($i = 0; $i < 5; $i++) {
                        // Generate a short sentence for list item
                        $list_text = explode('.', Eazly_Lorem_Ipsum_Generator::generate_paragraphs(1));
                        $list_item = trim($list_text[0]);
                        $content .= '  <li>' . $list_item . '</li>' . "\n";
                    }
                    $content .= '</ol>' . "\n\n";
                    break;

                case 'hr':
                    $content .= '<hr />' . "\n\n";
                    break;

                case 'img':
                    // Featured image is handled separately in ajax_generate_posts
                    // We don't add it to content, just skip
                    break;
            }
        }
        $paragraphs = Eazly_Lorem_Ipsum_Generator::generate_paragraphs($paragraph_count);
        $paragraph_array = explode("\n\n", $paragraphs);
        foreach ($paragraph_array as $para) {
            $content .= '<p>' . trim($para) . '</p>' . "\n\n";
        }
        return $content;
    }

    /**
     * Set featured image for a post
     */
    private function set_featured_image_for_post($post_id)
    {
        // Try to get a random image from media library
        $image_ids = $this->get_random_image_ids(1);

        if (!empty($image_ids)) {
            // Use existing image from library
            set_post_thumbnail($post_id, $image_ids[0]);
        } else {
            // No images in library, create placeholder
            $this->set_default_thumbnail($post_id);
        }
    }

    /**
     * Get random image IDs from media library
     */
    private function get_random_image_ids($count)
    {
        $count = absint($count);

        if ($count <= 0) {
            return [];
        }

        $query = new WP_Query([
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'post_mime_type' => 'image',
            'posts_per_page' => $count,
            'orderby'        => 'rand',
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ]);

        return ! empty($query->posts) ? $query->posts : [];
    }

    /**
     * Set a default thumbnail if no images exist in library
     */
    private function set_default_thumbnail($post_id)
    {
        // First check if we already created a placeholder
        $placeholder_id = get_option('eazly_placeholder_image_id');

        if ($placeholder_id && get_post($placeholder_id)) {
            set_post_thumbnail($post_id, $placeholder_id);
            return true;
        }

        // Create a simple placeholder image
        $image = imagecreatetruecolor(800, 600);
        $bg_color = imagecolorallocate($image, 204, 204, 204); // Light gray
        $text_color = imagecolorallocate($image, 119, 119, 119); // Dark gray
        imagefill($image, 0, 0, $bg_color);

        // Add text to the image
        imagestring($image, 5, 300, 250, 'Featured Image', $text_color);
        imagestring($image, 3, 250, 300, 'Generated by Eazly Content Generator', $text_color);

        // Save to temporary file
        $upload_dir = wp_upload_dir();
        $filename = 'eazly-placeholder-' . time() . '.jpg';
        $filepath = $upload_dir['path'] . '/' . $filename;

        imagejpeg($image, $filepath, 90);
        imagedestroy($image);

        // Prepare attachment data
        $filetype = wp_check_filetype($filename, null);

        $attachment = array(
            'post_mime_type' => $filetype['type'],
            'post_title' => 'Placeholder Image',
            'post_content' => '',
            'post_status' => 'inherit',
        );

        // Insert the attachment
        $attach_id = wp_insert_attachment($attachment, $filepath);

        if (!is_wp_error($attach_id)) {
            // Generate metadata and update
            require_once ABSPATH . 'wp-admin/includes/image.php';
            $attach_data = wp_generate_attachment_metadata($attach_id, $filepath);
            wp_update_attachment_metadata($attach_id, $attach_data);

            // Store the ID for future use
            update_option('eazly_placeholder_image_id', $attach_id);

            // Set as featured image
            set_post_thumbnail($post_id, $attach_id);
            return true;
        }

        return false;
    }
    /**
     * Add "Dummy Content" label to posts in admin list
     */
    public function add_eazly_content_label($post_states, $post)
    {
        if (get_post_meta($post->ID, '_eazly_dummy_content', true)) {
            $post_states['eazly_dummy_content'] = __('Dummy Content', 'eazly-content-generator');
        }
        return $post_states;
    }
}
