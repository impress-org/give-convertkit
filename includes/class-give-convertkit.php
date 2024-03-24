<?php
/**
 * Give ConvertKit - Admin Settings
 *
 * @package    Give
 * @since      1.0.3
 * @copyright  Copyright (c) 2019, GiveWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 * @subpackage Includes/Admin/Settings
 */

// Exit if accessed directly.
if ( ! defined('ABSPATH')) {
    exit;
}

/**
 * Class Give_ConvertKit
 *
 * @since 1.0.3
 */
class Give_ConvertKit_Settings
{
    
    /**
     * The ID for this newsletter Add-on, such as 'convertkit'
     */
    public $id;
    
    /**
     * The label for the Add-on, probably just shown as the title of the metabox
     */
    public $label;
    
    /**
     * Newsletter lists retrieved from the API
     */
    public $lists;
    
    /**
     * Newsletter lists retrieved from the API
     *
     * @var array
     */
    public $tags;
    
    /**
     * Checkbox label
     */
    public $checkbox_label;
    
    /**
     * Give Options
     */
    public $give_options;
    
    /**
     * Give_ConvertKit constructor.
     */
    public function __construct()
    {
        $this->id = 'convertkit';
        $this->label = 'ConvertKit';
        $this->give_options = give_get_settings();
        $this->api_key = trim(give_get_option('give_convertkit_api', ''));
        
        add_action('add_meta_boxes', [$this, 'add_metabox']);
        add_action('save_post', [$this, 'save_metabox']);
        
        add_filter('give_get_sections_addons', [$this, 'register_sections']);
        add_filter('give_get_settings_addons', [$this, 'register_settings']);
        add_action('give_donation_form_before_submit', [$this, 'form_fields'], 100, 1);
        add_action('give_insert_payment', [$this, 'completed_donation_signup'], 10, 2);
        
        // Donation metabox.
        add_filter('give_view_donation_details_totals_after', [$this, 'donation_metabox_notification'], 10, 1);
        
        // Get it started.
        add_action('init', [$this, 'init']);
        
        // Custom fields.
        add_action('give_admin_field_convertkit_list_select', [$this, 'convertkit_list_select_field'], 10, 2);
        add_action('give_admin_field_convertkit_tag_list', [$this, 'convertkit_tag_list_field'], 10, 2);
        
        add_action('wp_ajax_give_reset_convertkit_lists', [$this, 'give_reset_convertkit_lists']);
        add_action('wp_ajax_give_reset_convertkit_tags', [$this, 'give_reset_convertkit_tags']);
    }
    
    /**
     * Sets up the checkout label
     */
    public function init()
    {
        if ( ! empty($this->give_options['give_convertkit_label'])) {
            $this->checkbox_label = trim($this->give_options['give_convertkit_label']);
        } else {
            $this->checkbox_label = __('Signup for the newsletter', 'give-convertkit');
        }
    }
    
    /**
     * Output the signup checkbox, if enabled.
     *
     * @param int $form_id
     */
    public function form_fields($form_id)
    {
        // Check vars to see if this form should have the Opt-in field.
        if ( ! $this->show_subscribe_checkbox($form_id)) {
            return;
        }
        
        $this->give_options = give_get_settings();
        $custom_checkbox_label = get_post_meta($form_id, '_give_' . $this->id . '_custom_label', true);
        $override_option = get_post_meta($form_id, '_give_' . $this->id . '_override_option', true);
        $this->checkbox_label = __('Subscribe to our newsletter', 'give-convertkit');
        
        // What's the label gonna be?
        if ( ! empty($custom_checkbox_label) && $override_option !== 'default') {
            $this->checkbox_label = trim($custom_checkbox_label);
        } elseif ( ! empty($this->give_options['give_' . $this->id . '_label'])) {
            $this->checkbox_label = trim($this->give_options['give_' . $this->id . '_label']);
        }
        
        // What's the check gonna be? Should the opt-on be checked or unchecked by default...
        $form_checked_option = get_post_meta($form_id, '_give_' . $this->id . '_checked_default', true);
        $global_checked_option = give_get_option("give_{$this->id}_checked_default");
        $checked_option = 'enabled';
        
        if ( ! empty($form_checked_option) && $override_option !== 'default') {
            // Nothing to do here, option already set above.
            $checked_option = $form_checked_option;
        } elseif ( ! empty($global_checked_option)) {
            $checked_option = $global_checked_option;
        }
        
        ob_start(); ?>
        <fieldset id="give_<?php echo $this->id . '_' . $form_id; ?>" class="give-<?php echo $this->id; ?>-fieldset">
            <p>
                <input name="give_<?php echo $this->id; ?>_signup"
                       id="give_<?php echo $this->id . '_' . $form_id; ?>_signup"
                       type="checkbox" <?php echo($checked_option !== 'disabled' ? 'checked="checked"' : ''); ?>/>
                <label
                    for="give_<?php echo $this->id . '_' . $form_id; ?>_signup"><?php echo $this->checkbox_label; ?></label>
            </p>
        </fieldset>
        <?php
        echo ob_get_clean();
    }
    
