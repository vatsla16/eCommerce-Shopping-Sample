<?php

/**
 * Utility class of various static functions
 *
 * This class helps to manipulate with arrays
 *
 * @since      1.0.0
 * @package    WPF
 * @subpackage WPF/includes
 * @author     Themify
 */
class WPF_Utils {

    

    public static function get_reserved_terms() {

        return array(
            'attachment',
            'attachment_id',
            'author',
            'author_name',
            'calendar',
            'cat',
            'category',
            'category__and',
            'category__in',
            'category__not_in',
            'category_name',
            'comments_per_page',
            'comments_popup',
            'custom',
            'customize_messenger_channel',
            'customized',
            'cpage',
            'day',
            'debug',
            'embed',
            'error',
            'exact',
            'feed',
            'hour',
            'link_category',
            'm',
            'minute',
            'monthnum',
            'more',
            'name',
            'nav_menu',
            'nonce',
            'nopaging',
            'offset',
            'order',
            'orderby',
            'p',
            'page',
            'page_id',
            'paged',
            'pagename',
            'pb',
            'perm',
            'post',
            'post__in',
            'post__not_in',
            'post_format',
            'post_mime_type',
            'post_status',
            'post_tag',
            'post_type',
            'posts',
            'posts_per_archive_page',
            'posts_per_page',
            'preview',
            'robots',
            's',
            'search',
            'second',
            'sentence',
            'showposts',
            'static',
            'subpost',
            'subpost_id',
            'tag',
            'tag__and',
            'tag__in',
            'tag__not_in',
            'tag_id',
            'tag_slug__and',
            'tag_slug__in',
            'taxonomy',
            'tb',
            'term',
            'terms',
            'theme',
            'title',
            'type',
            'w',
            'withcomments',
            'withoutcomments',
            'year'
        );
    }


    /**
     * Returns the current language code
     *
     * @since 1.0.0
     *
     * @return string the language code, e.g. "en"
     */
    public static function get_current_language_code() {

        static $language_code = false;
        if ($language_code) {
            return $language_code;
        }
        if (defined('ICL_LANGUAGE_CODE')) {

            $language_code = ICL_LANGUAGE_CODE;
        } elseif (function_exists('qtrans_getLanguage')) {

            $language_code = qtrans_getLanguage();
        }
        if (!$language_code) {
            $language_code = substr(get_bloginfo('language'), 0, 2);
        }
        $language_code = strtolower(trim($language_code));
        return $language_code;
    }

    /**
     * Returns the site languages
     *
     * @since 1.0.0
     *
     * @return array the languages code, e.g. "en",name e.g English
     */
    public static function get_all_languages() {

        static $languages = array();
        if (!empty($languages)) {
            return $languages;
        }
        if (defined('ICL_LANGUAGE_CODE')) {
            $lng = self::get_current_language_code();
            if ($lng == 'all') {
                $lng = self::get_default_language_code();
            }
            $all_lang = icl_get_languages('skip_missing=0&orderby=KEY&order=DIR&link_empty_to=str');
            foreach ($all_lang as $key => $l) {
                if ($lng == $key) {
                    $languages[$key]['selected'] = true;
                }
                $languages[$key]['name'] = $l['native_name'];
            }
        } elseif (function_exists('qtrans_getLanguage')) {

            $languages = qtrans_getSortedLanguages();
        } else {

            $all_lang = self::get_default_language_code();
            $languages[$all_lang]['name'] = '';
            $languages[$all_lang]['selected'] = true;
        }
        return $languages;
    }

    /**
     * Returns the current language code
     *
     * @since 1.0.0
     *
     * @return string the language code, e.g. "en"
     */
    public static function get_default_language_code() {
        static $language_code = false;
        if ($language_code === false) {
            global $sitepress;
            if (isset($sitepress)) {
                $language_code = $sitepress->get_default_language();
            }

            $language_code = empty($language_code) ? substr(get_bloginfo('language'), 0, 2) : $language_code;
            $language_code = strtolower(trim($language_code));
        }
        return $language_code;
    }

    public static function get_label($label) {
        if (!is_array($label)) {
            return esc_attr($label);
        }
        static $lng = false;
        if ($lng === false) {
            $lng = self::get_current_language_code();
        }
        $value = '';
        if (isset($label[$lng]) && $label[$lng]) {
            $value = $label[$lng];
        } else {
            static $default_lng = false;
            if ($default_lng === false) {
                $default_lng = self::get_default_language_code();
            }
            $value = isset($label[$default_lng]) && $label[$default_lng] ? $label[$default_lng] : current($label);
        }
        return esc_attr($value);
    }

    /**
     * Echo multilanguage html text for template
     *
     * @since 1.0.0
     *
     * @param number $id input id
     * @param array $data saved data
     * @param array $languages languages array
     * @param string $key
     * @param string $name
     */
    public static function module_multi_text($id, array $data, array $languages, $key, $name, $input = 'text', $placeholder = false) {
        ?>
        <div class="wpf_back_active_module_row">
            <?php if (!$placeholder): ?>
                <div class="wpf_back_active_module_label">
                    <label for="wpf_<?php echo $id ?>_<?php echo $key ?>"><?php echo $name; ?></label>
                </div>
            <?php endif; ?>
            <?php self::module_language_tabs($id, $data, $languages, $key, $input, $placeholder); ?>
        </div>
        <?php
    }

