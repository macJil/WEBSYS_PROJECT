# Atok minimum application inventory

Generated for the Atok simplification. `sources/` and ignored local configuration are excluded.

## PHP pages
- `resources/views/admin/admin.index.php`
- `resources/views/admin/announcement/announcement.php`
- `resources/views/admin/announcement/announcement.update.php`
- `resources/views/admin/announcement/editor.php`
- `resources/views/admin/evacuation/center.php`
- `resources/views/admin/evacuation/centers.php`
- `resources/views/admin/household.php`
- `resources/views/admin/households.php`
- `resources/views/admin/records.php`
- `resources/views/admin/relief/distributions.php`
- `resources/views/admin/relief/inventory.php`
- `resources/views/admin/relief/location.php`
- `resources/views/admin/relief/locations.php`
- `resources/views/admin/relief/movements.php`
- `resources/views/admin/response/incident.php`
- `resources/views/admin/response/teams.php`
- `resources/views/admin/summaries/current.php`
- `resources/views/admin/summaries/disaster.php`
- `resources/views/components/dashboard-cards.php`
- `resources/views/components/errors.php`
- `resources/views/components/footer.php`
- `resources/views/components/header.php`
- `resources/views/components/location-picker.php`
- `resources/views/components/messages.php`
- `resources/views/components/navigation.php`
- `resources/views/components/saved-point.php`
- `resources/views/incidents/create.php`
- `resources/views/incidents/image.php`
- `resources/views/incidents/list.php`
- `resources/views/incidents/view.php`
- `resources/views/maps/centers.php`
- `resources/views/maps/data.php`
- `resources/views/maps/map.php`
- `resources/views/user/announcement.php`
- `resources/views/user/user.index.php`

## Shared helper functions
- `app/helpers.php`: `start_app_session()`
- `app/helpers.php`: `e()`
- `app/helpers.php`: `url()`
- `app/helpers.php`: `redirect()`
- `app/helpers.php`: `isPost()`
- `app/helpers.php`: `input()`
- `app/helpers.php`: `validDateInput()`
- `app/helpers.php`: `flash()`
- `app/helpers.php`: `dashboardPath()`
- `app/helpers.php`: `session_checker()`
- `app/helpers.php`: `requireRole()`
- `app/helpers.php`: `locationErrors()`
- `app/helpers.php`: `hasMapPoint()`
- `config/db.php`: `db()`

## Fresh schema tables and columns
- `users`: `id`, `full_name`, `username`, `email`, `phone`, `password`, `role`, `is_active`, `created_at`
- `households`: `archived_at`, `id`, `household_code`, `representative_name`, `contact_phone`, `purok`, `address_details`, `registry_reason`, `hazard_type`, `hazard_notes`, `vulnerability_category`, `vulnerability_notes`, `vulnerability_assessed_by`, `vulnerability_assessed_at`, `evacuation_status`, `latitude`, `longitude`, `created_at`
- `household_members`: `archived_at`, `id`, `household_id`, `full_name`, `age`, `notes`
- `incident_reports`: `archived_at`, `id`, `reported_by`, `report_source`, `reporter_name`, `occurred_at`, `incident_type`, `description`, `location_description`, `latitude`, `longitude`, `contact_phone`, `status`, `reviewed_by`, `reviewed_at`, `review_notes`, `created_at`, `updated_at`
- `incident_images`: `id`, `incident_id`, `stored_filename`, `original_filename`, `mime_type`, `file_size`, `uploaded_by`, `uploaded_at`
- `responders`: `id`, `team_name`, `contact_phone`, `is_active`
- `incident_assignments`: `id`, `incident_id`, `responder_id`, `assigned_by`, `assigned_at`, `status`
- `response_updates`: `id`, `incident_id`, `updated_by`, `action_taken`, `from_status`, `to_status`, `assignment_id`, `assignment_status`, `created_at`
- `announcements`: `archived_at`, `first_published_at`, `publication_history_unknown`, `id`, `title`, `message`, `is_published`, `created_by`, `created_at`
- `evacuation_centers`: `archived_at`, `id`, `name`, `address_details`, `latitude`, `longitude`, `capacity`, `status`
- `evacuation_walkins`: `archived_at`, `id`, `full_name`, `age`, `address_details`, `contact_phone`
- `evacuation_records`: `id`, `center_id`, `household_member_id`, `walkin_name`, `walkin_contact`, `checked_in_at`, `checked_out_at`, `recorded_by`, `walkin_id`, `checked_out_by`, `active_member_id`, `active_walkin_id`
- `relief_items`: `archived_at`, `id`, `item_name`, `unit`, `quantity_on_hand`
- `relief_locations`: `archived_at`, `id`, `name`, `location_type`, `address_details`, `latitude`, `longitude`
- `relief_distributions`: `id`, `household_id`, `relief_location_id`, `received_by`, `distributed_by`, `distributed_at`, `notes`
- `relief_distribution_items`: `id`, `distribution_id`, `item_id`, `quantity`
- `relief_stock_movements`: `id`, `item_id`, `movement_type`, `quantity`, `distribution_id`, `recorded_by`, `notes`, `created_at`

## Internal PHP page links
- `resources/views/admin/admin.index.php`
- `resources/views/admin/announcement/announcement.php`
- `resources/views/admin/announcement/announcement.update.php`
- `resources/views/admin/evacuation/center.php`
- `resources/views/admin/evacuation/centers.php`
- `resources/views/admin/household.php`
- `resources/views/admin/households.php`
- `resources/views/admin/records.php`
- `resources/views/admin/relief/distributions.php`
- `resources/views/admin/relief/inventory.php`
- `resources/views/admin/relief/location.php`
- `resources/views/admin/relief/locations.php`
- `resources/views/admin/relief/movements.php`
- `resources/views/admin/response/incident.php`
- `resources/views/admin/response/teams.php`
- `resources/views/admin/summaries/current.php`
- `resources/views/admin/summaries/disaster.php`
- `resources/views/components/errors.php`
- `resources/views/components/footer.php`
- `resources/views/components/header.php`
- `resources/views/incidents/create.php`
- `resources/views/incidents/image.php`
- `resources/views/incidents/list.php`
- `resources/views/incidents/view.php`
- `resources/views/maps/centers.php`
- `resources/views/maps/data.php`
- `resources/views/maps/map.php`
- `resources/views/user/announcement.php`
- `resources/views/user/user.index.php`

## Removed in this step
- `app/controllers/MapPublicationController.php`
- `app/models/MapPublication.php`
- `resources/views/admin/map-publication.php`
- Incident, center, and relief-location `is_public` fields (fresh schema and migration 007).

## Required behavior retained
- Eight modules, root login/registration, session guards, CRUD pages, one shared map, image records, response actions, evacuation stays, relief stock and distribution history.
- Public map SQL selects only incident type/status/date/point; open centers; active distribution points. Household markers and personal incident fields are absent.
