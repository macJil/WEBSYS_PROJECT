<?php if (($_SESSION['ROLE'] ?? '') === 'admin'): ?>
    <form method="post" action="<?= e(url('records/history.php')) ?>" class="mt-2"
        onsubmit="return confirm('Hide this history entry? Stored records and totals will be preserved.');">
        <input type="hidden" name="history_type" value="<?= e($historyType) ?>" />
        <input type="hidden" name="history_id" value="<?= (int) $historyId ?>" />
        <button class="btn btn-outline-danger btn-sm" type="submit">Hide history entry</button>
    </form>
<?php endif; ?>
