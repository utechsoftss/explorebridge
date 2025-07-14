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
</div>
<?php get_footer(); ?> 