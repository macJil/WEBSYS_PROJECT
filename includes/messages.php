<?php if (isset($_SESSION['message'])): $message = $_SESSION['message'];
    unset($_SESSION['message']); ?>
<div class="alert alert-<?= e($message['type']) ?>" role="status"><?= e($message['text']) ?></div>
<?php endif; ?>
