<?php
class Auth {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Check if a user is logged in.
     * @return bool
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    /**
     * Get the currently logged-in user's data.
     * @return array|null
     */
    public function user() {
        if ($this->isLoggedIn()) {
            $this->db->query("SELECT * FROM users WHERE id = :id");
            $this->db->bind(':id', $_SESSION['user_id']);
            return $this->db->single();
        }
        return null;
    }

    /**
     * Register a new user.
     * @param string $name
     * @param string $email
     * @param string $password
     * @param string|null $referred_by_code
     * @return bool
     */
    public function register($name, $email, $password, $referred_by_code = null) {
        // Check if email already exists
        $this->db->query("SELECT id FROM users WHERE email = :email");
        $this->db->bind(':email', $email);
        if ($this->db->rowCount() > 0) {
            return false; // Email already taken
        }

        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Handle referral
        $referred_by_id = null;
        if ($referred_by_code) {
            $this->db->query("SELECT id FROM users WHERE referral_code = :referral_code");
            $this->db->bind(':referral_code', $referred_by_code);
            $referrer = $this->db->single();
            if ($referrer) {
                $referred_by_id = $referrer['id'];
            }
        }
        
        // Generate a unique referral code for the new user
        $new_referral_code = substr(md5(uniqid(mt_rand(), true)), 0, 8);

        // Insert user into database
        $this->db->query(
            "INSERT INTO users (name, email, password, referral_code, referred_by) 
             VALUES (:name, :email, :password, :referral_code, :referred_by)"
        );
        $this->db->bind(':name', $name);
        $this->db->bind(':email', $email);
        $this->db->bind(':password', $hashed_password);
        $this->db->bind(':referral_code', $new_referral_code);
        $this->db->bind(':referred_by', $referred_by_id);

        // Execute the user insertion
        if ($this->db->execute()) {
            // Get the new user's ID
            $new_user_id = $this->db->lastInsertId();

            // Create a wallet for the new user
            require_once __DIR__ . '/Wallet.php';
            $wallet = new Wallet($this->db);
            $wallet->createWallet($new_user_id);

            // Add user to MLM tree
            require_once __DIR__ . '/MLM.php';
            $mlm = new MLM($this->db);
            return $mlm->addUserToTree($new_user_id, $referred_by_id);
        }

        return false;
    }

    /**
     * Attempt to log the user in.
     * @param string $email
     * @param string $password
     * @return array|false The user data on success, false on failure.
     */
    public function login($email, $password) {
        $this->db->query("SELECT * FROM users WHERE email = :email");
        $this->db->bind(':email', $email);
        
        $user = $this->db->single();

        if ($user && password_verify($password, $user['password'])) {
            return $user; // Login successful
        }

        return false; // Login failed
    }

    /**
     * Log the user out.
     */
    public function logout() {
        session_unset();
        session_destroy();
    }
} 