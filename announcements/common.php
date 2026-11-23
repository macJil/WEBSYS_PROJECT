<?php
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../config/db.php';

function announcementDbListing(array $filters, bool $resident): array
{
    $where = ['archived_at IS NULL'];
    $args = [];
    if ($resident) {
        $where[] = 'is_published=1';
    } elseif ($filters['status'] !== '') {
        $where[] = 'is_published=?';
        $args[] = $filters['status'] === 'published' ? 1 : 0;
    }
    if ($filters['q'] !== '') {
        $where[] = "title LIKE ? ESCAPE '!'";
        $args[] = '%' . strtr($filters['q'], ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
    }
    if ($filters['date'] !== '') {
        $where[] = 'DATE(created_at)=?';
        $args[] = $filters['date'];
    }
    $stmt = db()->prepare(
        'SELECT id,title,message,is_published,created_at FROM announcements WHERE ' .
            implode(' AND ', $where) .
            ' ORDER BY created_at DESC,id DESC',
    );
    $stmt->execute($args);
    return $stmt->fetchAll();
}

function announcementDbFind(int $id): array
{
    $stmt = db()->prepare('SELECT * FROM announcements WHERE id=?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: [];
}

function announcementDbSave(?int $id, string $title, string $message, int $published, int $staff): void
{
    if ($id === null) {
        $stmt = db()->prepare(
            'INSERT INTO announcements (title,message,is_published,created_by,first_published_at) VALUES (?,?,?,?,CASE WHEN ?=1 THEN CURRENT_TIMESTAMP ELSE NULL END)',
        );
        $stmt->execute([$title, $message, $published, $staff, $published]);
    } else {
        $stmt = db()->prepare(
            'UPDATE announcements SET title=?,message=?,is_published=?,first_published_at=CASE WHEN ?=1 THEN COALESCE(first_published_at,CURRENT_TIMESTAMP) ELSE first_published_at END WHERE id=? AND archived_at IS NULL',
        );
        $stmt->execute([$title, $message, $published, $published, $id]);
    }
}

function announcementQueryValue(string $key): string
{
    return is_string($_GET[$key] ?? null) ? trim($_GET[$key]) : '';
}

function announcementListAnnouncements(): array
{
    $resident = $_SESSION['ROLE'] === 'resident';
    $filters = [
        'q' => announcementQueryValue('q'),
        'date' => announcementQueryValue('date'),
        'status' => $resident ? '' : announcementQueryValue('status'),
    ];
    if (
        !in_array($filters['status'], ['', 'published', 'draft'], true) ||
        ($filters['date'] !== '' && !validDateInput($filters['date']))
    ) {
        http_response_code(400);
        exit('Invalid announcement filter.');
    }
    return announcementDbListing($filters, $resident);
}

function announcementSave(?int $id): array
{
    requireRole('admin');
    if (!isPost()) {
        return [];
    }
    if ($id !== null && !empty(announcementDbFind($id)['archived_at'])) {
        return ['Archived notices cannot be edited or republished.'];
    }
    $action = input('action');
    if (
        $id !== null &&
        $action === 'save_draft' &&
        announcementDbFind($id)['is_published'] &&
        input('confirm_withdraw') !== '1'
    ) {
        return ['Confirm withdrawal before saving this published notice as a draft.'];
    }
    $title = input('title');
    $message = input('message');
    if (!in_array($action, ['save_draft', 'publish'], true)) {
        return ['Choose Save draft / withdraw or Publish.'];
    }
    if ($title === '' || strlen($title) > 150 || $message === '' || strlen($message) > 10000) {
        return ['Enter a title (up to 150 bytes) and message (up to 10,000 bytes).'];
    }
    try {
        announcementDbSave($id, $title, $message, $action === 'publish' ? 1 : 0, (int) $_SESSION['ID']);
    } catch (PDOException $error) {
        return ['Announcement could not be saved. Please try again.'];
    }
    flash($action === 'publish'
            ? 'Announcement published to residents.'
            : 'Announcement saved privately as a draft.');
    redirect('announcements/index.php');
    return [];
}
