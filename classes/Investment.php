<?php
class Investment {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function getPlans() {
        $this->db->query("SELECT * FROM investment_plans ORDER BY created_at DESC");
        return $this->db->resultSet();
    }

    public function getPlanById($id) {
        $this->db->query("SELECT * FROM investment_plans WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function createPlan($data) {
        $this->db->query(
            "INSERT INTO investment_plans (name, return_percentage, duration_days, min_amount, max_amount, status) 
             VALUES (:name, :return_percentage, :duration_days, :min_amount, :max_amount, :status)"
        );

        $this->db->bind(':name', $data['name']);
        $this->db->bind(':return_percentage', $data['return_percentage']);
        $this->db->bind(':duration_days', $data['duration_days']);
        $this->db->bind(':min_amount', $data['min_amount']);
        $this->db->bind(':max_amount', $data['max_amount']);
        $this->db->bind(':status', $data['status']);

        return $this->db->execute();
    }

    public function updatePlan($data) {
        $this->db->query(
            "UPDATE investment_plans 
             SET name = :name, 
                 return_percentage = :return_percentage, 
                 duration_days = :duration_days, 
                 min_amount = :min_amount, 
                 max_amount = :max_amount, 
                 status = :status 
             WHERE id = :id"
        );

        $this->db->bind(':id', $data['id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':return_percentage', $data['return_percentage']);
        $this->db->bind(':duration_days', $data['duration_days']);
        $this->db->bind(':min_amount', $data['min_amount']);
        $this->db->bind(':max_amount', $data['max_amount']);
        $this->db->bind(':status', $data['status']);

        return $this->db->execute();
    }

    public function createInvestment($user_id, $plan_id, $amount) {
        $plan = $this->getPlanById($plan_id);
        if (!$plan) {
            return false;
        }

        $this->db->query(
            "INSERT INTO investments (user_id, plan_id, amount, status, end_date) 
             VALUES (:user_id, :plan_id, :amount, 'active', DATE_ADD(NOW(), INTERVAL :duration DAY))"
        );

        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':plan_id', $plan_id);
        $this->db->bind(':amount', $amount);
        $this->db->bind(':duration', $plan['duration_days']);
        
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }
} 