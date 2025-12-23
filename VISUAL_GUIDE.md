# Employee Management API - Visual Guide

## System Architecture

```
┌─────────────────────────────────────────────────────────────────────┐
│                         React Frontend                              │
│  (OnboardEmployee Component with Form & Document Upload)            │
└────────────────────────┬────────────────────────────────────────────┘
                         │
                         │ POST /employees/onboard
                         │ with FormData (files, fields)
                         │
                    ┌────▼─────────────────────────────────────┐
                    │    API Gateway / Routes (api.php)        │
                    │  - Authentication (Bearer Token)         │
                    │  - Authorization (Role-based)            │
                    └────┬─────────────────────────────────────┘
                         │
                    ┌────▼──────────────────────────────────────┐
                    │   EmployeeController.php                 │
                    │   - onboard()                            │
                    │   - index()                              │
                    │   - show()                               │
                    │   - update()                             │
                    │   - delete()                             │
                    └────┬──────────────────────────────────────┘
                         │
        ┌────────────────┼────────────────┐
        │                │                │
   ┌────▼────────┐  ┌───▼────────┐  ┌──▼───────────┐
   │ User Model  │  │ Employee    │  │ Storage      │
   │             │  │ Profile     │  │ (Files)      │
   │ - name      │  │ Model       │  │              │
   │ - email     │  │             │  │ - Photos     │
   │ - password  │  │ - position  │  │ - Documents  │
   │ - role_id   │  │ - salary    │  │              │
   │ - company_id│  │ - documents │  │              │
   └─────────────┘  │             │  └──────────────┘
                    │ (JSON array)│
                    └─────────────┘

                         │
                         │
                    ┌────▼──────────────────────┐
                    │   PostgreSQL Database     │
                    │                           │
                    │ - users table             │
                    │ - employee_profiles table │
                    │ - roles table             │
                    │ - companies table         │
                    └───────────────────────────┘
```

---

## Request/Response Flow

### 1. Employee Creation Flow

```
FRONTEND                           BACKEND                         DATABASE
   │                                 │                               │
   │──────────────────────────────────────────────────────────────────┐
   │  POST /api/employees/onboard                                     │
   │  Headers:                                                        │
   │  - Authorization: Bearer {token}                                 │
   │  - Body: FormData                                                │
   │    ├─ firstName                                                  │
   │    ├─ lastName                                                   │
   │    ├─ email                                                      │
   │    ├─ photo (file)                                               │
   │    ├─ documents[0][name] (Resume)                                │
   │    ├─ documents[0][file] (file)                                  │
   │    └─ ... more fields                                            │
   │                                                                   │
   ├──────────────────────────────────────────────────────────────────►
   │                      Validate Token
   │                      ├─ Token valid?
   │                      └─ User role = HR or Admin?
   │                                                                   │
   ├──────────────────────────────────────────────────────────────────►
   │                      Validate Data
   │                      ├─ Required fields?
   │                      ├─ Email unique?
   │                      ├─ Employee ID unique?
   │                      └─ File sizes < 5MB?
   │                                                                   │
   ├──────────────────────────────────────────────────────────────────►
   │                      Save Files
   │                      ├─ Store photo
   │                      └─ Store documents
   │                                                  ┌─────────────────┐
   │                                                  │ storage/app/    │
   │                                                  │ public/employees│
   │                                                  └─────────────────┘
   │
   ├──────────────────────────────────────────────────────────────────►
   │                      Create User
   │                                                  ┌──────────────┐
   │                                                  │ users table  │
   │                                                  │ ├─ id = 5    │
   │                                                  │ ├─ name      │
   │                                                  │ ├─ email     │
   │                                                  │ └─ role_id   │
   │                                                  └──────────────┘
   │
   ├──────────────────────────────────────────────────────────────────►
   │                      Create Profile
   │                                                  ┌─────────────────┐
   │                                                  │ employee_       │
   │                                                  │ profiles table  │
   │                                                  │ ├─ user_id = 5  │
   │                                                  │ ├─ position     │
   │                                                  │ ├─ salary       │
   │                                                  │ └─ documents    │
   │                                                  │   (JSON array)  │
   │                                                  └─────────────────┘
   │
   │◄──────────────────────────────────────────────────────────────────
   │  201 Created
   │  {
   │    "status": true,
   │    "message": "Employee successfully onboarded",
   │    "data": {
   │      "user_id": 5,
   │      "employee_id": "EMP001",
   │      "name": "John Doe",
   │      "temporary_password": "AaBbCc123..."
   │    }
   │  }
   │
```

---

## Document Upload Structure

