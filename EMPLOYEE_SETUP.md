# Employee Management API - Setup Checklist

## Prerequisites Checklist

### 1. Database Setup

-   [ ] Ensure migrations have been run: `php artisan migrate`
-   [ ] Verify the following roles exist in your `roles` table:
    ```sql
    SELECT * FROM roles WHERE name IN ('Employee', 'HR', 'Company Admin');
    ```
    If missing, add them:
    ```sql
    INSERT INTO roles (name, slug, created_at, updated_at) VALUES
    ('Employee', 'employee', NOW(), NOW()),
    ('HR', 'hr', NOW(), NOW()),
    ('Company Admin', 'company_admin', NOW(), NOW());
    ```

### 2. File Storage Configuration

-   [ ] Run storage link command:

    ```bash
    php artisan storage:link
    ```

    This creates a symlink from `public/storage` to `storage/app/public`

-   [ ] Verify `.env` file settings:

    ```
    FILESYSTEM_DISK=public
    ```

-   [ ] Ensure `storage/app/public` directory exists and is writable:
    ```bash
    chmod -R 775 storage/app/public
    ```

### 3. Laravel Configuration

-   [ ] Verify `config/filesystems.php` has the public disk configured (already done)
-   [ ] Ensure `config/auth.php` has sanctum guard enabled
-   [ ] Check that migration files exist:
    -   `database/migrations/*_create_users_table.php`
    -   `database/migrations/*_create_employee_profiles_table.php`

### 4. Code Files Created/Modified

-   [x] Created: `app/Http/Controllers/Api/EmployeeController.php`
-   [x] Modified: `routes/api.php` - Added employee routes
-   [x] Verified: `app/Models/User.php` - Has employeeProfile relationship
-   [x] Verified: `app/Models/EmployeeProfile.php` - Properly configured

## API Endpoints Summary

| Method | Endpoint                  | Auth Required | Role Required      |
| ------ | ------------------------- | ------------- | ------------------ |
| POST   | `/api/employees/onboard`  | Yes           | HR, Company Admin  |
| GET    | `/api/employees`          | Yes           | Any Authenticated  |
| GET    | `/api/employees/{userId}` | Yes           | Any (same company) |
| PUT    | `/api/employees/{userId}` | Yes           | HR, Company Admin  |
| DELETE | `/api/employees/{userId}` | Yes           | Company Admin      |

## Testing Steps

### Step 1: Verify Database

```bash
php artisan tinker
>>> DB::table('roles')->get();
>>> DB::table('companies')->first();
>>> DB::table('users')->first();
```

### Step 2: Test API with cURL

```bash
# Get authentication token first
curl -X POST http://backend.bheemchand.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@company.com","password":"password"}'

# Then use the token for employee onboarding
curl -X POST http://backend.bheemchand.com/api/employees/onboard \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "firstName=John" \
  -F "lastName=Doe" \
  -F "email=john@example.com" \
  -F "position=Developer" \
  -F "department=Engineering" \
  -F "employeeId=EMP001" \
  -F "company_id=1"
```

### Step 3: Test Frontend

1. Login to the admin dashboard
2. Navigate to employee onboarding
3. Fill in required fields:
    - First Name
    - Last Name
    - Email
    - Position
    - Department
    - Employee ID
4. (Optional) Add photo
5. (Optional) Add documents
6. Submit

## Troubleshooting

### Issue: 405 Method Not Allowed

**Solution:** Clear route cache and restart server

```bash
php artisan route:clear
php artisan serve
```

### Issue: Validation failed - email already taken

**Solution:** Use a unique email for testing

### Issue: File upload fails

**Solution:** Check permissions and storage link

```bash
php artisan storage:link
chmod -R 775 storage/app/public
```

### Issue: Role not found error

**Solution:** Verify roles exist in database

```bash
php artisan tinker
>>> DB::table('roles')->insert(['name' => 'Employee', 'slug' => 'employee', 'created_at' => now(), 'updated_at' => now()]);
```

### Issue: Documents not uploading

**Solution:** Verify FormData format in frontend

```javascript
// Correct format for documents
documents.forEach((doc, index) => {
    formDataToSend.append(`documents[${index}][name]`, doc.name);
    formDataToSend.append(`documents[${index}][file]`, doc.file);
});
```

## Environment Variables (if needed)

Add to `.env` if customizing:

```
APP_NAME="Attendance Backend"
FILESYSTEM_DISK=public
APP_DEBUG=true
APP_URL=http://backend.bheemchand.com

# Mail settings (for sending temporary password)
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Attendance System"
```

## Next Steps (Recommendations)

1. **Email Notification:** Create a mailable class to send temporary password to employee:

    ```bash
    php artisan make:mail EmployeeCredentialsMail
    ```

2. **Employee Self-Service:** Allow employees to change password on first login

3. **Document Management:** Create an endpoint to download/view employee documents

4. **Bulk Upload:** Create a CSV import feature for bulk employee onboarding

5. **Activity Logging:** Log all employee creation/modification/deletion actions

## File Storage Structure

After uploads, your storage will look like:

```
storage/
├── app/
│   └── public/
│       └── employees/
│           ├── 1/
│           │   ├── photos/
│           │   │   └── profile.jpg
│           │   └── documents/
│           │       ├── resume.pdf
│           │       └── certificate.pdf
│           ├── 2/
│           │   ├── photos/
│           │   └── documents/
│           └── ...
```

## References

-   See `EMPLOYEE_API.md` for complete API documentation
-   See `app/Http/Controllers/Api/EmployeeController.php` for implementation details
-   See `app/Models/EmployeeProfile.php` for data structure

---

**Created:** December 23, 2025
**Last Updated:** December 23, 2025
