-- SETUP TEST DATA - PEMINJAMAN OTOMATIS RFID
-- Jalankan di Supabase SQL Editor
-- Struktur sesuai dengan actual database
-- Note: ID akan auto-increment, tidak perlu specify

-- ===== DELETE EXISTING TEST DATA (Optional - uncomment jika perlu clean slate) =====
-- DELETE FROM transactions WHERE uid IN ('B100', 'B101', 'B102', 'B200', 'B301', 'B_ONEDAY', 'B_EMPTY');
-- DELETE FROM books WHERE code IN ('B100', 'B101', 'B102', 'B200', 'B301', 'B_ONEDAY', 'B_EMPTY');
-- DELETE FROM users WHERE uid IN ('USER001', 'USER002', 'USER_FROZEN', 'ADMIN001');

-- ===== INSERT TEST USERS (tanpa specify ID) =====
INSERT INTO users (nama, nisn, role, nip, jabatan, trust_score, password, "maxBorrow", class_id, num_borrows, is_freezed, uid, created_at, updated_at)
VALUES 
  ('Adi Pratama', '123456789', 'murid', NULL, NULL, 100.00, '$2a$06$placeholder1', 1, 1, 0, false, 'USER001', now(), now()),
  ('Budi Santoso', '123456790', 'murid', NULL, NULL, 95.00, '$2a$06$placeholder2', 2, 1, 0, false, 'USER002', now(), now()),
  ('Citra Dewi', '123456791', 'murid', NULL, NULL, 50.00, '$2a$06$placeholder3', 1, 1, 0, true, 'USER_FROZEN', now(), now()),
  ('Admin Perpus', NULL, 'admin', '987654321', 'Administrator', 100.00, '$2a$06$placeholder4', 999, NULL, 0, false, 'ADMIN001', now(), now())
ON CONFLICT (uid) DO NOTHING;

-- ===== INSERT TEST BOOKS =====
INSERT INTO books (code, uid, title, author, illustrator, publisher, series, genre, year, isbn, ddc_number, 
                   image, synopsis, notes, shelf_position, quantity, available, is_one_day_book, 
                   is_in_class, created_at, updated_at)
VALUES 
  ('B100', '["B100"]'::jsonb, 'Matematika Dasar', 'Slamet Santoso', NULL, 'Erlangga', NULL, 'Buku Pelajaran', 2020, 
   '9786021234567', NULL, NULL, NULL, NULL, NULL, 3, true, false, false, now(), now()),
   
  ('B101', '["B101"]'::jsonb, 'Cerita Rakyat Nusantara', 'Dewi Lestari', NULL, 'Gramedia', NULL, 'Fiksi', 2018, 
   '9786027654321', NULL, NULL, NULL, NULL, NULL, 2, true, false, false, now(), now()),
   
  ('B102', '["B102"]'::jsonb, 'IPA Terpadu', 'Agus Wijaya', NULL, 'Tiga Serangkai', NULL, 'Sains', 2021, 
   '9786029876543', NULL, NULL, NULL, NULL, NULL, 1, true, false, false, now(), now()),
   
  ('B200', '["B200"]'::jsonb, 'Buku Test Ownership', 'Test Author', NULL, 'Test Pub', NULL, 'Test', 2026, 
   '9999999999999', NULL, NULL, NULL, NULL, NULL, 1, true, false, false, now(), now()),
   
  ('B301', '["B301"]'::jsonb, 'Buku Case Sensitivity Test', 'Test Author', NULL, 'Test Pub', NULL, 'Test', 2026, 
   '9999999999998', NULL, NULL, NULL, NULL, NULL, 1, true, false, false, now(), now()),
   
  ('B_ONEDAY', '["B_ONEDAY"]'::jsonb, 'One Day Book', 'Test Author', NULL, 'Test Pub', NULL, 'Referensi', 2026, 
   '9999999999997', NULL, NULL, NULL, NULL, NULL, 1, true, true, false, now(), now()),
   
  ('B_EMPTY', '["B_EMPTY"]'::jsonb, 'Buku Habis', 'Test Author', NULL, 'Test Pub', NULL, 'Test', 2026, 
   '9999999999996', NULL, NULL, NULL, NULL, NULL, 0, false, false, false, now(), now())
ON CONFLICT (code) DO NOTHING;


-- ===== VERIFY SETUP =====

-- Check users inserted
SELECT '=== USERS DATA ===' as info;
SELECT id, uid, nama, role, "maxBorrow", trust_score, is_freezed FROM users WHERE uid IN ('USER001', 'USER002', 'USER_FROZEN', 'ADMIN001') ORDER BY id;

-- Check books inserted
SELECT '=== BOOKS DATA ===' as info;
SELECT id, code, uid, title, quantity, available, is_one_day_book FROM books WHERE code IN ('B100', 'B101', 'B102', 'B200', 'B301', 'B_ONEDAY', 'B_EMPTY') ORDER BY id;

-- Check transactions empty (as expected for fresh test)
SELECT '=== TRANSACTIONS (should be empty) ===' as info;
SELECT COUNT(*) as transaction_count FROM transactions WHERE uid IN ('B100', 'B101', 'B102', 'B200', 'B301', 'B_ONEDAY', 'B_EMPTY');

-- ===== TEST DATA READY =====
-- ✅ Setup complete! Ready for testing.
-- 
-- Test Users:
--   USER001 (Adi Pratama, murid, maxBorrow=1, trust_score=100)
--   USER002 (Budi Santoso, murid, maxBorrow=2, trust_score=95)
--   USER_FROZEN (Citra Dewi, murid, is_freezed=true)
--   ADMIN001 (Admin Perpus, admin)
--
-- Test Books:
--   B100 - Matematika Dasar (qty=3, normal)
--   B101 - Cerita Rakyat (qty=2, normal)
--   B102 - IPA Terpadu (qty=1, normal)
--   B200 - Test Ownership (qty=1, for ownership validation test)
--   B301 - Case Test (qty=1, for case sensitivity test)
--   B_ONEDAY - One Day (qty=1, is_one_day_book=true)
--   B_EMPTY - Habis (qty=0, for empty stock test)
