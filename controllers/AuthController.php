<?php
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Auth.php';

class AuthController {
    private $db;
    private $auth;

    public function __construct() {
        $this->db = new Database();
        $this->auth = new Auth($this->db);
    }

    public function handleRequest() {
        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'register':
                $this->register();
                break;
            case 'login':
                $this->login();
                break;
            default:
                // Redirect to home or show an error
                header('Location: ../index.php');
                exit();
        }
    }

    private function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); // Method Not Allowed
            exit('Invalid request method.');
        }

        // Sanitize and validate input
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $referral_code = filter_input(INPUT_POST, 'referral_code', FILTER_SANITIZE_STRING) ?: null;

        // Basic validation
        if (!$name || !$email || !$password || $password !== $confirm_password) {
            // In a real app, provide more specific error messages
            die("Registration failed: Invalid data.");
        }

        $result = $this->auth->register($name, $email, $password, $referral_code);

        if ($result) {
            // Redirect to login page with a success message
            header('Location: ../index.php?status=registered');
            exit();
        } else {
            // Show an error message
            die("Registration failed. Email might already be in use.");
        }
    }

    private function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Invalid request method.');
        }

        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'];

        if (!$email || !$password) {
            die("Login failed: Invalid data.");
        }

        $user = $this->auth->login($email, $password);

        if ($user) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            
            // Redirect to dashboard
            header('Location: ../dashboard.php');
            exit();
        } else {
            // Redirect back to login with an error
            header('Location: ../index.php?status=login_failed');
            exit();
        }
    }
}

// Entry point for the controller
$controller = new AuthController();
$controller->handleRequest(); 