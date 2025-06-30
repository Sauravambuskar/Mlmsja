<?php
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/KYC.php';

$db = new Database();
$auth = new Auth($db);

if (!$auth->isLoggedIn() || $auth->user()['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

$kyc_handler = new KYC($db);
$documents = $kyc_handler->getAllDocuments();

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<div id="content">
    <h2>KYC Management</h2>
    <p>Review and process user-submitted KYC documents.</p>

    <div class="card">
        <div class="card-header">
            <h4>Pending Submissions</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Document Type</th>
                            <th>Submitted At</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($documents)): ?>
                            <tr><td colspan="5" class="text-center">No documents awaiting review.</td></tr>
                        <?php else: ?>
                            <?php foreach($documents as $doc): ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($doc['user_name']); ?><br>
                                    <small><?php echo htmlspecialchars($doc['user_email']); ?></small>
                                </td>
                                <td><?php echo ucfirst(str_replace('_', ' ', $doc['document_type'])); ?></td>
                                <td><?php echo date('M d, Y', strtotime($doc['created_at'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        switch($doc['status']) {
                                            case 'verified': echo 'success'; break;
                                            case 'pending': echo 'warning'; break;
                                            case 'rejected': echo 'danger'; break;
                                        }
                                    ?>">
                                        <?php echo ucfirst($doc['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo URLROOT . '/uploads/kyc/' . $doc['file_path']; ?>" target="_blank" class="btn btn-sm btn-info">View</a>
                                    <?php if ($doc['status'] === 'pending'): ?>
                                        <a href="../controllers/KYCController.php?action=approve&id=<?php echo $doc['id']; ?>" class="btn btn-sm btn-success">Approve</a>
                                        <a href="../controllers/KYCController.php?action=reject&id=<?php echo $doc['id']; ?>" class="btn btn-sm btn-danger">Reject</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 