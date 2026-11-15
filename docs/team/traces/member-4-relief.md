# Member 4 — Multi-good relief distribution and protected removal

These are study notes for the current source, not application changes or a claim that a live demonstration has passed. Use fictional examples. The complete primary request is traced below, with exact source excerpts and explanations. Source links are relative to the repository and include line anchors. The main presentation should show only the marked 60–90 second segment; use the rest to prepare for questions.

All PHP save paths run on the server through MAMP. The browser sends form values, not PHP objects. A successful POST redirects to a new GET; validation errors remain on the form. `[]` means an empty array, `$this` means the current object, `->` accesses an object, `?int` accepts integer or null, and `: array` declares a returned array. `prepare()` creates a SQL statement and `execute()` supplies its values.
## Focus and example

Own stock/catalog, household distributions, relief locations and Manage Records. Present the distribution code on slide 19. Example: active household 8, receiving representative `Demo Receiver`, goods `[3 => 3, 9 => 4]`. Item 3 has ten packs and item 9 has twenty bottles. IDs are illustrative. A successful save leaves seven packs and sixteen bottles.

### Complete execution order

GET list/form; admin guard; load active goods/households. POST quantities keyed by item ID; controller omits blank/zero entries and validates positive quantities. Model locks household and goods, verifies every stock balance, creates distribution, deducts quantities, inserts details and movements, commits, then redirects to read-only distribution detail.

## 1. Choose form mode or historical detail

