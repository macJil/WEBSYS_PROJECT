-- Classroom sign-ins for this fictional dataset only:
-- Email: mdrrmo.atok@example.org | Password: ClassroomStaff!842
-- Email: maria.santos@example.org | Password: ClassroomResident!613
-- Optional fictional records. Use only in a fresh, separate presentation database.
START TRANSACTION;

INSERT INTO households
(id,household_code,representative_name,barangay,purok,address_details,registry_reason,hazard_type,latitude,longitude) VALUES
(1,'ATK-01','Maria Santos','Paoay','Block A','Lower Lane','both','flood',16.62366,120.7575),
(2,'ATK-02','Luis Reyes','Topdac','Block B','Upper Lane','monitored','landslide',NULL,NULL);
INSERT INTO household_members (id,household_id,full_name,age) VALUES
(1,1,'Maria Santos',32),(2,1,'Ana Santos',24),(3,2,'Luis Reyes',48);
INSERT INTO users (id,full_name,username,email,password,role,household_member_id,is_active)
VALUES (3,'Maria Santos','maria.santos','maria.santos@example.org','','resident',1,FALSE);

INSERT INTO incident_reports
(id,reported_by,barangay,report_source,reporter_name,incident_type,description,location_description,latitude,longitude,status,reviewed_by,reviewed_at,created_at) VALUES
(1,2,'Topdac','phone','Elena Cruz','landslide','Drill: debris cleared from the training road.','Upper Lane',NULL,NULL,'resolved',2,NOW()-INTERVAL 1 DAY,NOW()-INTERVAL 2 DAY),
(2,2,'Paoay','walk_in','Paolo Garcia','road_obstruction','Drill: a branch blocks the practice crossing.','Crossing',16.62366,120.76422,'responding',2,NOW(),NOW()-INTERVAL 2 HOUR),
(3,3,'Paoay','resident','Maria Santos','flood','Drill: water across the training lane.','Lower Lane',16.617,120.76422,'pending',NULL,NULL,NOW());
UPDATE incident_reports SET contact_phone='09000000000' WHERE id=3;
INSERT INTO responders (id,team_name) VALUES (1,'Response Alpha'),(2,'Response Bravo');
INSERT INTO incident_assignments (id,incident_id,responder_id,assigned_by,status,assigned_at) VALUES
(1,1,1,2,'completed',NOW()-INTERVAL 1 DAY),(2,2,2,2,'assigned',NOW());
INSERT INTO response_updates (incident_id,updated_by,action_taken,from_status,to_status,assignment_id,assignment_status,created_at) VALUES
(1,2,'Dispatch to training road.','pending','responding',1,'assigned',NOW()-INTERVAL 1 DAY),
(1,2,'Team cleared debris.','responding','resolved',1,'completed',NOW()-INTERVAL 23 HOUR),
(2,2,'Team dispatched to crossing.','pending','responding',2,'assigned',NOW());

INSERT INTO evacuation_centers (id,name,barangay,address_details,capacity,status,latitude,longitude) VALUES
(1,'Paoay Assembly Hall','Paoay','Assembly grounds',5,'open',16.63,120.76422),
(2,'Topdac Reserve Hall','Topdac','Upper Lane Hall',4,'closed',NULL,NULL);
INSERT INTO evacuation_walkins (id,full_name,age,address_details,contact_phone,identity_key) VALUES
(1,'Rosa Medina',42,'Upper Lane','09000000001',SHA2('["rosa medina","42","upper lane","09000000001"]',256));
INSERT INTO evacuation_records (center_id,household_member_id,recorded_by) VALUES (1,1,2);
INSERT INTO evacuation_records (center_id,walkin_id,walkin_name,walkin_contact,recorded_by,checked_in_at,checked_out_at,checked_out_by) VALUES
(1,1,'Rosa Medina','09000000001',2,NOW()-INTERVAL 1 DAY,NOW(),2);

INSERT INTO relief_locations (id,name,location_type,address_details,latitude,longitude) VALUES
(1,'East Relief Desk','distribution','East supply desk',16.62366,120.7714);
INSERT INTO relief_items (id,item_name,unit,quantity_on_hand) VALUES
(1,'Food pack','packs',18),(2,'Drinking water','bottles',18),(3,'Blanket','pieces',0);
INSERT INTO relief_distributions (id,household_id,relief_location_id,received_by,distributed_by,notes) VALUES
(1,1,1,'Maria Santos',2,'Household supply issue');
INSERT INTO relief_distribution_items (distribution_id,item_id,quantity) VALUES (1,1,2),(1,2,2);
INSERT INTO relief_stock_movements (item_id,movement_type,quantity,distribution_id,recorded_by,notes) VALUES
(1,'stock_in',20,NULL,2,'Delivery'),(2,'stock_in',20,NULL,2,'Delivery'),
(1,'distribution',2,1,2,'Household issue'),(2,'distribution',2,1,2,'Household issue'),
(3,'stock_in',3,NULL,2,'Donation'),(3,'stock_out',3,NULL,2,'Damaged supplies');
INSERT INTO announcements (title,message,is_published,first_published_at,created_by) VALUES
('Preparedness reminder','Check the location of Paoay Assembly Hall on the map.',1,NOW(),2),
('Supply preparation reminder','Prepare water and essentials before leaving home.',0,NULL,2);

