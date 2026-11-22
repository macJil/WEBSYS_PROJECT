# Member 8 · Reconcile a dashboard card and print a summary

The staff dashboard sends its selected barangay by GET. DashboardController::dashboardData validates the selection and filters the center and stay rows. DashboardModel::stays selects people whose checkout time is still empty. The active evacuee card and its linked list should agree after the checkout in Member 5’s demonstration. For printing, the summary form sends the dates and barangay to DisasterSummaryController. DisasterSummaryModel::summarize applies period limits to incidents and distributions; current affected households and occupancy remain current snapshots. Show the coverage label and generation time in print preview. [Dashboard form](../../../resources/views/admin/admin.index.php) · [Controller](../../../app/controllers/DashboardController.php) · [Model](../../../app/models/Dashboard.php) · [Summary form](../../../resources/views/admin/summaries/disaster.php) · [Summary controller](../../../app/controllers/DisasterSummaryController.php) · [Summary model](../../../app/models/DisasterSummary.php)

## Submit the barangay selection

[resources/views/admin/admin.index.php, lines 14–23](../../../resources/views/admin/admin.index.php#L14)

```php
<form method="get" class="mb-3">
    <?php
    $barangayPrompt = 'All barangays';
    $selectedBarangay = $overview['barangay'];
    require __DIR__ . '/../components/barangay.php';
    ?>
    <button class="btn btn-primary">Apply filter</button>
</form>
<?php
require __DIR__ . "/../components/dashboard-cards.php";
```

## Scope incidents and staff figures

[app/controllers/DashboardController.php, lines 20–35](../../../app/controllers/DashboardController.php#L20)

```php
        }
        $data = [
            'barangay' => $barangay,
            'incidents' => $this->model->incidents($admin ? null : (int) $_SESSION['ID'], $barangay),
            'centers' => $this->model->centers(),
        ];
        if ($admin) {
            $data['centers'] = filterBarangay($data['centers']);
            $data['affected'] = filterBarangay($this->model->affected());
            $data['stays'] = filterBarangay($this->model->stays());
            $data['inventory'] = $this->model->inventoryItemCount();
        } else {
            $data['notices'] = $this->model->notices();
        }
        return $data;
    }
```

## Count stays that have no checkout time

[app/models/Dashboard.php, lines 59–64](../../../app/models/Dashboard.php#L59)

```php
    public function stays(): array
    {
        return $this->rows(
            'SELECT c.barangay,e.id,e.center_id,c.name AS center_name,COALESCE(m.full_name,e.walkin_name) AS person_name,e.checked_in_at FROM evacuation_records e JOIN evacuation_centers c ON c.id=e.center_id LEFT JOIN household_members m ON m.id=e.household_member_id WHERE e.checked_out_at IS NULL ORDER BY e.id',
        );
    }
```

## Separate current rows from selected-period activity

[app/models/DisasterSummary.php, lines 29–45](../../../app/models/DisasterSummary.php#L29)

```php
            $affected = $this->rows(
                "SELECT COUNT(*) AS total FROM households WHERE archived_at IS NULL AND registry_reason IN ('affected','both')" . $filter, $area,
            )[0]['total'];
            $centers = $this->rows("SELECT c.id, c.name, c.status, c.archived_at, c.capacity,
                COUNT(e.id) AS occupancy
                FROM evacuation_centers c
                LEFT JOIN evacuation_records e ON e.center_id = c.id AND e.checked_out_at IS NULL
                " . $centerFilter . " GROUP BY c.id, c.name, c.status, c.archived_at, c.capacity ORDER BY c.name, c.id", $area);
            foreach ($centers as $index => $center) {
                $centers[$index]['available'] = availableCapacity($center);
            }
            $incidents = $this->rows(
                'SELECT incident_type,status,COUNT(*) AS total FROM incident_reports WHERE created_at>=? AND created_at<?' . $filter . ' GROUP BY incident_type,status',
                $period,
            );
            $goods = $this->rows(
                'SELECT i.id,i.item_name,i.unit,SUM(d.quantity) AS quantity FROM relief_distributions r JOIN households h ON h.id=r.household_id JOIN relief_distribution_items d ON d.distribution_id=r.id JOIN relief_items i ON i.id=d.item_id WHERE r.distributed_at>=? AND r.distributed_at<?' . $recipientFilter . ' GROUP BY i.id,i.item_name,i.unit ORDER BY i.item_name,i.unit,i.id',
```

## Demonstrate it

Select Topdac, follow the active evacuees card, then open print preview for a disaster summary with the same barangay.
