<?php
class MLM {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    /**
     * Adds a new user to the MLM tree structure.
     * This uses a closure table approach for efficient querying of hierarchies.
     *
     * @param int $user_id The ID of the new user being added.
     * @param int|null $referrer_id The ID of the user who referred the new user.
     * @return bool
     */
    public function addUserToTree($user_id, $referrer_id = null) {
        // Every user is a descendant of themselves at level 0.
        $this->db->query("INSERT INTO mlm_tree (user_id, ancestor_id, level) VALUES (:user_id, :user_id, 0)");
        $this->db->bind(':user_id', $user_id);
        $this->db->execute();

        // If the user was referred, link them to their referrer's entire upline.
        if ($referrer_id) {
            $this->db->query(
                "INSERT INTO mlm_tree (user_id, ancestor_id, level)
                 SELECT :user_id, ancestor_id, level + 1
                 FROM mlm_tree
                 WHERE user_id = :referrer_id"
            );
            $this->db->bind(':user_id', $user_id);
            $this->db->bind(':referrer_id', $referrer_id);
            return $this->db->execute();
        }

        return true;
    }

    /**
     * Get the direct referrals (downline) for a specific user.
     * @param int $user_id
     * @return array
     */
    public function getDownline($user_id) {
        $this->db->query(
            "SELECT u.id, u.name, u.email, u.created_at 
             FROM users u
             JOIN mlm_tree t ON u.id = t.user_id
             WHERE t.ancestor_id = :user_id AND t.level = 1"
        );
        $this->db->bind(':user_id', $user_id);
        return $this->db->resultSet();
    }

    /**
     * Get the upline (ancestors) for a specific user.
     * @param int $user_id
     * @return array
     */
    public function getUpline($user_id) {
        $this->db->query(
            "SELECT u.id, u.name, t.level
             FROM users u
             JOIN mlm_tree t ON u.id = t.ancestor_id
             WHERE t.user_id = :user_id AND t.level > 0
             ORDER BY t.level ASC"
        );
        $this->db->bind(':user_id', $user_id);
        return $this->db->resultSet();
    }

    /**
     * Get the total commission earned by a user.
     * @param int $user_id
     * @return float
     */
    public function getTotalCommission($user_id) {
        $this->db->query("SELECT SUM(amount) as total_commission FROM transactions WHERE user_id = :user_id AND type = 'commission'");
        $this->db->bind(':user_id', $user_id);
        $result = $this->db->single();
        return $result['total_commission'] ?? 0.0;
    }

    /**
     * Calculates and distributes commission up the referral chain.
     * @param int $investor_id The user who made the investment.
     * @param float $investment_amount The total amount of the investment.
     * @param int $investment_id The ID of the investment record.
     */
    public function distributeCommission($investor_id, $investment_amount, $investment_id) {
        $upline = $this->getUpline($investor_id);
        $commission_levels = defined('COMMISSION_LEVELS') ? COMMISSION_LEVELS : [];

        $wallet = new Wallet($this->db);
        $transaction = new Transaction($this->db);

        foreach ($upline as $ancestor) {
            $level = $ancestor['level'];
            
            // Check if a commission is defined for this level
            if (isset($commission_levels[$level])) {
                $commission_percentage = $commission_levels[$level];
                $commission_amount = ($investment_amount * $commission_percentage) / 100;

                // 1. Add funds to the ancestor's wallet
                $wallet->updateBalance($ancestor['id'], $commission_amount);

                // 2. Create a transaction record for the commission
                $description = "Level {$level} commission from investment #{$investment_id}";
                $transaction->create(
                    $ancestor['id'], 
                    'commission', 
                    $commission_amount, 
                    'completed', 
                    $description,
                    $investment_id
                );
            }
        }
    }
} 