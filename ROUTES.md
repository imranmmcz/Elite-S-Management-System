# Elite School Management System - Route Documentation

## Complete Route List

This document lists all available routes in the Elite School Management System.

---

## Authentication Routes

| Method | Route | Controller | Action | Description |
|--------|-------|------------|--------|-------------|
| GET | `/` | - | - | Welcome/Landing page |
| GET | `/login` | - | - | Login form |
| POST | `/login` | AuthController | login | Process login |
| GET | `/logout` | AuthController | logout | Logout user |

---

## Dashboard Routes

| Method | Route | Controller | Action | Description |
|--------|-------|------------|--------|-------------|
| GET | `/dashboard` | DashboardController | index | Main dashboard (role-based) |

---

## Student Management Routes

| Method | Route | Controller | Action | Description |
|--------|-------|------------|--------|-------------|
| GET | `/students` | StudentController | index | List all students |
| GET | `/students/create` | StudentController | create | Show student admission form |
| POST | `/students/store` | StudentController | store | Save new student |
| GET | `/students/{id}` | StudentController | show | View student profile |
| GET | `/students/{id}/edit` | StudentController | edit | Edit student form |
| POST | `/students/{id}/update` | StudentController | update | Update student data |

**Permissions Required:**
- View/List: `super_admin`, `admin`, `teacher`
- Create/Edit: `super_admin`, `admin`

---

## Attendance Routes

| Method | Route | Controller | Action | Description |
|--------|-------|------------|--------|-------------|
| GET | `/attendance` | AttendanceController | index | Attendance marking interface |
| POST | `/attendance/mark` | AttendanceController | mark | Save attendance records |

**Permissions Required:** `super_admin`, `admin`, `teacher`

---

## Exam Management Routes

| Method | Route | Controller | Action | Description |
|--------|-------|------------|--------|-------------|
| GET | `/exams` | ExamController | index | List all exams |
| GET | `/exams/create` | ExamController | create | Create exam form |
| GET | `/exams/{id}` | ExamController | show | View exam details |
| GET | `/exams/{id}/marks` | ExamController | marks | Mark entry interface |
| POST | `/exams/{id}/save-marks` | ExamController | saveMarks | Save exam marks |

**Permissions Required:**
- View: `super_admin`, `admin`, `teacher`, `student`, `parent`
- Create/Edit: `super_admin`, `admin`, `teacher`

---

## Fee Management Routes

| Method | Route | Controller | Action | Description |
|--------|-------|------------|--------|-------------|
| GET | `/fees` | FeeController | index | Fee invoices dashboard |
| GET | `/fees/structures` | FeeController | structures | Fee structures management |
| GET | `/fees/generate` | FeeController | generate | Invoice generation form |
| POST | `/fees/generate` | FeeController | generate | Generate invoices |
| GET | `/fees/{id}` | FeeController | show | View invoice details |

**Permissions Required:** `super_admin`, `admin`, `accountant`

---

## Payment Routes

| Method | Route | Controller | Action | Description |
|--------|-------|------------|--------|-------------|
| GET | `/payments` | PaymentController | index | Payment history |
| GET | `/payments/create` | PaymentController | create | Payment form |
| POST | `/payments/store` | PaymentController | store | Process payment |
| GET | `/payments/{id}` | PaymentController | show | Payment receipt |

**Permissions Required:**
- Create/Process: `super_admin`, `admin`, `accountant`
- View Own: `student`, `parent`

---

## Report Routes

| Method | Route | Controller | Action | Description |
|--------|-------|------------|--------|-------------|
| GET | `/reports` | ReportController | index | Reports dashboard |
| GET | `/reports/students` | ReportController | students | Student reports |
| GET | `/reports/attendance` | ReportController | attendance | Attendance reports |
| GET | `/reports/exams` | ReportController | exams | Exam reports |
| GET | `/reports/financial` | ReportController | financial | Financial reports |

**Permissions Required:** `super_admin`, `admin`, `teacher`, `accountant`

---

## Certificate Routes

| Method | Route | Controller | Action | Description |
|--------|-------|------------|--------|-------------|
| GET | `/certificates` | CertificateController | index | List certificates |
| GET | `/certificates/create` | CertificateController | create | Certificate generation form |
| POST | `/certificates/store` | CertificateController | store | Generate certificate |
| GET | `/certificates/{id}` | CertificateController | show | View certificate |
| GET | `/certificates/{id}/download` | CertificateController | download | Download PDF |

**Permissions Required:**
- Create: `super_admin`, `admin`
- View Own: `student`, `parent`

---

## Library Routes

