<?php
// src/pages/manage_noticeboard.php
$action = $_GET['action'] ?? 'list';
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'add') {
    try {
        $stmt = $pdo->prepare("INSERT INTO NoticeBoard (title, content, publish_date) VALUES (?, ?, ?)");
        $stmt->execute([$_POST['title'], $_POST['content'], date('Y-m-d H:i:s')]);
        $message = "Notice posted successfully!";
        $action = 'list';
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}

if ($action == 'list') {
    $stmt = $pdo->query("SELECT * FROM noticeboard ORDER BY publish_date DESC");
    $notices = $stmt->fetchAll();
}
?>
<div class="content-body">
    <?php if (!empty($message)): ?>
        <div class="success-message"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if ($action == 'add'): ?>
        <h3>Post New Notice</h3>
        <form method="POST" action="index.php?page=manage_noticeboard&action=add" class="data-form">
            <div class="form-group"><label>Title</label><input type="text" name="title" required></div>
            <div class="form-group"><label>Content</label><textarea name="content" required></textarea></div>
            <div class="form-actions">
                <button type="submit" class="btn">Post Notice</button>
                <a href="index.php?page=manage_noticeboard" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    <?php else: ?>
        <div class="page-actions"><a href="index.php?page=manage_noticeboard&action=add" class="btn">Post New Notice</a></div>
        <h3>All Notices</h3>
        <table class="data-table">
            <thead><tr><th>Date</th><th>Title</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($notices as $notice): ?>
                <tr>
                    <td><?php echo htmlspecialchars($notice['publish_date']); ?></td>
                    <td><a href="#" title="<?php echo htmlspecialchars($notice['content']); ?>"><?php echo htmlspecialchars($notice['title']); ?></a></td>
                    <td><a href="#" class="btn-sm btn-danger">Delete</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
