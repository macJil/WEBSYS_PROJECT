<?php
require_once __DIR__ . '/../../../../app/helpers.php';
requireRole('admin');
if (!isset($announcement)) {
    http_response_code(404);
    exit('Open the announcement editor from the announcement list.');
}
?>
<form method="post">
    <label class="d-block mb-3">Title<input
            class="form-control"
            name="title"
            maxlength="150"
            value="<?= e(isPost() ? input('title') : $announcement['title']) ?>"
            required /></label>
    <label class="d-block mb-3">Notice text<textarea
            class="form-control"
            name="message"
            rows="9"
            maxlength="10000"
            required>
<?= e(isPost() ? input('message') : $announcement['message']) ?></textarea>
    </label>
    <p>Include the affected area, what residents should do, and the relevant date/time. Classroom
        example contact: “DEMO ONLY — Sample DRRM Desk, demo@example.invalid.”</p>
    <button class="btn btn-secondary" name="action" value="save_draft"><?= $announcement['is_published'] ? 'Save and withdraw' : 'Save draft' ?></button>
    <button class="btn btn-primary" name="action" value="publish"><?= $announcement['is_published'] ? 'Save and keep published' : 'Publish' ?></button>
</form>