    /**
     * Complete Donation Sign up.
     *
     * Check if a donor needs to be subscribed upon completing donation on a specific donation form.
     *
     * @param $payment_id
     * @param $payment_data array
     */
    public function completed_donation_signup($payment_id, $payment_data)
    {
        // Check to see if the user has elected to subscribe.
        if ( ! isset($_POST['give_' . $this->id . '_signup']) || $_POST['give_' . $this->id . '_signup'] !== 'on') {
            return;
        }
        
        $form_id = give_get_payment_form_id($payment_id);
        $lists = get_post_meta($form_id, '_give_' . $this->id, true);
        $tags = (array)get_post_meta($form_id, '_give_' . $this->id . '_tags', true);
        $override_option = get_post_meta($form_id, '_give_' . $this->id . '_override_option', true);
        
        // Use custom lists from this form?
        if ($override_option !== 'customize' || empty($lists)) {
            // Not set so use global list.
            $lists = [0 => give_get_option('give_' . $this->id . '_list')];
        }
        
        // Subscribe Lists if array.
        if (is_array($lists)) {
            $lists = array_unique($lists);
            foreach ($lists as $list) {
                // Subscribe the donor to the email lists.
                $this->subscribe_email($payment_data['user_info'], $list);
            }
        } else {
            // Subscribe to single.
            $this->subscribe_email($payment_data['user_info'], $lists);
        }
        
        // Use custom lists from this form?
        if ($override_option !== 'customize' || empty($tags)) {
            // Not set so use global tags.
            $tags = give_get_option('_give_' . $this->id . '_tags');
        }
        
        // Subscribe to tags
        if ( ! empty($tags)) {
            // Subscribe Tags if array.
            if (is_array($tags)) {
                $tags = array_unique($tags);
                foreach ($tags as $tag) {
                    // Subscribe the donor to the subscriber tag.
                    $this->subscribe_email($payment_data['user_info'], false, $tag);
                }
            } else {
                // Subscribe to single tag.
                $this->subscribe_email($payment_data['user_info'], $tags);
            }
        }
    }
    
    /**
     * Subscribe an email to a list.
     *
     * @param array       $user_info
     * @param bool|string $list_id
     * @param bool|string $tag_id
     *
     * @return bool
     */
    public function subscribe_email($user_info = [], $list_id = false, $tag_id = false)
    {
        // Make sure an API key has been entered.
        if (empty($this->api_key)) {
            return false;
        }
        
        // Retrieve the global list ID if none is provided.
        if ( ! $list_id && empty($tags)) {
            $list_id = give_get_option('give_' . $this->id . '_list', false);
            if ( ! $list_id) {
                return false;
            }
        }
        
        // Setup args.
        $args = apply_filters('give_' . $this->id . '_subscribe_vars', [
            'email' => $user_info['email'],
            'name'  => $user_info['first_name'] . ' ' . $user_info['last_name'],
        ]);
        
        $return = false;
        
        // Subscribe to Lists (Forms). Don't subscribe is $tag_id param is false.
        if (empty($tag_id)) {
            // Hit the API.
            $request = wp_remote_post(
                'https://api.convertkit.com/v3/forms/' . $list_id . '/subscribe?api_key=' . $this->api_key,
                [
                    'body'    => $args,
                    'timeout' => 30,
                ]
            );
            
            // Success!
            if ( ! is_wp_error($request) && 200 == wp_remote_retrieve_response_code($request)) {
                $return = true;
            }
        }
        
        // Subscribe to Tags.
        if ( ! empty($tag_id)) {
            // Hit the API.
            $request = wp_remote_post(
                'https://api.convertkit.com/v3/tags/' . $tag_id . '/subscribe?api_key=' . $this->api_key,
                [
                    'body'    => $args,
                    'timeout' => 15,
                ]
            );
            
            // Success!
            if ( ! is_wp_error($request) && 200 == wp_remote_retrieve_response_code($request)) {
                // @TODO: Write to donation payment notes
                $return = true;
            }
        }
        
        return $return;
    }
    
    
    /**
     * Show Line item on donation details screen if the donor opted-in to the newsletter.
     *
     * @param $payment_id
     */
    function donation_metabox_notification($payment_id)
    {
        $opt_in_meta = get_post_meta($payment_id, '_give_' . $this->id . '_donation_optin_status', true);
        
        if ($opt_in_meta) { ?>
            <div class="give-admin-box-inside">
                <p>
                    <span class="label"><?php echo $this->label; ?>:</span>&nbsp;
                    <span><?php _e('Opted-in', 'give-convertkit'); ?></span>
                </p>
            </div>
        <?php }
    }
    
