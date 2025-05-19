/**
 * WooCommerce Customer Acquisition Tracker - Admin JavaScript
 */
(function($) {
    'use strict';
    
    // Chart objects
    let trendChart = null;
    let comparisonChart = null;
    
    // Dashboard initialization
    $(document).ready(function() {
        // Initialize tabs
        initTabs();
        
        // Initialize filters
        initFilters();
        
        // Load initial data
        loadData();
        
        // Export button handler
        $('.woo-cat-export-btn').on('click', exportReport);
        
        // Settings form handler
        $('#woo-cat-settings-form').on('submit', saveSettings);
    });
    
    /**
     * Initialize tab navigation
     */
    function initTabs() {
        $('.woo-cat-tab').on('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all tabs and content
            $('.woo-cat-tab').removeClass('active');
            $('.woo-cat-tab-content').removeClass('active');
            
            // Add active class to clicked tab
            $(this).addClass('active');
            
            // Show corresponding content
            const tabId = $(this).data('tab');
            $('#' + tabId).addClass('active');
        });
    }
    
    /**
     * Initialize filter controls
     */
    function initFilters() {
        $('#woo-cat-apply-filters').on('click', function() {
            loadData();
        });
    }
    
    /**
     * Load customer acquisition data via AJAX
     */
    function loadData() {
        // Show loading indicator
        $('.woo-cat-loading').show();
        $('.woo-cat-dashboard-content').hide();
        
        // Get filter values
        const timePeriod = $('#woo-cat-time-period').val();
        const dateRange = $('#woo-cat-date-range').val();
        
        // Make AJAX request
        $.ajax({
            url: wooCatData.ajax_url,
            type: 'POST',
            data: {
                action: 'woo_cat_get_customer_data',
                nonce: wooCatData.nonce,
                time_period: timePeriod,
                date_range: dateRange
            },
            success: function(response) {
                if (response.success) {
                    updateDashboard(response.data);
                } else {
                    // Show error message
                    alert('Error loading data: ' + response.data);
                }
                
                // Hide loading indicator
                $('.woo-cat-loading').hide();
                $('.woo-cat-dashboard-content').show();
            },
            error: function() {
                // Show error message
                alert('Error connecting to server. Please try again.');
                
                // Hide loading indicator
                $('.woo-cat-loading').hide();
            }
        });
    }
    
    /**
     * Update dashboard with new data
     */
    function updateDashboard(data) {
        // Update summary stats
        updateSummaryStats(data.summary);
        
        // Update data table
        updateDataTable(data);
        
        // Update charts
        updateTrendChart(data);
        updateComparisonChart(data);
    }
    
    /**
     * Update summary statistics
     */
    function updateSummaryStats(summary) {
        $('#woo-cat-current-rate').text(summary.current_rate + '%');
        $('#woo-cat-average-rate').text(summary.average_rate + '%');
        $('#woo-cat-new-customers').text(summary.current_new_customers);
        $('#woo-cat-total-customers').text(summary.current_total_customers);
        
        // Update trend indicator
        const trend = summary.current_rate - summary.previous_rate;
        let trendHtml = '';
        
        if (trend > 0) {
            trendHtml = '<span class="woo-cat-trend-up">↑ ' + trend.toFixed(1) + '%</span>';
        } else if (trend < 0) {
            trendHtml = '<span class="woo-cat-trend-down">↓ ' + Math.abs(trend).toFixed(1) + '%</span>';
        } else {
            trendHtml = '<span class="woo-cat-trend-neutral">–</span>';
        }
        
        $('#woo-cat-rate-trend').html(trendHtml);
    }
    
    /**
     * Update data table
     */
    function updateDataTable(data) {
        const tableBody = $('#woo-cat-data-table-body');
        tableBody.empty();
        
        // Add data rows
        for (let i = 0; i < data.periods.length; i++) {
            const row = $('<tr></tr>');
            
            row.append($('<td></td>').text(data.periods[i]));
            row.append($('<td></td>').text(data.new_customers[i]));
            row.append($('<td></td>').text(data.total_customers[i]));
            row.append($('<td></td>').text(data.acquisition_rates[i] + '%'));
            
            tableBody.append(row);
        }
    }
    
    /**
     * Update trend chart
     */
    function updateTrendChart(data) {
        const ctx = document.getElementById('woo-cat-trend-chart').getContext('2d');
        
        // Destroy existing chart if it exists
        if (trendChart) {
            trendChart.destroy();
        }
        
        // Get chart type from settings or use default
        const chartType = $('#woo-cat-chart-type').length ? $('#woo-cat-chart-type').val() : 'line';
        
        // Create new chart
        trendChart = new Chart(ctx, {
            type: chartType === 'area' ? 'line' : chartType,
            data: {
                labels: data.periods,
                datasets: [{
                    label: 'Customer Acquisition Rate (%)',
                    data: data.acquisition_rates,
                    fill: chartType === 'area',
                    backgroundColor: 'rgba(52, 152, 219, 0.2)',
                    borderColor: 'rgba(52, 152, 219, 1)',
                    tension: 0.1,
                    pointBackgroundColor: 'rgba(52, 152, 219, 1)',
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Acquisition Rate (%)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Period'
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.raw + '%';
                            }
                        }
                    }
                }
            }
        });
    }
    
    /**
     * Update comparison chart
     */
    function updateComparisonChart(data) {
        const ctx = document.getElementById('woo-cat-comparison-chart').getContext('2d');
        
        // Destroy existing chart if it exists
        if (comparisonChart) {
            comparisonChart.destroy();
        }
        
        // Create new chart
        comparisonChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.periods,
                datasets: [{
                    label: 'New Customers',
                    data: data.new_customers,
                    backgroundColor: 'rgba(52, 152, 219, 0.8)'
                }, {
                    label: 'Returning Customers',
                    data: data.returning_customers,
                    backgroundColor: 'rgba(46, 204, 113, 0.8)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Customers'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Period'
                        }
                    }
                }
            }
        });
    }
    
    /**
     * Export report as CSV
     */
    function exportReport() {
        // Get filter values
        const timePeriod = $('#woo-cat-time-period').val();
        const dateRange = $('#woo-cat-date-range').val();
        
        // Create form for POST submission
        const form = $('<form></form>')
            .attr('method', 'post')
            .attr('action', wooCatData.ajax_url)
            .css('display', 'none');
        
        // Add form fields
        form.append($('<input>').attr({
            type: 'hidden',
            name: 'action',
            value: 'woo_cat_export_report'
        }));
        
        form.append($('<input>').attr({
            type: 'hidden',
            name: 'nonce',
            value: wooCatData.nonce
        }));
        
        form.append($('<input>').attr({
            type: 'hidden',
            name: 'time_period',
            value: timePeriod
        }));
        
        form.append($('<input>').attr({
            type: 'hidden',
            name: 'date_range',
            value: dateRange
        }));
        
        // Submit form
        $('body').append(form);
        form.submit();
        form.remove();
    }
    
    /**
     * Save settings via AJAX
     */
    function saveSettings(e) {
        e.preventDefault();
        
        // Show loading indicator
        const submitButton = $('#submit');
        const originalText = submitButton.val();
        submitButton.val('Saving...');
        submitButton.prop('disabled', true);
        
        // Get form data
        const formData = $(this).serialize();
        
        // Add action
        formData.action = 'woo_cat_save_settings';
        
        // Make AJAX request
        $.ajax({
            url: wooCatData.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Show success message
                    const notice = $('<div class="notice notice-success is-dismissible"><p>' + response.data + '</p></div>');
                    $('#woo-cat-settings-form').before(notice);
                    
                    // Auto dismiss after 3 seconds
                    setTimeout(function() {
                        notice.fadeOut(function() {
                            notice.remove();
                        });
                    }, 3000);
                } else {
                    // Show error message
                    alert('Error saving settings: ' + response.data);
                }
                
                // Restore submit button
                submitButton.val(originalText);
                submitButton.prop('disabled', false);
            },
            error: function() {
                // Show error message
                alert('Error connecting to server. Please try again.');
                
                // Restore submit button
                submitButton.val(originalText);
                submitButton.prop('disabled', false);
            }
        });
    }
    
})(jQuery);