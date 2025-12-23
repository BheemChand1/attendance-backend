# Frontend Integration Guide - Employee Onboarding API

## Quick Reference

### API Base URL

```
http://backend.bheemchand.com/api
```

### Authentication

All employee endpoints require Bearer token authentication:

```javascript
headers: {
  'Authorization': `Bearer ${reduxState.token}`,
  'Content-Type': 'multipart/form-data' // For file uploads
}
```

## Endpoint Quick Links

### 1. Onboard Employee

```
POST /employees/onboard
Authorization: Required (HR, Company Admin)
```

### 2. Get All Employees

```
GET /employees
Authorization: Required
```

### 3. Get Employee Details

```
GET /employees/{userId}
Authorization: Required
```

### 4. Update Employee

```
PUT /employees/{userId}
Authorization: Required (HR, Company Admin)
```

### 5. Delete Employee

```
DELETE /employees/{userId}
Authorization: Required (Company Admin)
```

---

## Frontend Implementation Example

### React Hooks Setup

```javascript
import React, { useState } from "react";
import { useSelector } from "react-redux";
import { useToast } from "@chakra-ui/react";

export function EmployeeOnboarding() {
    const toast = useToast();
    const reduxState = useSelector((state) => state);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [documents, setDocuments] = useState([]);
    const [formData, setFormData] = useState({
        firstName: "",
        lastName: "",
        email: "",
        position: "",
        department: "",
        employeeId: "",
        // ... other fields
    });

    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setFormData({ ...formData, [name]: value });
    };

    const addDocumentRow = () => {
        setDocuments([
            ...documents,
            { id: Date.now(), name: "", file: null, fileName: "" },
        ]);
    };

    const removeDocumentRow = (id) => {
        setDocuments(documents.filter((doc) => doc.id !== id));
    };

    const handleSubmit = async () => {
        // Validation
        if (
            !formData.firstName ||
            !formData.lastName ||
            !formData.email ||
            !formData.position
        ) {
            toast({
                title: "Validation Error",
                description: "Please fill in all required fields",
                status: "error",
                duration: 3000,
                isClosable: true,
            });
            return;
        }

        setIsSubmitting(true);

        try {
            // Create FormData object
            const formDataToSend = new FormData();

            // Add personal details
            formDataToSend.append("firstName", formData.firstName);
            formDataToSend.append("lastName", formData.lastName);
            formDataToSend.append("email", formData.email);
            formDataToSend.append("phone", formData.phone || "");
            formDataToSend.append("dateOfBirth", formData.dateOfBirth || "");
            formDataToSend.append("gender", formData.gender || "");

            // Add address details
            formDataToSend.append("street", formData.street || "");
            formDataToSend.append("city", formData.city || "");
            formDataToSend.append("state", formData.state || "");
            formDataToSend.append("zipCode", formData.zipCode || "");
            formDataToSend.append("country", formData.country || "");

            // Add professional details
            formDataToSend.append("employeeId", formData.employeeId);
            formDataToSend.append("department", formData.department);
            formDataToSend.append("position", formData.position);
            formDataToSend.append("salary", formData.salary || "");
            formDataToSend.append("joiningDate", formData.joiningDate || "");
            formDataToSend.append("manager", formData.manager || "");
            formDataToSend.append("status", formData.status || "Active");

            // Add qualifications
            formDataToSend.append(
                "qualification",
                formData.qualification || ""
            );
            formDataToSend.append(
                "specialization",
                formData.specialization || ""
            );
            formDataToSend.append("university", formData.university || "");
            formDataToSend.append(
                "graduationYear",
                formData.graduationYear || ""
            );

            // Add company info
            formDataToSend.append("company_id", reduxState.company_id);

            // Add documents - IMPORTANT: Correct format for array
            if (documents.length > 0) {
                documents.forEach((doc, index) => {
                    formDataToSend.append(
                        `documents[${index}][name]`,
                        doc.name
                    );
                    formDataToSend.append(
                        `documents[${index}][file]`,
                        doc.file
                    );
                });
            }

            // Add photo if exists
            if (formData.photo) {
                formDataToSend.append("photo", formData.photo);
            }

            // API call
            const apiUrl =
                process.env.REACT_APP_API_URL || "http://localhost:3000/api";
            const response = await fetch(`${apiUrl}/employees/onboard`, {
                method: "POST",
                headers: {
                    Authorization: `Bearer ${reduxState.token}`,
                },
                body: formDataToSend,
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || "Failed to onboard employee");
            }

            // Show success message
            toast({
                title: "Success",
                description: "Employee successfully onboarded!",
                status: "success",
                duration: 3000,
                isClosable: true,
            });

            // Reset form
            setFormData({
                firstName: "",
                lastName: "",
                email: "",
                // ... reset all fields
            });
            setDocuments([]);

            // Redirect
            navigate("/admin/employee-management");
        } catch (error) {
            console.error("Error:", error);
            toast({
                title: "Error",
                description: error.message || "Failed to onboard employee",
                status: "error",
                duration: 3000,
                isClosable: true,
            });
        } finally {
            setIsSubmitting(false);
        }
    };

    return <div>{/* Your form JSX here */}</div>;
}
```

### Fetching Employees

```javascript
const fetchEmployees = async () => {
    const response = await fetch(`${apiUrl}/employees`, {
        method: "GET",
        headers: {
            Authorization: `Bearer ${reduxState.token}`,
        },
    });

    const data = await response.json();
    if (data.status) {
        setEmployees(data.data.data); // data.data.data for pagination
    }
};
```

