<?php
/*
 * Front-end User Dashboard Page Template
 */

session_start();

if (!is_user_logged_in()) {
    wp_redirect(site_url('/login/'));
    exit;
}
get_header();
$user = wp_get_current_user();
?>
<div class="container" style="max-width:700px;margin:40px auto;">
    <h2>Welcome, <?php echo esc_html($user->display_name ?: $user->user_login); ?>!</h2>
    <p>Email: <?php echo esc_html($user->user_email); ?></p>
    <h3>Your Bookings</h3>
    <?php echo do_shortcode('[user_bookings]'); ?>
    <p style="margin-top:30px;"><a href="<?php echo esc_url( wp_logout_url( site_url('/login/') ) ); ?>">Logout</a></p>

    <hr>
    <h3>My Tours</h3>
    <?php
    // Handle Add/Edit/Delete Actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tour_action']) && is_user_logged_in()) {
        $current_user = wp_get_current_user();
        // Add/Edit Tour
        if ($_POST['tour_action'] === 'add' || $_POST['tour_action'] === 'edit') {
            $tour_title = sanitize_text_field($_POST['tour_title']);
            $tour_content = sanitize_textarea_field($_POST['tour_content']);
            $tour_id = isset($_POST['tour_id']) ? intval($_POST['tour_id']) : 0;

            $tour_data = [
                'post_title'   => $tour_title,
                'post_content' => $tour_content,
                'post_type'    => 'tour',
                'post_status'  => 'publish',
                'post_author'  => $current_user->ID,
            ];

            if ($_POST['tour_action'] === 'edit' && $tour_id) {
                $tour_data['ID'] = $tour_id;
                wp_update_post($tour_data);
            } else {
                wp_insert_post($tour_data);
            }
        }
        // Delete Tour
        if ($_POST['tour_action'] === 'delete' && isset($_POST['tour_id'])) {
            $tour_id = intval($_POST['tour_id']);
            $tour = get_post($tour_id);
            if ($tour && $tour->post_author == get_current_user_id()) {
                wp_delete_post($tour_id, true);
            }
        }
    }

    // Fetch user's tours
    $user_tours = get_posts([
        'post_type' => 'tour',
        'author' => get_current_user_id(),
        'posts_per_page' => -1,
    ]);

    // Check if editing
    $edit_tour = null;
    if (isset($_GET['edit_tour'])) {
        $edit_tour = get_post(intval($_GET['edit_tour']));
        if ($edit_tour && $edit_tour->post_author != get_current_user_id()) {
            $edit_tour = null; // Not allowed
        }
    }
    ?>

    <!-- Add/Edit Tour Form -->
    <div style="margin-bottom:30px;">
        <form method="post">
            <input type="hidden" name="tour_action" value="<?php echo $edit_tour ? 'edit' : 'add'; ?>">
            <?php if ($edit_tour): ?>
                <input type="hidden" name="tour_id" value="<?php echo esc_attr($edit_tour->ID); ?>">
            <?php endif; ?>
            <div>
                <label>Tour Title</label>
                <input type="text" name="tour_title" required value="<?php echo esc_attr($edit_tour ? $edit_tour->post_title : ''); ?>" style="width:100%;margin-bottom:10px;">
            </div>
            <div>
                <label>Description</label>
                <textarea name="tour_content" required style="width:100%;height:80px;"><?php echo esc_textarea($edit_tour ? $edit_tour->post_content : ''); ?></textarea>
            </div>
            <button type="submit" class="btn btn-success"><?php echo $edit_tour ? 'Update Tour' : 'Add New Tour'; ?></button>
            <?php if ($edit_tour): ?>
                <a href="<?php echo site_url('/dashboard/'); ?>" class="btn btn-secondary">Cancel</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- List User's Tours -->
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr>
                <th style="text-align:left;">Title</th>
                <th style="text-align:left;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($user_tours as $tour): ?>
                <tr>
                    <td><?php echo esc_html($tour->post_title); ?></td>
                    <td>
                        <a href="<?php echo add_query_arg('edit_tour', $tour->ID, site_url('/dashboard/')); ?>" class="btn btn-sm btn-warning">Edit</a>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="tour_action" value="delete">
                            <input type="hidden" name="tour_id" value="<?php echo esc_attr($tour->ID); ?>">
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this tour?');">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($user_tours)): ?>
                <tr><td colspan="2">No tours found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php get_footer(); ?> 