# 📝 Faire Church Scheduling System — Change Log

> All notable fixes and changes are recorded here.  
> Format: `[Date] — Description — Files Affected`

---

## April 16, 2026 (Session 3)

### 📊 Feature: Admin Reports Page

**Request:** Add a Reports menu to the admin sidebar with graphs and a print feature, matching the system design.

**Implementation:**

- Created `navigation/admin/report/report.php` — full reports page with:
  - **Filter bar** — filter by Year, Month, and Service; "Apply" refreshes all charts and the table dynamically via AJAX
  - **6 KPI cards** — Total, Approved, Pending, Completed, Denied, Cancelled (each colour-coded to match status theme)
  - **3 charts (Chart.js v4)**:
    - Bar chart — Bookings by Status
    - Doughnut chart — Bookings by Service
    - Line chart — Monthly booking trend
  - **Detail table (DataTables)** — all booking records for the selected period with client, email, service, date, time, status, notes
  - **Print Table** button on the DataTable (prints only the table, landscape A4, with report title + filter summary)
  - **Print Report** button on the filter bar (prints the full page including KPI cards and charts)
- Created `navigation/admin/report/get_report_data.php` — AJAX endpoint that returns KPIs, chart data, and detail rows filtered by year/month/service
- Added **Reports** sidebar link (`<i class="fas fa-chart-bar">`) to all 6 admin pages: `dashboard.php`, `pending_page.php`, `approved_page.php`, `completed_page.php`, `servicepage.php`, `accountpage.php`

**Files changed:**
| File | Change |
|------|--------|
| `navigation/admin/report/report.php` | **New file** — full reports page |
| `navigation/admin/report/get_report_data.php` | **New file** — AJAX data endpoint |
| `navigation/admin/dashboard.php` | Added Reports sidebar link |
| `navigation/admin/schedule/pending_page.php` | Added Reports sidebar link |
| `navigation/admin/schedule/approved_page.php` | Added Reports sidebar link |
| `navigation/admin/schedule/completed_page.php` | Added Reports sidebar link |
| `navigation/admin/services/servicepage.php` | Added Reports sidebar link |
| `navigation/admin/account/accountpage.php` | Added Reports sidebar link |

### 📅 Feature: Booking Calendar on Home Page

**Request:** Add a public-facing calendar on the home page (`user_index.php`) that shows all bookings. Clicking an event opens a modal with booking details.

**Implementation:**

- Added a **FullCalendar v5** (dayGridMonth / timeGridWeek / listMonth views) section between the hero banner and footer
- Events are colour-coded by status: **Pending** (amber), **Approved** (green), **Completed** (blue), **Denied** (red), **Cancelled** (grey)
- Clicking any event opens a clean modal showing: service name, date, time, booked-by name, email, optional contact person, phone and notes
- Modal closes via ✕ button, clicking the backdrop, or pressing Escape
- Created a new AJAX endpoint `get_calendar_events.php` that fetches all bookings joined with user and service info

**Files changed:**
| File | Change |
|------|--------|
| `navigation/user/user_index.php` | Added FullCalendar CSS/JS, calendar section HTML, legend, booking detail modal, and calendar JS initialization |
| `navigation/user/includes/get_calendar_events.php` | **New file** — returns all bookings as FullCalendar-compatible JSON events |

### 🎨 Fix: Forgot Password — Eye Icon Overlapping Placeholder Text

**Problem:** The toggle-password eye icon was positioned with `top:38px` which caused it to overlap long placeholder text inside the New Password field.  
**Fix:** Wrapped each password input in a `position-relative` div; eye icon now uses `top:50%; transform:translateY(-50%)` for perfect vertical centering. Added `padding-right:2.5rem` to inputs so text never runs under the icon. Moved the password hint ("Min 8 chars…") to a `<small>` hint below the field.

**Files changed:**
| File | Change |
|------|--------|
| `registration/forgotpass_page.php` | Fixed eye icon positioning; moved password hint below input |

---

### 🔑 Fix: Forgot Password — 3-Step Inline Flow (Code Verified Before Showing Password Fields)

**Problem:** The password fields were visible immediately after entering the email — the verification code was not checked before allowing the user to set a new password.  
**Fix:** Split Step 2 into two parts:

1. **Step 2** — User enters verification code only → clicks "Verify Code" → backend `verify_passkey.php` checks it against DB
2. **Step 3** — Password fields are revealed **only** if the code is correct. Incorrect code shows an error and keeps the password fields hidden.

**Files changed:**
| File | Change |
|------|--------|
| `registration/forgotpass_page.php` | Rebuilt as 3-step flow; password fields hidden until code verified |
| `registration/verify_passkey.php` | **New file** — AJAX endpoint that checks passkey against DB without resetting password |

---

### 🐛 Fix: Booking — "Cannot Save" Error Despite Saving + Overlap Logic

**Problem 1:** The booking was saving to the DB and showing on the calendar but the UI displayed "Cannot save." This was caused by the overlap check running **outside** a transaction, and the response path falling through to an error state.  
**Problem 2:** The overlap query was scoped by `serviceID` — meaning two different services could be booked at the exact same time on the same date, which is wrong (the church has one venue).  
**Problem 3:** The submit button could be clicked multiple times rapidly, causing duplicate bookings to be inserted before the overlap check caught them.

