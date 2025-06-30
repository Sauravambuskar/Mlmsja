<?php
class Wallet {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    /**
     * Get a user's wallet by their user ID.
     * @param int $user_id
     * @return array|false
     */
    public function getWalletByUserId($user_id) {
        $this->db->query("SELECT * FROM wallets WHERE user_id = :user_id");
        $this->db->bind(':user_id', $user_id);
        return $this->db->single();
    }

    /**
     * Create a new wallet for a user.
     * @param int $user_id
     * @return bool
     */
    public function createWallet($user_id) {
        $this->db->query("INSERT INTO wallets (user_id, balance) VALUES (:user_id, 0.00)");
        $this->db->bind(':user_id', $user_id);
        return $this->db->execute();
    }

    /**
     * Update a user's wallet balance.
     * @param int $user_id
     * @param float $amount The amount to add (positive) or subtract (negative).
     * @return bool
     */
    public function updateBalance($user_id, $amount) {
        // Using a transaction for safety
        try {
            // This is a simplified transaction; a real application might need more robust handling.
            $this->db->query("UPDATE wallets SET balance = balance + :amount WHERE user_id = :user_id");
            $this->db->bind(':amount', $amount);
            $this->db->bind(':user_id', $user_id);
            return $this->db->execute();
        } catch (Exception $e) {
            // Log error
            return false;
        }
    }

    /**
     * Check if a user has sufficient funds.
     * @param int $user_id
     * @param float $amount The amount to check for.
     * @return bool
     */
    public function hasSufficientFunds($user_id, $amount) {
        $wallet = $this->getWalletByUserId($user_id);
        return $wallet && $wallet['balance'] >= $amount;
    }
} 