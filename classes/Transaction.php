<?php
class Transaction {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    /**
     * Create a new transaction record.
     * @param int $user_id
     * @param string $type The type of transaction (e.g., 'deposit', 'investment').
     * @param float $amount The amount of the transaction.
     * @param string $status The status of the transaction (e.g., 'completed', 'pending').
     * @param string|null $description A description of the transaction.
     * @param int|null $reference_id An ID to link to another table record (e.g., investment_id).
     * @return bool
     */
    public function create($user_id, $type, $amount, $status, $description = null, $reference_id = null) {
        $this->db->query(
            "INSERT INTO transactions (user_id, type, amount, status, description, reference_id) 
             VALUES (:user_id, :type, :amount, :status, :description, :reference_id)"
        );
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':type', $type);
        $this->db->bind(':amount', $amount);
        $this->db->bind(':status', $status);
        $this->db->bind(':description', $description);
        $this->db->bind(':reference_id', $reference_id);

        return $this->db->execute();
    }

    /**
     * Get all transactions for a specific user.
     * @param int $user_id
     * @param int $limit The number of records to return.
     * @return array
     */
    public function getByUserId($user_id, $limit = 25) {
        $this->db->query(
            "SELECT * FROM transactions 
             WHERE user_id = :user_id 
             ORDER BY created_at DESC 
             LIMIT :limit"
        );
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }

    /**
     * Get all transactions system-wide.
     * @return array
     */
    public function getAll() {
        $this->db->query("SELECT t.*, u.name as user_name FROM transactions t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC");
        return $this->db->resultSet();
    }
} 