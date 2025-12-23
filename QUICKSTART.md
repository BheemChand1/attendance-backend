# Quick Start Guide - Employee Management API

## 🚀 Get Started in 5 Minutes

### Step 1: Backend Setup (2 minutes)

```bash
# 1. Ensure migrations are run
php artisan migrate

# 2. Create storage link
php artisan storage:link

# 3. Verify roles in database (tinker)
php artisan tinker
```

In tinker console:

```php
DB::table('roles')->insert([
  ['name' => 'Employee', 'slug' => 'employee', 'created_at' => now(), 'updated_at' => now()],
  ['name' => 'HR', 'slug' => 'hr', 'created_at' => now(), 'updated_at' => now()],
  ['name' => 'Company Admin', 'slug' => 'company_admin', 'created_at' => now(), 'updated_at' => now()]
]);
exit
```

### Step 2: Test API (1 minute)

```bash
# 1. Login and get token
curl -X POST http://backend.bheemchand.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@company.com","password":"password"}'

# Copy the token from response
```

### Step 3: Create Employee (2 minutes)

```bash
curl -X POST http://backend.bheemchand.com/api/employees/onboard \
  -H "Authorization: Bearer YOUR_COPIED_TOKEN" \
  -F "firstName=John" \
  -F "lastName=Doe" \
  -F "email=john.doe@example.com" \
  -F "position=Developer" \
  -F "department=Engineering" \
  -F "employeeId=EMP001" \
  -F "company_id=1"
```

✅ **Done!** Your employee has been created.

---

## 📋 Required Fields

When creating an employee, these fields are **required**:

-   `firstName` - Employee's first name
-   `lastName` - Employee's last name
-   `email` - Must be unique
-   `position` - Job position
-   `department` - Department name
-   `employeeId` - Must be unique
-   `company_id` - Your company ID

All other fields are **optional**.

---

## 📁 File Uploads

### Upload Photo

```javascript
const formData = new FormData();
formData.append("firstName", "John");
formData.append("photo", photoFile); // from input[type=file]
formData.append("company_id", 1);
```

### Upload Documents (Multiple)

```javascript
const formData = new FormData();
// ... other fields

// Key point: Use indexed array notation
documents.forEach((doc, index) => {
    formData.append(`documents[${index}][name]`, doc.name);
    formData.append(`documents[${index}][file]`, doc.file);
});

// Example with 2 documents:
// formData: documents[0][name]=Resume, documents[0][file]=file1
//           documents[1][name]=Certificate, documents[1][file]=file2
```

---

## 🔐 Permissions

| Action          | Required Role          |
| --------------- | ---------------------- |
| Create Employee | HR or Company Admin    |
| Update Employee | HR or Company Admin    |
| Delete Employee | Company Admin only     |
| View Employees  | Any authenticated user |

---

## 📡 All Endpoints

```
POST   /api/employees/onboard          → Create employee
GET    /api/employees                  → List employees
GET    /api/employees/{userId}         → Get one employee
PUT    /api/employees/{userId}         → Update employee
DELETE /api/employees/{userId}         → Delete employee
```

All endpoints require: `Authorization: Bearer {token}`

---

## ✅ Response Format

### Success (201, 200)

```json
{
  "status": true,
  "message": "Employee successfully onboarded",
  "data": { ... }
}
```

### Error (400, 403, 404, 422, 500)

```json
{
  "status": false,
  "message": "Error message",
  "errors": { ... } // Only for 422
}
```

---

## 🐛 Troubleshooting

### "Unauthorized" Error (403)

-   ✅ Check you're using HR or Company Admin account
-   ✅ Check token is valid and not expired
-   ✅ Check Authorization header format: `Bearer {token}`

### "Email already taken" Error

-   ✅ Use a different email address
-   ✅ Email must be unique across system

### File Upload Failed

-   ✅ Check file size is under 5MB
-   ✅ Check file type is allowed (jpeg, png, gif, pdf, doc, docx, xls, xlsx)
-   ✅ Verify storage link exists: `php artisan storage:link`

### "Storage link not working"

```bash
# Fix storage access
php artisan storage:link
chmod -R 775 storage/app/public
```

---

## 📚 Full Documentation

For complete details, see:

-   **API Reference:** `EMPLOYEE_API.md`
-   **Setup Guide:** `EMPLOYEE_SETUP.md`
-   **Frontend Integration:** `FRONTEND_INTEGRATION.md`
-   **Implementation Details:** `IMPLEMENTATION_SUMMARY.md`

---

## 🎯 Common Tasks

### Get Employee List

```bash
curl -X GET http://backend.bheemchand.com/api/employees \
  -H "Authorization: Bearer TOKEN"
```

### Update Employee Salary

```bash
curl -X PUT http://backend.bheemchand.com/api/employees/5 \
  -H "Authorization: Bearer TOKEN" \
  -F "salary=150000"
```

### Delete Employee

```bash
curl -X DELETE http://backend.bheemchand.com/api/employees/5 \
  -H "Authorization: Bearer TOKEN"
```

---

## 🔗 Access Uploaded Files

After uploading, files are accessible at:

```
Photos:    http://backend.bheemchand.com/storage/employees/{userId}/photos/{filename}
Documents: http://backend.bheemchand.com/storage/employees/{userId}/documents/{filename}
```

---

## 💡 Pro Tips

1. **Save the temporary password** returned when creating employee - it's needed for first login
2. **Documents are optional** - employee can be created without any documents
3. **Photos are optional** - not required for employee creation
4. **Company isolation** - you can only see/manage employees of your own company
5. **Batch operations** - create multiple employees one after another using the same logic

---

## ⚡ Next Steps

1. ✅ Setup database (see Step 1)
2. ✅ Test API with cURL (see Step 2-3)
3. 🔲 Integrate with frontend (see FRONTEND_INTEGRATION.md)
4. 🔲 Send temporary password via email (not implemented yet)
5. 🔲 Test full workflow in UI

---

**Need help?** Check the detailed documentation files or look at the error message carefully - they usually tell you what's wrong!

**Last Updated:** December 23, 2025