-- Additional fictional incidents for type filters, the map and status histories.
SET @demo_day = (SELECT created_at FROM incident_reports WHERE id=3);
INSERT INTO incident_reports
(reported_by,report_source,reporter_name,barangay,incident_type,description,location_description,latitude,longitude,status,reviewed_by,reviewed_at,created_at)
VALUES (2,'phone','Elena Cruz','Caliking','fire','Drill: smoke reported at the practice shed.','Workshop',16.525,120.694,'verified',2,@demo_day - INTERVAL 5 HOUR + INTERVAL 1 HOUR,@demo_day - INTERVAL 5 HOUR);
SET @demo_report = LAST_INSERT_ID();
INSERT INTO response_updates (incident_id,updated_by,action_taken,from_status,to_status,created_at)
VALUES (@demo_report,2,'Staff confirmed the fictional smoke report.','pending','verified',@demo_day - INTERVAL 5 HOUR + INTERVAL 1 HOUR);
INSERT INTO incident_reports
(reported_by,report_source,reporter_name,barangay,incident_type,description,location_description,latitude,longitude,status,reviewed_by,reviewed_at,created_at)
VALUES (3,'resident','Maria Santos','Abiang','flood','Drill: water covers the exercise footbridge.','Footbridge',16.592,120.729,'verified',2,@demo_day - INTERVAL 10 HOUR + INTERVAL 1 HOUR,@demo_day - INTERVAL 10 HOUR);
SET @demo_report = LAST_INSERT_ID();
INSERT INTO response_updates (incident_id,updated_by,action_taken,from_status,to_status,created_at)
VALUES (@demo_report,2,'Staff confirmed water on the exercise route.','pending','verified',@demo_day - INTERVAL 10 HOUR + INTERVAL 1 HOUR);
INSERT INTO incident_reports
(reported_by,report_source,reporter_name,barangay,incident_type,description,location_description,latitude,longitude,status,reviewed_by,reviewed_at,created_at)
VALUES (2,'phone','Elena Cruz','Cattubo','landslide','Drill: loose soil covers the practice trail.','Hillside trail',16.652,120.759,'verified',2,@demo_day - INTERVAL 15 HOUR + INTERVAL 1 HOUR,@demo_day - INTERVAL 15 HOUR);
SET @demo_report = LAST_INSERT_ID();
INSERT INTO response_updates (incident_id,updated_by,action_taken,from_status,to_status,created_at)
VALUES (@demo_report,2,'Staff confirmed the simulated slide.','pending','verified',@demo_day - INTERVAL 15 HOUR + INTERVAL 1 HOUR);
INSERT INTO incident_reports
(reported_by,report_source,reporter_name,barangay,incident_type,description,location_description,latitude,longitude,status,reviewed_by,reviewed_at,created_at)
VALUES (2,'phone','Elena Cruz','Pasdong','other','Drill: a training shelter lost its lighting.','Equipment tent',16.631,120.713,'resolved',2,@demo_day - INTERVAL 20 HOUR + INTERVAL 1 HOUR,@demo_day - INTERVAL 20 HOUR);
SET @demo_report = LAST_INSERT_ID();
INSERT INTO response_updates (incident_id,updated_by,action_taken,from_status,to_status,created_at)
VALUES (@demo_report,2,'Staff verified the lighting outage.','pending','verified',@demo_day - INTERVAL 20 HOUR + INTERVAL 1 HOUR);
INSERT INTO response_updates (incident_id,updated_by,action_taken,from_status,to_status,created_at)
VALUES (@demo_report,2,'Lighting restored during the drill.','verified','resolved',@demo_day - INTERVAL 20 HOUR + INTERVAL 2 HOUR);
INSERT INTO incident_reports
(reported_by,report_source,reporter_name,barangay,incident_type,description,location_description,latitude,longitude,status,reviewed_by,reviewed_at,created_at)
VALUES (2,'phone','Elena Cruz','Naguey','road_obstruction','Drill: a fallen tree blocks the exercise route.','Orchard lane',16.607,120.686,'pending',NULL,NULL,@demo_day - INTERVAL 25 HOUR);
INSERT INTO incident_reports
(reported_by,report_source,reporter_name,barangay,incident_type,description,location_description,latitude,longitude,status,reviewed_by,reviewed_at,created_at)
VALUES (2,'phone','Elena Cruz','Poblacion','fire','Drill: a second caller reported the same exercise alarm.','Alarm station',16.571,120.675,'rejected',2,@demo_day - INTERVAL 30 HOUR + INTERVAL 1 HOUR,@demo_day - INTERVAL 30 HOUR);
SET @demo_report = LAST_INSERT_ID();
INSERT INTO response_updates (incident_id,updated_by,action_taken,from_status,to_status,created_at)
VALUES (@demo_report,2,'Duplicate exercise alarm.','pending','rejected',@demo_day - INTERVAL 30 HOUR + INTERVAL 1 HOUR);
INSERT INTO incident_reports
(reported_by,report_source,reporter_name,barangay,incident_type,description,location_description,latitude,longitude,status,reviewed_by,reviewed_at,created_at)
VALUES (2,'phone','Elena Cruz','Topdac','other','Drill: a power interruption was reported at a drill station.','Radio post',16.558,120.714,'pending',NULL,NULL,@demo_day - INTERVAL 35 HOUR);
UPDATE users SET password='$2y$10$z9G3i16ffk5iQdmTmiFe4.tpW9LFK0wVV7L.xpwOVqYE/AEv81UMO',is_active=1 WHERE is_active=0 AND password='' AND email='mdrrmo.atok@example.org';
UPDATE users SET password='$2y$10$rLnPTWpAedpHipdqYMTDJep.bb0d1t3xzF1Nb1cQepnLwZiVPlJxO',is_active=1 WHERE is_active=0 AND password='' AND email='maria.santos@example.org';
COMMIT;
