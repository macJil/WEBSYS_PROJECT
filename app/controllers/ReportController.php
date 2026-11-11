<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Report.php';
class ReportController extends Controller
{
    public const TYPES = ['flood'=>'Flooding','fire'=>'Fire','landslide'=>'Landslide','road_obstruction'=>'Road obstruction','other'=>'Other emergency'];
    public const STATUSES = ['pending','verified','responding','resolved','rejected'];
    private ReportModel $model;
    public function __construct(string $role) { requireRole($role); $this->model = new ReportModel(); }
    private function owner(): ?int { return $_SESSION['ROLE'] === 'admin' ? null : (int) $_SESSION['ID']; }
    public function id(): int
    {
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id || $id < 1) { http_response_code(400); exit('Invalid ID.'); } return $id;
    }
    public function find(int $id): array
    {
        $row = $this->model->find($id, $this->owner());
        if (!$row) { http_response_code(404); exit('Incident not found.'); } return $row;
    }
    public function filters(): array
    {
        $filters = [];
        foreach (['incident_type','status','date','location','search'] as $key) {
            $filters[$key] = is_string($_GET[$key] ?? null) ? trim($_GET[$key]) : '';
        }
        if (($filters['incident_type'] !== '' && !isset(self::TYPES[$filters['incident_type']])) ||
            ($filters['status'] !== '' && !in_array($filters['status'], self::STATUSES, true)) ||
            ($filters['date'] !== '' && !$this->validDate($filters['date']))) {
            http_response_code(400); exit('Invalid search filter.');
        }
        return $filters;
    }
    private function validDate(string $date): bool
    {
        $parsed = DateTime::createFromFormat('!Y-m-d', $date);
        return $parsed && $parsed->format('Y-m-d') === $date;
    }
    public function listing(array $filters): array { return $this->model->listing($filters, $this->owner()); }
    public function images(int $id): array { $this->find($id); return $this->model->images($id); }
    public function history(int $id): array { requireRole('admin'); return $this->model->history($id); }
    public function values(): array
    {
        $data = [];
        foreach (['incident_type','description','reporter_name','contact_phone','location_description','latitude','longitude','report_source'] as $key) { $data[$key] = input($key); }
        return $data;
    }
    private function storage(): string
    {
        global $settings;
        $path = $settings['incident_upload_dir'] ?? '';
        $root = realpath(__DIR__ . '/../..');
        if ($path === '' || $path[0] !== '/') { throw new RuntimeException('Configure an absolute private upload directory.'); }
        if (!is_dir($path) && !mkdir($path, 0700, true)) { throw new RuntimeException('Cannot create upload directory.'); }
        $real = realpath($path);
        if (!$real || $real === $root || str_starts_with($real, $root . DIRECTORY_SEPARATOR)) { throw new RuntimeException('Uploads must be outside the website directory.'); }
        $documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
        if ($documentRoot && ($real === $documentRoot || str_starts_with($real, $documentRoot . DIRECTORY_SEPARATOR))) { throw new RuntimeException('Uploads must be outside the web server document root.'); }
        return $real;
    }
    private function upload(): ?array
    {
        if (!isset($_FILES['image'])) { return null; }
        $file = $_FILES['image'];
        if (!is_array($file) || !isset($file['error']) || !is_int($file['error'])) { throw new InvalidArgumentException('Invalid image upload.'); }
        if ($file['error'] === UPLOAD_ERR_NO_FILE) { return null; }
        if ($file['error'] !== UPLOAD_ERR_OK) { throw new InvalidArgumentException('Image upload failed or exceeded the server limit. Choose the image again.'); }
        if (!is_string($file['tmp_name'] ?? null) || !is_uploaded_file($file['tmp_name'])) { throw new InvalidArgumentException('Invalid image upload.'); }
        $size = filesize($file['tmp_name']);
        if (!$size || $size > 5242880) { throw new InvalidArgumentException('Image must be between 1 byte and 5 MiB.'); }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $types = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
        $info = @getimagesize($file['tmp_name']);
        if (!isset($types[$mime]) || !$info || $info['mime'] !== $mime) { throw new InvalidArgumentException('Choose a valid JPEG, PNG, or WebP image.'); }
        $name = bin2hex(random_bytes(24)) . '.' . $types[$mime];
        $path = $this->storage() . '/' . $name;
        if (!move_uploaded_file($file['tmp_name'], $path)) { throw new RuntimeException('Could not store image.'); }
        chmod($path, 0600);
        return ['stored_filename'=>$name,'original_filename'=>substr(basename((string) $file['name']),0,255),'mime_type'=>$mime,'file_size'=>$size,'path'=>$path];
    }
    public function create(array $data): array
    {
        if (!isPost()) { return []; }
        if (input('action') !== 'create_incident') { return ['The form was incomplete or exceeded the server upload limit. Please try again.']; }
        $errors = [];
        foreach (['description'=>10000,'reporter_name'=>100,'contact_phone'=>20,'location_description'=>255] as $key=>$max) {
            if ($data[$key] === '' || strlen($data[$key]) > $max) { $errors[] = ucfirst(str_replace('_',' ', $key)) . " is required (maximum $max bytes)."; }
        }
        if (!isset(self::TYPES[$data['incident_type']])) { $errors[] = 'Choose an incident type.'; }
        if ($data['contact_phone'] !== '' && !preg_match('/^[0-9+() .-]{3,20}$/', $data['contact_phone'])) { $errors[] = 'Enter a valid contact number.'; }
        foreach (['latitude'=>90,'longitude'=>180] as $key=>$limit) {
            if ($data[$key] !== '' && (!is_numeric($data[$key]) || !is_finite((float)$data[$key]) || abs((float)$data[$key]) > $limit)) { $errors[] = 'Enter valid coordinates.'; }
        }
        if (($data['latitude'] === '') !== ($data['longitude'] === '')) { $errors[] = 'Provide both coordinates, or leave both blank.'; }
        $admin = $_SESSION['ROLE'] === 'admin';
        if ($admin && !in_array($data['report_source'], ['walk_in','phone','admin'], true)) { $errors[] = 'Choose how the report was received.'; }
        if ($errors) { return $errors; }
        $data['report_source'] = $admin ? $data['report_source'] : 'resident';
        foreach (['latitude','longitude'] as $key) { $data[$key] = $data[$key] === '' ? null : $data[$key]; }
        $data['reported_by'] = (int) $_SESSION['ID'];
        $image = null;
        try {
            $image = $this->upload(); $id = $this->model->create($data, $image);
        } catch (Throwable $error) {
            if ($image && is_file($image['path'])) { unlink($image['path']); }
            error_log((string)$error);
            return [$error instanceof InvalidArgumentException ? $error->getMessage() : 'Report could not be saved. Please try again; reselect your image.'];
        }
        $this->success('Incident submitted for staff review.', 'resources/views/incidents/view.php?id=' . $id);
        return [];
    }
    public function review(int $id): array
    {
        requireRole('admin');
        if (!isPost()) { return []; }
        if (input('action') !== 'review_incident' || !in_array(input('status'), self::STATUSES, true) || input('notes') === '' || strlen(input('notes')) > 255) { return ['Choose a status and enter review notes (maximum 255 bytes).']; }
        try { $this->model->review($id, input('status'), input('notes'), (int)$_SESSION['ID']); }
        catch (Throwable $error) { error_log((string)$error); return ['Review could not be saved. Please try again.']; }
        $this->success('Review saved.', 'resources/views/incidents/view.php?id=' . $id); return [];
    }
    public function serveImage(int $id): void
    {
        $image = $this->model->image($id, $this->owner());
        if (!$image || !preg_match('/^[a-f0-9]{48}\.(jpg|png|webp)$/', $image['stored_filename'])) { http_response_code(404); exit('Image not found.'); }
        $path = $this->storage() . '/' . $image['stored_filename'];
        if (!is_file($path)) { http_response_code(404); exit('Image not found.'); }
        header('Content-Type: ' . $image['mime_type']); header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store'); header('Content-Length: ' . filesize($path));
        readfile($path); exit;
    }
}
