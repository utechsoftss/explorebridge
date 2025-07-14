<?php
/*
 * Front-end Login Page Template
 */

session_start();

if (is_user_logged_in()) {
    wp_redirect(site_url('/dashboard/'));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_user($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $user = wp_authenticate($username, $password);
    if (is_wp_error($user)) {
        $error = 'Invalid username or password.';
    } else {
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID);
        wp_redirect(site_url('/dashboard/'));
        exit;
    }
}
get_header();
?>
<div class="container" style="max-width:400px;margin:40px auto;">
    <h2>Login</h2>
    <?php if ($error): ?><div style="color:red;"><?php echo esc_html($error); ?></div><?php endif; ?>
    <form method="post">
        <div style="margin-bottom:15px;">
            <label>Username or Email</label>
            <input type="text" name="username" required class="form-control" />
        </div>
        <div style="margin-bottom:15px;">
            <label>Password</label>
            <input type="password" name="password" required class="form-control" />
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
    <p style="margin-top:15px;">Don't have an account? <a href="<?php echo site_url('/register/'); ?>">Register</a></p>
    <p><a href="<?php echo site_url('/forgot-password/'); ?>">Forgot Password?</a></p>
</div>
<?php get_footer(); ?> 