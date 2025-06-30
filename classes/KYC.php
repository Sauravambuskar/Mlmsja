<?php
class KYC {
    private $db;
    const UPLOAD_DIR = __DIR__ . '/../uploads/kyc/';

    public function __construct(Database $db) {
        $this->db = $db;
        if (!is_dir(self::UPLOAD_DIR)) {
            mkdir(self::UPLOAD_DIR, 0755, true);
        }
    }

    /**
     * Get KYC documents for a specific user.
     * @param int $user_id
     * @return array
     */
    public function getDocumentsByUserId($user_id) {
        $this->db->query("SELECT * FROM kyc_documents WHERE user_id = :user_id ORDER BY created_at DESC");
        $this->db->bind(':user_id', $user_id);
        return $this->db->resultSet();
    }

    /**
     * Get all KYC documents for the admin panel.
     * @return array
     */
    public function getAllDocuments() {
        $this->db->query("
            SELECT k.*, u.name as user_name, u.email as user_email
            FROM kyc_documents k
            JOIN users u ON k.user_id = u.id
            ORDER BY k.created_at DESC
        ");
        return $this->db->resultSet();
    }

    /**
     * Update the status of a specific KYC document.
     * @param int $document_id
     * @param string $status 'verified' or 'rejected'
     * @param int $admin_id The ID of the admin reviewing the document.
     * @return bool
     */
    public function updateDocumentStatus($document_id, $status, $admin_id) {
        $this->db->query("
            UPDATE kyc_documents 
            SET status = :status, reviewed_by = :admin_id, updated_at = NOW()
            WHERE id = :document_id
        ");
        $this->db->bind(':status', $status);
        $this->db->bind(':admin_id', $admin_id);
        $this->db->bind(':document_id', $document_id);
        return $this->db->execute();
    }

    /**
     * Upload a KYC document for a user.
     * @param int $user_id
     * @param string $document_type
     * @param array $file The $_FILES['document'] array.
     * @return bool
     */
    public function uploadDocument($user_id, $document_type, array $file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            // Handle upload error
            return false;
        }

        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $safe_filename = "user_{$user_id}_" . uniqid() . '.' . $file_extension;
        $destination = self::UPLOAD_DIR . $safe_filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $this->db->query(
                "INSERT INTO kyc_documents (user_id, document_type, file_path, status)
                 VALUES (:user_id, :document_type, :file_path, 'pending')"
            );
            $this->db->bind(':user_id', $user_id);
            $this->db->bind(':document_type', $document_type);
            $this->db->bind(':file_path', $safe_filename);
            
            // Also update user's main KYC status to pending
            $this->updateUserKycStatus($user_id, 'pending');

            return $this->db->execute();
        }

        return false;
    }

    /**
     * Update the master KYC status for a user.
     * @param int $user_id
     * @param string $status 'pending', 'verified', or 'rejected'
     * @return bool
     */
    public function updateUserKycStatus($user_id, $status) {
        $this->db->query("UPDATE users SET kyc_status = :status WHERE id = :user_id");
        $this->db->bind(':status', $status);
        $this->db->bind(':user_id', $user_id);
        return $this->db->execute();
    }
} 