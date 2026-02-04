
# Student Attendance Management System (AMS)

## Login Flow

### Admin Login

* URL:
  `http://localhost/attendance-php/index.php`
* Role: **Administrator**
* Data source: `tbladmin`
* Redirects to: `Admin/index.php`

### Class Teacher Login

* URL:
  `http://localhost/attendance-php/classTeacherLogin.php`
* Data source: `tblclassteacher`
* Redirects to: `ClassTeacher/index.php`

(Admin and Teacher logins are intentionally **separate** to avoid conflicts.)
## Class Teacher Security

* Passwords are stored using **bcrypt** (`password_hash`)
* After **5 failed login attempts**, account is **locked**
* On localhost, email unlock will fail (normal)

### Manual Unlock (phpMyAdmin)

```sql
UPDATE tblclassteacher
SET status='active', failed_attempts=0
WHERE emailAddress='teacher@email.com';
```

---

## Important Files

```
index.php                → Admin login
classTeacherLogin.php    → Class Teacher login
Admin/index.php          → Admin dashboard
ClassTeacher/index.php   → Class Teacher dashboard
```

