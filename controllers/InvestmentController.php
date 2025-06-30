<?php
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Investment.php';
require_once __DIR__ . '/../classes/Wallet.php';
require_once __DIR__ . '/../classes/Transaction.php';

class InvestmentController {
    private $db;
    private $auth;
    private $investment;
    private $wallet;
    private $transaction;

    public function __construct() {
        $this->db = new Database();
        $this->auth = new Auth($this->db);
        $this->investment = new Investment($this->db);
        $this->wallet = new Wallet($this->db);
        $this->transaction = new Transaction($this->db);
    }

    public function handleRequest() {
        $action = $_GET['action'] ?? '';
        
        // Admin-only actions
        if (in_array($action, ['add_plan', 'edit_plan'])) {
            if (!$this->auth->isLoggedIn() || !$this->auth->user()['is_admin']) {
                http_response_code(403);
                die("Forbidden: You do not have permission to access this page.");
            }
        } elseif (!$this->auth->isLoggedIn()) { // Client actions require login
             http_response_code(401);
             die("Unauthorized: You must be logged in.");
        }

        switch ($_POST['action'] ?? $_GET['action'] ?? '') {
            case 'add_plan':
                $this->createPlan();
                break;
            case 'edit_plan':
                $this->updatePlan();
                break;
            case 'invest': // Client: invest in a plan
                $this->createInvestment();
                break;
            default:
                header('Location: ../index.php');
                exit();
        }
    }

    private function createInvestment() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Invalid request method.');
        }

        $user_id = $this->auth->user()['id'];
        $plan_id = filter_input(INPUT_POST, 'plan_id', FILTER_VALIDATE_INT);
        $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);

        if (!$plan_id || !$amount) {
            $this->redirectWithError('../invest.php', 'InvalidData');
        }

        // 1. Get Plan Details
        $plan = $this->investment->getPlanById($plan_id);
        if (!$plan || $plan['status'] !== 'active') {
            $this->redirectWithError('../invest.php', 'PlanNotFound');
        }

        // 2. Validate Amount
        if ($amount < $plan['min_amount'] || $amount > $plan['max_amount']) {
            $this->redirectWithError('../invest.php', 'InvalidAmount');
        }
        
        // 3. Check Wallet Balance
        if (!$this->wallet->hasSufficientFunds($user_id, $amount)) {
            $this->redirectWithError('../invest.php', 'InsufficientFunds');
        }

        // 4. Process Investment (Transactionally)
        // Note: A true DB transaction (BEGIN/COMMIT/ROLLBACK) is safer here.
        // This is a simplified implementation.
        
        // a. Deduct from wallet
        $this->wallet->updateBalance($user_id, -$amount);

        // b. Create investment record
        $investment_id = $this->investment->createInvestment($user_id, $plan_id, $amount);

        // c. Create transaction record
        $description = "Investment in " . $plan['name'];
        $this->transaction->create($user_id, 'investment', -$amount, 'completed', $description, $investment_id);

        // d. Distribute referral commissions
        if ($investment_id) {
            require_once __DIR__ . '/../classes/MLM.php';
            $mlm = new MLM($this->db);
            $mlm->distributeCommission($user_id, $amount, $investment_id);
        }

        header('Location: ../dashboard.php?status=success&msg=InvestmentSuccessful');
        exit();
    }

    private function redirectWithError($location, $message) {
        header("Location: $location?status=error&msg=$message");
        exit();
    }

    private function createPlan() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Invalid request method.');
        }

        $data = [
            'name' => filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING),
            'min_amount' => filter_input(INPUT_POST, 'min_amount', FILTER_VALIDATE_FLOAT),
            'max_amount' => filter_input(INPUT_POST, 'max_amount', FILTER_VALIDATE_FLOAT),
            'return_percentage' => filter_input(INPUT_POST, 'return_percentage', FILTER_VALIDATE_FLOAT),
            'duration_days' => filter_input(INPUT_POST, 'duration_days', FILTER_VALIDATE_INT),
            'status' => filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING)
        ];

        // Basic validation
        if (in_array(false, $data, true)) {
             // Redirect with error
            $this->redirectWithError('../admin/investment_plans.php', 'InvalidData');
        }

        if ($this->investment->createPlan($data)) {
            // Redirect with success message
            header('Location: ../admin/investment_plans.php?status=success&msg=PlanCreated');
            exit();
        } else {
            // Redirect with error
            $this->redirectWithError('../admin/investment_plans.php', 'CreationFailed');
        }
    }

    private function updatePlan() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Invalid request method.');
        }

        $data = [
            'id' => filter_input(INPUT_POST, 'plan_id', FILTER_VALIDATE_INT),
            'name' => filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING),
            'min_amount' => filter_input(INPUT_POST, 'min_amount', FILTER_VALIDATE_FLOAT),
            'max_amount' => filter_input(INPUT_POST, 'max_amount', FILTER_VALIDATE_FLOAT),
            'return_percentage' => filter_input(INPUT_POST, 'return_percentage', FILTER_VALIDATE_FLOAT),
            'duration_days' => filter_input(INPUT_POST, 'duration_days', FILTER_VALIDATE_INT),
            'status' => filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING)
        ];

        // Basic validation
        if (in_array(false, $data, true)) {
             // Redirect with error
            $this->redirectWithError('../admin/investment_plans.php', 'InvalidData');
        }

        if ($this->investment->updatePlan($data)) {
            // Redirect with success message
            header('Location: ../admin/investment_plans.php?status=success&msg=PlanUpdated');
            exit();
        } else {
            // Redirect with error
            $this->redirectWithError('../admin/investment_plans.php', 'UpdateFailed');
        }
    }
}

// Entry point for the controller
$controller = new InvestmentController();
$controller->handleRequest(); 