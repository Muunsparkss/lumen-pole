<?php
require_once 'Auth.php';
require_once '../config/Database.php';

class AdminAuth extends Auth {
    
    // Check if current user is admin
    public static function isAdmin() {
        if (!Auth::isLoggedIn()) {
            return false;
        }
        
        $user_id = $_SESSION['user_id'];
        
        $db = new Database();
        $conn = $db->connect();
        
        $query = "SELECT is_admin FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return false;
        }
        
        $user = $result->fetch_assoc();
        return $user['is_admin'] == 1;
    }
}
?>