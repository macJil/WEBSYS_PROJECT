<?php
// Capacity is measured in people; occupancy counts stays without a checkout.
function availableCapacity(array $center): int
{
    if ($center['status'] !== 'open' || !empty($center['archived_at'])) {
        return 0;
    }
    return max(0, (int)$center['capacity'] - (int)$center['occupancy']);
}
