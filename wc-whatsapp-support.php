<?php
/**
* Plugin Name: WooCommerce WhatsApp Support Pro
 * Plugin URI: https://keycart.net/
 * Description: Modern WhatsApp integration for WooCommerce with live chat support
 * Version: 1.1.0
 * Author: Wahba    
 * Author URI: https://github.com/Realwahba
 * License: GPL v2 or later
 * Text Domain: wc-whatsapp-support
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if WooCommerce is active
function wcws_check_woocommerce() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>WhatsApp Support requires WooCommerce to be installed and active.</p></div>';
        });
        return false;
    }
    return true;
}

/**
 * Main Plugin Class
 */
class WC_WhatsApp_Support_Fixed {
    
    public function __construct() {
        // Check if WooCommerce is active
        if (!wcws_check_woocommerce()) {
            return;
        }
        
        // Admin hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        
        // Frontend hooks - Use proper priority to avoid conflicts
        add_action('init', array($this, 'init_frontend_hooks'));
        
        // Add styles in wp_head to avoid conflicts
        add_action('wp_head', array($this, 'add_styles'));
    }
    
    /**
     * Initialize frontend hooks only when needed
     */
    public function init_frontend_hooks() {
        if (get_option('wcws_enabled') === 'yes') {
            // Add button to product summary with proper priority
            $position = intval(get_option('wcws_button_position', '35'));
            add_action('woocommerce_single_product_summary', array($this, 'display_whatsapp_button'), $position);
            
            // Add floating button
            if (get_option('wcws_floating_enabled') === 'yes') {
                add_action('wp_footer', array($this, 'display_floating_button'));
            }
        }
    }
    
    /**
     * Add Admin Menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'WhatsApp Support',
            'WhatsApp Support',
            'manage_options',
            'wc-whatsapp-support',
            array($this, 'admin_page'),
            'dashicons-format-chat',
            56
        );
    }
    
    /**
     * Register Settings
     */
    public function register_settings() {
        register_setting('wcws_settings', 'wcws_enabled');
        register_setting('wcws_settings', 'wcws_country_code');
        register_setting('wcws_settings', 'wcws_phone_number');
        register_setting('wcws_settings', 'wcws_message');
        register_setting('wcws_settings', 'wcws_button_text');
        register_setting('wcws_settings', 'wcws_floating_enabled');
        register_setting('wcws_settings', 'wcws_button_position');
        
        // Set defaults only if not set
        if (get_option('wcws_enabled') === false) {
            add_option('wcws_enabled', 'yes');
            add_option('wcws_country_code', '+1');
            add_option('wcws_phone_number', '');
            add_option('wcws_message', "Hi! I'm interested in: {product_name} - {product_url}");
            add_option('wcws_button_text', 'WhatsApp');
            add_option('wcws_floating_enabled', 'yes');
            add_option('wcws_button_position', '35');
        }
    }
    
