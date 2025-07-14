<?php get_header(); ?>

<div class="container mt-5">
  <button onclick="history.go(-2);" class="btn btn-warning mb-4">← Edit Your Search</button>

  <h1 class="mb-4"><?php the_title(); ?></h1>

  <div style="margin-top: 30px; padding: 25px; border: 1px solid #eee; border-radius: 10px; background-color: #fdfdfd; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
    <h3 style="margin-bottom: 20px; font-size: 24px; color: #cc6b37;">Trip Details</h3>
    <div style="display: flex; flex-wrap: wrap; row-gap: 12px;">
      <?php
        // Safely get start date from GET and parse it with correct format d/m/Y
        $start_date_raw = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '';
        $duration_days = (int) get_field('duration');
        $start_date_formatted = 'N/A';
        $end_date_formatted = 'N/A';

        if (!empty($start_date_raw) && $duration_days > 0) {
          $start_date_obj = DateTime::createFromFormat('d/m/Y', $start_date_raw);
          if ($start_date_obj) {
            $start_date_formatted = $start_date_obj->format('Y-m-d');
            $end_date_obj = clone $start_date_obj;
            $end_date_obj->modify('+' . ($duration_days - 1) . ' days');
            $end_date_formatted = $end_date_obj->format('Y-m-d');
          }
        }

        // Days & Nights
        $days = (int) get_field('total_days');
        $nights = (int) get_field('total_nights');
        $tour_length = 'N/A';
        if ($days > 0 || $nights > 0) {
          $tour_length = '';
          if ($days > 0) {
            $tour_length .= $days . ' Day' . ($days > 1 ? 's' : '');
          }
          if ($nights > 0) {
            $tour_length .= ($tour_length ? ', ' : '') . $nights . ' Night' . ($nights > 1 ? 's' : '');
          }
        }

        // Prepare details array with sanitization
        $details = [
          'Trip Type' => strtolower(sanitize_text_field(get_field('trip_type') ?: 'N/A')),
          'Destination' => get_the_title(),
          'Start Date' => $start_date_formatted,
          'End Date' => $end_date_formatted,
          'Duration' => $duration_days > 0 ? $duration_days . ' Days' : 'N/A',
          'Tour Length' => $tour_length,
          'Departure City' => isset($_GET['departure']) ? sanitize_text_field($_GET['departure']) : 'N/A',
          'Vehicle Type' => isset($_GET['vehicle_type']) ? sanitize_text_field($_GET['vehicle_type']) : 'N/A'
        ];

        foreach ($details as $label => $value) {
          echo '
          <div style="flex: 1 1 45%; margin-right: 5%; margin-bottom: 10px;">
            <p style="margin: 0; font-size: 16px;">
              <span style="font-weight: 600; color: #333;">' . esc_html($label) . ':</span>
              <span style="color: #555;"> ' . esc_html($value) . '</span>
            </p>
          </div>';
        }
      ?>
    </div>
  </div>

  <div class="pricing-table mt-4">
    <h3>Pricing Options</h3>

    <?php
      $price_adult = (int) get_field('price_per_adult');
      $price_kid = (int) get_field('price_per_kid');

      $adult_count = isset($_GET['adults']) ? (int) $_GET['adults'] : 0;
      $kid_count = isset($_GET['kids']) ? (int) $_GET['kids'] : 0;

      $total_adult_price = $price_adult * $adult_count;
      $total_kid_price = $price_kid * $kid_count;
      $total_price = $total_adult_price + $total_kid_price;
    ?>

    <div style="overflow-x: auto; margin-top: 30px;">
      <table style="width: 100%; border-collapse: collapse; background-color: #f9f9f9; font-family: Arial, sans-serif; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        <thead>
          <tr style="background-color: #e0e0e0; color: #333;">
            <th style="padding: 12px; border: 1px solid #ccc; text-align: center;">Traveler</th>
            <th style="padding: 12px; border: 1px solid #ccc; text-align: center;">Unit Price</th>
            <th style="padding: 12px; border: 1px solid #ccc; text-align: center;">Quantity</th>
            <th style="padding: 12px; border: 1px solid #ccc; text-align: center;">Total</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td style="padding: 12px; border: 1px solid #ddd; text-align: center;"><strong>Adult</strong></td>
            <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">PKR <?php echo number_format($price_adult); ?></td>
            <td style="padding: 12px; border: 1px solid #ddd; text-align: center;"><?php echo $adult_count; ?></td>
            <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">PKR <?php echo number_format($total_adult_price); ?></td>
          </tr>
          <tr>
            <td style="padding: 12px; border: 1px solid #ddd; text-align: center;"><strong>Kid</strong></td>
            <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">PKR <?php echo number_format($price_kid); ?></td>
            <td style="padding: 12px; border: 1px solid #ddd; text-align: center;"><?php echo $kid_count; ?></td>
            <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">PKR <?php echo number_format($total_kid_price); ?></td>
          </tr>
        </tbody>
        <tfoot>
          <tr style="background-color: #fff9e6;">
            <td colspan="3" style="padding: 16px; text-align: right; border: 1px solid #ccc; font-weight: bold; font-size: 18px;">Total Price:</td>
            <td style="padding: 16px; text-align: center; border: 1px solid #ccc; font-weight: bold; font-size: 20px; color: #d2691e;">PKR <?php echo number_format($total_price); ?></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  <div class="mt-4">
    <?php 
    // Collect all relevant query parameters
    $params = array(
      'adults' => isset($_GET['adults']) ? (int)$_GET['adults'] : 1,
      'kids' => isset($_GET['kids']) ? (int)$_GET['kids'] : 0,
      'start_date' => isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '',
      'departure' => isset($_GET['departure']) ? sanitize_text_field($_GET['departure']) : '',
      'vehicle_type' => isset($_GET['vehicle_type']) ? sanitize_text_field($_GET['vehicle_type']) : '',
      'trip_type' => isset($_GET['trip_type']) ? sanitize_text_field($_GET['trip_type']) : '',
      'book_now' => 1
    );
    $book_url = add_query_arg($params, get_permalink());
    $login_url = site_url('/login/');
    ?>
    <?php if (is_user_logged_in()): ?>
        <a style="background-color: #cc6b37; padding: 12px 20px; color: white; display: inline-block; margin-top: 10px; text-decoration: none; border-radius: 5px; font-weight: bold;" 
           href="<?php echo esc_url($book_url); ?>" 
           class="btn btn-primary">Book Now</a>
    <?php else: ?>
        <a style="background-color: #cc6b37; padding: 12px 20px; color: white; display: inline-block; margin-top: 10px; text-decoration: none; border-radius: 5px; font-weight: bold;" 
           href="<?php echo esc_url($login_url); ?>?redirect_to=<?php echo urlencode(get_permalink()); ?>" 
           class="btn btn-primary">Book Now</a>
    <?php endif; ?>
  </div>

  <!-- Tour Gallery -->
  <div class="tour-gallery" style="margin-top: 40px;">
    <h3 style="margin-bottom: 20px; font-size: 24px;">Tour Gallery</h3>
    <div style="display: flex; flex-wrap: wrap; gap: 20px;">
      <?php
        $image_fields = ['image_1', 'image_2', 'image_3', 'image_4', 'image_5', 'image_6'];

        foreach ($image_fields as $field_name) {
          $image_id = get_field($field_name);
          if (!empty($image_id)) {
            $image_url = wp_get_attachment_url($image_id);
            if ($image_url) {
              echo '<div style="flex: 0 1 calc(33.33% - 15px);">
                      <img src="' . esc_url($image_url) . '" alt="Tour Image" style="width: 100%; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
                    </div>';
            }
          }
        }
      ?>
    </div>
  </div>

  <!-- Itinerary -->
  <?php
    $days = [
      ['title' => get_field('day_1'), 'desc' => get_field('day_1_description')],
      ['title' => get_field('day_2'), 'desc' => get_field('day_2_description')],
      ['title' => get_field('day_3'), 'desc' => get_field('day_3_description')],
    ];

    $has_itinerary = false;
    foreach ($days as $d) {
      if (!empty($d['title']) || !empty($d['desc'])) {
        $has_itinerary = true;
        break;
      }
    }

    if ($has_itinerary): ?>
      <div class="itinerary mt-5">
        <h3>Trip Itinerary</h3>
        <div class="accordion" id="itineraryAccordion">
          <?php foreach ($days as $index => $day): ?>
            <?php if ($day['title'] || $day['desc']): ?>
              <div class="accordion-item">
                <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                  <button class="accordion-button <?php echo $index > 0 ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>" aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>" aria-controls="collapse<?php echo $index; ?>">
                    <?php echo esc_html($day['title']); ?>
                  </button>
                </h2>
                <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" aria-labelledby="heading<?php echo $index; ?>" data-bs-parent="#itineraryAccordion">
                  <div class="accordion-body">
                    <?php echo nl2br(esc_html($day['desc'])); ?>
                  </div>
                </div>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </div>
  <?php endif; ?>

</div>

<?php get_footer(); ?>