```
FormData Structure (JavaScript):
─────────────────────────────────

formData.append('firstName', 'John');
formData.append('lastName', 'Doe');
formData.append('email', 'john@example.com');
formData.append('company_id', '1');

// Single document
formData.append('documents[0][name]', 'Resume');
formData.append('documents[0][file]', fileObject1);

// Multiple documents
formData.append('documents[1][name]', 'Degree Certificate');
formData.append('documents[1][file]', fileObject2);

formData.append('documents[2][name]', 'License');
formData.append('documents[2][file]', fileObject3);


Server Receives (Laravel):
──────────────────────────

$request->input('documents') = [
    [
        'name' => 'Resume',
        'file' => UploadedFile instance
    ],
    [
        'name' => 'Degree Certificate',
        'file' => UploadedFile instance
    ],
    [
        'name' => 'License',
        'file' => UploadedFile instance
    ]
]


Stored in Database (JSON):
──────────────────────────

[
    {
        "name": "Resume",
        "path": "employees/5/documents/resume.pdf",
        "original_name": "resume.pdf",
        "uploaded_at": "2025-12-23 10:30:45"
    },
    {
        "name": "Degree Certificate",
        "path": "employees/5/documents/cert.pdf",
        "original_name": "certificate.pdf",
        "uploaded_at": "2025-12-23 10:31:00"
    },
    {
        "name": "License",
        "path": "employees/5/documents/license.jpg",
        "original_name": "license.jpg",
        "uploaded_at": "2025-12-23 10:31:15"
    }
]
```

---

## Authorization Flow

```
Request → Check Token
           ├─ Valid?
           ├─ Not expired?
           └─ User exists?
                  │
                  ├─ NO ──► 401 Unauthorized
                  │
                  └─ YES ──► Get User Role
                             │
                             ├─ Company Admin? ✅
                             ├─ HR? ✅
                             ├─ Employee? ❌ → 403 Forbidden
                             └─ Super Admin? ✅
                                    │
                                    └─ Proceed with operation
```

---

## Employee Status Lifecycle

```
           ┌─────────────────────────────────────┐
           │   EMPLOYEE ONBOARDED                │
           │   Status: Active                    │
           └──────────────┬──────────────────────┘
                          │
                ┌─────────┴──────────┐
                │                    │
           ┌────▼──────────┐  ┌─────▼────────────┐
           │ Update Status │  │   Assign to      │
           │ to "On Leave" │  │   Department     │
           └────┬──────────┘  └──────────────────┘
                │
           ┌────▼─────────────────┐
           │ Return to Work       │
           │ Status: Active       │
           └────┬─────────────────┘
                │
           ┌────▼──────────────────────┐
           │ Employee Resignation      │
           │ Status: Terminated        │
           │ Or Delete Record          │
           └───────────────────────────┘
```

---

## Error Codes Reference

```
✅ 200 OK             - Request successful
✅ 201 Created        - Employee created successfully
❌ 400 Bad Request    - Invalid data format
❌ 401 Unauthorized   - Token missing or invalid
❌ 403 Forbidden      - User lacks permissions
❌ 404 Not Found      - Employee not found
❌ 422 Unprocessable  - Validation failed
❌ 500 Server Error   - Internal server error
```

---

## File Storage Organization

```
storage/app/public/
└── employees/
    ├── 1/                           (user_id)
    │   ├── photos/
    │   │   └── photo.jpg
    │   └── documents/
    │       ├── resume.pdf
    │       ├── certificate.pdf
    │       └── license.jpg
    │
    ├── 2/
    │   ├── photos/
    │   │   └── avatar.jpg
    │   └── documents/
    │       └── resume.pdf
    │
    └── N/
        ├── photos/
        └── documents/


Accessible URLs:
http://backend.bheemchand.com/storage/employees/1/photos/photo.jpg
http://backend.bheemchand.com/storage/employees/1/documents/resume.pdf
```

---

## Request Validation Flow