**Fix:**

- Wrapped the overlap check + insert inside a **single `beginTransaction()` / `commit()` / `rollBack()`** block with a `FOR UPDATE` lock
- Changed the overlap SQL to be **global** (checks ALL active bookings on the same date/time, regardless of service)
- Added submit button disabled state during AJAX to prevent double-submission
- Fixed old Gmail credentials (`papermaxx99`) that were still in `save_schedule.php`

**Files changed:**
| File | Change |
|------|--------|
| `navigation/user/book/save_schedule.php` | Fixed overlap SQL (global, not service-scoped); wrapped in proper transaction; fixed Gmail credentials; commit after successful insert |
| `navigation/user/book/book.php` | Disable submit button during AJAX; re-enable on complete |

---

### 🔐 Fix: Session Logout — Back-Button Re-entry Prevention

**Problem:** After logging out, rapidly pressing the browser back button would show the cached authenticated page.  
**Fix:** Added `Cache-Control: no-store`, `Pragma: no-cache`, and `Expires` headers to all protected pages and `logout.php` so the browser never caches authenticated content.

**Files changed:**
| File | Change |
|------|--------|
| `logout.php` | Added no-cache headers before redirect | ✅
| `navigation/user/user_index.php` | Added no-cache headers + added missing session guard | ✅
| `navigation/user/book/book.php` | Added no-cache headers |✅
| `navigation/user/schedule/schedule.php` | Added no-cache headers |✅
| `navigation/user/about/about.php` | Added no-cache headers + added missing session guard |✅
| `navigation/admin/dashboard.php` | Added no-cache headers |✅

---

### 📞 Fix: Philippine Phone Number Validation — Booking Form

**Problem:** The "Other contact number" field in the booking modal accepted any input.  
**Fix:** Added HTML `pattern="^09\d{9}$"`, `maxlength="11"`, digits-only `input` event listener, and JS validation before form submission.

**Files changed:**
| File | Change |
|------|--------|
| `navigation/user/book/book.php` | Added PH phone pattern, digits-only enforcement, and SweetAlert validation |

---

### 🔍 Fix: Search Event Form on Home Page

**Problem:** The Search Event form on `user_index.php` referenced a non-existent `packages/book.php` route, used a broken `pkgMap` variable, and had a phantom `Package` dropdown that was never populated.  
**Fix:** Rewrote the form and its JS — removed the package dropdown, fixed service loading from DB, and redirected correctly to `book/book.php?service=ID` with full client-side validation (no past dates, time logic).

**Files changed:**
| File | Change |
|------|--------|
| `navigation/user/user_index.php` | Rewrote search form HTML and JS; fixed `$services` PHP query; removed `$pkgMap` |

---

### 🎂 Fix: Birthday Field — Future Date Not Allowed (Registration)

**Problem:** Users could enter a future date as their birthday with no error.  
**Fix:** Set `max` attribute to today's date on page load; added JS validation to block future dates on change and on submit.

**Files changed:**
| File | Change |
|------|--------|
| `registration/registrationpage.php` | Added `max` date init, future-date check on change and submit |

---

### 📞 Fix: Philippine Phone Number Validation — Registration Form

**Problem:** Mobile number field accepted any 11-digit number pattern, not specifically `09xxxxxxxxx`.  
**Fix:** Updated `pattern` to `^09\d{9}$`, added digits-only input enforcement, inline error message, and SweetAlert validation on submit.

**Files changed:**
| File | Change |
|------|--------|
| `registration/registrationpage.php` | Updated pattern, added digits-only listener, SweetAlert PH phone check |

---

### 🔑 Fix: Forgot Password — All-in-One Page Flow

**Problem:** After sending the email, the user was redirected to a separate `newpass_page.php` page which felt disjointed. The old page had no passkey verification inline.  
**Fix:** Rebuilt `forgotpass_page.php` as a **2-step single-page flow**:

1. **Step 1** — Enter email → click "Send Verification Code" → code sent via PHPMailer
2. **Step 2** — Passkey field + New Password + Confirm Password appear on the **same page** → submit calls `newpass.php` directly → success redirects to login

Added: password strength validation, show/hide toggles, back button to re-enter email.

**Files changed:**
| File | Change |
|------|--------|
| `registration/forgotpass_page.php` | Fully rewritten as 2-step inline flow |

---

## April 15, 2026

### ✉️ Fix: Gmail SMTP Configuration

**Problem:** PHPMailer was using old/incorrect Gmail credentials (`papermaxx99@gmail.com`).  
**Fix:** Updated all three PHPMailer-using files to use `fairechurchscheduling@gmail.com` with the correct app password (no spaces).

**Files changed:**
| File | Change |
|------|--------|
| `test_smtp.php` | Updated credentials, from-name, removed debug mode |
| `registration/forgotpass_email.php` | Updated credentials and from-name |
| `navigation/admin/schedule/update_schedule_status.php` | Updated credentials |
