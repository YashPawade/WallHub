<?php
// includes/permissions.php - User permission system

function hasPermission($required_role) {
    if (!isset($_SESSION['user_id'])) {
        return $required_role === 'guest';
    }
    
    $role = $_SESSION['role'] ?? 'member';
    
    $role_hierarchy = [
        'guest' => 0,
        'member' => 1,
        'premium' => 2,
        'admin' => 3,
        'owner' => 4
    ];
    
    $user_level = $role_hierarchy[$role] ?? 0;
    $required_level = $role_hierarchy[$required_role] ?? 0;
    
    return $user_level >= $required_level;
}

function isOwner() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'owner';
}

function isAdmin() {
    return isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'owner');
}

function isPremium() {
    return isset($_SESSION['role']) && ($_SESSION['role'] === 'premium' || $_SESSION['role'] === 'admin' || $_SESSION['role'] === 'owner');
}

function canDeleteUser($target_user_id, $target_role) {
    if (isOwner()) {
        return $target_user_id != $_SESSION['user_id'];
    }
    
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        return in_array($target_role, ['member', 'premium']);
    }
    
    return false;
}

function canChangeRole($target_role) {
    if (isOwner()) {
        return true;
    }
    
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        return in_array($target_role, ['member', 'premium']);
    }
    
    return false;
}
?>