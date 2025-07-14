<?php
/*
Plugin Name: WP Trip Booking System
Description: Booking system for Design My Trip workflow. Registers booking CPT, handles booking logic, and provides user dashboard shortcode.
Version: 1.0
Author: Usama Razzaq
*/ 

if (!defined('ABSPATH')) exit;

// 1. Register Booking Custom Post Type
function wp_trip_booking_register_cpt() {
    register_post_type('booking', [
        'labels' => [
            'name' => 'Bookings',
            'singular_name' => 'Booking',
        ],
        'public' => false,
        'show_ui' => true,
        'supports' => ['title', 'author'],
        'capability_type' => 'post',
        'has_archive' => false,
        'menu_icon' => 'dashicons-tickets-alt',
    ]);
}
add_action('init', 'wp_trip_booking_register_cpt');

// 2. Flush rewrite rules on activation
function wp_trip_booking_activate() {
    wp_trip_booking_register_cpt();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'wp_trip_booking_activate');

// 3. Booking Handler (to be called from single tour template)
function wp_trip_booking_handle_booking() {
    if (!is_singular('tour')) return;
    if (!isset($_GET['book_now']) || $_GET['book_now'] != '1') return;

    if (!is_user_logged_in()) {
        // Redirect to login with redirect back
        wp_redirect(wp_login_url(get_permalink()));
        exit;
    }

    $user_id = get_current_user_id();
    $tour_id = get_the_ID();

    // Collect booking data from query string
    $booking_data = [
        'tour_id' => $tour_id,
        'adults' => intval($_GET['adults'] ?? 1),
        'kids' => intval($_GET['kids'] ?? 0),
        'start_date' => sanitize_text_field($_GET['start_date'] ?? ''),
        'departure_city' => sanitize_text_field($_GET['departure'] ?? ''),
        'vehicle_type' => sanitize_text_field($_GET['vehicle_type'] ?? ''),
        'trip_type' => sanitize_text_field($_GET['trip_type'] ?? ''),
    ];

    // Prevent duplicate booking for same user/tour/date
    $existing = get_posts([
        'post_type' => 'booking',
        'author' => $user_id,
        'meta_query' => [
            [
                'key' => 'tour_id',
                'value' => $tour_id,
            ],
            [
                'key' => 'start_date',
                'value' => $booking_data['start_date'],
            ]
        ]
    ]);
    if (!$existing) {
        $booking_id = wp_insert_post([
            'post_type' => 'booking',
            'post_title' => 'Booking for ' . get_the_title($tour_id) . ' by ' . wp_get_current_user()->user_login,
            'post_status' => 'publish',
            'post_author' => $user_id,
        ]);
        foreach ($booking_data as $key => $value) {
            update_post_meta($booking_id, $key, $value);
        }
        // Redirect to dashboard or show success message
        wp_redirect(site_url('/user-dashboard/?booking=success'));
        exit;
    } else {
        // Already booked, show message or redirect
        wp_redirect(site_url('/user-dashboard/?booking=exists'));
        exit;
    }
}
add_action('template_redirect', 'wp_trip_booking_handle_booking');

// 4. User Bookings Shortcode
function wp_trip_booking_user_bookings_shortcode() {
    if (!is_user_logged_in()) {
        return '<p>Please <a href="' . esc_url(wp_login_url()) . '">login</a> to view your bookings.</p>';
    }
    $user_id = get_current_user_id();
    $bookings = get_posts([
        'post_type' => 'booking',
        'author' => $user_id,
        'posts_per_page' => -1,
    ]);
    if (!$bookings) return '<p>No bookings found.</p>';

    $output = '<h2>Your Bookings</h2><ul>';
    foreach ($bookings as $booking) {
        $tour_id = get_post_meta($booking->ID, 'tour_id', true);
        $tour_title = get_the_title($tour_id);
        $start_date = get_post_meta($booking->ID, 'start_date', true);
        $adults = get_post_meta($booking->ID, 'adults', true);
        $kids = get_post_meta($booking->ID, 'kids', true);
        $output .= '<li><strong>' . esc_html($tour_title) . '</strong> - Start Date: ' . esc_html($start_date) . ' - Adults: ' . esc_html($adults) . ' - Kids: ' . esc_html($kids) . '</li>';
    }
    $output .= '</ul>';
    return $output;
}
add_shortcode('user_bookings', 'wp_trip_booking_user_bookings_shortcode');

// 5. Add ACF fields to Booking CPT if ACF is active
add_action('acf/init', function() {
    if (function_exists('acf_add_local_field_group')) {
        acf_add_local_field_group([
            'key' => 'group_booking_fields',
            'title' => 'Booking Details',
            'fields' => [
                [
                    'key' => 'field_booking_tour_id',
                    'label' => 'Tour ID',
                    'name' => 'tour_id',
                    'type' => 'text',
                ],
                [
                    'key' => 'field_booking_start_date',
                    'label' => 'Start Date',
                    'name' => 'start_date',
                    'type' => 'text',
                ],
                [
                    'key' => 'field_booking_adults',
                    'label' => 'Adults',
                    'name' => 'adults',
                    'type' => 'number',
                ],
                [
                    'key' => 'field_booking_kids',
                    'label' => 'Kids',
                    'name' => 'kids',
                    'type' => 'number',
                ],
                [
                    'key' => 'field_booking_departure_city',
                    'label' => 'Departure City',
                    'name' => 'departure_city',
                    'type' => 'text',
                ],
                [
                    'key' => 'field_booking_vehicle_type',
                    'label' => 'Vehicle Type',
                    'name' => 'vehicle_type',
                    'type' => 'text',
                ],
                [
                    'key' => 'field_booking_trip_type',
                    'label' => 'Trip Type',
                    'name' => 'trip_type',
                    'type' => 'text',
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'booking',
                    ],
                ],
            ],
        ]);
    }
});

// 6. Admin columns for Bookings
add_filter('manage_booking_posts_columns', function($columns) {
    $columns['tour'] = 'Tour';
    $columns['start_date'] = 'Start Date';
    $columns['adults'] = 'Adults';
    $columns['kids'] = 'Kids';
    return $columns;
});
add_action('manage_booking_posts_custom_column', function($column, $post_id) {
    if ($column === 'tour') {
        $tour_id = get_post_meta($post_id, 'tour_id', true);
        echo esc_html(get_the_title($tour_id));
    } elseif ($column === 'start_date') {
        echo esc_html(get_post_meta($post_id, 'start_date', true));
    } elseif ($column === 'adults') {
        echo esc_html(get_post_meta($post_id, 'adults', true));
    } elseif ($column === 'kids') {
        echo esc_html(get_post_meta($post_id, 'kids', true));
    }
}, 10, 2);

// 7. Integration Instructions (as comments)
/*
- Place this file in your wp-content/plugins/ directory.
- Activate the plugin from the WordPress admin.
- In your single tour template, update the Book Now button:
    <?php if (is_user_logged_in()): ?>
        <a href="<?php echo esc_url( add_query_arg('book_now', '1') ); ?>" class="btn btn-primary">Book Now</a>
    <?php else: ?>
        <a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>" class="btn btn-primary">Book Now</a>
    <?php endif; ?>
- Create a page called "User Dashboard" and add the shortcode [user_bookings] to display the user's bookings.
- Bookings will appear in the admin under Bookings.
- Requires ACF plugin for custom fields (optional, but recommended).
*/ 