<?php
/*
 * Front-end Forgot Password Page Template
 */

session_start();

if (is_user_logged_in()) {
    wp_redirect(site_url('/dashboard/'));
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_login = sanitize_text_field($_POST['user_login'] ?? '');
    $user = get_user_by('login', $user_login);
    if (!$user && is_email($user_login)) {
        $user = get_user_by('email', $user_login);
    }
    if ($user) {
        $reset = retrieve_password($user->user_login);
        if ($reset) {
            $message = 'Password reset email sent. Please check your inbox.';
        } else {
            $message = 'Could not send reset email. Try again later.';
        }
    } else {
        $message = 'No user found with that username or email.';
    }
}
get_header();
?>
<div class="container" style="max-width:400px;margin:40px auto;">
    <h2>Forgot Password</h2>
    <?php if ($message): ?><div style="color:green;"><?php echo esc_html($message); ?></div><?php endif; ?>
    <form method="post">
        <div style="margin-bottom:15px;">
            <label>Username or Email</label>
            <input type="text" name="user_login" required class="form-control" />
        </div>
        <button type="submit" class="btn btn-primary">Send Reset Link</button>
    </form>
    <p style="margin-top:15px;"><a href="<?php echo site_url('/login/'); ?>">Back to Login</a></p>
</div>
<?php get_footer(); ?> 