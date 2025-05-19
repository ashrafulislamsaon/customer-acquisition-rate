<?php
/**
 * Plugin Name: WooCommerce Customer Acquisition Tracker
 * Plugin URI: https://boomdevs.com/plugins/woocommerce-customer-acquisition-tracker
 * Description: Track and analyze customer acquisition rates and retention metrics for your WooCommerce store.
 * Version: 1.0.0
 * Author: Boomdevs
 * Author URI: https://boomdevs.com
 * Text Domain: woo-customer-acquisition
 * Domain Path: /languages
 * WC requires at least: 5.0.0
 * WC tested up to: 8.0.0
 * Requires PHP: 7.2
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WOO_CAT_VERSION', '1.0.0');
define('WOO_CAT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WOO_CAT_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main Plugin Class
 */
class WooCommerce_Customer_Acquisition_Tracker {
    /**
     * Constructor
     */
    public function __construct() {
        // Check if WooCommerce is active
        if (!$this->is_woocommerce_active()) {
            add_action('admin_notices', array($this, 'woocommerce_not_active_notice'));
            return;
        }

        // Plugin initialization
        add_action('plugins_loaded', array($this, 'init'));
    }

    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain for translations
        load_plugin_textdomain('woo-customer-acquisition', false, dirname(plugin_basename(__FILE__)) . '/languages');

