<?php
/**
 * Template Name: Trip Results
 */

get_header();

// Get filter inputs
$trip_type = $_GET['trip_type'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$destination = $_GET['destination'] ?? '';
$departure_city = $_GET['departure_city'] ?? '';
$vehicle_type = $_GET['vehicle_type'] ?? '';
$adults = intval($_GET['adults'] ?? 1);
$kids = intval($_GET['kids'] ?? 0);

// WP_Query to match ACF fields
$args = [
    'post_type' => 'tour',
    'posts_per_page' => -1,
    'meta_query' => [
        'relation' => 'AND',
        [
            'key' => 'trip_type',
            'value' => $trip_type,
            'compare' => '='
        ],
        [
            'key' => 'departure_city',
            'value' => $departure_city,
            'compare' => '='
        ],
        [
            'key' => 'vehicle_type',
            'value' => $vehicle_type,
            'compare' => '='
        ]
    ]
];

$query = new WP_Query($args);
?>

<div class="container" style="padding: 40px 20px;">
    <h1 style="margin-bottom: 20px;">Trip Results</h1>

    <button onclick="history.go(-1)" class="edit-search-btn" style="margin: 10px 0; padding: 10px 20px; background-color: #daa520; color: black; font-weight: bold; border: none; border-radius: 5px;">
        ← Edit Your Search
    </button>

    <?php if ($query->have_posts()) : ?>
        <?php while ($query->have_posts()) : $query->the_post(); 
            $price_per_adult = get_field('price_per_adult') ?: 0;
            $price_per_kid = get_field('price_per_kid') ?: 0;
            $total_price = ($adults * $price_per_adult) + ($kids * $price_per_kid);
        ?>

        <div class="trip-box" style="border: 1px solid #ddd; padding: 20px; border-radius: 10px; margin-top: 20px;">
            <h2><?php the_title(); ?></h2>
            <p><strong>Trip Type:</strong> <?php echo esc_html(get_field('trip_type')); ?></p>
            <p><strong>Destination:</strong> <?php the_title(); ?></p>
            <p><strong>Start Date:</strong> <?php echo esc_html($start_date); ?></p>
            <p><strong>Departure City:</strong> <?php echo esc_html($departure_city); ?></p>
            <p><strong>Vehicle:</strong> <?php echo esc_html($vehicle_type); ?></p>
            <p><strong>Travelers:</strong> <?php echo $adults . ' Adults, ' . $kids . ' Kids'; ?></p>

            <h3>Pricing Summary</h3>
            <ul>
                <li>Price per Adult: <strong>PKR <?php echo number_format($price_per_adult); ?></strong></li>
                <li>Price per Kid: <strong>PKR <?php echo number_format($price_per_kid); ?></strong></li>
                <li>Total Price: <strong style="color: green;">PKR <?php echo number_format($total_price); ?></strong></li>
            </ul>

            <a class="btn-itinerary" 
               style="background-color: #cc6b37; padding: 12px 20px; color: white; display: inline-block; margin-top: 10px; text-decoration: none; border-radius: 5px; font-weight: bold;" 
               href="<?php echo add_query_arg([
                   'adults' => $adults,
                   'kids' => $kids,
                   'start_date' => $start_date,
                   'departure' => $departure_city,
                   'vehicle_type' => $vehicle_type,
                   'trip_type' => $trip_type,
               ], get_permalink()); ?>">
               View Itinerary
            </a>
        </div>

        <?php endwhile; wp_reset_postdata(); ?>
    <?php else: ?>
        <p style="margin-top: 30px;">❌ No tours found matching your filters.</p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