```
FormData Received
       │
       ├─► firstName (required, string, max 255)
       │   └─ ❌ Missing? → Error
       │   └─ ✅ Valid? → Continue
       │
       ├─► lastName (required, string, max 255)
       │   └─ ❌ Missing? → Error
       │   └─ ✅ Valid? → Continue
       │
       ├─► email (required, email, unique)
       │   └─ ❌ Missing? → Error
       │   └─ ❌ Invalid format? → Error
       │   └─ ❌ Already exists? → Error
       │   └─ ✅ Valid? → Continue
       │
       ├─► position (required, string, max 255)
       │   └─ ❌ Missing? → Error
       │   └─ ✅ Valid? → Continue
       │
       ├─► department (required, string, max 255)
       │   └─ ❌ Missing? → Error
       │   └─ ✅ Valid? → Continue
       │
       ├─► employeeId (required, unique)
       │   └─ ❌ Missing? → Error
       │   └─ ❌ Already exists? → Error
       │   └─ ✅ Valid? → Continue
       │
       ├─► company_id (required, exists in companies)
       │   └─ ❌ Missing? → Error
       │   └─ ❌ Doesn't exist? → Error
       │   └─ ✅ Valid? → Continue
       │
       ├─► photo (optional, image, jpeg|png|gif, max 5MB)
       │   └─ ❌ Not image? → Error
       │   └─ ❌ Size > 5MB? → Error
       │   └─ ✅ Valid or missing? → Continue
       │
       └─► documents (optional, array of files)
           └─ ❌ Not array? → Error
           └─ ❌ File > 5MB? → Error
           └─ ❌ Invalid type? → Error
           └─ ✅ Valid or missing? → Continue


           ┌─────────────────────┐
           │ ALL VALIDATIONS OK? │
           └──────────┬──────────┘
                      │
           ┌──────────┴──────────┐
           │                     │
           ▼ NO              YES  ▼
      Return 422              Create
      Validation            Employee
      Errors                    │
                                ▼
                           Return 201
                           Created
```

---

## Data Model Relationships

```
┌─────────────────────────┐
│     User                │
├─────────────────────────┤
│ id (PK)                 │
│ name                    │
│ email (unique)          │
│ password (hashed)       │
│ company_id (FK)         │───────────┐
│ role_id (FK)            │───────────┼──────────┐
│ phone                   │           │          │
│ is_active               │           │          │
│ created_at              │           │          │
│ updated_at              │           │          │
└─────────────────────────┘           │          │
         │                            │          │
         │ (1:1)                      │          │
         │                            │          │
         ▼                            │          │
┌──────────────────────────────────┐ │          │
│   EmployeeProfile                │ │          │
├──────────────────────────────────┤ │          │
│ id (PK)                          │ │          │
│ user_id (FK) ◄─────────┐         │ │          │
│ company_id (FK) ◄──────┼─────────┼─┘          │
│ employee_code (unique) │         │ │          │
│ position               │         │ │          │
│ salary                 │         │ │          │
│ joining_date           │         │ │          │
│ status                 │         │ │          │
│ documents (JSON)       │         │ │          │
│ employee_photo         │         │ │          │
│ created_at             │         │ │          │
│ updated_at             │         │ │          │
└──────────────────────────────────┘ │          │
                                      │          │
                                      │          │
                ┌─────────────────────┘          │
                │                                │
                ▼                                │
        ┌──────────────┐                        │
        │  Company     │                        │
        ├──────────────┤                        │
        │ id (PK)      │                        │
        │ name         │                        │
        │ email        │                        │
        └──────────────┘                        │
                                                │
                                        ┌───────▼─────────┐
                                        │   Role          │
                                        ├─────────────────┤
                                        │ id (PK)         │
                                        │ name            │
                                        │ slug            │
                                        │ created_at      │
                                        │ updated_at      │
                                        └─────────────────┘
```

---

## Complete Request Example

```javascript
// Frontend Code
const createEmployee = async () => {
    const formData = new FormData();

    // Basic Info
    formData.append("firstName", "John");
    formData.append("lastName", "Doe");
    formData.append("email", "john@example.com");
    formData.append("phone", "+1234567890");

    // Address
    formData.append("street", "123 Main St");
    formData.append("city", "New York");
    formData.append("state", "NY");
    formData.append("zipCode", "10001");
    formData.append("country", "USA");

    // Professional
    formData.append("employeeId", "EMP001");
    formData.append("department", "Engineering");
    formData.append("position", "Senior Developer");
    formData.append("salary", "150000");
    formData.append("joiningDate", "2025-01-15");
    formData.append("status", "Active");

    // Company
    formData.append("company_id", 1);

    // Files
    formData.append("photo", photoFile);
    formData.append("documents[0][name]", "Resume");
    formData.append("documents[0][file]", resumeFile);
    formData.append("documents[1][name]", "Certificate");
    formData.append("documents[1][file]", certFile);

    // Send
    const response = await fetch("/api/employees/onboard", {
        method: "POST",
        headers: {
            Authorization: `Bearer ${token}`,
            // Don't set Content-Type - browser will set it with boundary
        },
        body: formData,
    });

    const result = await response.json();
    console.log(result);
};
```

---

**Version:** 1.0
**Last Updated:** December 23, 2025
