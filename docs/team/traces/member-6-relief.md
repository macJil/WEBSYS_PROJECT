# Member 6 · Record a multi-item distribution

The form submits household_id, received_by and quantities keyed by item ID. ReliefController::distribute validates the selections and quantities. ReliefModel::distribute checks available stock and writes the distribution, item rows, deductions and movement history in one transaction. Staff can read the saved lines on the detail page. Follow one item ID from its input to its outgoing quantity. A shortage in the second item must leave the first balance intact. [Form and detail](../../../resources/views/admin/relief/distributions.php) · [Controller](../../../app/controllers/ReliefController.php) · [Model](../../../app/models/Relief.php)

## Quantities are keyed by relief item ID

[resources/views/admin/relief/distributions.php, lines 92–105](../../../resources/views/admin/relief/distributions.php#L92)

```php
                                aria-label="<?= e('Quantity of ' . $item['item_name']) ?>"
                                type="number"
                                min="0"
                                max="1000000000"
                                name="goods[<?= (int)$item['id'] ?>]"
                                value="<?= e(is_scalar($value)?$value:'') ?>"
                            />
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <button class="btn btn-primary" name="action" value="distribute">Save distribution and deduct stock</button>
```

## Pass the receiver, household and validated items

[app/controllers/ReliefController.php, lines 168–183](../../../app/controllers/ReliefController.php#L168)

```php
                return [
                    'Goods quantities must be positive whole numbers (up to 1 billion), or blank/zero to omit.',
                ];
            }
            $goods[$id] = $quantity;
        }
        if (!$goods) {
            return ['Select at least one good by entering its quantity.'];
        }
        $household = $this->recordId(input('household_id'));
        try {
            $id = $this->model->distribute($household, $receiver, $notes, $goods, (int) $_SESSION['ID']);
        } catch (Throwable $e) {
            return $this->failure($e);
        }
        $this->success(
```

## Record distribution details and stock movements together

[app/models/Relief.php, lines 163–189](../../../app/models/Relief.php#L163)

```php
                    throw new DomainException(
                        'Insufficient stock for ' . $item['item_name'] . '. No goods were distributed.',
                    );
                }
            }
            $stmt = $this->pdo->prepare(
                'INSERT INTO relief_distributions (household_id,received_by,distributed_by,notes) VALUES (?,?,?,?)',
            );
            $stmt->execute([$household, $receiver, $staff, $notes]);
            $distribution = (int) $this->pdo->lastInsertId();
            foreach ($goods as $id => $quantity) {
                $stmt = $this->pdo->prepare(
                    'UPDATE relief_items SET quantity_on_hand=quantity_on_hand-? WHERE id=?',
                );
                $stmt->execute([$quantity, $id]);
                $stmt = $this->pdo->prepare(
                    'INSERT INTO relief_distribution_items (distribution_id,item_id,quantity) VALUES (?,?,?)',
                );
                $stmt->execute([$distribution, $id, $quantity]);
                $this->movement($id, 'distribution', $quantity, $distribution, $staff, $notes);
            }
            $this->pdo->commit();
            return $distribution;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
```

## Demonstrate it

With five units each of two goods, request two and six. Correct the second amount to two. Show the resulting balances of three.