    /**
     * Admin Page
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('WhatsApp Support Settings', 'wc-whatsapp-support'); ?></h1>
            
            <?php if (isset($_GET['settings-updated'])): ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php _e('Settings saved successfully!', 'wc-whatsapp-support'); ?></p>
                </div>
            <?php endif; ?>
            
            <form method="post" action="options.php">
                <?php settings_fields('wcws_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Enable WhatsApp Support', 'wc-whatsapp-support'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="wcws_enabled" value="yes" <?php checked(get_option('wcws_enabled'), 'yes'); ?> />
                                <?php _e('Enable WhatsApp button on product pages', 'wc-whatsapp-support'); ?>
                            </label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('WhatsApp Number', 'wc-whatsapp-support'); ?></th>
                        <td>
                            <select name="wcws_country_code" style="width: 200px;">
                                <option value="+1" <?php selected(get_option('wcws_country_code'), '+1'); ?>>🇺🇸 USA (+1)</option>
                                <option value="+44" <?php selected(get_option('wcws_country_code'), '+44'); ?>>🇬🇧 UK (+44)</option>
                                <option value="+20" <?php selected(get_option('wcws_country_code'), '+20'); ?>>🇪🇬 Egypt (+20)</option>
                                <option value="+971" <?php selected(get_option('wcws_country_code'), '+971'); ?>>🇦🇪 UAE (+971)</option>
                                <option value="+966" <?php selected(get_option('wcws_country_code'), '+966'); ?>>🇸🇦 Saudi Arabia (+966)</option>
                                <option value="+91" <?php selected(get_option('wcws_country_code'), '+91'); ?>>🇮🇳 India (+91)</option>
                                <option value="+92" <?php selected(get_option('wcws_country_code'), '+92'); ?>>🇵🇰 Pakistan (+92)</option>
                                <option value="+62" <?php selected(get_option('wcws_country_code'), '+62'); ?>>🇮🇩 Indonesia (+62)</option>
                                <option value="+234" <?php selected(get_option('wcws_country_code'), '+234'); ?>>🇳🇬 Nigeria (+234)</option>
                                <option value="+55" <?php selected(get_option('wcws_country_code'), '+55'); ?>>🇧🇷 Brazil (+55)</option>
                                <option value="+52" <?php selected(get_option('wcws_country_code'), '+52'); ?>>🇲🇽 Mexico (+52)</option>
                                <option value="+49" <?php selected(get_option('wcws_country_code'), '+49'); ?>>🇩🇪 Germany (+49)</option>
                                <option value="+33" <?php selected(get_option('wcws_country_code'), '+33'); ?>>🇫🇷 France (+33)</option>
                                <option value="+39" <?php selected(get_option('wcws_country_code'), '+39'); ?>>🇮🇹 Italy (+39)</option>
                                <option value="+34" <?php selected(get_option('wcws_country_code'), '+34'); ?>>🇪🇸 Spain (+34)</option>
                                <option value="+90" <?php selected(get_option('wcws_country_code'), '+90'); ?>>🇹🇷 Turkey (+90)</option>
                                <option value="+27" <?php selected(get_option('wcws_country_code'), '+27'); ?>>🇿🇦 South Africa (+27)</option>
                                <option value="+61" <?php selected(get_option('wcws_country_code'), '+61'); ?>>🇦🇺 Australia (+61)</option>
                            </select>
                            <input type="text" name="wcws_phone_number" value="<?php echo esc_attr(get_option('wcws_phone_number')); ?>" placeholder="123456789" style="width: 200px;" />
                            <p class="description"><?php _e('Enter phone number without country code', 'wc-whatsapp-support'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Button Text', 'wc-whatsapp-support'); ?></th>
                        <td>
                            <input type="text" name="wcws_button_text" value="<?php echo esc_attr(get_option('wcws_button_text')); ?>" class="regular-text" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Button Position', 'wc-whatsapp-support'); ?></th>
                        <td>
                            <select name="wcws_button_position">
                                <option value="15" <?php selected(get_option('wcws_button_position'), '15'); ?>><?php _e('After Product Price', 'wc-whatsapp-support'); ?></option>
                                <option value="25" <?php selected(get_option('wcws_button_position'), '25'); ?>><?php _e('After Product Short Description', 'wc-whatsapp-support'); ?></option>
                                <option value="35" <?php selected(get_option('wcws_button_position'), '35'); ?>><?php _e('After Add to Cart Button', 'wc-whatsapp-support'); ?></option>
                                <option value="45" <?php selected(get_option('wcws_button_position'), '45'); ?>><?php _e('After Product Meta', 'wc-whatsapp-support'); ?></option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Pre-filled Message', 'wc-whatsapp-support'); ?></th>
                        <td>
                            <textarea name="wcws_message" rows="3" class="large-text"><?php echo esc_textarea(get_option('wcws_message')); ?></textarea>
                            <p class="description">
                                <?php _e('Available variables:', 'wc-whatsapp-support'); ?><br>
                                <code>{product_name}</code> - <?php _e('Product name', 'wc-whatsapp-support'); ?><br>
                                <code>{product_url}</code> - <?php _e('Product URL', 'wc-whatsapp-support'); ?><br>
                                <code>{product_price}</code> - <?php _e('Product price', 'wc-whatsapp-support'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Floating Button', 'wc-whatsapp-support'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="wcws_floating_enabled" value="yes" <?php checked(get_option('wcws_floating_enabled'), 'yes'); ?> />
                                <?php _e('Show floating WhatsApp button', 'wc-whatsapp-support'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
            
            <div style="background: #f1f1f1; padding: 20px; margin-top: 20px; border-radius: 5px;">
                <h3><?php _e('Test Your WhatsApp Link', 'wc-whatsapp-support'); ?></h3>
                <?php
                $test_number = str_replace(array('+', ' ', '-'), '', get_option('wcws_country_code') . get_option('wcws_phone_number'));
                if (!empty($test_number)) {
                    $test_url = 'https://wa.me/' . $test_number . '?text=' . urlencode('Test message');
                    echo '<p>' . __('Your WhatsApp URL:', 'wc-whatsapp-support') . ' <a href="' . esc_url($test_url) . '" target="_blank">' . esc_html($test_url) . '</a></p>';
                } else {
                    echo '<p style="color: red;">' . __('Please enter your WhatsApp number above.', 'wc-whatsapp-support') . '</p>';
                }
                ?>
            </div>
            
            <div style="background: #fff3cd; padding: 15px; margin-top: 20px; border-left: 4px solid #ffc107; border-radius: 5px;">
                <h4 style="margin-top: 0;">⚠️ Troubleshooting</h4>
                <p>If the button is causing issues with your product page:</p>
                <ol>
                    <li>Try changing the "Button Position" setting above</li>
                    <li>Clear your cache (both browser and any caching plugins)</li>
                    <li>Check for theme conflicts by temporarily switching to a default WooCommerce theme</li>
                    <li>Ensure WooCommerce is up to date</li>
                </ol>
            </div>
        </div>
        <?php
    }
    
    /**
     * Display WhatsApp Button on Product Page
     */
    public function display_whatsapp_button() {
        // Safety check - ensure we're on a product page
        if (!is_product()) {
            return;
        }
        
        // Get the global product object safely
        global $product;
        
        // Check if product exists
        if (!$product || !is_object($product)) {
            return;
        }
        
        $country_code = get_option('wcws_country_code');
        $phone_number = get_option('wcws_phone_number');
        $button_text = get_option('wcws_button_text');
        $message_template = get_option('wcws_message');
        
        // Check if phone number is set
        if (empty($phone_number)) {
            return;
        }
        
        // Build WhatsApp URL
        $whatsapp_number = str_replace(array('+', ' ', '-', '(', ')'), '', $country_code . $phone_number);
        
        // Get product details safely
        $product_name = '';
        $product_url = '';
        $product_price = '';
        
        if (method_exists($product, 'get_name')) {
            $product_name = $product->get_name();
        }
        
        if (method_exists($product, 'get_id')) {
            $product_url = get_permalink($product->get_id());
        }
        
        if (method_exists($product, 'get_price')) {
            $product_price = $product->get_price();
        }
        
        // Replace variables in message
        $message = str_replace(
            array('{product_name}', '{product_url}', '{product_price}'),
            array($product_name, $product_url, $product_price),
            $message_template
        );
        
        $whatsapp_url = 'https://wa.me/' . $whatsapp_number . '?text=' . urlencode($message);
        
        // Output the button with error handling
        ?>
        <div class="wcws-whatsapp-button-wrapper" style="margin: 20px 0; clear: both;">
            <div class="wcws-whatsapp-inline">
                <span class="wcws-label">
                    <span class="wcws-label-icon">💬</span>
                    <span class="wcws-label-text">Need Help?</span>
                </span>
                <a href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" class="wcws-whatsapp-btn">
                    <span class="wcws-btn-bg"></span>
                    <span class="wcws-btn-content">
                        <svg class="wcws-icon" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.149-.67.149-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414-.074-.123-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                        </svg>
                        <span class="wcws-btn-text"><?php echo esc_html($button_text); ?></span>
                        <span class="wcws-status-badge">
                            <span class="wcws-status-dot"></span>
                            <span class="wcws-status-text">LIVE</span>
                        </span>
                    </span>
                </a>
            </div>
        </div>
        <?php
    }
    
