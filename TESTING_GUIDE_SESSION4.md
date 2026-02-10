# Testing Guide - Session 4: Form Validation & Checklist Fixes

## Summary of Changes Made

### 1. **Form Validation - HTML5 Hidden Field Error Fix**
**Problem:** Console error "An invalid form control with name='judulCari' is not focusable" appearing 5+ times when using pengembalian (return) form.

**Root Cause:** HTML5 form validation tried to focus hidden required input fields (peminjaman section) when modal switched to pengembalian mode.

**Solution Implemented:**
- Remove `required` attribute from peminjaman fields (#namaCari, #judulCari) when switching to pengembalian tab
- Conditionally add required attribute based on which form is active during submit
- Store form type in modal dataset to track whether user is borrowing or returning

**Files Modified:**
- `app/Views/peminjaman_kelas.php` (Lines 814-870)
  - Updated `pengembalianBtn` listener to call removeAttr('required')
  - Updated form submit handler to conditionally set required based on formType

---

### 2. **Checklist Loading - Transactions Fetch Before Render**
**Problem:** Pengembalian checklist might be empty or undefined because activeBorrowings wasn't populated.

**Root Cause:** When pengembalianBtn clicked, loadPengembalianChecklist() called immediately without fetching fresh transaction data first.

**Solution Implemented:**
- Fetch active borrowings from backend in pengembalianBtn listener BEFORE calling loadPengembalianChecklist()
- Wait for AJAX response to populate activeBorrowings array before rendering checklist
- Added comprehensive logging to track what data is being loaded

**Files Modified:**
- `app/Views/peminjaman_kelas.php` (Lines 835-873)
  - `pengembalianBtn` now makes $.get request to `/peminjaman-kelas/transactions?class_id=X&type=borrow`
  - Filters response to `status === 'active'` transactions
  - Calls loadPengembalianChecklist() in success callback

**Backend Data Flow:**
```
pengembalianBtn clicked
  ↓
$.get('/peminjaman-kelas/transactions', {class_id, type: 'borrow'})
  ↓
ClassTransactionController.getClassTransactions()
  - Gets all students in class
  - Fetches transactions for those students
  - Enriches with user_name and book_title mappings
  - Returns {success: true, transactions: [...]}
  ↓
Response.success → Filter status === 'active' → Set activeBorrowings
  ↓
loadPengembalianChecklist() groups by student and renders checkboxes
```

---

### 3. **Enhanced Logging & Debugging**
**Added to Frontend (peminjaman_kelas.php):**

#### `loadPengembalianChecklist()` (Lines 679-755)
```javascript
console.log('=== LOADING PENGEMBALIAN CHECKLIST ===');
console.log('Total active borrowings:', activeLoans.length);
console.log('Class students count:', classStudents.length);
console.log('Active borrowings data:', activeLoans);
console.log('Class students data:', classStudents);
// ... per-student grouping logs
// ... loan-missing-ID warnings
```

#### `pengembalianBtn` listener (Lines 835-873)
```javascript
console.log('=== PENGEMBALIAN BUTTON CLICKED ===');
console.log('Current class ID:', currentClassId);
// ... after response
console.log('Transactions fetch response:', response);
console.log('Active borrowings set to:', activeBorrowings);
```

#### `handlePengembalianAdd()` (Lines 955-1015)
```javascript
console.log('=== HANDLING PENGEMBALIAN (RETURN) ===');
// Per-checkbox logging
console.log('Checked loan:', { loanId, userId });
console.log('Total selected loans:', selectedLoans.length);
console.log('Selected loans data:', selectedLoans);
console.log('FormData to send:', {
  'class_id': currentClassId,
  'selectedLoans': JSON.stringify(selectedLoans)
});
// ... after response
console.log('Return response:', response);
```

#### Error handling (Lines 1003-1020)
```javascript
console.error('AJAX Error Status:', xhr.status);
console.error('AJAX Error Text:', xhr.responseText);
// Better error messages based on HTTP status
```

---

## Step-by-Step Testing Procedure

### **Test 1: Verify No Console Errors on Pengembalian Form**

1. **Setup:**
   - Refresh browser (Ctrl+F5 to clear cache)
   - Open browser DevTools (F12)
   - Navigate to "Console" tab

2. **Execute:**
   - Click class selector (e.g., "Kelas 1A")
   - Click "Tambah Pengembalian" button
   - Wait for checklist to load (should see console logs)
   - Submit form without selecting anything (should see error message)

3. **Expected Results:**
   ```
   Console Logs (in order):
   === PENGEMBALIAN BUTTON CLICKED ===
   Current class ID: [number]
   Transactions fetch response: {success: true, transactions: [...]}
   Active borrowings set to: [...]
   === LOADING PENGEMBALIAN CHECKLIST ===
   Total active borrowings: [number]
   Class students count: [number]
   Active borrowings data: [...]
   Grouped by student: {...}
   Checklist HTML generated, total students: [number]
   
   Toast message: "Pilih minimal satu buku untuk dikembalikan!"
   
   ✅ NO errors about "not focusable" or undefined
   ```

4. **Failure Indicators:**
   - ❌ "An invalid form control with name='judulCari' is not focusable"
   - ❌ "Cannot read property 'length' of undefined" (activeBorrowings)
   - ❌ Empty checklist with "Tidak ada peminjaman aktif"
   - ❌ 404 or 500 errors on AJAX request

---

### **Test 2: Verify Checklist Populates Correctly**

1. **Setup:**
   - Ensure class has at least 1 active borrow (if not, create one via Peminjaman form first)
   - Select the class
   - Click "Tambah Pengembalian"

2. **Execute:**
   - Observe checklist renders with student names and book titles
   - Check browser console for logs
   - Inspect first checkbox element (F12 → Elements tab)

3. **Expected Results:**
   ```
   Checklist renders with structure:
   ┌─ Student Name 1
   │  ☐ Book Title 1 (Borrow Date)
   │  ☐ Book Title 2 (Borrow Date)
   │
   └─ Student Name 2
      ☐ Book Title (Borrow Date)
   
   HTML inspection shows:
   <input class="form-check-input return-checkbox" 
          data-loan-id="[number]" 
          data-user-id="[number]">
   
   Console shows:
   Grouped by student: {
     "1": { student: {id: 1, nama: "John"}, loans: [{...}, {...}] },
     "2": { student: {id: 2, nama: "Jane"}, loans: [{...}] }
   }
   ```

4. **Failure Indicators:**
   - ❌ "Tidak ada peminjaman aktif" message when borrowings exist
   - ❌ Loan IDs missing or showing as "undefined"
   - ❌ Malformed HTML with broken student grouping
   - ❌ "Loan missing ID" warnings in console

---

### **Test 3: Complete Return Transaction**

1. **Setup:**
   - Populate checklist (Test 2)
   - Select at least 1 book from checklist

2. **Execute:**
   - Check 1-3 books in checklist
   - Click "Save" button
   - Observe console logs
   - Verify toast message appears

3. **Expected Results:**
   ```
   Console shows:
   === HANDLING PENGEMBALIAN (RETURN) ===
   Checked loan: {loanId: [number], userId: [number]}
   Checked loan: {loanId: [number], userId: [number]}
   Total selected loans: [number]
   Selected loans data: [{loanId: ..., userId: ...}, ...]
   FormData to send: {
     class_id: [number],
     selectedLoans: JSON_STRING
   }
   Return response: {success: true, message: "Pengembalian berhasil...", processed: 2}
   
   Toast shows: "Pengembalian berhasil dicatat untuk 2 buku!"
   Modal closes automatically
   ```

4. **Verify Backend Processing:**
   - Check database that borrow transaction `status = 'completed'`
   - Check that return transaction created with `type = 'return'`
   - Check book quantity increased by 1 for each returned book
   - Check trust_score updated for each return

5. **Failure Indicators:**
   - ❌ Toast shows "Class dan Loan IDs wajib diisi"
   - ❌ "AJAX Error" messages in console
   - ❌ Response shows `success: false`
   - ❌ Transactions show status still 'active' instead of 'completed'

---

### **Test 4: Edge Cases**

#### **4a. Empty Checklist (No Active Borrowings)**
```
Expected: Message "Tidak ada peminjaman aktif di kelas ini." displayed
Console: 
  Total active borrowings: 0
  Tidak ada peminjaman aktif atau data siswa tidak cocok
```

#### **4b. Student with No Matching ID**
```
If loan.user_id not found in classStudents:
Console: 
  (Skipped in forEach with early return)
Console later:
  Grouped by student: {...}  (won't include this student)
```

#### **4c. Return Multiple Books from Same Student**
```
Student "John Doe" has 3 active loans
Expected:
  Checklist shows all 3 under same student name
  Check all 3
  Submit
  Toast shows "Pengembalian berhasil dicatat untuk 3 buku!"
```

#### **4d. Network Error While Fetching Checklist**
```
When pengembalianBtn clicked:
AJAX error occurs (network down, server error)
Expected:
  isFormSubmitting = false (prevent UI lock)
  Toast shows specific error message
  Console shows:
    AJAX Error Status: [status code]
    AJAX Error Text: [response body]
```

---

## Console Output Reference

### Success Flow Indicators
```
✅ When EVERYTHING is working correctly, console will show:

=== PENGEMBALIAN BUTTON CLICKED ===
Current class ID: 1
Transactions fetch response: {success: true, transactions: [Array(2)]}
Active borrowings set to: [Array(2)]

=== LOADING PENGEMBALIAN CHECKLIST ===
Total active borrowings: 2
Class students count: 3
Active borrowings data: [
  {id: 101, user_id: 1, book_id: 5, status: 'active', ...},
  {id: 102, user_id: 2, book_id: 7, status: 'active', ...}
]
Class students data: [
  {id: 1, nama: 'John Doe', class_id: 1},
  {id: 2, nama: 'Jane Smith', class_id: 1},
  {id: 3, nama: 'Bob Johnson', class_id: 1}
]
Grouped by student: {
  "1": {student: {...}, loans: [Array]},
  "2": {student: {...}, loans: [Array]}
}
Checklist HTML generated, total students: 2

[User selects checkboxes]

=== HANDLING PENGEMBALIAN (RETURN) ===
Checked loan: {loanId: 101, userId: 1}
Checked loan: {102, userId: 2}
Total selected loans: 2
Selected loans data: (2) [{...}, {...}]
FormData to send:
- class_id: 1
- selectedLoans (JSON): "[{\"loanId\":101,\"userId\":1},{\"loanId\":102,\"userId\":2}]"
Return response: {success: true, message: "Pengembalian berhasil...", processed: 2, errors: []}
```

### Error Flow Indicators
```
❌ When something breaks, look for:

ERROR: "Cannot read property 'length' of undefined"
→ activeBorrowings not initialized or response malformed

ERROR: "An invalid form control with name='judulCari' is not focusable"
→ required attribute not removed from hidden peminjaman fields

WARN: "No active borrowings to display"
→ Checklist loaded but response.transactions is empty array

WARN: "Loan missing ID" {id: undefined, ...}
→ Transaction missing 'id' field | Check data structure from backend

ERROR: "AJAX Error Status: 500"
→ Backend error | Check ClassTransactionController logs

NOTICE: "Grouped by student: {...}" shows empty object {}
→ No students matched with borrowings | Check user_id in transactions vs classStudents
```

---

## Quick Debugging Checklist

If tests fail, check in this order:

```
[ ] 1. Browser console - any JavaScript errors? (F12)
[ ] 2. Network tab - is AJAX request reaching backend? (F12 → Network)
[ ] 3. Network response - is JSON valid? Check Status code (200, 400, 500)
[ ] 4. Console logs - check sequence: pengembalian button → fetch → checklist load
[ ] 5. Elements tab - inspect checklist HTML structure (F12 → Elements)
[ ] 6. Data attributes - are loan IDs populated? (right-click checkbox → Inspect)
[ ] 7. Backend logs - any PHP errors? Check writable/logs/
[ ] 8. Database - verify transactions record exists with user_id, status='active'
```

---

## Files Modified in This Session

```
app/Views/peminjaman_kelas.php
├── Line 679-755: loadPengembalianChecklist() - Added enhanced logging
├── Line 835-873: pengembalianBtn listener - Added transaction fetching
├── Line 955-1020: handlePengembalianAdd() - Added comprehensive logging & improved error handling
```

No backend controller changes in this session (returnMultiple was fixed in Session 3).

---

## Next Steps

1. **Test all 4 test cases above**
2. **If console shows all expected logs → Move to production testing**
3. **If any test fails → Review console error in "Failure Indicators" → Check debugging checklist**
4. **After successful return → Verify database changes:**
   - Transaction status updated to 'completed'
   - Books quantity incremented
   - Trust score updated

