<?php

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    WPF
 * @subpackage WPF/public
 * @author     Themify
 */
class WPF_Public {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;
    private static $result = '';
    private static $result_page = null;
    private $shortcode_id = '';
    private $pagination = false;
    private $post_count;
    private $templates = array();
    private $append = false;

    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        if (!defined('DOING_AJAX') || !DOING_AJAX) {
            add_action('wp_head', array($this, 'result_page'), 1);
            add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'), 15);
        } else {
            add_action('wp_ajax_wpf_search', array($this, 'do_search'));
            add_action('wp_ajax_nopriv_wpf_search', array($this, 'do_search'));
            add_action('wp_ajax_wpf_autocomplete', array($this, 'autocomplete'));
            add_action('wp_ajax_nopriv_wpf_autocomplete', array($this, 'autocomplete'));
        }

        add_filter('widget_text', array($this, 'widget_text'), 10, 2);
        add_filter('widget_text_content', array($this, 'get_shortcode'), 12);

        add_shortcode('searchandfilter', array($this, 'shortcode'));
    }

    /**
     * Process plugin's shortcode in Text widget
     *
     * @since 1.0.8
     * @return string $text
     */
    function widget_text($text, $instance = array( ) ) {
		global $wp_widget_factory;

		/* check for WP 4.8.1+ widget */
		if( isset( $wp_widget_factory->widgets['WP_Widget_Text'] ) && method_exists( $wp_widget_factory->widgets['WP_Widget_Text'], 'is_legacy_instance' ) && ! $wp_widget_factory->widgets['WP_Widget_Text']->is_legacy_instance( $instance ) ) {
			return $text;
		}

		/*
		 * if $instance['filter'] is set to "content", this is a WP 4.8 widget,
		 * leave it as is, since it's processed in the widget_text_content filter
		 */
		if( isset( $instance['filter'] ) && 'content' === $instance['filter'] ) {
			return $text;
		}

        $text = $this->get_shortcode($text);
        return $text;
    }

    public function get_shortcode($text) {
        if ($text && has_shortcode($text, 'searchandfilter')) {
            $text = WPF_Utils::format_text($text);
            $text = do_shortcode($text);
        }
        return $text;
    }

    /**
     * Register the Javascript/Stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        $plugin_url = plugin_dir_url(__FILE__);
        $translation_ = array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'url' => $plugin_url,
            'ver' => $this->version,
            'rtl' => is_rtl()
        );
        wp_enqueue_style('themify-icons', plugin_dir_url(dirname(__FILE__)) . 'admin/themify-icons/themify-icons.css', array(), $this->version, 'all');
        wp_register_style($this->plugin_name . '-select', $plugin_url . 'css/select2.min.css', false, $this->version, false);
        wp_register_script($this->plugin_name . '-select', $plugin_url . 'js/select2.min.js', array('jquery'), $this->version, true);
        wp_register_script($this->plugin_name . 'ui-touch', $plugin_url . 'js/jquery.ui.touch-punch.min.js', array('jquery'), $this->version, true);

        wp_register_script($this->plugin_name, $plugin_url . 'js/wpf-public.js', array('jquery'), $this->version, true);
        wp_localize_script($this->plugin_name, 'wpf', $translation_);
        global $wp_scripts;
        $ui = $wp_scripts->query('jquery-ui-core');
        wp_register_style($this->plugin_name . 'ui-css', '//ajax.googleapis.com/ajax/libs/jqueryui/' . $ui->ver . '/themes/smoothness/jquery-ui.min.css', false, $this->version, false);
        wp_enqueue_style($this->plugin_name, $plugin_url . 'css/wpf-public.css', array(), $this->version, 'all');
        wp_enqueue_script($this->plugin_name);
    }

    /**
     * @since 1.0.0
     *
     * @param $atts
     *
     * @return string|void
     */
    public function shortcode($atts) {
        if (empty($atts['id'])) {
            return;
        }
        $id = sanitize_text_field($atts['id']);
        $option = WPF_Options::get_option($this->plugin_name, $this->version);
        $forms = $option->get();
        if (empty($forms[$id])) {
            return;
        }
        if (!wp_script_is($this->plugin_name)) {
            wp_enqueue_script($this->plugin_name);
            self::load_wc_scripts();
        }
        $request = array();
        $this->shortcode_id = $id;
        if (!empty($_REQUEST['id']) && !empty($_REQUEST['wpf'])) {
            $option = WPF_Options::get_option($this->plugin_name, $this->version);
            $forms = $option->get();
            if (!empty($forms[$_REQUEST['id']])) {
                $this->shortcode_id = $_REQUEST['id'];
                $request = $this->parse_query($_REQUEST, $forms[$_REQUEST['id']], false);
            }
        }
        $wpf_form = new WPF_Form($this->plugin_name, $this->version, $id);
        return $wpf_form->public_themplate($forms[$id], self::$result_page, $request);
    }

    public function result_page() {
        if (!empty($_REQUEST['id']) && !empty($_REQUEST['wpf'])) {
            $option = WPF_Options::get_option($this->plugin_name, $this->version);
            $forms = $option->get();

            if (!empty($forms[$_REQUEST['id']])) {
                self::$result_page = WPF_Utils::get_current_page();
                $data = $forms[$_REQUEST['id']]['data'];
                if ((!empty($data['result_type']) && $data['result_type'] === 'same_page') || self::$result_page == $data['page']) {
                    self::load_wc_scripts();
                    add_filter('body_class', array($this, 'body_class'), 10, 1);
                    self::$result = $this->get_result($_REQUEST, $forms[$_REQUEST['id']]);

                    if (is_singular('product')) {
                        add_filter('wc_get_template', array($this, 'filter_not_found'), 30, 5);
                    } elseif (is_woocommerce()) {
                        global $wp_query;
                        $this->post_count = $wp_query->post_count;
                        $wp_query->post_count = 0;
                        add_action('woocommerce_after_main_content', array($this, 'refresh_post_count'), 1);
                        add_filter('wc_get_template', array($this, 'filter_not_found'), 30, 5);
                    }
                }
            }
        }
        if (is_woocommerce()) {
            add_action('woocommerce_before_main_content', array($this, 'result_container'), 100);
            add_action('woocommerce_after_main_content', array($this, 'close_div'), 1);
        } else {
            add_filter('the_content', array($this, 'result_container'), 20, 1);
        }
    }

    public function result_container($content = '') {
        global $wp_current_filter;
        if (!in_array('wpseo_head', $wp_current_filter)) {//fix conflict with wpseo(calling the content in the header)
            remove_filter('the_content', array($this, 'result_container'), 20, 1);
        }
        $slug = !empty($_REQUEST['id']) ? sanitize_key($_REQUEST['id']) : $this->shortcode_id;
        $option = WPF_Options::get_option($this->plugin_name, $this->version);
        $forms = $option->get();
	$is_infinity = '';
        if (!empty($forms[$slug])) {
            $template = $forms[$slug];
            $is_result_page = (!empty($template['data']['result_type']) && $template['data']['result_type'] === 'same_page' ) || self::$result_page == $template['data']['page'];
            $show_result_in_same_page = isset($template['data']['result_type']) && $template['data']['result_type'] === 'same_page';
            $show_form_in_results = !isset($template['data']['show_form_in_results']) || $template['data']['show_form_in_results'] !== 'show_form_in_results' ? false : true;
	    $is_infinity = isset($template['data']['pagination_type']) && $template['data']['pagination_type']!=='pagination'?' wpf_infinity_container':'';
            if ($is_result_page && !$show_result_in_same_page && $show_form_in_results) {
                $request = array();
                $this->shortcode_id = $slug;
                if (!empty($_REQUEST['id']) && !empty($_REQUEST['wpf'])) {
                    $option = WPF_Options::get_option($this->plugin_name, $this->version);
                    $forms = $option->get();
                    if (!empty($forms[$_REQUEST['id']])) {
                        $this->shortcode_id = $_REQUEST['id'];
                        $request = $this->parse_query($_REQUEST, $forms[$_REQUEST['id']], false);
                    }
                }
                $wpf_form = new WPF_Form($this->plugin_name, $this->version, $slug);
                echo $wpf_form->public_themplate($forms[$slug], self::$result_page, $request);
            }
        }
        if (is_woocommerce()) {
            echo '<div data-slug="' . $slug . '" class="wpf-search-container'.$is_infinity.'">';
        } else {

            return $content . '<div data-slug="' . $slug . '" class="wpf-search-container'.$is_infinity.'">' . self::$result . '</div>';
        }
    }

    public function close_div() {
        echo self::$result . '</div>';
    }

    public function wrap_pagination() {
        remove_action('woocommerce_after_shop_loop', array($this, 'wrap_pagination'), 1);
		$cl = 'wpf-pagination';
		if($this->pagination===false || $this->pagination!=='pagination'){
			$cl.=' wpf-hide-pagination';
		}
        echo '<div class="'.$cl.'">';
    }

    public function refresh_post_count() {
        global $wp_query;
        $wp_query->post_count = $this->post_count;
    }

    public function filter_not_found($located, $template_name, $args, $template_path, $default_path) {

        return $template_name === 'loop/no-products-found.php' ? plugin_dir_path(__FILE__) . 'templates/no-products-found.php' : $located;
    }

    public function hide_templates($located, $template_name, $args, $template_path, $default_path) {

        return in_array($template_name, $this->templates,true) ? $this->filter_not_found($located, 'loop/no-products-found.php', $args, $template_path, $default_path) : $located;
    }

    public function set_pagination() {
        remove_action('woocommerce_after_shop_loop', array($this, 'set_pagination'), 100);
        global $wp_query;
        $current_page = isset($_REQUEST['wpf_page']) ? (int) ($_REQUEST['wpf_page']) : 1;
        if ($wp_query->max_num_pages > 1 && $wp_query->max_num_pages > $current_page):
            ?>
            <div class="wpf_infinity<?php if ($this->pagination === 'infinity_auto'): ?> wpf_infinity_auto<?php endif; ?>">
                <a data-max="<?php echo $wp_query->max_num_pages ?>" data-current="<?php echo ($current_page + 1) ?>"  href="javascript:void(0);"><?php _e('Load More', 'wpf') ?></a>
            </div>
            <?php
        endif;
    }

    public function do_search() {
        if (!empty($_POST['id'])) {
            $id = sanitize_key($_POST['id']);
            $option = WPF_Options::get_option($this->plugin_name, $this->version);
            $forms = $option->get();

            if (!empty($forms[$id])) {
                $this->append = isset($_POST['append']);
                unset($_POST['append']);
                $result = $this->get_result($_POST, $forms[$id]);
                if ($this->append) {
                    $result = '<div>' . $result . '</div>';
                }
                if ($result) {
                    echo $result;
                }
            }
        }
        wp_die();
    }

    public function get_result(array $data, array $form) {
        $query_args = $this->parse_query($data, $form);
        $result = false;

        if (!empty($query_args)) {


            if (!empty($this->templates)) {
                add_filter('wc_get_template', array($this, 'hide_templates'), 100, 5);
            }
            add_action('woocommerce_after_shop_loop', array($this, 'wrap_pagination'), 1);
            if (!WPF_Utils::is_ajax() || (isset($_POST['wpf_page_id']) && wc_get_page_id('shop') != $_POST['wpf_page_id'])) {
                add_filter('woocommerce_show_page_title', '__return_false', 99, 1);
            } else {
                add_filter('woocommerce_page_title', array($this, 'get_page_title'));
            }
            add_filter('post_class', array($this, 'post_classes'), 10, 1);
            $sort_bar = !is_woocommerce() && (empty($form['data']['result']) || empty($form['data']['sort']));		
            query_posts(apply_filters('wpf_query', $query_args));
			
            if ($sort_bar) {
                global $wp_query;
                $is_post_type_archive = $wp_query->is_post_type_archive;
                $wp_query->is_post_type_archive = true;
            }
            if (WPF_Utils::is_ajax()) {
                global $wp_filter;
                unset($wp_filter['woocommerce_archive_description']);
            }
            ob_start();
            woocommerce_content();
            $result = ob_get_contents();
            ob_end_clean();
            wp_reset_query();
            remove_action('woocommerce_after_shop_loop', array($this, 'wrap_pagination'), 1);
            if (!WPF_Utils::is_ajax()) {
                remove_filter('woocommerce_show_page_title', '__return_false', 99, 1);
            } else {
                remove_filter('woocommerce_page_title', array($this, 'get_page_title'));
            }
            remove_filter('post_class', array($this, 'post_classes'), 10, 1);
            if ($sort_bar) {
                $wp_query->is_post_type_archive = $is_post_type_archive;
            }
            remove_filter('wc_get_template', array($this, 'hide_templates'), 100, 5);
        }
        return '<div class="wpf-search-wait"></div>' . $result . '</div>';
    }

    public function parse_query(array $post, array $form, $build = true) {
        $layout = $form['layout'];
        $data = $form['data'];
        if ($build) {
            $query_args = array(
                'post_type' => 'product',
                'post_status' => 'publish',
                'wc_query'=>1,
                'is_paginated'=>1,
                'meta_query' => array(),
                'tax_query' => array(),
                'posts_per_page' => !empty($data['posts_per_page']) ? (int) $data['posts_per_page'] : apply_filters('loop_shop_per_page', get_option('posts_per_page')),
                'paged' => !empty($post['wpf_page']) ? intval($post['wpf_page']) : (is_front_page() ? get_query_var('page', 1) : get_query_var('paged', 1)),
            );
            $this->pagination = $data['pagination_type'];
			
			if (!empty($data['out_of_stock'])) {
				$query_args['meta_query'] = array( array(
							'key'       => '_stock_status',
							'value'     => 'outofstock',
							'compare'   => 'NOT IN'
						)
					);
			}
            if (!empty($data['pagination'])) {
                remove_action('woocommerce_after_shop_loop', 'woocommerce_pagination', 10);
                remove_action('woocommerce_before_shop_loop', 'woocommerce_pagination', 20);
                $this->templates[] = 'loop/pagination.php';
				$this->pagination  = false;
                $this->append = false;
            } elseif (isset($data['pagination_type']) && $data['pagination_type'] !== 'pagination') {
                remove_action('woocommerce_after_shop_loop', 'woocommerce_pagination', 10);
                add_action('woocommerce_after_shop_loop', array($this, 'set_pagination'), 100);
                $this->templates[] = 'loop/pagination.php';
            }
            if (!$this->append && !empty($post['orderby']) && empty($data['sort'])) {
                $this->set_order($post['orderby'], $query_args);
            } elseif ($this->append || !empty($data['sort'])) {
                remove_action('woocommerce_after_shop_loop', 'woocommerce_catalog_ordering', 30);
                remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
                $this->templates[] = 'loop/orderby.php';
            }else{
                $this->set_order(get_option( 'woocommerce_default_catalog_orderby' ), $query_args);
            }
            if ($this->append || !empty($data['result'])) {
                remove_action('woocommerce_after_shop_loop', 'woocommerce_result_count', 20);
                remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
                $this->templates[] = 'loop/result-count.php';
            }
        }
        $args = array();
        foreach ($layout as $type => $item) {
            if ($type !== 'submit') {
                $key = WPF_Utils::strtolower(WPF_Utils::get_field_name($item, $type));
                if ($type === 'price' && (empty($item['price_type']) || $item['price_type'] === 'slider')) {
                    $key.='-from';
                }
                if (!empty($post[$key]) || (isset($post[$key]) && $post[$key] === '0')) {
                    
                    if ($type === 'price') {
                        if (empty($item['price_type']) || $item['price_type'] === 'slider') {
                            $key2 = WPF_Utils::strtolower(WPF_Utils::get_field_name($item, $type));
                            $val = array('from' => intval($post[$key]), 'to' => intval($post[$key2 . '-to']));
                        } else {
                            $tmp_v = explode('-', $post[$key]);
                            $val = array('from' => floatval($tmp_v[0]), 'to' => floatval($tmp_v[1]));
                        }
                    } else {
                        $val = is_array($post[$key]) ? $post[$key] : sanitize_text_field(urldecode($post[$key]));
                    }
                    if ($build) {
                        $this->build_query($type, $val, $query_args, $item);
                    } else {
                        $args[$type] = $val;
                    }
                }
            }
        }

		if ( $build && ! empty( $query_args['tax_query'] ) ) {
			$query_args['tax_query']['relation'] = isset($data['tax_relation']) ? $data['tax_relation'] : 'AND';
		}

        return $build ? $query_args : $args;
    }

    public function build_query($type, $value, &$query_args, $data = false) {
    
        if (!isset($data['logic'])) {
            $data['logic'] = false;
        }
        switch ($type) {
            case 'title':
                $query_args['s'] = sanitize_text_field($value);
                break;
            case 'sku':
                $query_args['meta_query'][] = array(
                    'key' => '_sku',
                    'value' => sanitize_text_field($value),
                    'compare' => 'LIKE'
                );
                break;
            case 'price':
                $query_args['meta_query'][] = array(
                    'key' => '_price',
                    'value' => array($value['from'], $value['to']),
                    'compare' => 'BETWEEN',
                    'type' => 'DECIMAL'
                );
                break;

            case 'onsale':
                $query_args['post__in'] = array_merge(array(0), wc_get_product_ids_on_sale());
                break;

            case 'instock':
                $query_args['meta_query'][] = array(
                    'key' => '_stock_status',
                    'value' => 'instock',
                    'compare' => '=',
                );
                break;

            case 'wpf_tag':
            case 'wpf_cat':
                $query_args['tax_query'][] = array(
                    'taxonomy' => str_replace('wpf', 'product', $type),
                    'field' => 'slug',
                    'terms' => is_array($value) ? $value : explode(',', $value),
                    'operator' => $data['logic'] === 'and' ? 'AND' : 'IN',
                    'include_children'=>!isset($data['include']) || $data['include']==='yes'
                );
                break;

            default:
                $attributes = WPF_Utils::get_wc_attributes();

                if (isset($attributes[$type])) {
                    $query_args['tax_query'][] = array(
                        'taxonomy' => $type,
                        'field' => 'slug',
                        'terms' => is_array($value) ? $value : explode(',', $value),
                        'operator' => $data['logic'] === 'and' ? 'AND' : 'IN'
                    );
                }

                break;
        }
        return $query_args;
    }

    public function get_page_title($title) {
        if (!empty($_POST['wpf_page_id'])) {
            $p = get_post($_POST['wpf_page_id']);
            if (!empty($p)) {
                $title = $p->post_title;
            }
        }
        return $title;
    }

    public function post_classes($classes) {
        $classes[] = 'product';
        return $classes;
    }

    public function body_class($classes) {
        $classes[] = 'woocommerce';
        $classes[] = ' woocommerce-page';
        return $classes;
    }

    private static function load_wc_scripts() {

        static $script_loaded = false;
        if (!$script_loaded) {
            $script_loaded = true;
            WC_Frontend_Scripts::load_scripts();
        }
    }

    private function set_order($order, &$query) {

        $_GET['orderby'] = $order;
        switch ($order) {
            case 'rand' :
                $query['orderby'] = 'rand';
                break;
            case 'title' :
            case 'title-desc' :
                $query['orderby'] = 'title';
                $query['order'] = $order == 'title-desc' ? 'DESC' : 'ASC';
                break;
            case 'price':
            case 'price-desc':
                $query['meta_key'] = '_price';
                $query['orderby'] = "meta_value_num ID";
                $query['order'] = $order === 'price' ? 'asc' : 'desc';
                break;
            case 'date':
                $query['orderby'] = 'date';
                $query['order'] = 'desc';
                break;
            case 'popularity':
                $query['meta_key'] = 'total_sales';
                // Sorting handled later though a hook
                add_filter('posts_clauses', array($this, 'order_by_popularity'), 10, 1);
                break;
            case 'rating' :
                // Sorting handled later though a hook
                add_filter('posts_clauses', array($this, 'order_by_rating'), 10, 1);
                break;
        }
    }

    /**
     * WP Core doens't let us change the sort direction for invidual orderby params
     *
     * This lets us sort by meta value desc, and have a second orderby param.
     *
     * @access public
     * @param array $args
     * @return array
     */
    public function order_by_popularity($args) {
        global $wpdb;
        $args['orderby'] = "$wpdb->postmeta.meta_value+0 DESC, $wpdb->posts.post_date DESC";
        remove_filter('posts_clauses', array($this, 'order_by_popularity'), 10, 1);
        return $args;
    }

    /**
     * Order by rating post clauses.
     *
     * @access public
     * @param array $args
     * @return array
     */
    public function order_by_rating($args) {
        global $wpdb;

        $args['fields'] .= ", AVG( $wpdb->commentmeta.meta_value ) as average_rating ";
        $args['where'] .= " AND ( $wpdb->commentmeta.meta_key = 'rating' OR $wpdb->commentmeta.meta_key IS null ) ";
        $args['join'] .= "
                    LEFT OUTER JOIN $wpdb->comments ON($wpdb->posts.ID = $wpdb->comments.comment_post_ID)
                    LEFT JOIN $wpdb->commentmeta ON($wpdb->comments.comment_ID = $wpdb->commentmeta.comment_id)
            ";
        $args['orderby'] = "average_rating DESC, $wpdb->posts.post_date DESC";
        $args['groupby'] = "$wpdb->posts.ID";
        remove_filter('posts_clauses', array($this, 'order_by_rating'), 10, 1);
        return $args;
    }

    public function autocomplete() {
        if (!empty($_POST['key']) && in_array($_POST['key'], array('sku', 'title')) && !empty($_POST['term']) && strlen($_POST['term']) > 0) {
            $args = array(
                'post_type' => array('product', 'product_variation'),
                'post_status' => 'publish',
                'posts_per_page' => 10,
                'no_found_rows' => true
            );
            $term = sanitize_text_field($_POST['term']);
            $by_title = $_POST['key'] === 'title';
            if ($by_title) {
                $args['s'] = $term;
            } else {
                $args['meta_query'] = array(array(
                        'key' => '_sku',
                        'value' => $term,
                        'compare' => 'LIKE',
                ));
            }
            $posts = new WP_Query($args);
            $options = array();
            while ($posts->have_posts()) {
                $posts->the_post();
                $id = get_the_ID();
                $label = $by_title ? get_the_title() : get_post_meta($id, '_sku', true);
                $options[$id] = array('id' => $id, 'label' => $label);
            }
            echo wp_json_encode($options);
        }
        wp_die();
    }

}