    /**
     * Register the metabox on the 'give_forms' post type.
     */
    public function add_metabox()
    {
        if (current_user_can('edit_give_forms', get_the_ID())) {
            add_meta_box('give_' . $this->id, $this->label, [$this, 'render_metabox'], 'give_forms', 'side');
        }
    }
    
    /**
     * Display the metabox, which is a list of newsletter lists.
     */
    public function render_metabox()
    {
        global $post;
        
        // Add an nonce field so we can check for it later.
        wp_nonce_field('give_' . $this->id . '_meta_box', 'give_' . $this->id . '_meta_box_nonce');
        
        // Using a custom label?
        $custom_label = get_post_meta($post->ID, '_give_' . $this->id . '_custom_label', true);
        
        // Form select
        $list_value = get_post_meta($post->ID, '_give_' . $this->id, true);
        $list_value = ! empty($list_value) ? $list_value : give_get_option("give_{$this->id}_list");
        
        // Global label
        $global_label = isset($this->give_options['give_' . $this->id . '_label']) ? $this->give_options['give_' . $this->id . '_label'] : __(
            'Signup for the newsletter',
            'give-convertkit'
        );;
        
        // Globally enabled option.
        $globally_enabled = give_get_option('give_' . $this->id . '_show_subscribe_checkbox');
        $override_option = get_post_meta($post->ID, '_give_' . $this->id . '_override_option', true);
        $checked_option = get_post_meta($post->ID, '_give_' . $this->id . '_checked_default', true);
        
        // Start the buffer.
        ob_start(); ?>

        <div class="give-<?php echo $this->id; ?>-global-override-wrap">
            <label for="_give_<?php echo $this->id; ?>_custom_label"
                   style="font-weight:bold;"><?php _e('ConvertKit Options', 'give-convertkit'); ?></label>
            <span class="cmb2-metabox-description give-description"
                  style="margin: 0 0 10px;"><?php _e(
                    'Customize the options for this form or use the default global settings.',
                    'give-convertkit'
                ); ?></span>
            <ul class="cmb2-radio-list cmb2-list">

                <li>
                    <input type="radio" class="cmb2-option" name="_give_<?php echo $this->id; ?>_override_option"
                           id="give_<?php echo $this->id; ?>_override_option1"
                           value="default" <?php echo checked('', $override_option, false); ?><?php echo checked(
                        'default',
                        $override_option,
                        false
                    ); ?>>
                    <label
                        for="give_<?php echo $this->id; ?>_override_option1"><?php _e(
                            'Use Default',
                            'give-convertkit'
                        ); ?></label>
                </li>

                <li>
                    <input type="radio" class="cmb2-option" name="_give_<?php echo $this->id; ?>_override_option"
                           id="give_<?php echo $this->id; ?>_override_option2"
                           value="customize" <?php echo checked('customize', $override_option, false); ?>>
                    <label
                        for="give_<?php echo $this->id; ?>_override_option2"><?php _e(
                            'Customize',
                            'give-convertkit'
                        ); ?></label>
                </li>
                <li>
                    <input type="radio" class="cmb2-option" name="_give_<?php echo $this->id; ?>_override_option"
                           id="give_<?php echo $this->id; ?>_override_option3"
                           value="disabled" <?php echo checked('disabled', $override_option, false); ?>>
                    <label
                        for="give_<?php echo $this->id; ?>_override_option3"><?php _e(
                            'Disabled',
                            'give-convertkit'
                        ); ?></label>
                </li>
            </ul>
        </div>
        <div
            class="give-<?php echo $this->id; ?>-field-wrap" <?php echo($globally_enabled == false && empty($enable_option) ? "style='display:none;'" : '') ?>>
            <p>
                <label for="_give_<?php echo $this->id; ?>_custom_label"
                       style="font-weight:bold;"><?php _e('Custom Label', 'give-convertkit'); ?></label>
                <span class="cmb2-metabox-description give-description"
                      style="margin: 0 0 10px;"><?php echo sprintf(
                        __('Customize the label for the %1$s opt-in checkbox', 'give-convertkit'),
                        $this->label
                    ); ?></span>
                <input type="text" id="_give_<?php echo $this->id; ?>_custom_label"
                       name="_give_<?php echo $this->id; ?>_custom_label"
                       value="<?php echo esc_attr($custom_label); ?>"
                       placeholder="<?php echo esc_attr($global_label); ?>" style="width:100%;" />
            </p>
            
            <?php // Field: Default checked or unchecked option.
            ?>
            <div>

                <label for="_give_<?php echo $this->id; ?>_checked_default"
                       style="font-weight:bold;"><?php _e('Opt-in Default', 'give-convertkit'); ?></label>
                <span class="cmb2-metabox-description"
                      style="margin: 0 0 10px;"><?php _e(
                        'Customize the newsletter opt-in option for this form.',
                        'give-convertkit'
                    ); ?></span>

                <ul class="cmb2-radio-list cmb2-list">
                    <li>
                        <input type="radio" class="cmb2-option" name="_give_<?php echo $this->id; ?>_checked_default"
                               id="give_<?php echo $this->id; ?>_checked_default1"
                               value="" <?php echo checked('', $checked_option, false); ?>>
                        <label
                            for="give_<?php echo $this->id; ?>_checked_default1"><?php _e(
                                'Use Default',
                                'give-convertkit'
                            ); ?></label>
                    </li>

                    <li>
                        <input type="radio" class="cmb2-option" name="_give_<?php echo $this->id; ?>_checked_default"
                               id="give_<?php echo $this->id; ?>_checked_default2"
                               value="enabled" <?php echo checked('enabled', $checked_option, false); ?>>
                        <label
                            for="give_<?php echo $this->id; ?>_checked_default2"><?php _e(
                                'Checked',
                                'give-convertkit'
                            ); ?></label>
                    </li>
                    <li>
                        <input type="radio" class="cmb2-option" name="_give_<?php echo $this->id; ?>_checked_default"
                               id="give_<?php echo $this->id; ?>_checked_default3"
                               value="disabled" <?php echo checked('disabled', $checked_option, false); ?>>
                        <label
                            for="give_<?php echo $this->id; ?>_checked_default3"><?php _e(
                                'Unchecked',
                                'give-convertkit'
                            ); ?></label>
                    </li>
                </ul>

            </div>
            
            <?php // Field: subscription lists.
            ?>
            <div class="give-<?php echo $this->id; ?>-list-container">
                <label for="give_<?php echo $this->id; ?>_lists"
                       style="font-weight:bold; float:left;"><?php _e('ConvertKit Form', 'give-convertkit'); ?></label>
                <button class="give-reset-convertkit-button button button-small"
                        style="float:left; margin: -2px 0 0 15px;"
                        data-action="give_reset_convertkit_lists"
                        data-field_type="select"><?php echo esc_html__('Refresh Forms', 'give-convertkit'); ?></button>
                <span class="give-spinner spinner" style="float:left;margin: 0 0 0 10px;"></span>

                <span class="cmb2-metabox-description give-description"
                      style="margin: 10px 0; clear: both;"><?php _e(
                        'Customize the form you wish donors to subscribe to.',
                        'give-convertkit'
                    ); ?></span>

                <div class="give-<?php echo $this->id; ?>-select-wrap">
                    <select id="give_<?php echo $this->id; ?>_lists" name="_give_<?php echo $this->id; ?>"
                            class="give-<?php echo $this->id; ?>-select">
                        <?php
                        // Select options.
                        echo $this->get_list_options($this->get_lists(), $list_value, 'select'); ?>
                    </select>
                </div> <!-- give-convertkit-select-wrap-->
            </div> <!-- give-convertkit-list-container -->

            <div class="give-<?php echo $this->id; ?>-tag-container">
                <?php
                // Display tags if there are any in users' ConvertKit account.
                $tags = $this->get_tags();
                if ( ! empty($tags)) { ?>
                    <div class="give-convertkit-tag-label-wrap give-clearfix">
                        <label for="give_<?php echo $this->id; ?>_tags"
                               style="font-weight:bold; float:left;"><?php _e(
                                'Tag Subscribers',
                                'give-convertkit'
                            ); ?></label>
                        <button class="give-reset-tags-convertkit-button button button-small"
                                style="float:left; margin: -2px 0 0 15px;"
                                data-action="give_reset_convertkit_tags"
                                data-field_type="checkbox"><?php echo esc_html__(
                                'Refresh Tags',
                                'give-convertkit'
                            ); ?></button>
                        <span class="give-spinner spinner" style="float:left;margin: 0 0 0 10px;"></span>

                        <span class="cmb2-metabox-description give-description"
                              style="margin: 10px 0; clear: both;"><?php _e(
                                'Customize the tags you wish donors to subscribe to.',
                                'give-convertkit'
                            ); ?></span>

                    </div>
                    <div class="give-<?php echo $this->id; ?>-tag-wrap">
                        <?php
                        $checked = get_post_meta($post->ID, '_give_' . esc_attr($this->id) . '_tags', true);
                        $checked = ! empty($checked) ? $checked : $this->give_options['_give_' . $this->id . '_tags'];
                        echo $this->get_tag_options($this->get_tags(), $checked, 'checkbox'); ?>
                    </div>
                <?php } ?>
            </div>

        </div>
        <?php
        
        // Return the metabox.
        echo ob_get_clean();
    }
    
