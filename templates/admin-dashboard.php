<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap woo-cat-dashboard">
    <h1><?php _e('Customer Acquisition Tracker', 'woo-customer-acquisition'); ?></h1>
    
    <div class="woo-cat-container">
        <div class="woo-cat-header">
            <div class="woo-cat-tabs">
                <a href="#" class="woo-cat-tab active" data-tab="dashboard"><?php _e('Dashboard', 'woo-customer-acquisition'); ?></a>
                <a href="#" class="woo-cat-tab" data-tab="settings"><?php _e('Settings', 'woo-customer-acquisition'); ?></a>
                <a href="#" class="woo-cat-tab" data-tab="help"><?php _e('Help', 'woo-customer-acquisition'); ?></a>
            </div>
            
            <div class="woo-cat-actions">
                <button class="button button-primary woo-cat-export-btn"><?php _e('Export Report', 'woo-customer-acquisition'); ?></button>
            </div>
        </div>
        
        <div class="woo-cat-content">
            <!-- Dashboard Tab -->
            <div class="woo-cat-tab-content active" id="dashboard">
                <div class="woo-cat-controls">
                    <div class="woo-cat-filter">
                        <label for="woo-cat-time-period"><?php _e('Time Period:', 'woo-customer-acquisition'); ?></label>
                        <select id="woo-cat-time-period" class="woo-cat-filter-control">
                            <option value="monthly"><?php _e('Monthly', 'woo-customer-acquisition'); ?></option>
                            <option value="quarterly"><?php _e('Quarterly', 'woo-customer-acquisition'); ?></option>
                            <option value="yearly"><?php _e('Yearly', 'woo-customer-acquisition'); ?></option>
                        </select>
                        
                        <label for="woo-cat-date-range"><?php _e('Date Range:', 'woo-customer-acquisition'); ?></label>
                        <select id="woo-cat-date-range" class="woo-cat-filter-control">
                            <option value="last-6"><?php _e('Last 6 Periods', 'woo-customer-acquisition'); ?></option>
                            <option value="last-12"><?php _e('Last 12 Periods', 'woo-customer-acquisition'); ?></option>
                            <option value="custom"><?php _e('Custom Range', 'woo-customer-acquisition'); ?></option>
                        </select>
                        
                        <button id="woo-cat-apply-filters" class="button"><?php _e('Apply', 'woo-customer-acquisition'); ?></button>
                    </div>
                </div>
                
                <div class="woo-cat-loading">
                    <div class="spinner is-active"></div>
                    <p><?php _e('Loading data...', 'woo-customer-acquisition'); ?></p>
                </div>
                
                <div class="woo-cat-dashboard-content" style="display: none;">
                    <div class="woo-cat-summary">
                        <div class="woo-cat-stat-box">
                            <h3><?php _e('Current Acquisition Rate', 'woo-customer-acquisition'); ?></h3>
                            <p class="woo-cat-stat-value" id="woo-cat-current-rate">0%</p>
                            <p class="woo-cat-stat-trend" id="woo-cat-rate-trend"></p>
                        </div>
                        
                        <div class="woo-cat-stat-box">
                            <h3><?php _e('Average Rate', 'woo-customer-acquisition'); ?></h3>
                            <p class="woo-cat-stat-value" id="woo-cat-average-rate">0%</p>
                        </div>
                        
                        <div class="woo-cat-stat-box">
                            <h3><?php _e('New Customers (Current Period)', 'woo-customer-acquisition'); ?></h3>
                            <p class="woo-cat-stat-value" id="woo-cat-new-customers">0</p>
                        </div>
                        
                        <div class="woo-cat-stat-box">
                            <h3><?php _e('Total Customer Base', 'woo-customer-acquisition'); ?></h3>
                            <p class="woo-cat-stat-value" id="woo-cat-total-customers">0</p>
                        </div>
                    </div>
                    
                    <div class="woo-cat-chart-container">
                        <h2><?php _e('Customer Acquisition Rate Trend', 'woo-customer-acquisition'); ?></h2>
                        <div class="woo-cat-chart">
                            <canvas id="woo-cat-trend-chart"></canvas>
                        </div>
                    </div>
                    
                    <div class="woo-cat-data-grid">
                        <div class="woo-cat-data-table">
                            <h2><?php _e('Period Breakdown', 'woo-customer-acquisition'); ?></h2>
                            <table class="widefat">
                                <thead>
                                    <tr>
                                        <th><?php _e('Period', 'woo-customer-acquisition'); ?></th>
                                        <th><?php _e('New Customers', 'woo-customer-acquisition'); ?></th>
                                        <th><?php _e('Total Customers', 'woo-customer-acquisition'); ?></th>
                                        <th><?php _e('Acquisition Rate', 'woo-customer-acquisition'); ?></th>
                                    </tr>
                                </thead>
                                <tbody id="woo-cat-data-table-body">
                                    <!-- Data rows will be inserted here -->
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="woo-cat-chart-container">
                            <h2><?php _e('New vs. Returning Customer Analysis', 'woo-customer-acquisition'); ?></h2>
                            <div class="woo-cat-chart">
                                <canvas id="woo-cat-comparison-chart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Settings Tab -->
            <div class="woo-cat-tab-content" id="settings">
                <form id="woo-cat-settings-form" method="post" action="">
                    <?php wp_nonce_field('woo_cat_settings_nonce', 'woo_cat_settings_nonce'); ?>
                    
                    <div class="woo-cat-settings-section">
                        <h2><?php _e('Calculation Settings', 'woo-customer-acquisition'); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="woo-cat-acquisition-formula"><?php _e('Customer Acquisition Rate Formula', 'woo-customer-acquisition'); ?></label>
                                </th>
                                <td>
                                    <select name="woo_cat_acquisition_formula" id="woo-cat-acquisition-formula">
                                        <option value="standard" <?php selected(get_option('woo_cat_acquisition_formula', 'standard'), 'standard'); ?>>
                                            <?php _e('New Customers ÷ Total Customers', 'woo-customer-acquisition'); ?>
                                        </option>
                                        <option value="alternative" <?php selected(get_option('woo_cat_acquisition_formula', 'standard'), 'alternative'); ?>>
                                            <?php _e('New Customers ÷ Previous Period Customers', 'woo-customer-acquisition'); ?>
                                        </option>
                                    </select>
                                    <p class="description"><?php _e('Choose how customer acquisition rate is calculated.', 'woo-customer-acquisition'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="woo-cat-new-customer-definition"><?php _e('New Customer Definition', 'woo-customer-acquisition'); ?></label>
                                </th>
                                <td>
                                    <select name="woo_cat_new_customer_definition" id="woo-cat-new-customer-definition">
                                        <option value="first-order" <?php selected(get_option('woo_cat_new_customer_definition', 'first-order'), 'first-order'); ?>>
                                            <?php _e('First Order Date', 'woo-customer-acquisition'); ?>
                                        </option>
                                        <option value="registration" <?php selected(get_option('woo_cat_new_customer_definition', 'first-order'), 'registration'); ?>>
                                            <?php _e('Registration Date', 'woo-customer-acquisition'); ?>
                                        </option>
                                    </select>
                                    <p class="description"><?php _e('Define when a customer is considered "new".', 'woo-customer-acquisition'); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="woo-cat-settings-section">
                        <h2><?php _e('Display Settings', 'woo-customer-acquisition'); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="woo-cat-default-period"><?php _e('Default Time Period', 'woo-customer-acquisition'); ?></label>
                                </th>
                                <td>
                                    <select name="woo_cat_default_period" id="woo-cat-default-period">
                                        <option value="monthly" <?php selected(get_option('woo_cat_default_period', 'monthly'), 'monthly'); ?>>
                                            <?php _e('Monthly', 'woo-customer-acquisition'); ?>
                                        </option>
                                        <option value="quarterly" <?php selected(get_option('woo_cat_default_period', 'monthly'), 'quarterly'); ?>>
                                            <?php _e('Quarterly', 'woo-customer-acquisition'); ?>
                                        </option>
                                        <option value="yearly" <?php selected(get_option('woo_cat_default_period', 'monthly'), 'yearly'); ?>>
                                            <?php _e('Yearly', 'woo-customer-acquisition'); ?>
                                        </option>
                                    </select>
                                    <p class="description"><?php _e('Select the default time period for reports.', 'woo-customer-acquisition'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="woo-cat-chart-type"><?php _e('Default Chart Type', 'woo-customer-acquisition'); ?></label>
                                </th>
                                <td>
                                    <select name="woo_cat_chart_type" id="woo-cat-chart-type">
                                        <option value="line" <?php selected(get_option('woo_cat_chart_type', 'line'), 'line'); ?>>
                                            <?php _e('Line Chart', 'woo-customer-acquisition'); ?>
                                        </option>
                                        <option value="bar" <?php selected(get_option('woo_cat_chart_type', 'line'), 'bar'); ?>>
                                            <?php _e('Bar Chart', 'woo-customer-acquisition'); ?>
                                        </option>
                                        <option value="area" <?php selected(get_option('woo_cat_chart_type', 'line'), 'area'); ?>>
                                            <?php _e('Area Chart', 'woo-customer-acquisition'); ?>
                                        </option>
                                    </select>
                                    <p class="description"><?php _e('Select the default chart type for visualizing data.', 'woo-customer-acquisition'); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <p class="submit">
                        <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Settings', 'woo-customer-acquisition'); ?>">
                    </p>
                </form>
            </div>
            
            <!-- Help Tab -->
            <div class="woo-cat-tab-content" id="help">
                <div class="woo-cat-help-section">
                    <h2><?php _e('Understanding Customer Acquisition Metrics', 'woo-customer-acquisition'); ?></h2>
                    
                    <div class="woo-cat-help-content">
                        <h3><?php _e('Customer Acquisition Rate', 'woo-customer-acquisition'); ?></h3>
                        <p><?php _e('The customer acquisition rate measures how quickly your business is gaining new customers relative to your total customer base. It tells you what percentage of your customer base is newly acquired within a specific time period.', 'woo-customer-acquisition'); ?></p>
                        <p><strong><?php _e('Formula:', 'woo-customer-acquisition'); ?></strong> <?php _e('Customer Acquisition Rate = (New customers in period ÷ Total customers at end of period) × 100', 'woo-customer-acquisition'); ?></p>
                        
                        <h3><?php _e('New vs. Returning Customers', 'woo-customer-acquisition'); ?></h3>
                        <p><?php _e('This analysis compares the number of first-time buyers with customers who have previously purchased from your store. A healthy business typically maintains a balance between acquiring new customers and encouraging repeat purchases.', 'woo-customer-acquisition'); ?></p>
                        
                        <h3><?php _e('Year-over-Year Analysis', 'woo-customer-acquisition'); ?></h3>
                        <p><?php _e('Comparing customer metrics across corresponding periods helps identify seasonal trends and overall growth patterns. Use the date filters to compare the same time periods across different years.', 'woo-customer-acquisition'); ?></p>
                    </div>
                </div>
                
                <div class="woo-cat-help-section">
                    <h2><?php _e('Using This Plugin', 'woo-customer-acquisition'); ?></h2>
                    
                    <div class="woo-cat-help-content">
                        <h3><?php _e('Dashboard Overview', 'woo-customer-acquisition'); ?></h3>
                        <p><?php _e('The dashboard provides a visual representation of your customer acquisition metrics. Use the filters at the top to adjust the time period and date range. The summary cards show key metrics at a glance, while the charts and table provide detailed breakdowns.', 'woo-customer-acquisition'); ?></p>
                        
                        <h3><?php _e('Exporting Data', 'woo-customer-acquisition'); ?></h3>
                        <p><?php _e('Click the "Export Report" button to download the current data as a CSV file. This is useful for creating custom reports or sharing data with team members.', 'woo-customer-acquisition'); ?></p>
                        
                        <h3><?php _e('Customizing Settings', 'woo-customer-acquisition'); ?></h3>
                        <p><?php _e('Use the Settings tab to customize how the acquisition rate is calculated and how data is displayed. Changes will apply to all future reports and dashboards.', 'woo-customer-acquisition'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>