Source: [resources/views/admin/relief/distributions.php, lines 1–17](../../../resources/views/admin/relief/distributions.php#L1). Exact excerpt, not pseudocode.

```php
<?php
require_once __DIR__ . '/../../../../app/controllers/ReliefController.php';
$controller = new ReliefController();
$id = isset($_GET['id'])?$controller->recordId($_GET['id']):null;
$distribution = $id?$controller->distribution($id):null;
if ($id && isPost()) {
    http_response_code(405);
    exit('Distribution history cannot be edited.');
}
$errors = $id?[]:$controller->distribute();
$items = $id?$controller->distributedGoods($id):$controller->items();
$households = $id?[]:$controller->households();
$q = $controller->queryValue('q');
$history = $id?[]:$controller->distributions($q);
$pageTitle = 'Relief distributions';
require __DIR__ . '/../../components/header.php';
require __DIR__ . '/../../components/errors.php';
```

A missing ID means list/create mode. An ID means historical detail. POST to a detail URL returns 405 because history is not edited through that page. `$errors` is the controller result; `$items` contains either selectable goods or already distributed goods depending on mode. Successful processing occurs before the header is rendered.

## 2. Validate the posted good-to-quantity map

Source: [app/controllers/ReliefController.php, lines 124–158](../../../app/controllers/ReliefController.php#L124). Exact excerpt, not pseudocode.

```php
    public function distribute(): array
    {
        if (!isPost()) {
            return [];
        }
        $receiver = input('received_by');
        $notes = input('notes');
        $raw = $_POST['goods'] ?? null;
        if (input('action') !== 'distribute' || $receiver === '' || strlen($receiver) > 100 || strlen($notes) > 255 || !is_array($raw)) {
            return ['Enter receiving representative (up to 100 bytes), notes (up to 255 bytes), and goods.'];
        }
        $goods = [];
        foreach ($raw as $key => $value) {
            if ($value === '' || $value === '0') {
                continue;
            }
            $id = $this->recordId($key);
            $quantity = $this->quantity($value);
            if ($quantity === false) {
                return ['Goods quantities must be positive whole numbers (up to 1 billion), or blank/zero to omit.'];
            }
            $goods[$id] = $quantity;
        }
        if (!$goods) {
            return ['Select at least one good by entering its quantity.'];
        }
        $household = $this->recordId(input('household_id'));
        try {
            $id = $this->model->distribute($household, $receiver, $notes, $goods, (int)$_SESSION['ID']);
        } catch (Throwable $e) {
            return $this->failure($e);
        }
        $this->success('Distribution recorded and stock updated.', 'resources/views/admin/relief/distributions.php?id=' . $id);
        return [];
    }
```

The form's `goods[itemId]` names become an associative array. `$raw` is untrusted input; `$goods` is the validated map. Blank or literal zero values are omitted. Each included ID must be positive and each quantity a supported positive integer. At least one good, a representative and an active household selection are required. The current staff ID comes from the session. Errors return before redirecting; success opens the new distribution ID.

## 3. Positive whole-number quantity rule

Source: [app/controllers/ReliefController.php, lines 79–82](../../../app/controllers/ReliefController.php#L79). Exact excerpt, not pseudocode.

```php
    private function quantity($value)
    {
        return is_scalar($value)?filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1,'max_range' => 1000000000]]):false;
    }
```

Scalar input avoids arrays masquerading as a number. The integer filter accepts quantities from one to one billion. The controller converts accepted quantities to integers before passing them to the model. Fractions, negative values and malformed quantities fail.

## 4. Save every related change in one transaction

Source: [app/models/Relief.php, lines 107–149](../../../app/models/Relief.php#L107). Exact excerpt, not pseudocode.

```php
    public function distribute(int $household, string $receiver, string $notes, array $goods, int $staff): int
    {
        if (!$goods) {
            throw new DomainException('Choose at least one good.');
        }
        foreach ($goods as $id => $quantity) {
            if (!is_int($id) || $id < 1 || !is_int($quantity) || $quantity < 1 || $quantity > 1000000000) {
                throw new DomainException('Every good must have a valid ID and positive whole quantity.');
            }
        }
        $this->pdo->beginTransaction();
        try {
            if (!$this->rows('SELECT id FROM households WHERE id=? AND archived_at IS NULL FOR UPDATE', [$household])) {
                throw new DomainException('Select a registered household.');
            }
            // Lock in the same item order for every distribution to reduce deadlocks.
            ksort($goods, SORT_NUMERIC);
            foreach ($goods as $id => $quantity) {
                $item = $this->rows('SELECT * FROM relief_items WHERE id=? FOR UPDATE', [$id])[0] ?? null;
                if (!$item || $item['archived_at'] !== null) {
                    throw new DomainException('One selected item no longer exists.');
                }
                if ($quantity > (int)$item['quantity_on_hand']) {
                    throw new DomainException('Insufficient stock for ' . $item['item_name'] . '. No goods were distributed.');
                }
            }
            $stmt = $this->pdo->prepare('INSERT INTO relief_distributions (household_id,received_by,distributed_by,notes) VALUES (?,?,?,?)');
            $stmt->execute([$household,$receiver,$staff,$notes]);
            $distribution = (int)$this->pdo->lastInsertId();
            foreach ($goods as $id => $quantity) {
                $stmt = $this->pdo->prepare('UPDATE relief_items SET quantity_on_hand=quantity_on_hand-? WHERE id=?');
                $stmt->execute([$quantity,$id]);
                $stmt = $this->pdo->prepare('INSERT INTO relief_distribution_items (distribution_id,item_id,quantity) VALUES (?,?,?)');
                $stmt->execute([$distribution,$id,$quantity]);
                $this->movement($id, 'distribution', $quantity, $distribution, $staff, $notes);
            }
            $this->pdo->commit();
            return $distribution;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
```

Read the method as two phases:

**Validate under locks:** confirm the map is nonempty and has integer IDs/quantities, begin a transaction, lock the active household, then sort the item IDs. Each good is locked in this consistent order, checked for existence/non-archived state, and compared with its requested quantity. These checks finish before any stock deduction.

**Write together:** insert the distribution header, get its new ID, then for each good deduct quantity, insert the distribution detail and insert a movement. Commit returns the distribution ID. An exception rolls back the header, details, movements and balances together.

In the example, goods 3 and 9 each produce one detail and one movement. There is one header identifying household and receiver. Asking for eleven packs fails before the ten-pack balance is reduced, even if the water quantity was valid. `ksort()` reduces inconsistent lock-order problems; it does not mean all database failures are impossible.

## 5. Record the stock movement

Source: [app/models/Relief.php, lines 52–56](../../../app/models/Relief.php#L52). Exact excerpt, not pseudocode.

```php
    private function movement(int $item, string $type, int $quantity, ?int $distribution, int $staff, string $notes): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO relief_stock_movements (item_id,movement_type,quantity,distribution_id,recorded_by,notes) VALUES (?,?,?,?,?,?)');
        $stmt->execute([$item,$type,$quantity,$distribution,$staff,$notes]);
    }
```

This helper inserts the item, movement type, quantity, distribution link, staff and notes. The caller's transaction covers it; the helper does not start a separate transaction. Distribution movements link to the distribution ID. Standalone stock-in/out operations use null for that link.

## 6. Reload distribution metadata

Source: [app/models/Relief.php, lines 154–157](../../../app/models/Relief.php#L154). Exact excerpt, not pseudocode.

```php
    public function distribution(int $id): array
    {
        return $this->rows('SELECT d.*,h.household_code,u.full_name FROM relief_distributions d JOIN households h ON h.id=d.household_id JOIN users u ON u.id=d.distributed_by WHERE d.id=?', [$id])[0] ?? [];
    }
```

The detail query joins the household and staff names for display. It does not recalculate or re-execute the distribution. Returning to the page is a read, so refreshing after redirect does not itself deduct stock again.

## 7. Reload each distributed good

Source: [app/models/Relief.php, lines 158–161](../../../app/models/Relief.php#L158). Exact excerpt, not pseudocode.

```php
    public function distributedGoods(int $id): array
    {
        return $this->rows('SELECT d.quantity,i.item_name,i.unit FROM relief_distribution_items d JOIN relief_items i ON i.id=d.item_id WHERE d.distribution_id=? ORDER BY d.id', [$id]);
    }
```

The stored detail quantity is displayed with its good name and unit. Historical joins still work when linked catalog rows are retained internally rather than physically deleted. Separate units must not be added into one meaningful stock quantity.
## Stock and removal traces

- Standalone stock: inventory/movement form calls `ReliefController::stock()`, which checks action, type, quantity and reason. `ReliefModel::stock()` locks the item, calculates the next balance, rejects negative/overflow quantities, updates the item and adds a movement in one transaction.
- Item editing: `saveItem()` allows a new catalog item but protects a used item's unit so previous quantities keep their meaning. Relief locations store a physical location/type, not an independent stock ledger.
- Removal: `admin/records.php` loads the filtered page, the controller's `delete()` checks selection against displayed rows, then `RecordActionsModel::deleteSelected()` checks locked records and applies the allowed removal modes in one transaction. `removalMode()` can permanently delete unused records, hide linked records while preserving history, or return a blocking guide.
- A zero-stock good with movements is retained internally and removed from active lists. Remaining stock blocks removal. An occupied center or active stay needs the operational step described by its rule. The JavaScript confirmation does not replace these server checks.

## Code speech (about 75 seconds)

“The quantities arrive as a map keyed by each good's ID. The controller skips blank or zero entries and accepts positive whole quantities. The model then starts one transaction. It locks the registered household and selected goods, and checks every requested quantity before deducting anything. Next it creates one distribution record, one detail for each good and a stock movement for each deduction. Commit saves all of those changes together. If any step throws an error, rollback restores the previous state. The returned distribution ID takes us to a read-only detail page, so viewing that page does not distribute the goods again.”
