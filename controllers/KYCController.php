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
        
        $action = $_POST['action'] ?? $_GET['action'] ?? '';

        switch ($action) {
            case 'upload':
                $this->uploadDocument();
                break;
            case 'approve_kyc':
            case 'reject_kyc':
                $this->reviewDocument($action);
                break;
            default:
                header('Location: ../profile.php');
                exit();
        }
    }

    private function reviewDocument($action) {
        if (!$this->auth->user()['is_admin']) {
            http_response_code(403);
            die("Forbidden.");
        }

        $kyc_id = filter_input(INPUT_POST, 'kyc_id', FILTER_VALIDATE_INT);
        if (!$kyc_id) {
            header('Location: ../admin/kyc.php?status=error&msg=InvalidID');
            exit();
        }

        $status = ($action === 'approve_kyc') ? 'approved' : 'rejected';
        $admin_id = $this->auth->user()['id'];
        $reason = ($action === 'reject_kyc') ? filter_input(INPUT_POST, 'rejection_reason', FILTER_SANITIZE_STRING) : null;

        $kyc_doc = $this->kyc->getDocumentById($kyc_id);
        if (!$kyc_doc) {
            header('Location: ../admin/kyc.php?status=error&msg=NotFound');
            exit();
        }
        
        if ($this->kyc->updateDocumentStatus($kyc_id, $status, $admin_id, $reason)) {
            $this->kyc->updateUserKycStatus($kyc_doc['user_id'], $status);

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