    /**
     * Echo multilanguage html text for template
     *
     * @since 1.0.0
     *
     * @param number $id input id
     * @param array $data saved data
     * @param array $languages languages array
     * @param string $key
     */
    public static function module_language_tabs($id, array $data, array $languages, $key, $input = 'text', $placeholder = false, $as_array = false) {
        ?>
        <?php if (!empty($languages)): ?>
            <div class="wpf_back_active_module_input">
                <?php if (count($languages) > 1): ?>
                    <ul class="wpf_language_tabs">
                        <?php foreach ($languages as $code => $lng): ?>
                            <li <?php if (isset($lng['selected'])): ?>class="wpf_active_tab_lng"<?php endif; ?>>
                                <a class="wpf_lng_<?php echo $code ?>"  title="<?php echo $lng['name'] ?>" href="#"></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <?php
                $name = $as_array ? $id : '[' . $id . ']';
                if ($key) {
                    $name.='[' . $key . ']';
                }
                ?>
                <ul class="wpf_language_fields">
                    <?php foreach ($languages as $code => $lng): ?>
                        <li <?php if (isset($lng['selected'])): ?>class="wpf_active_lng"<?php endif; ?>>
                            <?php
                            switch ($input) {
                                case 'text':
                                    ?>
                                    <input id="wpf_<?php echo $id ?><?php if ($key): ?>_<?php echo $key ?><?php endif; ?>" <?php if ($placeholder): ?>placeholder="<?php echo $placeholder ?>"<?php endif; ?> type="text" class="wpf_towidth"
                                           name="<?php echo $name ?>[<?php echo $code ?>]"
                                           <?php if (isset($data[$key]) && isset($data[$key][$code])): ?>value="<?php esc_attr_e($data[$key][$code]) ?>"<?php endif; ?>/>
                                           <?php
                                           break;
                                       case 'textarea':
                                           ?>
                                    <textarea id="wpf_<?php echo $id ?><?php if ($key): ?>_<?php echo $key ?><?php endif; ?>" <?php if ($placeholder): ?>placeholder="<?php echo $placeholder ?>"<?php endif; ?> class="wpf_towidth"
                                              name="<?php echo $name ?>[<?php echo $code ?>]"><?php if (isset($data[$key]) && isset($data[$key][$code])): ?> <?php echo stripslashes_deep(esc_textarea(trim($data[$key][$code]))) ?><?php endif; ?></textarea>
                                              <?php
                                              break;
                                          case 'wp_editor':
                                              $value = isset($data[$key]) && isset($data[$key][$code]) ? $data[$key][$code] : '';
                                              $id = 'wpf_' . $id;
                                              if ($key) {
                                                  $id.='_' . $key;
                                              }
                                              $tname = $name . '[' . $code . ']';
                                              wp_editor($value, $id, array('textarea_name' => $tname, 'media_buttons' => false));
                                              break;
                                      }
                                      ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php
    }

    public static function get_default_fields() {
        static $labels = array();
        if (empty($labels)) {
            $labels = array(
                'title' => __('Product Title', 'wpf'),
                'sku' => __('SKU', 'wpf'),
                'wpf_cat' => __('Category', 'wpf'),
                'wpf_tag' => __('Tag', 'wpf'),
                'price' => __('Price', 'wpf'),
                'instock' => __('In Stock', 'wpf'),
                'onsale' => __('On Sale', 'wpf'),
                'submit' => __('Submit Button', 'wpf')
            );
        }

        return $labels;
    }

    public static function get_wc_attributes($receate = FALSE) {
        static $attributes = null;
        if (is_null($attributes)) {
            $attribute_taxonomies = wc_get_attribute_taxonomies();
            if (!empty($attribute_taxonomies)) {
                foreach ($attribute_taxonomies as $tax) {
                    $name = wc_attribute_taxonomy_name($tax->attribute_name);
                    $attributes[$name] = $tax->attribute_label ? $tax->attribute_label : $tax->attribute_name;
                }
            } else {
                $attributes = array();
            }
        }
        return $attributes;
    }

    public static function get_current_page() {
        static $page = NULL;
        if (is_null($page)) {
            $page = is_shop() ? wc_get_page_id('shop') : (is_page()?get_the_ID():false);
        }
        return $page;
    }

    public static function strtolower($text, $escape = true) {
        $text = function_exists('mb_strtolower') ? mb_strtolower($text) : strtolower($text);
        if ($escape) {
            $text = sanitize_title($text);
        }
        if (in_array($text, self::get_reserved_terms())) {
            $text = 'wpf_' . $text;
        }
        return $text;
    }

    public static function get_field_name(array $item, $orig_name) {

        $title = !empty($item['field_title']) ? WPF_Utils::get_label($item['field_title']) : $orig_name;
        if (empty($title)) {
            $title = $orig_name;
        }
        return sanitize_text_field($title);
    }

    public static function format_text($text) {
        global $wp_embed;

        $text = convert_smilies($text);
        $text = convert_chars($text);
        $text = $wp_embed->autoembed($text);
        $text = wptexturize($text);
        $text = wpautop($text);
        $text = shortcode_unautop($text);
        $text = $wp_embed->run_shortcode($text);
        if (!has_shortcode($text, 'searchandfilter')) {
            $text = do_shortcode($text);
        }
        return $text;
    }
    
    public static function format_price($price,$args=array()){
        if($price===''){
            return $price;
        }
        $price = floatval($price);
        if(strpos($price,'.',1)===false){
            $price = intval($price);
            $args['decimals'] =0;
        }
        return wc_price($price,$args);
    }
    
    /**
     * Check if ajax request
     *
     * @param void
     *
     * return boolean
     */
    public static function is_ajax() {
        static $is_ajax = null;
        if(is_null($is_ajax)){
            $is_ajax = defined('DOING_AJAX') || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
        }
        return $is_ajax;
    }
}
