<?php
/*
 * Front-end Registration Page Template
 */

session_start();

if (is_user_logged_in()) {
    wp_redirect(site_url('/dashboard/'));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_user($_POST['username'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (username_exists($username)) {
        $error = 'Username already exists.';
    } elseif (!is_email($email) || email_exists($email)) {
        $error = 'Invalid or already used email.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $user_id = wp_create_user($username, $password, $email);
        if (is_wp_error($user_id)) {
            $error = 'Registration failed.';
        } else {
            wp_set_current_user($user_id);
            wp_set_auth_cookie($user_id);
            wp_redirect(site_url('/dashboard/'));
            exit;
        }
    }
}
get_header();
?>
<div class="container" style="max-width:400px;margin:40px auto;">
    <h2>Register</h2>
    <?php if ($error): ?><div style="color:red;"><?php echo esc_html($error); ?></div><?php endif; ?>
    <form method="post">
        <div style="margin-bottom:15px;">
            <label>Username</label>
            <input type="text" name="username" required class="form-control" />
        </div>
        <div style="margin-bottom:15px;">
            <label>Email</label>
            <input type="email" name="email" required class="form-control" />
        </div>
        <div style="margin-bottom:15px;">
            <label>Password</label>
            <input type="password" name="password" required class="form-control" />
        </div>
        <div style="margin-bottom:15px;">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" required class="form-control" />
        </div>
        <button type="submit" class="btn btn-primary">Register</button>
    </form>
    <p style="margin-top:15px;">Already have an account? <a href="<?php echo site_url('/login/'); ?>">Login</a></p>
</div>
<?php get_footer(); ?> 