    /**
     * Display Floating Button
     */
    public function display_floating_button() {
        // Only show on product pages
        if (!is_product()) {
            return;
        }
        
        $country_code = get_option('wcws_country_code');
        $phone_number = get_option('wcws_phone_number');
        
        if (empty($phone_number)) {
            return;
        }
        
        $whatsapp_number = str_replace(array('+', ' ', '-', '(', ')'), '', $country_code . $phone_number);
        $message = urlencode("Hi! I need help with my order.");
        $whatsapp_url = 'https://wa.me/' . $whatsapp_number . '?text=' . $message;
        
        ?>
        <div class="wcws-floating-whatsapp">
            <a href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" class="wcws-floating-btn">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.149-.67.149-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414-.074-.123-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                </svg>
            </a>
        </div>
        <?php
    }
    
    /**
     * Add Styles
     */
    public function add_styles() {
        // Only add styles on product pages
        if (!is_product() || get_option('wcws_enabled') !== 'yes') {
            return;
        }
        ?>
        <style type="text/css">
            /* Main Button Styles */
            .wcws-whatsapp-button-wrapper {
                clear: both;
                display: block;
            }
            
            .wcws-whatsapp-inline {
                display: inline-flex;
                align-items: center;
                gap: 12px;
                animation: fadeInScale 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            }
            
            @keyframes fadeInScale {
                0% {
                    opacity: 0;
                    transform: scale(0.9);
                }
                100% {
                    opacity: 1;
                    transform: scale(1);
                }
            }
            
            .wcws-label {
                display: flex;
                align-items: center;
                gap: 6px;
                color: #64748b;
                font-size: 14px;
                font-weight: 500;
            }
            
            .wcws-label-icon {
                font-size: 18px;
                animation: bounce 2s infinite;
            }
            
            @keyframes bounce {
                0%, 100% {
                    transform: translateY(0);
                }
                50% {
                    transform: translateY(-3px);
                }
            }
            
            .wcws-whatsapp-btn {
                display: inline-flex;
                align-items: center;
                position: relative;
                padding: 0;
                text-decoration: none !important;
                border-radius: 30px;
                overflow: hidden;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 0 4px 15px rgba(37, 211, 102, 0.2);
            }
            
            .wcws-whatsapp-btn:hover {
                transform: translateY(-2px) scale(1.05);
                box-shadow: 0 8px 25px rgba(37, 211, 102, 0.4);
            }
            
            .wcws-btn-bg {
                position: absolute;
                inset: 0;
                background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
                z-index: 1;
            }
            
            .wcws-btn-content {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 10px 18px;
                color: white !important;
                position: relative;
                z-index: 3;
            }
            
            .wcws-icon {
                width: 20px;
                height: 20px;
                filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
            }
            
            .wcws-btn-text {
                font-weight: 600;
                font-size: 14px;
                letter-spacing: 0.3px;
                text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
                color: white !important;
            }
            
            .wcws-status-badge {
                display: flex;
                align-items: center;
                gap: 4px;
                padding: 3px 8px;
                background: rgba(255, 255, 255, 0.25);
                backdrop-filter: blur(10px);
                border-radius: 12px;
                margin-left: 4px;
            }
            
            .wcws-status-dot {
                width: 6px;
                height: 6px;
                background: #4ade80;
                border-radius: 50%;
                animation: blink 1.5s infinite;
            }
            
            @keyframes blink {
                0%, 100% {
                    opacity: 1;
                    box-shadow: 0 0 0 2px rgba(74, 222, 128, 0.3);
                }
                50% {
                    opacity: 0.5;
                }
            }
            
            .wcws-status-text {
                font-size: 10px;
                font-weight: 700;
                letter-spacing: 0.5px;
                color: white !important;
            }
            
            /* Floating Button Styles */
            .wcws-floating-whatsapp {
                position: fixed;
                bottom: 30px;
                right: 30px;
                z-index: 9999;
                animation: slideUp 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            }
            
            @keyframes slideUp {
                0% {
                    opacity: 0;
                    transform: translateY(100px);
                }
                100% {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .wcws-floating-btn {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 60px;
                height: 60px;
                background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
                border-radius: 50%;
                text-decoration: none;
                box-shadow: 0 5px 20px rgba(37, 211, 102, 0.4);
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            
            .wcws-floating-btn:hover {
                transform: scale(1.1) rotate(5deg);
                box-shadow: 0 8px 30px rgba(37, 211, 102, 0.6);
            }
            
            .wcws-floating-btn svg {
                width: 28px;
                height: 28px;
                color: white;
                fill: white;
            }
            
            /* Mobile Responsive */
            @media (max-width: 768px) {
                .wcws-label-text {
                    display: none;
                }
                
                .wcws-btn-text {
                    font-size: 13px;
                }
                
                .wcws-floating-whatsapp {
                    bottom: 20px;
                    right: 20px;
                }
                
                .wcws-floating-btn {
                    width: 50px;
                    height: 50px;
                }
                
                .wcws-floating-btn svg {
                    width: 24px;
                    height: 24px;
                }
            }
        </style>
        <?php
    }
}

// Initialize the plugin
add_action('plugins_loaded', function() {
    new WC_WhatsApp_Support_Fixed();
});
?>