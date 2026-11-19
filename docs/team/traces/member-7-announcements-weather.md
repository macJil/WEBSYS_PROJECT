# Member 7 · Publish an advisory

Show the staff editor’s title, message and publish action. AnnouncementController::save checks the staff role, valid action and text lengths. AnnouncementModel::save stores the publication state. The resident list calls the listing query in resident mode, which selects published, unarchived notices. Reopen the same notice on the resident page. Then show the separate weather component. Its JavaScript requests a fixed Atok forecast, displays a valid result or fallback text and leaves the PAGASA links accessible. [Editor](../../../resources/views/admin/announcement/editor.php) · [Controller](../../../app/controllers/AnnouncementController.php) · [Model](../../../app/models/Announcement.php) · [Resident list](../../../resources/views/user/announcement.php) · [Forecast script](../../../resources/assets/js/atok-weather.js)

## Choose draft, withdrawal or publication

[resources/views/admin/announcement/editor.php, lines 17–21](../../../resources/views/admin/announcement/editor.php#L17)

```php
        <label class="d-block mb-3">
            <input type="checkbox" name="confirm_withdraw" value="1" <?= input('confirm_withdraw') === '1' ? 'checked' : '' ?> /> I confirm that saving as a draft will withdraw this notice from residents. </label>
    <?php endif; ?>
    <button class="btn <?= $announcement['is_published'] ? 'btn-outline-danger' : 'btn-primary' ?>" name="action" value="save_draft"><?= $announcement['is_published']?'Save and withdraw':'Save draft' ?></button>
    <button class="btn btn-primary" name="action" value="publish"><?= $announcement['is_published']?'Save and keep published':'Publish' ?></button>
```

## Translate the form action into publication state

[app/controllers/AnnouncementController.php, lines 72–84](../../../app/controllers/AnnouncementController.php#L72)

```php
        if (!in_array($action, ['save_draft', 'publish'], true)) {
            return ['Choose Save draft / withdraw or Publish.'];
        }
        if ($title === '' || strlen($title) > 150 || $message === '' || strlen($message) > 10000) {
            return ['Enter a title (up to 150 bytes) and message (up to 10,000 bytes).'];
        }
        try {
            $this->model->save($id, $title, $message, $action === 'publish' ? 1 : 0, (int) $_SESSION['ID']);
        } catch (PDOException $error) {
            return ['Announcement could not be saved. Please try again.'];
        }
        $this->success(
            $action === 'publish'
```

## Restrict the resident list to published notices

[app/models/Announcement.php, lines 11–21](../../../app/models/Announcement.php#L11)

```php
    public function listing(array $filters, bool $resident): array
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
```

## Read the forecast or display its unavailable state

[resources/assets/js/atok-weather.js, lines 41–56](../../../resources/assets/js/atok-weather.js#L41)

```javascript
        });
        if (!response.ok) throw new Error();
        const data = await response.json();
        const entry = { daily: data.daily, fetched: Date.now() };
        if (!display(entry)) throw new Error();
        try {
            localStorage.setItem(key, JSON.stringify(entry));
        } catch {
            // An uncached forecast is still displayed.
        }
    } catch {
        panel.textContent = "Forecast unavailable. Check the PAGASA links below for advisories.";
    } finally {
        clearTimeout(timer);
    }
})();
```

## Demonstrate it

Publish a fictional notice and open it as a resident. Point to the weather panel’s provider and explain what appears during an outage.
