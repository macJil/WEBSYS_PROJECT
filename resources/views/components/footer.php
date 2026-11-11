<?php require_once dirname(__DIR__, 3) . '/app/helpers.php'; ?>
    </main> 

    <footer>
        <p>&copy; <?= date('Y'); ?> Plain PHP Application</p>
    </footer>
    <script src="<?= htmlspecialchars(BASE_URL . '/resources/assets/js/bootstrap.bundle.min.js', ENT_QUOTES, 'UTF-8') ?>"></script>
</body>
</html>
