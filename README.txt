# WooCommerce Customer Acquisition Tracker

Track and analyze customer acquisition rates and retention metrics for your WooCommerce store.

## Description

The WooCommerce Customer Acquisition Tracker plugin helps you monitor and analyze your store's customer acquisition and retention metrics. This plugin provides:

- Customer acquisition rate tracking (monthly, quarterly, yearly)
- New vs. returning customer analysis
- Detailed period-by-period breakdowns
- Visual reports with charts and graphs
- Data export functionality

### Key Features

- **Customer Acquisition Dashboard**: Visual representation of customer acquisition trends
- **Multiple Time Periods**: Analyze data by month, quarter, or year
- **Flexible Date Ranges**: View last 6 periods, last 12 periods, or custom date ranges
- **Data Exporting**: Export reports as CSV files for further analysis
- **Customizable Settings**: Configure calculation methods and display preferences
- **Seamless WooCommerce Integration**: Works with your existing WooCommerce store data

## Installation

1. Upload the `woocommerce-customer-acquisition-tracker` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to WooCommerce → Customer Acquisition to view your dashboard

## Requirements

- WordPress 5.0 or higher
- WooCommerce 5.0 or higher
- PHP 7.2 or higher

## Frequently Asked Questions

### How is the customer acquisition rate calculated?

By default, the customer acquisition rate is calculated as: (New customers in period ÷ Total customers at end of period) × 100.

For example, if you acquired 50 new customers in a month and had 500 total customers at the end of that month, your acquisition rate would be 10%.

You can change this calculation method in the plugin settings.

### How does the plugin define "new" customers?

By default, a customer is considered "new" based on their first order date. This can be changed in the settings to use registration date instead.

### Does the plugin count guest customers?

Yes, the plugin tracks both registered customers and guest customers.

### Can I export the data for use in other tools?

Yes, you can export any report as a CSV file by clicking the "Export Report" button in the dashboard.

### Will this plugin slow down my store?

No, the plugin uses optimized database queries and caches results to minimize performance impact.

## Screenshots

1. Customer Acquisition Dashboard
2. Acquisition Rate Trend Chart
3. New vs. Returning Customer Analysis
4. Period Breakdown Table
5. Plugin Settings

## Changelog

### 1.0.0
* Initial release

## Upgrade Notice

### 1.0.0
Initial release

## Support

For support, please visit [boomdevs.com/support](https://boomdevs.com/support).

## Credits

This plugin was developed by [Boomdevs].

## License

This plugin is released under the GPL v2 or later.