<?php
require_once __DIR__ . '/../../config/db.php';
class ReliefModel
{
    private PDO $pdo;
    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? db();
    }
    private function rows(string $sql, array $args = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($args);
        return $stmt->fetchAll();
    }
    private function term(string $q): string
    {
        return '%' . strtr($q, ['!' => '!!','%' => '!%','_' => '!_']) . '%';
    }
    public function items(string $q = ''): array
    {
        return $this->rows("SELECT * FROM relief_items WHERE archived_at IS NULL AND (item_name LIKE ? ESCAPE '!' OR unit LIKE ? ESCAPE '!') ORDER BY item_name,unit", [$this->term($q),$this->term($q)]);
    }
    public function item(int $id): array
    {
        return $this->rows('SELECT * FROM relief_items WHERE id=?', [$id])[0] ?? [];
    }
    public function saveItem(?int $id, string $name, string $unit): void
    {
        $this->pdo->beginTransaction();
        try {
            if ($id) {
                $old = $this->rows('SELECT * FROM relief_items WHERE id=? FOR UPDATE', [$id])[0] ?? null;
                if (!$old || $old['archived_at'] !== null) {
                    throw new DomainException('Item not found.');
                }
                if ($old['unit'] !== $unit && $this->rows('SELECT id FROM relief_stock_movements WHERE item_id=? LIMIT 1', [$id])) {
                    throw new DomainException('An item with stock history must keep its unit. Create a separate item for a different unit.');
                }
                $stmt = $this->pdo->prepare('UPDATE relief_items SET item_name=?,unit=? WHERE id=?');
                $stmt->execute([$name,$unit,$id]);
            } else {
                $stmt = $this->pdo->prepare('INSERT INTO relief_items (item_name,unit) VALUES (?,?)');
                $stmt->execute([$name,$unit]);
            }
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    private function movement(int $item, string $type, int $quantity, ?int $distribution, int $staff, string $notes): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO relief_stock_movements (item_id,movement_type,quantity,distribution_id,recorded_by,notes) VALUES (?,?,?,?,?,?)');
        $stmt->execute([$item,$type,$quantity,$distribution,$staff,$notes]);
    }
    public function stock(int $id, string $type, int $quantity, int $staff, string $notes): void
    {
        if (!in_array($type, ['stock_in','stock_out'], true) || $quantity < 1 || $quantity > 1000000000) {
            throw new DomainException('Choose a valid movement and positive whole quantity.');
        }
        $this->pdo->beginTransaction();
        try {
            $item = $this->rows('SELECT * FROM relief_items WHERE id=? FOR UPDATE', [$id])[0] ?? null;
            if (!$item || $item['archived_at'] !== null) {
                throw new DomainException('Item not found.');
            }
            $next = (int)$item['quantity_on_hand'] + ($type === 'stock_in'?$quantity:-$quantity);
            if ($next < 0) {
                throw new DomainException('Stock-out exceeds available stock.');
            }
            if ($next > 4294967295) {
                throw new DomainException('Resulting stock exceeds the supported quantity.');
            }
            $stmt = $this->pdo->prepare('UPDATE relief_items SET quantity_on_hand=? WHERE id=?');
            $stmt->execute([$next,$id]);
            $this->movement($id, $type, $quantity, null, $staff, $notes);
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    public function movements(string $q, string $type, string $date): array
    {
        $sql = "SELECT m.*,i.item_name,i.unit,u.full_name FROM relief_stock_movements m JOIN relief_items i ON i.id=m.item_id JOIN users u ON u.id=m.recorded_by WHERE (i.item_name LIKE ? ESCAPE '!' OR m.notes LIKE ? ESCAPE '!' OR u.full_name LIKE ? ESCAPE '!')";
        $args = array_fill(0, 3, $this->term($q));
        if ($type !== '') {
            $sql .= ' AND m.movement_type=?';
            $args[] = $type;
        }
        if ($date !== '') {
            $sql .= ' AND DATE(m.created_at)=?';
            $args[] = $date;
        }
        return $this->rows($sql . ' ORDER BY m.id DESC', $args);
    }
    public function households(): array
    {
        return $this->rows('SELECT id,household_code,representative_name FROM households WHERE archived_at IS NULL ORDER BY household_code');
    }
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
    public function distributions(string $q): array
    {
        return $this->rows("SELECT d.*,h.household_code,u.full_name FROM relief_distributions d JOIN households h ON h.id=d.household_id JOIN users u ON u.id=d.distributed_by WHERE h.household_code LIKE ? ESCAPE '!' OR d.received_by LIKE ? ESCAPE '!' ORDER BY d.id DESC", [$this->term($q),$this->term($q)]);
    }
    public function distribution(int $id): array
    {
        return $this->rows('SELECT d.*,h.household_code,u.full_name FROM relief_distributions d JOIN households h ON h.id=d.household_id JOIN users u ON u.id=d.distributed_by WHERE d.id=?', [$id])[0] ?? [];
    }
    public function distributedGoods(int $id): array
    {
        return $this->rows('SELECT d.quantity,i.item_name,i.unit FROM relief_distribution_items d JOIN relief_items i ON i.id=d.item_id WHERE d.distribution_id=? ORDER BY d.id', [$id]);
    }
}
