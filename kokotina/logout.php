<?php
/**
 * Logout stránka
 */

require_once 'inc/config.php';

// Zrušenie session
$_SESSION['user_id'] = null;
$_SESSION['user_email'] = null;
$_SESSION['user_name'] = null;

setFlashMessage('Ste odhlásený/á', 'info');
header('Location: ' . SITE_URL . '/index.php');
exit();
?>
