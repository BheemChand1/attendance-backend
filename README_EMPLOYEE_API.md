# Employee Management API - README

> Complete Employee Onboarding API with Document Management

## 🎯 What's Included

A fully functional REST API for managing employees with:

-   ✅ Employee creation with comprehensive information
-   ✅ Multiple document uploads (resume, certificates, licenses, etc.)
-   ✅ Photo upload with preview
-   ✅ Role-based access control (HR, Company Admin, Employee)
-   ✅ Company data isolation
-   ✅ Complete CRUD operations
-   ✅ Comprehensive documentation

## 🚀 Quick Start

### 1. Setup (2 minutes)

```bash
php artisan migrate
php artisan storage:link

# Add roles to database
php artisan tinker
# Then run:
DB::table('roles')->insert([
  ['name' => 'Employee', 'slug' => 'employee', 'created_at' => now(), 'updated_at' => now()],
  ['name' => 'HR', 'slug' => 'hr', 'created_at' => now(), 'updated_at' => now()],
  ['name' => 'Company Admin', 'slug' => 'company_admin', 'created_at' => now(), 'updated_at' => now()]
]);
exit
```

### 2. Test API (1 minute)

```bash
TOKEN="your_auth_token"

# Create employee
curl -X POST http://backend.bheemchand.com/api/employees/onboard \
  -H "Authorization: Bearer $TOKEN" \
  -F "firstName=John" \
  -F "lastName=Doe" \
  -F "email=john@example.com" \
  -F "position=Developer" \
  -F "department=Engineering" \
  -F "employeeId=EMP001" \
  -F "company_id=1"
```

### 3. Integrate with Frontend

See `FRONTEND_INTEGRATION.md` for React examples

## 📚 Documentation

| Document                        | Purpose                       |
| ------------------------------- | ----------------------------- |
| **QUICKSTART.md**               | Get started in 5 minutes      |
| **EMPLOYEE_API.md**             | Complete API reference        |
| **EMPLOYEE_SETUP.md**           | Setup & troubleshooting       |
| **FRONTEND_INTEGRATION.md**     | React implementation examples |
| **VISUAL_GUIDE.md**             | Architecture & flow diagrams  |
| **IMPLEMENTATION_SUMMARY.md**   | What was implemented          |
| **IMPLEMENTATION_CHECKLIST.md** | Verification checklist        |

## 📡 API Endpoints

```
POST   /api/employees/onboard          # Create employee (HR, Admin)
GET    /api/employees                  # List employees
GET    /api/employees/{userId}         # Get employee
PUT    /api/employees/{userId}         # Update employee (HR, Admin)
DELETE /api/employees/{userId}         # Delete employee (Admin only)
```

**All endpoints require:** `Authorization: Bearer {token}`

## 🔑 Key Features

### Employee Data

-   Personal information (name, email, phone, DOB, gender, photo)
-   Address information
-   Professional information (position, department, salary, joining date)
-   Educational background
-   Multiple documents support

### File Management

-   Photo uploads (JPEG, PNG, GIF - max 5MB)
-   Multiple document uploads (PDF, Word, Excel - max 5MB each)
-   Automatic file organization by employee
-   Public URL access to files
-   Automatic cleanup when employee deleted

### Security

-   Bearer token authentication
-   Role-based access control
-   Company data isolation
-   Comprehensive input validation
-   Proper error handling

## 📋 Required Fields

When creating an employee, these fields are required:

-   `firstName` - Employee's first name
-   `lastName` - Employee's last name
-   `email` - Must be unique across system
-   `position` - Job position
-   `department` - Department name
-   `employeeId` - Must be unique
-   `company_id` - Your company ID

All other fields are optional.

## 📝 Example Request

```javascript
// React example
const createEmployee = async () => {
    const formData = new FormData();

    // Required fields
    formData.append("firstName", "John");
    formData.append("lastName", "Doe");
    formData.append("email", "john@example.com");
    formData.append("position", "Developer");
    formData.append("department", "Engineering");
    formData.append("employeeId", "EMP001");
    formData.append("company_id", 1);

    // Optional: Photo
    formData.append("photo", photoFile);

    // Optional: Multiple documents (IMPORTANT FORMAT)
    documents.forEach((doc, index) => {
        formData.append(`documents[${index}][name]`, doc.name);
        formData.append(`documents[${index}][file]`, doc.file);
    });

    const response = await fetch(`${apiUrl}/employees/onboard`, {
        method: "POST",
        headers: { Authorization: `Bearer ${token}` },
        body: formData,
    });

    const data = await response.json();
    console.log(data);
};
```

## ✅ Success Response

```json
{
    "status": true,
    "message": "Employee successfully onboarded",
    "data": {
        "user_id": 5,
        "employee_id": "EMP001",
        "name": "John Doe",
        "email": "john.doe@example.com",
        "temporary_password": "AaBbCc123!@#",
        "profile": {
            "id": 3,
            "user_id": 5,
            "employee_code": "EMP001",
            "position": "Developer",
            "department": "Engineering",
            "status": "active"
        }
    }
}
```

## ❌ Error Responses

### Validation Error (422)

```json
{
    "status": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email has already been taken."]
    }
}
```

