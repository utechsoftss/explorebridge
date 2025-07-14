<?php
/**
 * Theme functions and definitions.
 */


// usama

// Register Custom Post Type: Tour
function register_tour_post_type() {
    $labels = array(
        'name' => 'Tours',
        'singular_name' => 'Tour',
        'menu_name' => 'Tours',
        'name_admin_bar' => 'Tour',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Tour',
        'new_item' => 'New Tour',
        'edit_item' => 'Edit Tour',
        'view_item' => 'View Tour',
        'all_items' => 'All Tours',
        'search_items' => 'Search Tours',
        'not_found' => 'No tours found.',
        'not_found_in_trash' => 'No tours found in Trash.',
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'tour'),
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-palmtree',
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
        'show_in_rest' => true, // for Gutenberg/Block Editor
    );

    register_post_type('tour', $args);
}
add_action('init', 'register_tour_post_type');



// .......................
add_action('wp_ajax_load_trip_options', 'load_trip_options');
add_action('wp_ajax_nopriv_load_trip_options', 'load_trip_options');

function load_trip_options() {
    $trip_type = sanitize_text_field($_GET['trip_type']);

    $args = [
        'post_type' => 'tour',
        'posts_per_page' => -1,
        'meta_query' => [
            [
                'key' => 'trip_type',
                'value' => $trip_type,
                'compare' => '='
            ]
        ]
    ];

    $query = new WP_Query($args);
    $dates = [];
    $destinations = [];
    $cities = [];
    $vehicles = [];

    while ($query->have_posts()) {
        $query->the_post();

        $start = get_field('start_date');
        if ($start && !in_array($start, $dates)) $dates[] = $start;

        $slug = get_post_field('post_name', get_the_ID());
        if (!in_array($slug, array_column($destinations, 'slug'))) {
            $destinations[] = ['slug' => $slug, 'name' => get_the_title()];
        }

        $city = get_field('departure_city');
        if ($city && !in_array($city, $cities)) $cities[] = $city;

        $vehicle = get_field('vehicle_type');
        if ($vehicle && !in_array($vehicle, $vehicles)) $vehicles[] = $vehicle;
    }

    wp_reset_postdata();

    wp_send_json([
        'dates' => $dates,
        'destinations' => $destinations,
        'departure_cities' => $cities,
        'vehicles' => $vehicles
    ]);
}

