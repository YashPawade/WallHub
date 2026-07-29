<?php
// admin_messages.php - Admin/Owner Message Management
session_start();
include('includes/db.php');

// Check if admin or owner
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'owner')) {
    header('Location: login.php');
    exit();
}

$user_role = $_SESSION['role'];
$admin_id = $_SESSION['user_id'];
$admin_name = $_SESSION['username'];

// Get admin email from database
$admin_email_query = mysqli_query($conn, "SELECT email FROM users WHERE id = $admin_id");
$admin_email = mysqli_fetch_assoc($admin_email_query)['email'] ?? '';

// Ensure table has required columns
$alterQueries = [
    "ALTER TABLE contact_messages ADD COLUMN IF NOT EXISTS replied_by INT NULL",
    "ALTER TABLE contact_messages ADD COLUMN IF NOT EXISTS replied_by_name VARCHAR(100) NULL",
    "ALTER TABLE contact_messages ADD COLUMN IF NOT EXISTS replied_by_email VARCHAR(100) NULL",
    "ALTER TABLE contact_messages ADD COLUMN IF NOT EXISTS is_read_by_admin TINYINT(1) DEFAULT 0",
    "ALTER TABLE contact_messages ADD COLUMN IF NOT EXISTS user_seen_reply_at TIMESTAMP NULL"
];
foreach ($alterQueries as $query) {
    @mysqli_query($conn, $query);
}

// Open a message → mark admin-read
if (isset($_GET['open'])) {
    $id = (int) $_GET['open'];
    mysqli_query($conn, "UPDATE contact_messages SET is_read_by_admin = 1 WHERE id = $id");
    header("Location: admin_messages.php#msg-$id");
    exit();
}

// Mark all admin-read
if (isset($_GET['mark_all_admin_read'])) {
    mysqli_query($conn, "UPDATE contact_messages SET is_read_by_admin = 1 WHERE is_read_by_admin = 0");
    header('Location: admin_messages.php');
    exit();
}

// Handle reply to message
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reply_message'])) {
    $message_id = (int)$_POST['message_id'];
    $admin_reply = mysqli_real_escape_string($conn, trim($_POST['admin_reply']));
    
    if (!empty($admin_reply)) {
        mysqli_query($conn, "UPDATE contact_messages SET 
                            admin_reply = '$admin_reply', 
                            status = 'replied', 
                            replied_at = NOW(),
                            replied_by = $admin_id,
                            replied_by_name = '$admin_name',
                            replied_by_email = '$admin_email',
                            is_read_by_user = 0,
                            user_seen_reply_at = NULL,
                            is_read_by_admin = 1
                            WHERE id = $message_id");
    }
    header("Location: admin_messages.php#msg-$message_id");
    exit();
}

// Handle delete message (OWNER ONLY)
if (isset($_GET['delete']) && $_SESSION['role'] == 'owner') {
    $message_id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM contact_messages WHERE id = $message_id");
    header('Location: admin_messages.php');
    exit();
}

// Get unread count for badge
$unread_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM contact_messages WHERE is_read_by_admin = 0"))['count'];

