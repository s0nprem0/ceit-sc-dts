<?php
// helpers/AuthHelper.php
class AuthHelper {
    public static function authenticateAdmin($username, $password) {
        // You may want to move credentials to env/config in production
        if ($username === 'CEIT_SC2026' && $password === '081825_ceitsc') {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            return array('success' => true, 'message' => 'Login successful');
        } else {
            return array('success' => false, 'message' => 'Invalid username or password');
        }
    }
}