### Updating Employee

```javascript
const updateEmployee = async (userId) => {
    const formDataToSend = new FormData();
    formDataToSend.append("position", "Senior Developer");
    formDataToSend.append("salary", "150000");

    const response = await fetch(`${apiUrl}/employees/${userId}`, {
        method: "PUT",
        headers: {
            Authorization: `Bearer ${reduxState.token}`,
        },
        body: formDataToSend,
    });

    const data = await response.json();
};
```

### Deleting Employee

```javascript
const deleteEmployee = async (userId) => {
    const response = await fetch(`${apiUrl}/employees/${userId}`, {
        method: "DELETE",
        headers: {
            Authorization: `Bearer ${reduxState.token}`,
        },
    });

    const data = await response.json();
    if (data.status) {
        toast({
            title: "Success",
            description: "Employee deleted successfully",
            status: "success",
        });
    }
};
```

---

## Error Handling

### Handle Different Response Codes

```javascript
const handleApiResponse = async (response) => {
    const data = await response.json();

    if (response.status === 401) {
        // Token expired, redirect to login
        redirectToLogin();
    } else if (response.status === 403) {
        // Unauthorized - insufficient permissions
        toast({
            title: "Access Denied",
            description: "You do not have permission to perform this action",
            status: "error",
        });
    } else if (response.status === 422) {
        // Validation errors
        console.log("Validation Errors:", data.errors);
        displayValidationErrors(data.errors);
    } else if (response.status === 500) {
        // Server error
        toast({
            title: "Server Error",
            description: "An error occurred on the server",
            status: "error",
        });
    }

    return data;
};
```

---

## Document Upload Tips

### Correct Format for Multiple Documents

```javascript
// ✅ CORRECT - Using indexed array notation
documents.forEach((doc, index) => {
    formDataToSend.append(`documents[${index}][name]`, doc.name);
    formDataToSend.append(`documents[${index}][file]`, doc.file);
});

// ❌ WRONG - Using .append() multiple times with same key
documents.forEach((doc) => {
    formDataToSend.append("documents", doc.name);
    formDataToSend.append("documents", doc.file);
});
```

### Allowed File Types

-   PDF: `.pdf`
-   Word: `.doc`, `.docx`
-   Excel: `.xls`, `.xlsx`
-   Images: `.jpg`, `.jpeg`, `.png`
-   Max file size: 5MB per file

---

## Request/Response Examples

### Success Response (201)

```json
{
    "message": "Employee successfully onboarded",
    "status": true,
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
            "department": "Engineering",
            "position": "Developer"
            // ... more fields
        }
    }
}
```

### Validation Error Response (422)

```json
{
    "message": "Validation failed",
    "status": false,
    "errors": {
        "email": ["The email has already been taken."],
        "position": ["The position field is required."]
    }
}
```

### Unauthorized Response (403)

```json
{
    "message": "Unauthorized. Only HR and Company Admin can create employees.",
    "status": false
}
```

---

## Environment Variables

Create a `.env.local` file in your React project:

```
REACT_APP_API_URL=http://backend.bheemchand.com/api
REACT_APP_STORAGE_URL=http://backend.bheemchand.com/storage
```

Then access in code:

```javascript
const apiUrl = process.env.REACT_APP_API_URL;
const storageUrl = process.env.REACT_APP_STORAGE_URL;
```

---

## Common Issues & Solutions

### Issue: FormData not being sent correctly

```javascript
// Make sure headers do NOT include Content-Type
// Let the browser set it automatically with boundary
const response = await fetch(url, {
    method: "POST",
    headers: {
        Authorization: `Bearer ${token}`,
        // ❌ Don't set Content-Type manually
    },
    body: formData, // FormData object
});
```

### Issue: Documents not uploading

```javascript
// Ensure documents have both name and file
const isValid = documents.every((doc) => doc.name && doc.file);
if (!isValid) {
    toast({
        title: "Error",
        description: "All documents must have a name and file",
        status: "error",
    });
    return;
}
```

### Issue: CORS errors

-   Check that backend allows your frontend domain
-   Ensure backend has proper CORS middleware configured

---

## Testing with Postman

1. **Set up environment variable:**

    - Variable: `token`
    - Value: (paste your auth token)

2. **Create onboard request:**
    - Method: POST
    - URL: `{{baseUrl}}/employees/onboard`
    - Headers: `Authorization: Bearer {{token}}`
    - Body: form-data
        - firstName: John
        - lastName: Doe
        - email: john@example.com
        - position: Developer
        - department: Engineering
        - employeeId: EMP001
        - company_id: 1
        - documents[0][name]: Resume
        - documents[0][file]: (select file)

---

## Performance Optimization

### Lazy Load Employee List

```javascript
const [page, setPage] = useState(1);

const loadMoreEmployees = async () => {
    const response = await fetch(`${apiUrl}/employees?page=${page}`, {
        headers: { Authorization: `Bearer ${token}` },
    });
    const data = await response.json();
    setEmployees([...employees, ...data.data.data]);
    setPage(page + 1);
};
```

### Cancel Upload on Unmount

```javascript
const abortController = new AbortController();

useEffect(() => {
    return () => abortController.abort();
}, []);

// In fetch:
fetch(url, {
    signal: abortController.signal,
    // ... other options
});
```

---

## Documentation Links

-   See `EMPLOYEE_API.md` for complete API reference
-   See `EMPLOYEE_SETUP.md` for backend setup instructions
