<?php
class KYC {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function uploadDocument($user_id, $document_type, $file) {
        $uploadDir = 'uploads/kyc/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filename = uniqid() . '-' . basename($file['name']);
        $targetPath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $this->db->query(
                "INSERT INTO kyc_documents (user_id, document_type, document_path, status) 
                 VALUES (:user_id, :document_type, :document_path, 'pending')"
            );
            $this->db->bind(':user_id', $user_id);
            $this->db->bind(':document_type', $document_type);
            $this->db->bind(':document_path', $targetPath);
            
            if ($this->db->execute()) {
                // Also update the main user kyc status to pending
                $this->updateUserKycStatus($user_id, 'pending');
                return true;
            }
        }
        return false;
    }

    public function getDocumentsByUserId($user_id) {
        $this->db->query("SELECT * FROM kyc_documents WHERE user_id = :user_id ORDER BY created_at DESC");
        $this->db->bind(':user_id', $user_id);
        return $this->db->resultSet();
    }

    public function getAllDocuments() {
        $this->db->query(
            "SELECT k.*, u.username 
             FROM kyc_documents k
             JOIN users u ON k.user_id = u.id
             ORDER BY k.created_at DESC"
        );
        return $this->db->resultSet();
    }

    public function getDocumentById($id) {
        $this->db->query("SELECT * FROM kyc_documents WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function updateDocumentStatus($id, $status, $reviewed_by, $reason = null) {
        $this->db->query(
            "UPDATE kyc_documents 
             SET status = :status, reviewed_by = :reviewed_by, rejection_reason = :rejection_reason, reviewed_at = NOW() 
             WHERE id = :id"
        );
        $this->db->bind(':id', $id);
        $this->db->bind(':status', $status);
        $this->db->bind(':reviewed_by', $reviewed_by);
        $this->db->bind(':rejection_reason', $reason);
        return $this->db->execute();
    }

    public function updateUserKycStatus($user_id, $status) {
        $this->db->query("UPDATE users SET kyc_status = :status WHERE id = :user_id");
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':status', $status);
        return $this->db->execute();
    }
} 