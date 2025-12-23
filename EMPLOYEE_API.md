# Employee Management API Documentation

## Overview

This API handles employee onboarding and management. Only HR and Company Admin roles can create employees.

## Prerequisites

1. Make sure you have an "Employee" role in your database
2. Ensure the storage link is configured: `php artisan storage:link`
3. Configure `.env` file with proper file storage settings

## Database Setup

### Required Role

Ensure the following role exists in your `roles` table:

```sql
INSERT INTO roles (name, slug, created_at, updated_at)
VALUES ('Employee', 'employee', NOW(), NOW());
```

### Required Roles for HR Operations

-   'HR' (slug: 'hr')
-   'Company Admin' (slug: 'company_admin')

## API Endpoints

### 1. Onboard New Employee

**Endpoint:** `POST /api/employees/onboard`

**Authentication:** Required (Bearer Token)

**Authorization:** HR or Company Admin only

**Request Headers:**

```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body (Form Data):**

**Personal Details:**

-   `firstName` (required, string): Employee's first name
-   `lastName` (required, string): Employee's last name
-   `email` (required, email): Unique email address
-   `phone` (optional, string): Phone number
-   `dateOfBirth` (optional, date): Format: YYYY-MM-DD
-   `gender` (optional, select): Male | Female | Other
-   `photo` (optional, file): JPEG, PNG, GIF (max 5MB)

**Address Details:**

-   `street` (optional, string): Street address
-   `city` (optional, string): City name
-   `state` (optional, string): State/Province
-   `zipCode` (optional, string): Zip/Postal code
-   `country` (optional, string): Country name

**Professional Details:**

-   `employeeId` (required, string): Unique employee ID
-   `department` (required, string): Department name
-   `position` (required, string): Job position
-   `salary` (optional, number): Annual salary
-   `joiningDate` (optional, date): Format: YYYY-MM-DD
-   `manager` (optional, string): Manager name
-   `status` (optional, select): Active | Inactive | On Leave (default: Active)

**Qualifications:**

-   `qualification` (optional, select): High School | Bachelor's Degree | Master's Degree | PhD | Diploma
-   `specialization` (optional, string): Field of specialization
-   `university` (optional, string): University/Institution name
-   `graduationYear` (optional, number): Year of graduation (1900-current year)

**Company & Documents:**

-   `company_id` (required, number): Company ID
-   `documents` (optional, array of objects):
    ```
    [
      {
        "name": "Resume",
        "file": <file object>
      },
      {
        "name": "Degree Certificate",
        "file": <file object>
      }
    ]
    ```

**Response (Success - 201):**

```json
{
    "message": "Employee successfully onboarded",
    "status": true,
    "data": {
        "user_id": 5,
        "employee_id": "EMP001",
        "name": "John Doe",
        "email": "john.doe@example.com",
        "temporary_password": "random123secure",
        "profile": {
            "id": 3,
            "user_id": 5,
            "company_id": 1,
            "employee_code": "EMP001",
            "date_of_birth": "1990-05-15",
            "gender": "male",
            "employee_photo": "employees/5/photos/abc123.jpg",
            "street_address": "123 Main St",
            "city": "New York",
            "state": "NY",
            "zip_code": "10001",
            "country": "USA",
            "department": "Engineering",
            "position": "Senior Developer",
            "salary": "120000.00",
            "joining_date": "2025-01-15",
            "status": "active",
            "qualification": "bachelor's degree",
            "specialization": "Computer Science",
            "university": "MIT",
            "graduation_year": 2015,
            "documents": "[{\"name\":\"Resume\",\"path\":\"employees/5/documents/resume.pdf\",\"original_name\":\"resume.pdf\",\"uploaded_at\":\"2025-12-23 10:30:45\"}]",
            "created_at": "2025-12-23T10:30:45.000000Z",
            "updated_at": "2025-12-23T10:30:45.000000Z"
        }
    }
}
```

**Response (Validation Error - 422):**

```json
{
    "message": "Validation failed",
    "status": false,
    "errors": {
        "firstName": ["The firstName field is required."],
        "email": ["The email has already been taken."]
    }
}
```

**Response (Unauthorized - 403):**

```json
{
    "message": "Unauthorized. Only HR and Company Admin can create employees.",
    "status": false
}
```

---

### 2. Get All Employees

**Endpoint:** `GET /api/employees`

**Authentication:** Required (Bearer Token)

**Response (200):**

```json
{
    "status": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 5,
                "name": "John Doe",
                "email": "john.doe@example.com",
                "company_id": 1,
                "role_id": 4,
                "phone": "+1234567890",
                "is_active": true,
                "created_at": "2025-12-23T10:30:45.000000Z",
                "updated_at": "2025-12-23T10:30:45.000000Z",
                "employee_profile": {
                    "id": 3,
                    "user_id": 5,
                    "employee_code": "EMP001",
                    "department": "Engineering",
                    "position": "Senior Developer",
                    "status": "active"
                }
            }
        ],
        "first_page_url": "http://backend.bheemchand.com/api/employees?page=1",
        "last_page": 1,
        "per_page": 15,
        "total": 1
    }
}
```

---

### 3. Get Employee Details

**Endpoint:** `GET /api/employees/{userId}`

**Authentication:** Required (Bearer Token)

**Parameters:**

-   `userId` (required, integer): User ID of the employee

**Response (200):**

```json
{
    "status": true,
    "data": {
        "user": {
            "id": 5,
            "name": "John Doe",
            "email": "john.doe@example.com",
            "phone": "+1234567890",
            "is_active": true
        },
        "profile": {
            "id": 3,
            "user_id": 5,
            "company_id": 1,
            "employee_code": "EMP001",
            "department": "Engineering",
            "position": "Senior Developer",
            "documents": "[...]"
        }
    }
}
```

---

### 4. Update Employee Profile

**Endpoint:** `PUT /api/employees/{userId}`

**Authentication:** Required (Bearer Token)

**Authorization:** HR or Company Admin only

**Parameters:**

-   `userId` (required, integer): User ID of the employee

**Request Body:** Same as onboard endpoint, but all fields are optional

**Response (200):**

```json
{
  "message": "Employee profile updated successfully",
  "status": true,
  "data": {
    "user": {...},
    "profile": {...}
  }
}
```

---

### 5. Delete Employee

**Endpoint:** `DELETE /api/employees/{userId}`

**Authentication:** Required (Bearer Token)

**Authorization:** Company Admin only

**Parameters:**

-   `userId` (required, integer): User ID of the employee

**Response (200):**

```json
{
    "message": "Employee deleted successfully",
    "status": true
}
```

---

## Frontend Implementation

### Using FormData for Multiple Files

```javascript
const handleSubmit = async () => {
    const formDataToSend = new FormData();

    // Add simple fields
    formDataToSend.append("firstName", formData.firstName);
    formDataToSend.append("lastName", formData.lastName);
    formDataToSend.append("company_id", reduxState.company_id);

    // Add photo
    if (formData.photo) {
        formDataToSend.append("photo", formData.photo);
    }

    // Add documents correctly - this is the key part
    if (documents && documents.length > 0) {
        documents.forEach((doc, index) => {
            formDataToSend.append(`documents[${index}][name]`, doc.name);
            formDataToSend.append(`documents[${index}][file]`, doc.file);
        });
    }

    const response = await fetch(`${apiUrl}/employees/onboard`, {
        method: "POST",
        headers: {
            Authorization: `Bearer ${reduxState.token}`,
        },
        body: formDataToSend,
    });

    const data = await response.json();
};
```

---

## File Storage

-   **Photos:** Stored in `storage/app/public/employees/{userId}/photos/`
-   **Documents:** Stored in `storage/app/public/employees/{userId}/documents/`

To access files via URL:

```
http://backend.bheemchand.com/storage/employees/{userId}/photos/{filename}
http://backend.bheemchand.com/storage/employees/{userId}/documents/{filename}
```

---

## Error Handling

### Common Errors

**400 - Bad Request**

-   Missing required fields
-   Invalid data format

**403 - Forbidden**

-   User doesn't have permission
-   Trying to access another company's employee

**404 - Not Found**

-   Employee not found
-   User not found

**422 - Validation Error**

-   Invalid email format
-   Employee ID already exists
-   File size exceeds limit

**500 - Server Error**

-   Database error
-   File storage error

---

## Testing with cURL

```bash
# Onboard employee
curl -X POST http://backend.bheemchand.com/api/employees/onboard \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "firstName=John" \
  -F "lastName=Doe" \
  -F "email=john@example.com" \
  -F "position=Developer" \
  -F "department=Engineering" \
  -F "employeeId=EMP001" \
  -F "company_id=1" \
  -F "photo=@/path/to/photo.jpg" \
  -F "documents[0][name]=Resume" \
  -F "documents[0][file]=@/path/to/resume.pdf"

# Get all employees
curl -X GET http://backend.bheemchand.com/api/employees \
  -H "Authorization: Bearer YOUR_TOKEN"

# Get specific employee
curl -X GET http://backend.bheemchand.com/api/employees/5 \
  -H "Authorization: Bearer YOUR_TOKEN"

# Update employee
curl -X PUT http://backend.bheemchand.com/api/employees/5 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "position=Senior Developer" \
  -F "salary=120000"

# Delete employee
curl -X DELETE http://backend.bheemchand.com/api/employees/5 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## Notes

1. **Temporary Password:** The temporary password is returned in the onboard response. Send it to the employee via email.
2. **Documents as JSON:** Documents are stored as a JSON array in the database with paths and metadata.
3. **File Cleanup:** When an employee is deleted, all associated files are automatically deleted.
4. **Authorization:** Always verify user role and company_id for security.
