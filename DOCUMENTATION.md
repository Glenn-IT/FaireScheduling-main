# 📅 Faire Church Scheduling System — Project Documentation

> **System Name:** Faire Church Scheduling System  
> **Stack:** PHP (PDO), MySQL (MariaDB), Bootstrap 4/5, jQuery, FullCalendar v5/v6, DataTables, PHPMailer  
> **Server:** XAMPP (Apache + MariaDB)  
> **Database:** `dbschedule`  
> **Developers:** John Michael Llovido · Romney Mendoza Narag

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Project Structure](#2-project-structure)
3. [Database Schema](#3-database-schema)
4. [System Setup & Installation](#4-system-setup--installation)
5. [Authentication & Registration Flow](#5-authentication--registration-flow)
6. [User Module](#6-user-module)
7. [Admin Module](#7-admin-module)
8. [Notification System](#8-notification-system)
9. [Email System (PHPMailer)](#9-email-system-phpmailer)
10. [Security Features](#10-security-features)
11. [Technology Stack & Libraries](#11-technology-stack--libraries)

---

## 1. Project Overview

The **Faire Church Scheduling System** is a web-based booking and scheduling platform built for **Faire Church** (Piat, Cagayan, Philippines). It allows registered users to book church services (e.g., Weddings, Masses) by selecting a date and time slot via an interactive calendar. Admins manage all bookings, user accounts, and available services through a dedicated dashboard.

### Core Features

| Feature                   | Description                                                           |
| ------------------------- | --------------------------------------------------------------------- |
| User Registration & Login | Secure sign-up with password strength validation and role-based login |
| Forgot Password           | Email-based password reset via PHPMailer                              |
| Service Booking           | Users book a service on a date/time slot via FullCalendar             |
| Booking Management        | Users view, edit, and cancel their own bookings                       |
| Admin Dashboard           | KPI cards, booking status charts, and upcoming schedules table        |
| Schedule Approval         | Admin approves, denies, or completes bookings                         |
| Account Management        | Admin activates, deactivates, and resets user accounts                |
| Service Management        | Admin adds and edits available church services                        |
| Real-time Notifications   | In-app bell notification panel for users                              |
| Email Notifications       | Automated emails sent on booking status changes                       |

---

## 2. Project Structure

```
FaireScheduling-main/
│
├── index.php                        # Landing/Login page
├── logout.php                       # Session destroy & redirect
│
├── database/
│   ├── connection.php               # PDO database connection
│   ├── dbschedule.sql               # Full database dump (import this)
│   └── note.txt                     # Setup instructions
│
├── img/                             # Global images (backgrounds, developer photos)
│   ├── bg_index1.jpg
│   ├── bg_index2.jpg
│   ├── bg-time.jpg
│   ├── dev1.jpg
│   ├── dev2.jpg
│   └── Flag-Philippines.webp
│
├── registration/                    # Auth pages & backend handlers
│   ├── registrationpage.php         # Registration form (UI)
│   ├── register.php                 # Registration AJAX handler (POST → JSON)
│   ├── signin.php                   # Login AJAX handler (POST → JSON)
│   ├── forgotpass_page.php          # Forgot password form (UI)
│   ├── forgotpass_email.php         # Sends reset email via PHPMailer
│   ├── newpass_page.php             # New password form (UI)
│   ├── newpass.php                  # New password save handler
│   └── style.css                    # Shared auth page styles
│
├── navigation/
│   │
│   ├── admin/                       # 🔐 Admin-only section
│   │   ├── dashboard.php            # Admin dashboard (KPIs + charts + table)
│   │   ├── css/
│   │   │   ├── admin.css
│   │   │   ├── style.css            # Main admin layout styles
│   │   │   └── style1.css
│   │   │
│   │   ├── account/                 # User account management
│   │   │   ├── accountpage.php      # Account list UI
│   │   │   ├── fetch_accounts.php   # AJAX: fetch all user accounts
│   │   │   ├── fetch_edit.php       # AJAX: fetch single account for edit modal
│   │   │   ├── update_accounts.php  # AJAX: update account details
│   │   │   ├── activate_account.php # AJAX: set user_active = 1
│   │   │   ├── deactivate_account.php # AJAX: set user_active = 0
│   │   │   └── reset_account.php    # AJAX: reset user password
│   │   │
│   │   ├── services/                # Church service management
│   │   │   ├── servicepage.php      # Services list UI
│   │   │   ├── fetch_service.php    # AJAX: fetch all services
│   │   │   ├── add_service.php      # AJAX: insert new service
│   │   │   └── edit_service.php     # AJAX: update existing service
│   │   │
│   │   └── schedule/                # Booking/schedule management
│   │       ├── pending_page.php     # Pending bookings list + calendar
│   │       ├── approved_page.php    # Approved bookings list
│   │       ├── completed_page.php   # Completed bookings list
│   │       ├── fetch_pending_schedules.php  # AJAX: fetch pending rows
│   │       ├── fetch_calendar_events.php    # AJAX: fetch calendar events
│   │       └── update_schedule_status.php   # AJAX: approve/deny/complete + email
│   │
│   └── user/                        # 👤 Authenticated user section
│       ├── user_index.php           # User home/landing page
│       │
│       ├── about/
│       │   └── about.php            # About / Developer info page
│       │
│       ├── book/
│       │   ├── book.php             # Booking calendar UI (FullCalendar)
│       │   ├── fetch_schedules.php  # AJAX: fetch existing bookings for calendar
│       │   └── save_schedule.php    # AJAX redirect to includes/add_schedule.php
│       │
│       ├── schedule/
│       │   ├── schedule.php         # User's personal bookings list
│       │   └── cancel_booking.php   # AJAX: cancel a booking
│       │
│       ├── contact/
│       │   └── contact.php          # Contact page (church contact info)
│       │
│       ├── includes/                # Shared backend helpers (user)
│       │   ├── add_schedule.php     # AJAX: insert new booking with overlap check
│       │   ├── edit_schedule.php    # AJAX: edit an existing booking
│       │   ├── delete_schedule.php  # AJAX: delete a booking
│       │   ├── fetch_schedules.php  # AJAX: fetch user's own schedules
│       │   ├── fetch_schedules_table.php  # AJAX: table view of schedules
│       │   ├── fetch_services.php   # AJAX: fetch available services list
│       │   ├── get_schedule.php     # AJAX: fetch single schedule for edit
│       │   ├── notify.php           # Helper function: add_notification()
│       │   ├── get_notifications.php       # AJAX: fetch user notifications
│       │   ├── get_unread_count.php        # AJAX: get unread notification count
│       │   └── mark_notifications_read.php # AJAX: mark all as read
│       │
│       ├── css/                     # User-side CSS (Bootstrap, FontAwesome, etc.)
│       ├── js/                      # User-side JS files
│       ├── fonts/                   # Font files
│       ├── img/                     # User section images
│       ├── scss/                    # SCSS source files
│       └── style/                   # Compiled style assets
│
└── phpmailer/                       # PHPMailer library (v6)
    ├── src/
    │   ├── PHPMailer.php
    │   ├── SMTP.php
    │   └── Exception.php
    └── language/                    # 50+ language translation files
```

---

## 3. Database Schema

**Database name:** `dbschedule`

### Tables Overview

```
tblusers ──────────────────────┐
                               │ (user_id FK)
notifications ─────────────────┤
                               │ (userID FK)
schedules ─────────────────────┘
    └── (serviceID FK) → services
```

---

### `tblusers` — Registered Users

| Column         | Type         | Description                     |
| -------------- | ------------ | ------------------------------- |
| `id`           | INT (PK)     | Auto-increment primary key      |
| `lastname`     | VARCHAR(50)  | Last name                       |
| `firstname`    | VARCHAR(50)  | First name                      |
| `middlename`   | VARCHAR(50)  | Middle name                     |
| `birthday`     | DATE         | Date of birth                   |
| `age`          | INT(3)       | Age                             |
| `mobilenumber` | VARCHAR(20)  | Mobile/phone number             |
| `email`        | VARCHAR(100) | Email (used for login)          |
| `password`     | VARCHAR(150) | Bcrypt-hashed password          |
| `datecreated`  | DATETIME     | Account creation date           |
| `user_role`    | VARCHAR(50)  | `'Admin'` or `'User'`           |
| `code`         | INT(11)      | Password reset code             |
| `user_active`  | INT(4)       | `1` = Active, `0` = Deactivated |

---

### `services` — Church Services

| Column         | Type        | Description                        |
| -------------- | ----------- | ---------------------------------- |
| `ID`           | INT (PK)    | Auto-increment primary key         |
| `service_name` | VARCHAR(50) | Service name (e.g., Wedding, Mass) |
| `description`  | TEXT        | Service description                |
| `date_created` | DATETIME    | When service was added             |

---

### `schedules` — Bookings

| Column                 | Type         | Description                                               |
| ---------------------- | ------------ | --------------------------------------------------------- |
| `ID`                   | INT (PK)     | Auto-increment primary key                                |
| `userID`               | INT          | FK → `tblusers.id`                                        |
| `serviceID`            | INT          | FK → `services.ID` (nullable)                             |
| `date`                 | DATE         | Booking date                                              |
| `time_start`           | TIME         | Start time of the booking                                 |
| `time_end`             | TIME         | End time of the booking                                   |
| `other_contact_person` | VARCHAR(120) | Optional alternative contact                              |
| `contact_phone`        | VARCHAR(30)  | Contact phone number                                      |
| `notes`                | TEXT         | Additional notes                                          |
| `date_created`         | DATETIME     | When booking was created                                  |
| `status`               | VARCHAR(50)  | `Pending`, `Approved`, `Completed`, `Cancelled`, `Denied` |

---

### `notifications` — User Notifications

| Column       | Type         | Description                               |
| ------------ | ------------ | ----------------------------------------- |
| `id`         | INT (PK)     | Auto-increment primary key                |
| `user_id`    | INT          | FK → `tblusers.id` (CASCADE DELETE)       |
| `type`       | ENUM         | `info`, `success`, `warning`, `error`     |
| `title`      | VARCHAR(120) | Short notification title                  |
| `message`    | TEXT         | Full notification body                    |
| `link`       | VARCHAR(255) | Redirect URL when notification is clicked |
| `is_read`    | TINYINT(1)   | `0` = Unread, `1` = Read                  |
| `created_at` | DATETIME     | Timestamp of notification                 |

---

## 4. System Setup & Installation

### Requirements

- **XAMPP** (Apache + MariaDB + PHP 8.2+)
- Web browser

### Steps

1. **Clone or copy** the project folder into:

   ```
   C:\xampp\htdocs\FaireScheduling-main\
   ```

2. **Start XAMPP** — Enable **Apache** and **MySQL** services.

3. **Create the database:**
   - Open `http://localhost/phpmyadmin`
   - Create a new database named: `dbschedule`
   - Import the file: `database/dbschedule.sql`

4. **Configure database credentials** (if needed) in `database/connection.php`:

   ```php
   $host     = "localhost";
   $dbname   = "dbschedule";
   $username = "root";   // Change if needed
   $password = "";       // Change if needed
   ```

5. **Access the system:**

   ```
   http://localhost/FaireScheduling-main/
   ```

6. **Default admin credentials** (from the SQL seed data):
   - Email: `diewithasmile@gmail.com`
   - Password: _(hashed — reset via phpMyAdmin or use the forgot password flow)_

---

## 5. Authentication & Registration Flow

### Login Flow (`index.php` → `registration/signin.php`)

```
User submits login form (AJAX)
        ↓
signin.php (POST JSON handler)
        ↓
  Validate fields → Sanitize email
        ↓
  Query tblusers WHERE email = ?
        ↓
  password_verify() against bcrypt hash
        ↓
  Store session: userid, lastname, firstname, middlename, userrole
        ↓
  user_role === 'Admin'  →  navigation/admin/dashboard.php
  user_role === 'User'   →  navigation/user/user_index.php
```

- Failed login triggers a **30-second lockout** using `localStorage` (client-side) after multiple attempts.
- Password field has a **show/hide toggle** (Font Awesome eye icon).

---

### Registration Flow (`registration/registrationpage.php` → `registration/register.php`)

```
User fills registration form (AJAX)
        ↓
register.php validates:
  • All fields required
  • Valid email format
  • Password strength regex:
      ≥8 chars, uppercase, lowercase, digit, special char
  • Email not already registered
        ↓
password_hash() with PASSWORD_DEFAULT (bcrypt)
        ↓
INSERT INTO tblusers (..., user_role='User', user_active=1)
        ↓
Redirect → index.php (login page)
```

---

### Forgot Password Flow

```
forgotpass_page.php  →  forgotpass_email.php
  • Validates email exists in tblusers
  • Generates random reset code → saves to tblusers.code
  • Sends email via PHPMailer with reset link

newpass_page.php  →  newpass.php
  • Validates reset code
  • Hashes new password → UPDATE tblusers SET password = ?
  • Clears code field
```

---

### Session Protection

All protected pages check for a valid session at the top:

```php
session_start();
if (!isset($_SESSION['userid'])) {
    header("Location: ../../logout.php");
    exit();
}
```

`logout.php` destroys the session and redirects to `index.php`.

---

## 6. User Module

Accessed after login when `user_role = 'User'`.  
Base path: `navigation/user/`

### 6.1 Home Page (`user_index.php`)

- Landing page after user login.
- Displays a navigation bar with links to: **Home**, **Book**, **My Schedule**, **Contact**, **About**.
- Shows a **bell icon** (notification badge) and **profile dropdown** in the header.
- Hero/banner section promoting church service scheduling.

---

### 6.2 Book a Schedule (`book/book.php`)

The core booking feature — powered by **FullCalendar v5**.

**How it works:**

1. User navigates to `Book` in the menu.
2. An interactive **month/week/day calendar** is displayed.
3. User **clicks a date/time slot** on the calendar.
4. A **booking modal** opens with:
   - Service dropdown (fetched from `services` table)
   - Date (auto-filled from click)
   - Start time / End time
   - Optional: alternate contact person, phone, notes
5. On submit, AJAX calls `includes/add_schedule.php`:
   - Validates all required fields
   - Checks for **time slot conflicts** (overlap detection using a `FOR UPDATE` transaction lock)
   - If no conflict → inserts booking with `status = 'Pending'`
   - Returns JSON `{status: 'success'}` or `{status: 'conflict'}`
6. On success, a notification is created for the user:  
   _"We received your booking for [date], [time]. Status: Pending review."_

**Existing bookings** are shown on the calendar (fetched via `fetch_schedules.php`) so users can see occupied time slots.

---

### 6.3 My Schedule (`schedule/schedule.php`)

Displays a **table/card list** of the current user's bookings.

| Column     | Details                                                                          |
| ---------- | -------------------------------------------------------------------------------- |
| Date & Day | Formatted: `Mon Oct 27, 2025`                                                    |
| Time       | `9:00 AM – 11:00 AM`                                                             |
| Service    | e.g., `Wedding`                                                                  |
| Status     | Color-coded badge: Pending 🟡 / Approved 🟢 / Completed ⚫ / Cancelled/Denied 🔴 |
| Actions    | Edit (if Pending) · Cancel (if Pending)                                          |

- **Edit** opens a modal pre-filled with booking details (via `includes/get_schedule.php`).
- **Cancel** calls `cancel_booking.php` and updates status to `Cancelled`.
- Only **Pending** bookings can be edited or cancelled.

---

### 6.4 Contact Page (`contact/contact.php`)

Static page displaying the church's contact information, address, and possibly a map embed.

---

### 6.5 About Page (`about/about.php`)

Displays information about the **Faire Church** and the **system developers**:

| Developer            | Birthdate    | Address                    |
| -------------------- | ------------ | -------------------------- |
| John Michael Llovido | Feb 10, 2003 | Poblacion 02, Piat Cagayan |
| Romney Mendoza Narag | Feb 16, 2004 | Villareyno, Piat Cagayan   |

Ages are **dynamically computed** from the birthdates using PHP's `DateTime` class.

---

## 7. Admin Module

Accessed after login when `user_role = 'Admin'`.  
Base path: `navigation/admin/`

The admin panel uses a **collapsible sidebar** layout with Bootstrap 5.

### Admin Sidebar Navigation

```
📊 Dashboard
👥 Accounts
🔧 Services
📅 Schedules
    ├── ⏳ Pending
    ├── ✅ Approved
    └── 🏁 Completed
🚪 Logout
```

---

### 7.1 Dashboard (`dashboard.php`)

#### KPI Cards (Top Summary)

| Card             | Query                                                |
| ---------------- | ---------------------------------------------------- |
| Total Members    | `COUNT(*) FROM tblusers`                             |
| Active Bookings  | `COUNT(*) WHERE status='Approved' AND date >= TODAY` |
| Pending Bookings | `COUNT(*) WHERE status='Pending'`                    |

#### Charts (Chart.js)

- **Booking Status Bar Chart** — shows count per status: Pending, Approved, Completed, Denied.
- **Bookings Per Month Line Chart** — last 12 months of booking activity.

#### Upcoming Schedules Table

- Shows the next 10 bookings (from today onwards), including user name, service, date, time, and status.
- Rendered with **DataTables** (searchable, sortable, printable via Buttons extension).

---

### 7.2 Account Management (`account/accountpage.php`)

The admin can manage all registered user accounts.

| Action            | File                                     | Description                              |
| ----------------- | ---------------------------------------- | ---------------------------------------- |
| View all accounts | `fetch_accounts.php`                     | Returns all rows from `tblusers` as JSON |
| Edit account      | `fetch_edit.php` + `update_accounts.php` | Load user into modal, save changes       |
| Activate          | `activate_account.php`                   | Sets `user_active = 1`                   |
| Deactivate        | `deactivate_account.php`                 | Sets `user_active = 0`                   |
| Reset Password    | `reset_account.php`                      | Resets password to a default/new value   |

- Displayed in a **DataTables** table with search, sort, and pagination.
- Responsive layout with Bootstrap 5.

---

### 7.3 Service Management (`services/servicepage.php`)

The admin manages church services that users can select when booking.

| Action            | File                | Description                              |
| ----------------- | ------------------- | ---------------------------------------- |
| View all services | `fetch_service.php` | Returns all rows from `services` as JSON |
| Add service       | `add_service.php`   | INSERT into `services`                   |
| Edit service      | `edit_service.php`  | UPDATE `services` by ID                  |

- Rendered in a **DataTables** table.
- Add/Edit forms open in Bootstrap modals.

---

### 7.4 Schedule Management

#### Pending Schedules (`schedule/pending_page.php`)

- Lists all bookings with `status = 'Pending'`.
- Includes a **FullCalendar v6** view showing pending events.
- Each row has **Approve** and **Deny** action buttons.
- On action → AJAX call to `update_schedule_status.php`.

#### Approved Schedules (`schedule/approved_page.php`)

- Lists all `status = 'Approved'` bookings.
- Admin can mark as **Completed** from here.

#### Completed Schedules (`schedule/completed_page.php`)

- Archive view of all `status = 'Completed'` bookings.
- Read-only — no further actions.

#### Status Update Flow (`update_schedule_status.php`)

```
Admin clicks Approve / Deny / Complete
        ↓
AJAX POST → update_schedule_status.php
        ↓
Validates session + allowed status
        ↓
Fetches schedule + linked user + service from DB
        ↓
UPDATE schedules SET status = ? WHERE ID = ?
        ↓
INSERT into notifications (for the user)
        ↓
Send email via PHPMailer (to user's email)
        ↓
Returns JSON { success: true }
```

**Allowed status transitions triggered by admin:**

| Admin Action | New Status  | Email/Notification Sent                    |
| ------------ | ----------- | ------------------------------------------ |
| Approve      | `Approved`  | ✅ Yes — "Your booking has been approved." |
| Deny         | `Denied`    | ✅ Yes — "Your booking has been denied."   |
| Complete     | `Completed` | ✅ Yes — "Your booking is now Completed."  |

All pages support **print** via DataTables Buttons (`@media print` CSS hides UI chrome).

---

## 8. Notification System

### Architecture

- Notifications are stored in the **`notifications`** table.
- Each notification is tied to a `user_id`.
- Unread notifications appear with a **red badge** on the bell icon in the navbar.

### User-side Flow

| File                          | Purpose                                                                      |
| ----------------------------- | ---------------------------------------------------------------------------- |
| `get_notifications.php`       | Fetches latest notifications for the logged-in user                          |
| `get_unread_count.php`        | Returns count of `is_read = 0` notifications                                 |
| `mark_notifications_read.php` | Sets all `is_read = 1` for the user                                          |
| `notify.php`                  | Helper function `add_notification(PDO, user_id, title, message, type, link)` |

### Notification Types

| Type      | Color / Icon | Used For                 |
| --------- | ------------ | ------------------------ |
| `info`    | Blue ℹ️      | Booking received/pending |
| `success` | Green ✅     | Booking approved         |
| `warning` | Yellow ⚠️    | Booking denied           |
| `error`   | Red ❌       | System errors            |

### Triggers

| Event                | Type      | Message Example                              |
| -------------------- | --------- | -------------------------------------------- |
| User creates booking | `info`    | "We received your booking for Oct 27..."     |
| Admin approves       | `success` | "Your booking for Oct 27 has been approved." |
| Admin denies         | `warning` | "Your booking for Oct 27 has been denied."   |
| Admin completes      | `success` | "Your booking is now marked as Completed."   |

---

## 9. Email System (PHPMailer)

**Library:** PHPMailer v6 (included in `/phpmailer/src/`)

### Used In

| File                                                   | Purpose                            |
| ------------------------------------------------------ | ---------------------------------- |
| `registration/forgotpass_email.php`                    | Sends password reset link to user  |
| `navigation/admin/schedule/update_schedule_status.php` | Sends booking status update emails |

### Email Triggers

| Trigger           | Recipient | Subject / Content                    |
| ----------------- | --------- | ------------------------------------ |
| Forgot password   | User      | Reset link with unique code          |
| Booking Approved  | User      | Approval confirmation with date/time |
| Booking Denied    | User      | Denial notice with date/time         |
| Booking Completed | User      | Completion confirmation              |

### Configuration

PHPMailer is configured with SMTP settings inside each script that uses it. To configure your own email:

```php
$mail->isSMTP();
$mail->Host       = 'smtp.gmail.com';      // Your SMTP host
$mail->SMTPAuth   = true;
$mail->Username   = 'your@email.com';      // SMTP username
$mail->Password   = 'your_app_password';   // SMTP password or app password
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port       = 587;
```

> **Note:** For Gmail, use an **App Password** (not your regular Google password). Enable 2FA on your Google account first.

---

## 10. Security Features

| Feature                       | Implementation                                                                  |
| ----------------------------- | ------------------------------------------------------------------------------- |
| Password hashing              | `password_hash()` with `PASSWORD_DEFAULT` (bcrypt)                              |
| Password verification         | `password_verify()`                                                             |
| SQL injection prevention      | PDO prepared statements with named parameters throughout                        |
| XSS prevention                | `htmlspecialchars()` used on output                                             |
| Session protection            | All protected pages check `$_SESSION['userid']` before loading                  |
| Session hijack mitigation     | `session_start()` + session destroy on logout                                   |
| Login rate limiting           | 30-second client-side lockout via `localStorage`                                |
| Password strength enforcement | Regex: ≥8 chars, uppercase, lowercase, digit, special char                      |
| CSRF (partial)                | AJAX-only POST endpoints with `Content-Type: application/json`                  |
| Race condition prevention     | Transaction + `FOR UPDATE` lock in `add_schedule.php` to prevent double booking |
| Method restriction            | `$_SERVER["REQUEST_METHOD"] !== "POST"` check in API handlers                   |
| PDO error mode                | `PDO::ERRMODE_EXCEPTION` with `error_log()` (no public exposure)                |

---

## 11. Technology Stack & Libraries

### Backend

| Technology     | Version | Role                       |
| -------------- | ------- | -------------------------- |
| PHP            | 8.2+    | Server-side scripting      |
| PDO (MySQL)    | —       | Database abstraction layer |
| MariaDB        | 10.4.32 | Database server            |
| PHPMailer      | v6      | Email sending (SMTP)       |
| Apache (XAMPP) | —       | Web server                 |

### Frontend

| Library                | Version       | Role                                       |
| ---------------------- | ------------- | ------------------------------------------ |
| Bootstrap              | 4.5 / 5.3     | UI framework (admin uses v5, user uses v4) |
| jQuery                 | 3.5 / 3.6     | AJAX, DOM manipulation                     |
| Font Awesome           | 5.x / 6.x     | Icons                                      |
| FullCalendar           | 5.11 / 6.1.14 | Interactive booking calendar               |
| DataTables             | 1.13.6        | Sortable/searchable/printable tables       |
| Chart.js               | (via CDN)     | Dashboard bar and line charts              |
| SweetAlert2            | v11           | Styled alert/confirmation dialogs          |
| Google Fonts (Poppins) | —             | Typography                                 |

### Dev Tools

| Tool       | Purpose                  |
| ---------- | ------------------------ |
| XAMPP      | Local development server |
| phpMyAdmin | Database management GUI  |
| VS Code    | Code editor              |

---

## Appendix — Booking Status Lifecycle

```
User creates booking
        │
        ▼
   [ Pending ] ──── Admin Denies ────▶ [ Denied ]
        │
        │ Admin Approves
        ▼
   [ Approved ] ─── Admin Completes ─▶ [ Completed ]
        │
        │ User Cancels
        ▼
   [ Cancelled ]
```

---

## Appendix — URL Map (Page Routes)

| URL                                             | Role   | Description              |
| ----------------------------------------------- | ------ | ------------------------ |
| `/index.php`                                    | Public | Login page               |
| `/registration/registrationpage.php`            | Public | Registration form        |
| `/registration/forgotpass_page.php`             | Public | Forgot password          |
| `/registration/newpass_page.php`                | Public | Set new password         |
| `/navigation/admin/dashboard.php`               | Admin  | Admin dashboard          |
| `/navigation/admin/account/accountpage.php`     | Admin  | User account management  |
| `/navigation/admin/services/servicepage.php`    | Admin  | Service management       |
| `/navigation/admin/schedule/pending_page.php`   | Admin  | Pending bookings         |
| `/navigation/admin/schedule/approved_page.php`  | Admin  | Approved bookings        |
| `/navigation/admin/schedule/completed_page.php` | Admin  | Completed bookings       |
| `/navigation/user/user_index.php`               | User   | User home page           |
| `/navigation/user/book/book.php`                | User   | Book a schedule          |
| `/navigation/user/schedule/schedule.php`        | User   | View my bookings         |
| `/navigation/user/contact/contact.php`          | User   | Contact page             |
| `/navigation/user/about/about.php`              | User   | About page               |
| `/logout.php`                                   | Both   | Logout & session destroy |

---

_Documentation generated: April 10, 2026_
