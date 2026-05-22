-- =====================================================
-- JEE PROJECT - DATABASE MIGRATION
-- Run this in phpMyAdmin (jee database) or MySQL CLI
-- =====================================================

USE jee;

-- 1. Fix: Increase Name column size in registered table
--    (was VARCHAR(20), too small for full names)
ALTER TABLE registered MODIFY Name VARCHAR(100);

-- 2. Fix: Add Registeration_No foreign key to applications table
--    (links applications to the registered user properly)
ALTER TABLE applications
    ADD COLUMN IF NOT EXISTS Registeration_No INT,
    ADD CONSTRAINT fk_app_reg
        FOREIGN KEY (Registeration_No)
        REFERENCES registered(Registeration_No)
        ON DELETE CASCADE;

-- 3. Fix: admit_cards should link by application_id (not roll_number)
--    This column already exists, but verify the FK exists:
-- (Skip if already present)
-- ALTER TABLE admit_cards
--     ADD CONSTRAINT fk_ac_app
--         FOREIGN KEY (application_id)
--         REFERENCES applications(application_id)
--         ON DELETE CASCADE;

-- =====================================================
-- VERIFY: Check all columns after migration
-- =====================================================
DESC registered;
DESC applications;
DESC admit_cards;