### Authorization Error (403)

```json
{
    "status": false,
    "message": "Unauthorized. Only HR and Company Admin can create employees."
}
```

## 🔐 Permissions

| Action          | Required Role          |
| --------------- | ---------------------- |
| Create Employee | HR, Company Admin      |
| Update Employee | HR, Company Admin      |
| Delete Employee | Company Admin only     |
| View Employees  | Any authenticated user |

## 📁 Files Created

```
✨ app/Http/Controllers/Api/EmployeeController.php
✨ QUICKSTART.md
✨ EMPLOYEE_API.md
✨ EMPLOYEE_SETUP.md
✨ FRONTEND_INTEGRATION.md
✨ IMPLEMENTATION_SUMMARY.md
✨ VISUAL_GUIDE.md
✨ IMPLEMENTATION_CHECKLIST.md
✨ README.md (this file)
```

## 🔧 Modified Files

```
✏️ routes/api.php - Added employee endpoints
```

## 🐛 Common Issues

### 405 Method Not Allowed

```bash
php artisan route:clear
php artisan serve
```

### Storage Link Not Working

```bash
php artisan storage:link
chmod -R 775 storage/app/public
```

### Documents Not Uploading

Ensure FormData format is correct:

```javascript
// ✅ CORRECT
documents.forEach((doc, index) => {
    formData.append(`documents[${index}][name]`, doc.name);
    formData.append(`documents[${index}][file]`, doc.file);
});
```

### Role Not Found Error

Create roles in database:

```bash
php artisan tinker
DB::table('roles')->insert(['name' => 'Employee', 'slug' => 'employee', ...]);
exit
```

## 📞 Documentation References

-   **Quick Start Guide:** `QUICKSTART.md`
-   **Complete API Docs:** `EMPLOYEE_API.md`
-   **Setup & Troubleshooting:** `EMPLOYEE_SETUP.md`
-   **Frontend Examples:** `FRONTEND_INTEGRATION.md`
-   **Visual Diagrams:** `VISUAL_GUIDE.md`
-   **Implementation Details:** `IMPLEMENTATION_SUMMARY.md`
-   **Checklist:** `IMPLEMENTATION_CHECKLIST.md`

## 🎯 Next Steps

1. ✅ Run database setup (see QUICKSTART.md)
2. ✅ Test API endpoints (see EMPLOYEE_API.md)
3. 🔲 Integrate with frontend (see FRONTEND_INTEGRATION.md)
4. 🔲 Add email notifications (future enhancement)
5. 🔲 Add employee self-service (future enhancement)

## 💡 Pro Tips

1. **Save Temporary Password:** Returned in response - needed for first login
2. **Documents are Optional:** Employee can be created without documents
3. **File Access:** Use public URLs to access photos and documents
4. **Company Isolation:** Users can only manage their own company employees
5. **Status Options:** Active, Inactive, On Leave (case-insensitive)

## 📊 Database Schema

```
Users Table
├── id (PK)
├── name
├── email (unique)
├── password
├── company_id (FK)
├── role_id (FK)
└── ...

Employee Profiles Table
├── id (PK)
├── user_id (FK, unique)
├── company_id (FK)
├── employee_code (unique)
├── position
├── department
├── salary
├── documents (JSON)
├── employee_photo
└── ...
```

## 🚀 Production Checklist

Before deploying to production:

-   [ ] All migrations run
-   [ ] Roles created in database
-   [ ] Storage link created
-   [ ] File permissions set (775)
-   [ ] Routes cached properly
-   [ ] CORS configured
-   [ ] Email sending configured
-   [ ] Database backups scheduled
-   [ ] Error logging configured
-   [ ] Rate limiting configured

## 📈 Features Included

✅ Create employee with complete information
✅ Upload multiple documents (resume, certificates, etc.)
✅ Upload employee photo
✅ Update employee information
✅ Delete employee (with automatic file cleanup)
✅ List all company employees
✅ Role-based access control
✅ Company data isolation
✅ Comprehensive validation
✅ Error handling
✅ File management
✅ JSON document storage with metadata

## 🔄 File Structure

```
storage/app/public/
└── employees/
    └── {userId}/
        ├── photos/
        │   └── profile.jpg
        └── documents/
            ├── resume.pdf
            ├── certificate.pdf
            └── license.jpg

Accessible via:
http://backend.bheemchand.com/storage/employees/{userId}/photos/profile.jpg
```

## 🎓 Learning Resources

-   **Laravel Documentation:** https://laravel.com/docs
-   **Laravel Sanctum:** https://laravel.com/docs/sanctum
-   **FormData API:** https://developer.mozilla.org/en-US/docs/Web/API/FormData

## 💬 Support

For issues or questions:

1. Check the relevant documentation file
2. Review the error message carefully
3. Check the EMPLOYEE_SETUP.md troubleshooting section
4. Review FRONTEND_INTEGRATION.md for implementation details

## 📅 Version Info

-   **Version:** 1.0
-   **Created:** December 23, 2025
-   **Status:** ✅ Complete
-   **Ready for:** Development, Testing, Production

---

**Get started now:** Read `QUICKSTART.md` for a 5-minute setup guide!