// Fetch all messages - admins see everything
$messages = mysqli_query($conn, "SELECT cm.*, u.username, u.role as user_role
                                 FROM contact_messages cm 
                                 LEFT JOIN users u ON cm.user_id = u.id 
                                 ORDER BY 
                                   CASE WHEN cm.is_read_by_admin = 0 THEN 0 ELSE 1 END,
                                   cm.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Messages - WallHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 100%);
            font-family: 'Poppins', sans-serif;
            padding-top: 80px;
        }
        .admin-container { max-width: 1100px; margin: 0 auto; padding: 20px; }
        h2 { color: #fff; margin-bottom: 24px; }
        h2 i { color: #e11d1d; margin-right: 10px; }
        .role-badge { background: #ffd700; color: #000; padding: 5px 15px; border-radius: 20px; font-size: 0.8rem; margin-left: 15px; }
        .card-msg { background: #181826; border: 1px solid #262638; border-radius: 14px; padding: 20px; margin-bottom: 20px; }
        .card-msg.unread-admin { border-left: 4px solid #e11d1d; background: #1f1722; }
        .meta { font-size: 0.8rem; color: #9a9ab0; margin-bottom: 8px; }
        .subject { font-weight: 600; color: #ffd166; font-size: 1.05rem; margin-bottom: 5px; }
        .body { background: #0c0c14; border-radius: 10px; padding: 15px; margin: 10px 0; white-space: pre-wrap; }
        .reply-box { background: rgba(40, 167, 69, 0.08); border-left: 3px solid #28a745; border-radius: 10px; padding: 15px; margin-top: 10px; }
        textarea { width: 100%; background: #0c0c14; color: #fff; border: 1px solid #2a2a3d; border-radius: 8px; padding: 10px; min-height: 90px; }
        .btn-send { background: #28a745; border: none; color: #fff; padding: 8px 20px; border-radius: 8px; margin-top: 8px; cursor: pointer; }
        .btn-send:hover { background: #1e7e34; }
        .badge { font-size: 0.7rem; padding: 4px 10px; border-radius: 20px; margin-left: 8px; display: inline-block; }
        .b-new { background: #e11d1d; color: #fff; }
        .b-replied { background: #28a745; color: #fff; }
        .b-seen { background: #2d6a4f; color: #fff; }
        .b-waiting { background: #b08900; color: #fff; }
        .toolbar { margin-bottom: 20px; }
        .toolbar a { color: #28a745; border: 1px solid #28a745; padding: 6px 14px; border-radius: 20px; text-decoration: none; font-size: 0.85rem; margin-right: 10px; }
        .toolbar a:hover { background: #28a745; color: #fff; }
        .btn-delete { background: #e11d1d; color: white; border: none; padding: 6px 14px; border-radius: 20px; font-size: 0.8rem; cursor: pointer; margin-left: 10px; }
        .btn-delete:hover { background: #b81818; }
        .empty-state { text-align: center; padding: 60px; color: #888; }
        .empty-state i { font-size: 4rem; margin-bottom: 20px; }
        .mark-read-link { font-size: 0.75rem; color: #6c5ce7; text-decoration: none; float: right; }
        .mark-read-link:hover { text-decoration: underline; }
        
        @media (max-width: 768px) {
            .admin-container { padding: 15px; }
            .subject { font-size: 1rem; }
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="admin-container">
        <h2>
            <i class="fas fa-inbox"></i> Contact Messages
            <span class="role-badge">
                <i class="fas <?php echo ($user_role == 'owner') ? 'fa-crown' : 'fa-shield-alt'; ?>"></i>
                <?php echo strtoupper($user_role); ?>
            </span>
            <?php if ($unread_count > 0): ?>
                <span class="badge b-new"><?php echo $unread_count; ?> new</span>
            <?php endif; ?>
        </h2>

        <div class="toolbar">
            <?php if ($unread_count > 0): ?>
                <a href="?mark_all_admin_read=1"><i class="fas fa-check-double"></i> Mark all as read</a>
            <?php endif; ?>
        </div>

        <?php if ($messages && mysqli_num_rows($messages) > 0): ?>
            <?php while ($m = mysqli_fetch_assoc($messages)):
                $adminUnread = (int)$m['is_read_by_admin'] === 0;
                $hasReply = !empty($m['admin_reply']);
                $userSawIt = $hasReply && !empty($m['user_seen_reply_at']);
                $awaitingUser = $hasReply && (int)$m['is_read_by_user'] === 0;
            ?>
            <div id="msg-<?php echo (int)$m['id']; ?>" class="card-msg <?php echo $adminUnread ? 'unread-admin' : ''; ?>">
                <div class="d-flex justify-content-between align-items-start">
                    <div style="flex:1;">
                        <div class="subject">
                            <?php echo htmlspecialchars($m['subject'] ?? '(no subject)'); ?>
                            <?php if ($adminUnread): ?>
                                <span class="badge b-new">NEW</span>
                            <?php endif; ?>
                            <?php if ($hasReply): ?>
                                <span class="badge b-replied"><i class="fas fa-reply"></i> Replied</span>
                            <?php endif; ?>
                            <?php if ($userSawIt): ?>
                                <span class="badge b-seen" title="<?php echo htmlspecialchars($m['user_seen_reply_at']); ?>">
                                    <i class="fas fa-eye"></i> Seen by user
                                </span>
                            <?php elseif ($awaitingUser): ?>
                                <span class="badge b-waiting">
                                    <i class="fas fa-clock"></i> Awaiting user
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="meta">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($m['name'] ?? 'Unknown'); ?>
                            &lt;<?php echo htmlspecialchars($m['email'] ?? ''); ?>&gt;
                            · <i class="far fa-clock"></i> <?php echo date('M d, Y h:i A', strtotime($m['created_at'])); ?>
                        </div>
                    </div>
                    <?php if ($adminUnread): ?>
                        <a class="mark-read-link" href="?open=<?php echo (int)$m['id']; ?>">
                            <i class="fas fa-check"></i> Mark read
                        </a>
                    <?php endif; ?>
                </div>

                <div class="body"><?php echo nl2br(htmlspecialchars($m['message'])); ?></div>

                <?php if ($hasReply): ?>
                    <div class="reply-box">
                        <div class="meta" style="color: #7ad88a;">
                            <i class="fas fa-reply"></i> Your reply
                            <?php if (!empty($m['replied_by_name'])): ?>
                                · <?php echo htmlspecialchars($m['replied_by_name']); ?>
                            <?php endif; ?>
                            <?php if (!empty($m['replied_at'])): ?>
                                · <?php echo date('M d, Y h:i A', strtotime($m['replied_at'])); ?>
                            <?php endif; ?>
                        </div>
                        <div><?php echo nl2br(htmlspecialchars($m['admin_reply'])); ?></div>
                    </div>
                <?php endif; ?>

                <form method="post" class="mt-3">
                    <input type="hidden" name="message_id" value="<?php echo (int)$m['id']; ?>">
                    <textarea name="admin_reply" placeholder="<?php echo $hasReply ? 'Update reply...' : 'Type a reply...'; ?>" rows="3"></textarea>
                    <button type="submit" name="reply_message" class="btn-send">
                        <i class="fas fa-paper-plane"></i> <?php echo $hasReply ? 'Update reply' : 'Send reply'; ?>
                    </button>
                    <?php if ($user_role == 'owner'): ?>
                        <button type="button" class="btn-delete" onclick="if(confirm('Delete this message?')) window.location.href='?delete=<?php echo $m['id']; ?>'">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    <?php endif; ?>
                </form>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>No messages</h3>
                <p>No contact messages yet.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include('footer.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