| Method | Route | Controller | Action | Description |
|--------|-------|------------|--------|-------------|
| GET | `/library` | LibraryController | index | Library dashboard |
| GET | `/library/books` | LibraryController | books | Browse books |
| GET | `/library/issue` | LibraryController | issue | Issue book form |
| POST | `/library/issue` | LibraryController | issue | Issue book |
| GET | `/library/return` | LibraryController | return | Return book form |
| POST | `/library/return` | LibraryController | return | Process return |

**Permissions Required:**
- Issue/Return: `super_admin`, `admin`, `librarian`
- View: `teacher`, `student`

---

## Settings Routes

| Method | Route | Controller | Action | Description |
|--------|-------|------------|--------|-------------|
| GET | `/settings` | SettingsController | index | Settings dashboard |
| GET | `/settings/general` | SettingsController | general | General settings |
| POST | `/settings/general` | SettingsController | general | Save general settings |
| GET | `/settings/academic` | SettingsController | academic | Academic settings |
| POST | `/settings/academic` | SettingsController | academic | Save academic settings |
| GET | `/settings/classes` | SettingsController | classes | Classes/sections management |
| GET | `/settings/users` | SettingsController | users | User management |
| GET | `/settings/notifications` | SettingsController | notifications | Notification settings |
| POST | `/settings/notifications` | SettingsController | notifications | Save notification settings |

**Permissions Required:** `super_admin`, `admin` (some sections super_admin only)

---

## API Routes

| Method | Route | Description |
|--------|-------|-------------|
| GET | `/api/sections.php?class_id={id}` | Get sections by class ID |

---

## Role-Based Access Summary

### Super Admin
- Full access to all routes
- Can manage all settings
- Can manage users and permissions

### Admin
- Access to most routes except super admin settings
- Can manage students, attendance, exams, fees
- Can view reports and certificates

### Teacher
- Can manage students, attendance, exams
- Can view reports
- Limited access to settings

### Accountant
- Full access to fees and payments
- Can view financial reports
- No access to academic modules

### Librarian
- Full access to library module
- Can view student list
- No access to other modules

### Student
- Can view own profile, attendance, exams, fees
- Can view own certificates
- Can view library books

### Parent
- Can view children's data
- Can view attendance, exams, fees
- Can make payments online

---

## URL Patterns

### Dynamic Routes (with ID parameter)
Routes with `{id}` are replaced with actual numeric IDs:
- `/students/{id}` → `/students/1`, `/students/2`, etc.
- `/exams/{id}/marks` → `/exams/5/marks`

### Query Parameters
Some routes accept query parameters:
- `/students?page=2&search=john&class_id=3`
- `/attendance?date=2024-01-15&class_id=5`
- `/reports/attendance?class_id=3&date_from=2024-01-01&date_to=2024-01-31`

---

## Navigation Menu Structure

The sidebar menu is dynamically generated based on user role:

1. **Dashboard** - All roles
2. **Students** - Admin, Teacher
3. **Attendance** - Admin, Teacher
4. **Exams** - Admin, Teacher, Student, Parent
5. **Fees** - Admin, Accountant
6. **Payments** - Admin, Accountant, Student, Parent
7. **Library** - Admin, Librarian, Teacher, Student
8. **Reports** - Admin, Accountant
9. **Certificates** - Admin, Student, Parent
10. **Settings** - Super Admin

---

## Error Pages

| Code | Route | Description |
|------|-------|-------------|
| 404 | `/views/errors/404.php` | Page not found |
| 403 | `/views/errors/403.php` | Access denied |

---

## Testing Routes

To test all routes are working:

1. **Authentication Test:**
   - Visit `/login`
   - Login with demo credentials
   - Should redirect to `/dashboard`

2. **Navigation Test:**
   - Click each menu item in sidebar
   - All should load without 404 errors

3. **CRUD Test:**
   - Students: List → Create → View → Edit
   - Each step should work properly

4. **Permission Test:**
   - Login with different roles
   - Verify menu items change based on role
   - Try accessing restricted URLs (should get 403)

---

## Development Notes

### Adding New Routes

1. Add route to `index.php` routes array
2. Create controller with action method
3. Create view file
4. Add menu item to `auth_helper.php` getMenuItems()
5. Update this documentation

### Route Naming Convention

- **List:** `/module`
- **Create Form:** `/module/create`
- **Store:** `/module/store` (POST)
- **View:** `/module/{id}`
- **Edit Form:** `/module/{id}/edit`
- **Update:** `/module/{id}/update` (POST)
- **Delete:** `/module/{id}/delete` (POST)

---

**Last Updated:** 2024-01-15
**System Version:** 1.0.0
