<?php
require_once '../config/Database.php';

class Auth {
    private $conn;
    private $table = 'users';

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    // REGISTER a new user
    public function register($username, $email, $full_name, $password) {
        // Validate input
        if (empty($username) || empty($email) || empty($full_name) || empty($password)) {
            return ['success' => false, 'message' => 'All fields are required'];
        }

        // Check if username or email already exists
        $query = "SELECT id FROM " . $this->table . " WHERE username = ? OR email = ?";
        $stmt = $this->conn->prepare($query);
        
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return ['success' => false, 'message' => 'Username or email already exists'];
        }

        // Hash password with bcrypt (includes automatic salt)
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insert user into database
        $query = "INSERT INTO " . $this->table . " (username, email, full_name, password) 
                  VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("ssss", $username, $email, $full_name, $hashed_password);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'User registered successfully'];
        } else {
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }

    // LOGIN user
    public function login($username, $password) {
        // Validate input
        if (empty($username) || empty($password)) {
            return ['success' => false, 'message' => 'Username and password are required'];
        }

        // Get user from database
        $query = "SELECT id, username, email, password, full_name FROM " . $this->table . " 
                  WHERE username = ?";
        $stmt = $this->conn->prepare($query);
        
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => 'Invalid username or password'];
        }

        $user = $result->fetch_assoc();

        // Verify password using bcrypt
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid username or password'];
        }

        // Create SESSION (not cookie)
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email'] = $user['email'];

        return ['success' => true, 'message' => 'Login successful'];
    }

    // LOGOUT user
    public function logout() {
        session_destroy();
        return ['success' => true, 'message' => 'Logged out successfully'];
    }

    // CHECK if user is logged in
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    // GET current user information
    public static function getCurrentUser() {
        if (self::isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'full_name' => $_SESSION['full_name'],
                'email' => $_SESSION['email']
            ];
        }
        return null;
    }
}
?>