        // Register admin menu
        add_action('admin_menu', array($this, 'register_admin_menu'));

        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));

        // Register Ajax handlers
        add_action('wp_ajax_woo_cat_get_customer_data', array($this, 'ajax_get_customer_data'));
        add_action('wp_ajax_woo_cat_export_report', array($this, 'ajax_export_report'));

        // Add custom meta for tracking first order date
        add_action('woocommerce_checkout_update_user_meta', array($this, 'track_first_order'), 10, 2);
    }

    /**
     * Check if WooCommerce is active
     */
    private function is_woocommerce_active() {
        return in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')));
    }

    /**
     * Admin notice if WooCommerce is not active
     */
    public function woocommerce_not_active_notice() {
        ?>
        <div class="notice notice-error">
            <p><?php _e('WooCommerce Customer Acquisition Tracker requires WooCommerce to be installed and active.', 'woo-customer-acquisition'); ?></p>
        </div>
        <?php
    }

    /**
     * Register admin menu
     */
    public function register_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('Customer Acquisition', 'woo-customer-acquisition'),
            __('Customer Acquisition', 'woo-customer-acquisition'),
            'manage_woocommerce',
            'woo-customer-acquisition',
            array($this, 'render_admin_page')
        );
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_assets($hook) {
        // Only enqueue on our plugin page
        if ($hook != 'woocommerce_page_woo-customer-acquisition') {
            return;
        }

        // Enqueue Chart.js
        wp_enqueue_script('chartjs', 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js', array(), '3.7.1', true);
        
        // Enqueue plugin styles
        wp_enqueue_style('woo-cat-admin-styles', WOO_CAT_PLUGIN_URL . 'assets/css/admin.css', array(), WOO_CAT_VERSION);
        
        // Enqueue plugin scripts
        wp_enqueue_script('woo-cat-admin-scripts', WOO_CAT_PLUGIN_URL . 'assets/js/admin.js', array('jquery', 'chartjs'), WOO_CAT_VERSION, true);
        
        // Localize script for Ajax
        wp_localize_script('woo-cat-admin-scripts', 'wooCatData', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('woo_cat_nonce'),
        ));
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
        include WOO_CAT_PLUGIN_DIR . 'templates/admin-dashboard.php';
    }

    /**
     * Track first order date for new customers
     */
    public function track_first_order($customer_id, $meta_data) {
        // Check if this is the customer's first order
        $first_order_date = get_user_meta($customer_id, '_woo_cat_first_order_date', true);
        
        if (empty($first_order_date)) {
            update_user_meta($customer_id, '_woo_cat_first_order_date', current_time('mysql'));
        }
    }

    /**
     * Ajax handler for getting customer data
     */
    public function ajax_get_customer_data() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'woo_cat_nonce')) {
            wp_send_json_error('Invalid nonce');
        }

        // Get report parameters
        $time_period = isset($_POST['time_period']) ? sanitize_text_field($_POST['time_period']) : 'monthly';
        $date_range = isset($_POST['date_range']) ? sanitize_text_field($_POST['date_range']) : 'last-6';
        
        // Get customer data
        $data = $this->get_customer_acquisition_data($time_period, $date_range);
        
        wp_send_json_success($data);
    }

    /**
     * Ajax handler for exporting report
     */
    public function ajax_export_report() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'woo_cat_nonce')) {
            wp_send_json_error('Invalid nonce');
        }

        // Get report parameters
        $time_period = isset($_POST['time_period']) ? sanitize_text_field($_POST['time_period']) : 'monthly';
        $date_range = isset($_POST['date_range']) ? sanitize_text_field($_POST['date_range']) : 'last-6';
        
        // Get customer data
        $data = $this->get_customer_acquisition_data($time_period, $date_range);
        
        // Generate CSV
        $csv_data = $this->generate_csv_data($data);
        
        // Send CSV headers
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="customer_acquisition_report.csv"');
        
        echo $csv_data;
        exit;
    }

    /**
     * Get customer acquisition data
     */
    private function get_customer_acquisition_data($time_period, $date_range) {
        global $wpdb;
        
        // Calculate date ranges based on period and range
        $date_intervals = $this->calculate_date_intervals($time_period, $date_range);
        
        $result = array(
            'periods' => array(),
            'new_customers' => array(),
            'total_customers' => array(),
            'acquisition_rates' => array(),
            'returning_customers' => array(),
            'summary' => array(
                'current_rate' => 0,
                'previous_rate' => 0,
                'average_rate' => 0,
                'current_new_customers' => 0,
                'current_total_customers' => 0
            )
        );
        
        // For each period, get customer data
        foreach ($date_intervals as $index => $interval) {
            $start_date = $interval['start'];
            $end_date = $interval['end'];
            $period_label = $interval['label'];
            
            // Get new customers in this period
            $new_customers = $this->get_new_customers_count($start_date, $end_date);
            
            // Get total customers up to end of period
            $total_customers = $this->get_total_customers_count($end_date);
            
            // Get returning customers in this period
            $returning_customers = $this->get_returning_customers_count($start_date, $end_date);
            
            // Calculate acquisition rate
            $acquisition_rate = ($total_customers > 0) ? round(($new_customers / $total_customers) * 100, 1) : 0;
            
            // Store data
            $result['periods'][] = $period_label;
            $result['new_customers'][] = $new_customers;
            $result['total_customers'][] = $total_customers;
            $result['acquisition_rates'][] = $acquisition_rate;
            $result['returning_customers'][] = $returning_customers;
            
            // Store summary data for current period (most recent)
            if ($index === 0) {
                $result['summary']['current_rate'] = $acquisition_rate;
                $result['summary']['current_new_customers'] = $new_customers;
                $result['summary']['current_total_customers'] = $total_customers;
            }
            // Store previous period rate for comparison
            else if ($index === 1) {
                $result['summary']['previous_rate'] = $acquisition_rate;
            }
        }
        
        // Calculate average acquisition rate
        if (count($result['acquisition_rates']) > 0) {
            $result['summary']['average_rate'] = round(array_sum($result['acquisition_rates']) / count($result['acquisition_rates']), 1);
        }
        
        // Reverse arrays to show oldest to newest
        $result['periods'] = array_reverse($result['periods']);
        $result['new_customers'] = array_reverse($result['new_customers']);
        $result['total_customers'] = array_reverse($result['total_customers']);
        $result['acquisition_rates'] = array_reverse($result['acquisition_rates']);
        $result['returning_customers'] = array_reverse($result['returning_customers']);
        
        return $result;
    }

    /**
     * Calculate date intervals based on period and range
     */
    private function calculate_date_intervals($time_period, $date_range) {
        $intervals = array();
        $now = current_time('timestamp');
        
        // Determine number of periods to look back
        $num_periods = 6; // Default
        if ($date_range == 'last-12') {
            $num_periods = 12;
        } else if ($date_range == 'custom' && isset($_POST['start_date']) && isset($_POST['end_date'])) {
            // Custom date range handling would go here
            // For now, default to 6 periods
        }
        
        // Calculate intervals based on period type
        for ($i = 0; $i < $num_periods; $i++) {
            $interval = array();
            
            if ($time_period == 'monthly') {
                // End date is last day of month - i months ago
                $end_date = strtotime(date('Y-m-t', strtotime("-$i month", $now)));
                // Start date is first day of month - i months ago
                $start_date = strtotime(date('Y-m-01', strtotime("-$i month", $now)));
                $label = date('M Y', $start_date);
            } else if ($time_period == 'quarterly') {
                // Current quarter
                $current_quarter = ceil(date('n', $now) / 3);
                // Target quarter
                $target_quarter = $current_quarter - $i;
                $target_year = date('Y', $now);
                
                // Adjust year if needed
                while ($target_quarter <= 0) {
                    $target_quarter += 4;
                    $target_year--;
                }
                
                // Calculate quarter start/end dates
                $quarter_start_month = (($target_quarter - 1) * 3) + 1;
                $quarter_end_month = $target_quarter * 3;
                
                $start_date = strtotime("$target_year-$quarter_start_month-01");
                $end_date = strtotime(date('Y-m-t', strtotime("$target_year-$quarter_end_month-01")));
                
                $label = "Q$target_quarter " . $target_year;
            } else if ($time_period == 'yearly') {
                $target_year = date('Y', $now) - $i;
                $start_date = strtotime("$target_year-01-01");
                $end_date = strtotime("$target_year-12-31");
                $label = $target_year;
            }
            
            $intervals[] = array(
                'start' => date('Y-m-d 00:00:00', $start_date),
                'end' => date('Y-m-d 23:59:59', $end_date),
                'label' => $label
            );
        }
        
        return $intervals;
    }

    /**
     * Get count of new customers in a date range
     */
    private function get_new_customers_count($start_date, $end_date) {
        global $wpdb;
        
        // Count users who were created in this date range
        $query = $wpdb->prepare(
            "SELECT COUNT(DISTINCT user_id) 
            FROM {$wpdb->prefix}usermeta 
            WHERE meta_key = '_woo_cat_first_order_date' 
            AND meta_value >= %s 
            AND meta_value <= %s",
            $start_date,
            $end_date
        );
        
        $count = $wpdb->get_var($query);
        
        // Also count guest orders as they are unique customers too
        $guest_query = $wpdb->prepare(
            "SELECT COUNT(DISTINCT post_id) 
            FROM {$wpdb->prefix}postmeta 
            WHERE meta_key = '_customer_user' 
            AND meta_value = 0 
            AND post_id IN (
                SELECT ID FROM {$wpdb->prefix}posts 
                WHERE post_type = 'shop_order' 
                AND post_date >= %s 
                AND post_date <= %s
            )",
            $start_date,
            $end_date
        );
        
        $guest_count = $wpdb->get_var($guest_query);
        
        return intval($count) + intval($guest_count);
    }

    /**
     * Get total customer count up to a date
     */
    private function get_total_customers_count($end_date) {
        global $wpdb;
        
        // Count all users who had their first order before or on end_date
        $query = $wpdb->prepare(
            "SELECT COUNT(DISTINCT user_id) 
            FROM {$wpdb->prefix}usermeta 
            WHERE meta_key = '_woo_cat_first_order_date' 
            AND meta_value <= %s",
            $end_date
        );
        
        $count = $wpdb->get_var($query);
        
        // Also count all guest orders up to this date
        $guest_query = $wpdb->prepare(
            "SELECT COUNT(DISTINCT post_id) 
            FROM {$wpdb->prefix}postmeta 
            WHERE meta_key = '_customer_user' 
            AND meta_value = 0 
            AND post_id IN (
                SELECT ID FROM {$wpdb->prefix}posts 
                WHERE post_type = 'shop_order' 
                AND post_date <= %s
            )",
            $end_date
        );
        
        $guest_count = $wpdb->get_var($guest_query);
        
        return intval($count) + intval($guest_count);
    }

    /**
     * Get count of returning customers in a date range
     */
    private function get_returning_customers_count($start_date, $end_date) {
        global $wpdb;
        
        // Get orders in this period from users who had their first order before this period
        $query = $wpdb->prepare(
            "SELECT COUNT(DISTINCT pm.meta_value) 
            FROM {$wpdb->prefix}posts p
            JOIN {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id
            JOIN {$wpdb->prefix}usermeta um ON pm.meta_value = um.user_id
            WHERE p.post_type = 'shop_order'
            AND p.post_date >= %s 
            AND p.post_date <= %s
            AND pm.meta_key = '_customer_user'
            AND pm.meta_value > 0
            AND um.meta_key = '_woo_cat_first_order_date'
            AND um.meta_value < %s",
            $start_date,
            $end_date,
            $start_date
        );
        
        $count = $wpdb->get_var($query);
        return intval($count);
    }

    /**
     * Generate CSV data from report data
     */
    private function generate_csv_data($data) {
        $csv = array();
        
        // Header row
        $csv[] = array('Period', 'New Customers', 'Total Customers', 'Acquisition Rate (%)', 'Returning Customers');
        
        // Data rows
        for ($i = 0; $i < count($data['periods']); $i++) {
            $csv[] = array(
                $data['periods'][$i],
                $data['new_customers'][$i],
                $data['total_customers'][$i],
                $data['acquisition_rates'][$i],
                $data['returning_customers'][$i]
            );
        }
        
        // Convert to CSV string
        $output = '';
        foreach ($csv as $row) {
            $output .= implode(',', $row) . "\n";
        }
        
        return $output;
    }
}

// Initialize plugin
$woo_customer_acquisition_tracker = new WooCommerce_Customer_Acquisition_Tracker();