    /**
     * Save the metabox data.
     *
     * @param int $post_id The ID of the post being saved.
     *
     * @return bool|string
     */
    public function save_metabox($post_id)
    {
        $this->give_options = give_get_settings();
        
        /**
         * We need to verify this came from our screen and with proper authorization,
         * because the save_post action can be triggered at other times.
         */
        // Check if our nonce is set.
        if ( ! isset($_POST['give_' . $this->id . '_meta_box_nonce'])) {
            return false;
        }
        
        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce($_POST['give_' . $this->id . '_meta_box_nonce'], 'give_' . $this->id . '_meta_box')) {
        }
        
        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return false;
        }
        
        // Check the user's permissions.
        if ($_POST['post_type'] == 'give_forms') {
            if ( ! current_user_can('edit_give_forms', $post_id)) {
                return $post_id;
            }
        } else {
            if ( ! current_user_can('edit_give_forms', $post_id)) {
                return $post_id;
            }
        }
        
        // OK, its safe for us to save the data now.
        // Sanitize the user input.
        $give_custom_label = isset($_POST['_give_' . $this->id . '_custom_label']) ? sanitize_text_field(
            $_POST['_give_' . $this->id . '_custom_label']
        ) : '';
        $give_custom_lists = isset($_POST['_give_' . $this->id]) ? $_POST['_give_' . $this->id] : give_get_option(
            "give_{$this->id}_list"
        );
        
        $global_tags = isset($this->give_options['give_' . $this->id . '_tags']) ? $this->give_options['give_' . $this->id . '_tags'] : '';
        
        $give_custom_tags = isset($_POST['_give_' . $this->id]) ? $_POST['_give_' . $this->id . '_tags'] : $global_tags;
        $give_override_option = isset($_POST['_give_' . $this->id . '_override_option']) ? esc_html(
            $_POST['_give_' . $this->id . '_override_option']
        ) : '';
        $give_subscribe_checked = isset($_POST['_give_' . $this->id . '_checked_default']) ? esc_html(
            $_POST['_give_' . $this->id . '_checked_default']
        ) : '';
        
        // Update the meta field.
        update_post_meta($post_id, '_give_' . $this->id . '_custom_label', $give_custom_label);
        update_post_meta($post_id, '_give_' . $this->id, $give_custom_lists);
        update_post_meta($post_id, '_give_' . $this->id . '_tags', $give_custom_tags);
        update_post_meta($post_id, '_give_' . $this->id . '_override_option', $give_override_option);
        update_post_meta($post_id, '_give_' . $this->id . '_checked_default', $give_subscribe_checked);
        
        return true;
    }
    
    /**
     * Retrieves the lists from ConvertKit
     *
     * @return array
     */
    public function get_lists()
    {
        if ( ! empty($this->api_key)) {
            $lists = get_transient('give_' . $this->id . '_lists');
            
            if (false === $lists) {
                $request = wp_remote_get('https://api.convertkit.com/v3/forms?api_key=' . $this->api_key);
                
                if ( ! is_wp_error($request) && 200 == wp_remote_retrieve_response_code($request)) {
                    $lists = json_decode(wp_remote_retrieve_body($request));
                    
                    set_transient('give_' . $this->id . '_lists', $lists, 24 * 24 * 24);
                }
            }
            
            if ( ! empty($lists) && ! empty($lists->forms)) {
                foreach ($lists->forms as $key => $form) {
                    $this->lists[$form->id] = $form->name;
                }
            }
        }
        
        return (array)$this->lists;
    }
    
    /**
     * Retrieve ConvertKil tags.
     *
     * @return array
     */
    public function get_tags()
    {
        if ( ! empty($this->api_key)) {
            $tags = get_transient('give_convertkit_tags');
            
            if (false === $tags) {
                $request = wp_remote_get('https://api.convertkit.com/v3/tags?api_key=' . $this->api_key);
                
                if ( ! is_wp_error($request) && 200 == wp_remote_retrieve_response_code($request)) {
                    $tags = json_decode(wp_remote_retrieve_body($request));
                    
                    set_transient('give_convertkit_tags', $tags, 24 * 24 * 24);
                }
            }
            
            if ( ! empty($tags) && ! empty($tags->tags)) {
                foreach ($tags->tags as $key => $tag) {
                    $this->tags[$tag->id] = $tag->name;
                }
            }
        }
        
        return (array)$this->tags;
    }
    
    /**
     * Register sections.
     *
     * @since  1.0.3
     * @access public
     *
     * @param array $sections List of sections.
     *
     * @return mixed
     */
    public function register_sections($sections)
    {
        $sections['convertkit-settings'] = __('Convertkit Settings', 'give-convertkit');
        
        return $sections;
    }
    
    /**
     * Registers the plugin's settings.
     *
     * @param $settings
     *
     * @return array
     */
    public function register_settings($settings)
    {
        switch (give_get_current_setting_section()) {
            case 'convertkit-settings':
                $settings = [
                    [
                        'id'   => 'give_title_convertkit',
                        'type' => 'title',
                    ],
                    [
                        'name' => __('ConvertKit Settings', 'give-convertkit'),
                        'desc' => '<hr>',
                        'id'   => 'give_title_' . $this->id,
                        'type' => 'give_title',
                    ],
                    [
                        'id'   => 'give_convertkit_api',
                        'name' => __('ConvertKit API Key', 'give-convertkit'),
                        'desc' => sprintf(
                            __(
                                'Enter your ConvertKit API key. You may retrieve your ConvertKit API key from your <a href="%s" target="_blank" title="Will open new window">account settings</a>.',
                                'give-convertkit'
                            ),
                            'https://app.convertkit.com/account/edit'
                        ),
                        'type' => 'text',
                        'size' => 'regular',
                    ],
                    [
                        'id'   => 'give_convertkit_api_secret',
                        'name' => __('ConvertKit API Secret', 'give-convertkit'),
                        'desc' => sprintf(
                            __(
                                'Enter your ConvertKit API Secret. You may retrieve your ConvertKit API Secret from your <a href="%s" target="_blank" title="Will open new window">account settings</a>.',
                                'give-convertkit'
                            ),
                            'https://app.convertkit.com/account/edit'
                        ),
                        'type' => 'text',
                        'size' => 'regular',
                    ],
                    [
                        'id'      => 'give_convertkit_show_subscribe_checkbox',
                        'name'    => __('Enable Globally', 'give-convertkit'),
                        'desc'    => __(
                            'Allow donors to sign up for the forms selected below on all donation forms? Note: the forms(s) can be customized per form.',
                            'give-convertkit'
                        ),
                        'type'    => 'radio_inline',
                        'default' => 'enabled',
                        'options' => [
                            'enabled'  => __('Enabled', 'give-convertkit'),
                            'disabled' => __('Disabled', 'give-convertkit'),
                        ],
                    ],
                    [
                        'id'   => 'give_convertkit_list',
                        'name' => __('Choose a Form', 'give-convertkit'),
                        'desc' => __('Select the form you wish to subscribe donors to by default.', 'give-convertkit'),
                        'type' => 'convertkit_list_select',
                    ],
                    [
                        'id'   => '_give_convertkit_tags',
                        'name' => __('Choose Tags', 'give-convertkit'),
                        'desc' => __('Select the tags you wish to subscribe donors to by default.', 'give-convertkit'),
                        'type' => 'convertkit_tag_list',
                    ],
                    [
                        'id'      => 'give_convertkit_checked_default',
                        'name'    => __('Opt-in Default', 'give-convertkit'),
                        'desc'    => __(
                            'Would you like the newsletter opt-in checkbox checked by default? This option can be customized per form.',
                            'give-convertkit'
                        ),
                        'options' => [
                            'enabled'  => __('Checked', 'give-convertkit'),
                            'disabled' => __('Unchecked', 'give-convertkit'),
                        ],
                        'default' => 'enabled',
                        'type'    => 'radio_inline',
                    ],
                    [
                        'id'         => 'give_convertkit_label',
                        'name'       => __('Default Label', 'give-convertkit'),
                        'desc'       => __(
                            'This is the text shown next to the signup option. This can also be customized per form.',
                            'give-convertkit'
                        ),
                        'type'       => 'text',
                        'size'       => 'regular',
                        'attributes' => [
                            'placeholder' => __('Subscribe to our newsletter', 'give-convertkit'),
                        ],
                    ],
                    [
                        'id'   => 'give_title_convertkit',
                        'type' => 'sectionend',
                    ],
                ];
                break;
        }
        
        return $settings;
    }
    
    /**
     * Determines if the checkout signup option should be displayed.
     *
     * @param $form_id
     *
     * @return bool
     */
    public function show_subscribe_checkbox($form_id)
    {
        $override_option = get_post_meta($form_id, '_give_' . $this->id . '_override_option', true);
        $global_option = give_get_option("give_{$this->id}_show_subscribe_checkbox");
        
        // Is disabled on the form?
        if ($override_option == 'disabled') {
            return false;
        } elseif ($global_option == 'disabled' && $override_option == 'default'
                  || $global_option == 'disabled' && empty($override_option)
        ) {
            // Global option = disabled and override option = default;
            // OR global option = disable and override option not present.
            return false;
        } else {
            // Default to true.
            return true;
        }
    }
    
    /**
     * Give add ConvertKit list select with refresh button.
     *
     * @since  1.0.3
     * @access public
     *
     * @param $value
     *
     * @param $field
     */
    public function convertkit_list_select_field($field, $value)
    {
        ob_start();
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="<?php echo esc_attr($field['id']); ?>">
                    <?php echo esc_attr($field['name']); ?>
                </label>
            </th>
            <td scope="row" class="">
                <select class="give-convertkit-list-select" name="<?php echo "{$field['id']}"; ?>"
                        id="<?php echo "{$field['id']}"; ?>">
                    <?php echo $this->get_list_options($this->get_lists(), $value); ?>
                </select>

                <button class="give-reset-convertkit-button button-secondary" style="margin:3px 0 0 2px !important;"
                        data-action="give_reset_convertkit_lists" data-field_type="select">
                    <?php echo esc_html__('Refresh Lists', 'give-convertkit'); ?>
                </button>
                <span class="give-spinner spinner"></span>

                <p class="give-description"><?php echo "{$field['desc']}"; ?></p>
            </td>
        </tr>
        <?php
        echo ob_get_clean();
    }
    
    /**
     * Give add ConvertKit tags select with refresh button.
     *
     * @since  1.0.3
     * @access public
     *
     * @param $value
     *
     * @param $field
     */
    public function convertkit_tag_list_field($field, $value)
    {
        ob_start();
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="<?php echo esc_attr($field['id']); ?>">
                    <?php echo esc_attr($field['name']); ?>
                </label>
            </th>
            <td scope="row" class="">
                <div class="give-<?php echo $this->id; ?>-tag-wrap">
                    <?php
                    $checked = give_get_option("{$field['id']}");
                    echo $this->get_tag_options($this->get_tags(), $checked, 'checkbox'); ?>
                </div>

                <p class="give-description"><?php echo "{$field['desc']}"; ?></p>

                <button class="give-reset-tags-convertkit-button button-secondary" style="margin:3px 0 0 0 !important;"
                        data-action="give_reset_convertkit_tags"
                        data-field_type="checklist"><?php esc_html_e('Refresh Tags', 'give-convertkit'); ?></button>
                <span class="give-spinner spinner"></span>
            </td>
        </tr>
        <?php
        echo ob_get_clean();
    }
    
    /**
     * Get the list options in an appropriate field format. This is used to output on page load and also refresh via AJAX.
     *
     * @param        $lists
     * @param string $value
     * @param string $field_type
     *
     * @return string
     */
    public function get_list_options($lists, $value = '', $field_type = 'select')
    {
        $options = '';
        
        if ($field_type == 'select') {
            // Select options
            foreach ($lists as $list_id => $list) {
                $options .= '<option value="' . $list_id . '"' . selected(
                        $value,
                        $list_id,
                        false
                    ) . '>' . $list . '</option>';
            }
        } else {
            // Checkboxes.
            foreach ($lists as $list_id => $list_name) {
                $options .= '<label class="list"><input type="checkbox" name="_give_' . esc_attr(
                        $this->id
                    ) . '[]"  value="' . esc_attr($list_id) . '" ' . checked(
                                true,
                                in_array($list_id, $value),
                                false
                            ) . '> <span>' . $list_name . '</span></label>';
            }
        }
        
        return $options;
    }
    
    /**
     * Get the tag options in an appropriate field format.
     *
     * This is used to output on page load and also refresh via AJAX.
     *
     * @param        $tags
     * @param string $value
     * @param string $field_type
     *
     * @return string
     */
    public function get_tag_options($tags, $value = '', $field_type = 'checklist')
    {
        $options = '';
        
        if ($field_type == 'select') {
            // Select options
            foreach ($tags as $tag_id => $tag_name) {
                $options .= '<option value="' . $tag_id . '"' . selected(
                        $value,
                        $tag_id,
                        false
                    ) . '>' . $tag_name . '</option>';
            }
        } else{
            // Checkboxes.
            foreach ($tags as $tag_id => $tag_name) {
                $options .= '<label class="list"><input type="checkbox" name="_give_' . esc_attr(
                        $this->id
                    ) . '_tags[]"  value="' . esc_attr($tag_id) . '" ' . checked(
                                true,
                        is_array($value) && in_array($tag_id, $value),
                                false
                            ) . '> <span>' . $tag_name . '</span></label>';
            }
        }
        
        return $options;
    }
    
    /**
     * AJAX reset ConvertKit lists.
     */
    public function give_reset_convertkit_lists()
    {
        // Delete transient.
        delete_transient('give_convertkit_lists');
        $lists = '';
        
        if (isset($_POST['field_type']) && $_POST['field_type'] == 'select') {
            $lists = $this->get_list_options($this->get_lists(), give_get_option('give_convertkit_list'));
        } elseif (isset($_POST['post_id'])) {
            $lists = $this->get_list_options(
                $this->get_lists(),
                get_post_meta($_POST['post_id'], '_give_convertkit', true),
                'checkboxes'
            );
        } else {
            wp_send_json_error();
        }
        
        $return = [
            'lists' => $lists,
        ];
        
        wp_send_json_success($return);
    }
    
    
    /**
     * AJAX reset ConvertKit lists.
     */
    public function give_reset_convertkit_tags()
    {
        // Delete transient.
        delete_transient('give_convertkit_tags');
        $lists = '';
        
        if (isset($_POST['field_type']) && $_POST['field_type'] == 'select') {
            $lists = $this->get_tag_options($this->get_tags(), give_get_option('give_convertkit_tags'));
        } elseif (isset($_POST['post_id'])) {
            $lists = $this->get_tag_options(
                $this->get_tags(),
                get_post_meta($_POST['post_id'], '_give_convertkit_tags', true),
                'checkboxes'
            );
        } else {
            wp_send_json_error();
        }
        
        $return = [
            'lists' => $lists,
        ];
        
        wp_send_json_success($return);
    }
    
}
