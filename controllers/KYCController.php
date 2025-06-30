<?php
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/KYC.php';

class KYCController {
    private $db;
    private $auth;
    private $kyc;

    public function __construct() {
        $this->db = new Database();
        $this->auth = new Auth($this->db);
        $this->kyc = new KYC($this->db);
    }

    public function handleRequest() {
        if (!$this->auth->isLoggedIn()) {
            http_response_code(401);
            die("Unauthorized: You must be logged in.");
        }
        
        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'upload':
                $this->uploadDocument();
                break;
            case 'approve':
            case 'reject':
                $this->reviewDocument($action);
                break;
            default:
                header('Location: ../profile.php');
                exit();
        }
    }

    private function reviewDocument($action) {
        if ($this->auth->user()['role'] !== 'admin') {
            http_response_code(403);
            die("Forbidden.");
        }

        $document_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$document_id) {
            header('Location: ../admin/kyc.php?status=error&msg=InvalidID');
            exit();
        }

        // The status is the action name (e.g., 'approve' becomes 'verified')
        $status = ($action === 'approve') ? 'verified' : 'rejected';
        $admin_id = $this->auth->user()['id'];

        // We need the user_id from the document to update their master status
        // (A more optimized way would be to have getDocumentById in the KYC class)
        $all_docs = $this->kyc->getAllDocuments(); // Re-using existing method
        $user_id_to_update = null;
        foreach($all_docs as $doc) {
            if ($doc['id'] === $document_id) {
                $user_id_to_update = $doc['user_id'];
                break;
            }
        }
        
        if ($this->kyc->updateDocumentStatus($document_id, $status, $admin_id)) {
            // If this approval verifies the user, update their master status
            // A more complex logic could be: check if all required docs are verified.
            // For now, we'll assume one verified doc is enough.
            if ($status === 'verified' && $user_id_to_update) {
                $this->kyc->updateUserKycStatus($user_id_to_update, 'verified');
            }

            header('Location: ../admin/kyc.php?status=success&msg=UpdateSuccess');
            exit();
        } else {
            header('Location: ../admin/kyc.php?status=error&msg=UpdateFailed');
            exit();
        }
    }

    private function uploadDocument() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Invalid request method.');
        }

        $user_id = $this->auth->user()['id'];
        $document_type = filter_input(INPUT_POST, 'document_type', FILTER_SANITIZE_STRING);
        $file = $_FILES['document'] ?? null;

        if (!$document_type || !$file || $file['error'] !== UPLOAD_ERR_OK) {
            header('Location: ../profile.php?status=error&msg=InvalidFile');
            exit();
        }

        if ($this->kyc->uploadDocument($user_id, $document_type, $file)) {
            header('Location: ../profile.php?status=success&msg=UploadSuccess');
            exit();
        } else {
            header('Location: ../profile.php?status=error&msg=UploadFailed');
            exit();
        }
    }
}

// Entry point for the controller
$controller = new KYCController();
$controller->handleRequest(); 