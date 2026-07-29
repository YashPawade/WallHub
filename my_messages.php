<?php
// my_messages.php - User's inbox
session_start();
include('includes/db.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$username = $_SESSION['username'] ?? '';

// Get user's email
$user_email = '';
if ($stmt = mysqli_prepare($conn, "SELECT email FROM users WHERE id = ? LIMIT 1")) {
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($res)) {
        $user_email = $row['email'] ?? '';
    }
    mysqli_stmt_close($stmt);
}

// Mark single message as read
if (isset($_GET['mark_read'])) {
    $msg_id = (int) $_GET['mark_read'];
    $sql = "UPDATE contact_messages
            SET is_read_by_user = 1,
                user_seen_reply_at = COALESCE(user_seen_reply_at, NOW())
            WHERE id = ? AND (user_id = ? OR email = ?)";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, 'iis', $msg_id, $user_id, $user_email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header('Location: my_messages.php');
    exit();
}

// Mark all replied messages as read
if (isset($_GET['mark_all_read'])) {
    $sql = "UPDATE contact_messages
            SET is_read_by_user = 1,
                user_seen_reply_at = COALESCE(user_seen_reply_at, NOW())
            WHERE (user_id = ? OR email = ?)
              AND status = 'replied'
              AND admin_reply IS NOT NULL AND admin_reply <> ''";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, 'is', $user_id, $user_email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header('Location: my_messages.php');
    exit();
}

// All messages for this user
$messages = null;
if ($stmt = mysqli_prepare($conn, "SELECT * FROM contact_messages WHERE user_id = ? OR email = ? ORDER BY created_at DESC")) {
    mysqli_stmt_bind_param($stmt, 'is', $user_id, $user_email);
    mysqli_stmt_execute($stmt);
    $messages = mysqli_stmt_get_result($stmt);
}

// Unread reply count
$unread_count = 0;
if ($stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS c FROM contact_messages
     WHERE (user_id = ? OR email = ?)
       AND status = 'replied'
       AND admin_reply IS NOT NULL AND admin_reply <> ''
       AND is_read_by_user = 0")) {
    mysqli_stmt_bind_param($stmt, 'is', $user_id, $user_email);
    mysqli_stmt_execute($stmt);
    $r = mysqli_stmt_get_result($stmt);
    $unread_count = (int) (mysqli_fetch_assoc($r)['c'] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Messages - WallHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 100%); font-family: 'Poppins', sans-serif; padding-top: 80px; color: #ddd; }
        .container { max-width: 900px; margin: 0 auto; padding: 20px; }
        .page-title { color: #fff; margin-bottom: 30px; }
        .page-title i { color: #e11d1d; margin-right: 10px; }
        .message-card { background: rgba(20, 20, 30, 0.95); border-radius: 15px; padding: 20px; margin-bottom: 20px; border: 1px solid rgba(225, 29, 29, 0.2); transition: transform 0.3s; }
        .message-card:hover { transform: translateY(-3px); border-color: rgba(225, 29, 29, 0.5); }
        .message-card.unread { border-left: 4px solid #e11d1d; background: rgba(225, 29, 29, 0.1); }
        .message-subject { font-size: 1.2rem; font-weight: bold; color: #ffd700; margin-bottom: 10px; }
        .message-date { color: #888; font-size: 0.8rem; margin-bottom: 10px; }
        .message-content { color: #ddd; line-height: 1.6; margin-bottom: 15px; padding: 15px; background: rgba(0, 0, 0, 0.3); border-radius: 10px; }
        .admin-reply { background: rgba(40, 167, 69, 0.15); border-left: 4px solid #28a745; padding: 15px; border-radius: 10px; margin-top: 15px; }
        .admin-reply-label { color: #28a745; font-weight: bold; margin-bottom: 10px; }
        .badge-status { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.7rem; margin-left: 10px; }
        .badge-replied { background: #28a745; color: #fff; }
        .badge-read { background: #2d6a4f; color: #fff; }
        .badge-unread { background: #e11d1d; color: #fff; }
        .empty-state { text-align: center; padding: 60px; color: #888; }
        .empty-state i { font-size: 4rem; margin-bottom: 20px; }
        .btn-back { background: #6c5ce7; color: #fff; padding: 10px 20px; border-radius: 25px; text-decoration: none; display: inline-block; margin-bottom: 20px; }
        .btn-back:hover { background: #5b4bc4; color: #fff; }
        .mark-all-btn { background: rgba(40, 167, 69, 0.2); border: 1px solid #28a745; color: #28a745; padding: 8px 18px; border-radius: 25px; text-decoration: none; font-size: 0.85rem; margin-left: 15px; }
        .mark-all-btn:hover { background: #28a745; color: #fff; }
        .mark-read-link { float: right; font-size: 0.75rem; color: #6c5ce7; text-decoration: none; }
        .mark-read-link:hover { text-decoration: underline; }
        @media (max-width: 768px) {
            .container { padding: 15px; }
            .message-subject { font-size: 1rem; }
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
            <a href="index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Home</a>
            <?php if ($unread_count > 0): ?>
                <a href="?mark_all_read=1" class="mark-all-btn"><i class="fas fa-check-double"></i> Mark all as read</a>
            <?php endif; ?>
        </div>

        <h2 class="page-title">
            <i class="fas fa-inbox"></i> My Messages
            <?php if ($unread_count > 0): ?>
                <span class="badge-status badge-unread"><?php echo $unread_count; ?> New</span>
            <?php endif; ?>
        </h2>

        <?php if ($messages && mysqli_num_rows($messages) > 0): ?>
            <?php while ($m = mysqli_fetch_assoc($messages)):
                $hasReply = !empty($m['admin_reply']);
                $isUnread = $hasReply && (int)$m['is_read_by_user'] === 0;
            ?>
                <div class="message-card <?php echo $isUnread ? 'unread' : ''; ?>">
                    <div class="message-subject">
                        <?php echo htmlspecialchars($m['subject'] ?? '(no subject)'); ?>
                        <?php if ($isUnread): ?>
                            <span class="badge-status badge-unread"><i class="fas fa-bell"></i> New Reply</span>
                        <?php elseif ($hasReply): ?>
                            <span class="badge-status badge-replied">Replied</span>
                        <?php endif; ?>
                        <?php if ($isUnread): ?>
                            <a class="mark-read-link" href="?mark_read=<?php echo (int)$m['id']; ?>">
                                <i class="fas fa-check"></i> Mark as read
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="message-date">
                        <i class="far fa-clock"></i> <?php echo date('F d, Y h:i A', strtotime($m['created_at'])); ?>
                    </div>
                    <div class="message-content"><?php echo nl2br(htmlspecialchars($m['message'])); ?></div>

                    <?php if ($hasReply): ?>
                        <div class="admin-reply">
                            <div class="admin-reply-label"><i class="fas fa-reply"></i> Admin Response:</div>
                            <div><?php echo nl2br(htmlspecialchars($m['admin_reply'])); ?></div>
                            <?php if (!empty($m['replied_at'])): ?>
                                <div class="message-date mt-2">Replied on: <?php echo date('F d, Y h:i A', strtotime($m['replied_at'])); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($m['replied_by_name'])): ?>
                                <div class="message-date">Replied by: <?php echo htmlspecialchars($m['replied_by_name']); ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="far fa-envelope-open"></i>
                <h3>No messages yet</h3>
                <p>When you send messages through the contact form, they will appear here.</p>
                <a href="contact.php" class="btn-back"><i class="fas fa-paper-plane"></i> Send a Message</a>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
