# Member 4 · Complete the second team assignment

The response form sends update_assignment with assignment ID, status and notes for the open incident. ResponseController::record validates the status and passes the authenticated staff ID. ResponseModel::record updates the assignment, records the action and synchronizes the incident within the transaction. Point to the rule for the remaining assignments. Completing the first of two active teams leaves the incident responding. Completing both resolves it. Earlier actions remain available in history. [Form and result](../../../resources/views/admin/response/incident.php) · [Controller](../../../app/controllers/ResponseController.php) · [Model](../../../app/models/Response.php)

## Submit a team assignment status

[resources/views/admin/response/incident.php, lines 58–67](../../../resources/views/admin/response/incident.php#L58)

```php
                <input type="hidden" name="assignment_id" value="<?= (int)$assignment['id'] ?>" />
                <label>Status<select class="form-select" name="assignment_status">
                        <option value="assigned" <?= $selectedStatus === 'assigned' ? 'selected' : '' ?>>Assigned</option>
                        <option value="on_scene" <?= $selectedStatus === 'on_scene' ? 'selected' : '' ?>>On scene</option>
                        <option value="completed" <?= $selectedStatus === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= $selectedStatus === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select></label>
                <label class="d-block my-2">Action taken<textarea class="form-control" name="notes" maxlength="2000" required>
<?= e($notes) ?></textarea> </label>
                <button class="btn btn-primary" name="action" value="update_assignment">Save team and incident status</button>
```

## Validate the assignment action and attach the staff ID

[app/controllers/ResponseController.php, lines 102–114](../../../app/controllers/ResponseController.php#L102)

```php
        if ($notes === '' || strlen($notes) > 2000) {
            return ['Describe the action taken (maximum 2,000 bytes).'];
        }
        $teamId = $action === 'assign_team' ? $this->recordId(input('team_id')) : null;
        $assignmentId = $action === 'update_assignment' ? $this->recordId(input('assignment_id')) : null;
        $status = $action === 'assign_team' ? 'assigned' : input('assignment_status');
        if (!in_array($status, self::STATUSES, true)) {
            return ['Choose a valid assignment status.'];
        }
        try {
            $this->model->record($id, $teamId, $assignmentId, $status, $notes, (int) $_SESSION['ID']);
        } catch (DomainException $error) {
            return [$error->getMessage()];
```

## Synchronize the incident and preserve the action

[app/models/Response.php, lines 148–169](../../../app/models/Response.php#L148)

```php
                ) {
                    throw new DomainException('Reactivate the response team before assigning it again.');
                }
                $stmt = $this->pdo->prepare(
                    'UPDATE incident_assignments SET status=? WHERE id=? AND incident_id=?',
                );
                $stmt->execute([$status, $assignmentId, $incidentId]);
                $action =
                    $assignment['team_name'] . ': ' . $assignment['status'] . ' → ' . $status . '. ' . $notes;
            }
            // One update saves both the team state and the incident's overall state.
            $next = $this->assignmentStatus($incidentId);
            $stmt = $this->pdo->prepare('UPDATE incident_reports SET status=? WHERE id=?');
            $stmt->execute([$next, $incidentId]);
            $stmt = $this->pdo->prepare(
                'INSERT INTO response_updates (incident_id,updated_by,action_taken,from_status,to_status,assignment_id,assignment_status) VALUES (?,?,?,?,?,?,?)',
            );
            $stmt->execute([$incidentId, $staff, $action, $old, $next, $assignmentId, $status]);
            $this->pdo->commit();
        } catch (Throwable $error) {
            $this->pdo->rollBack();
            throw $error;
```

## Demonstrate it

Assign two teams. Complete one and then both. Read the overall incident status after each update.
