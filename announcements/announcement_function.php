<?php
require_once __DIR__ . '/../includes/session.php';

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
