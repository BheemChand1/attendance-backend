# 🔗 DATABASE RELATIONSHIPS MAP

## 📋 MODEL RELATIONSHIPS OVERVIEW

### 1️⃣ **USER Model**

```
User
├── belongsTo → Role (one-to-one)
├── belongsTo → Company (one-to-one, nullable for superadmin)
├── hasMany → Attendance (one-to-many)
└── hasOne → EmployeeProfile (one-to-one)
```

**Fields:** id, name, email, password, company_id (FK), role_id (FK), phone, is_active

---

### 2️⃣ **ROLE Model**

```
Role
└── hasMany → User (one-to-many)
```

**Fields:** id, name, slug
**Roles:** superadmin, company_admin, hr, employee

---

### 3️⃣ **COMPANY Model**

```
Company
├── hasMany → User (one-to-many)
├── hasMany → Attendance (one-to-many)
├── hasMany → EmployeeProfile (one-to-many)
├── hasMany → CompanySubscription (one-to-many)
└── hasOne → currentSubscription (latest active subscription)
```

**Fields:** id, name, email, phone, address, company_size, location, is_active

---

### 4️⃣ **ATTENDANCE Model**

```
Attendance
├── belongsTo → User (many-to-one)
└── belongsTo → Company (many-to-one)
```

**Fields:** id, company_id (FK), user_id (FK), date, check_in, check_out, status
**Constraint:** unique(user_id, date)

---

### 5️⃣ **EMPLOYEE_PROFILE Model**

```
EmployeeProfile
├── belongsTo → User (many-to-one)
└── belongsTo → Company (many-to-one)
```

**Fields:** id, user_id (FK), company_id (FK), employee_code, date_of_birth, gender, salary, department, position, joining_date, status, education info, documents
**Constraint:** unique(user_id)

---

### 6️⃣ **SUBSCRIPTION Model** (Plan Templates)

```
Subscription
├── hasMany → CompanySubscription (one-to-many)
└── hasMany → SubscriptionFeature (one-to-many)
```

**Fields:** id, name, description, price, max_employees, max_departments, storage_gb, support_level, is_active
**Plans:** Basic, Professional, Enterprise

---

### 7️⃣ **COMPANY_SUBSCRIPTION Model** (Company → Plan Assignment)

```
CompanySubscription
├── belongsTo → Company (many-to-one)
└── belongsTo → Subscription (many-to-one)
```

**Fields:** id, company_id (FK), subscription_id (FK), start_date, end_date, status, price, billing_cycle, next_billing_date, employee_count, notes
**Status:** active, cancelled, paused, expired

---

### 8️⃣ **SUBSCRIPTION_FEATURE Model**

```
SubscriptionFeature
└── belongsTo → Subscription (many-to-one)
```

**Fields:** id, subscription_id (FK), feature_key, feature_name

---

## 🗺️ RELATIONSHIP DIAGRAM

```
                    ROLE
                     ▲
                     │ 1
                     │
          has_many──┤
                     │
                    USER ─────┐
                  ▲    ▲      │
                  │    │      │ belongs_to
           has_many    │      │
                  │    │      ▼
            COMPANY ◄─┘       │
               ▲               │
               │ 1             │
         has_many              ▼
               │        EMPLOYEE_PROFILE
         ATTENDANCE◄────────┐
                           │
                    has_one/many


    SUBSCRIPTION ────────┐
         ▲               │
         │               │ 1
    has_many             │
         │               ▼
         │      COMPANY_SUBSCRIPTION
         │               │
         │               │ belongs_to
         │               ▼
         │           COMPANY
         │
    has_many
         │
         ▼
  SUBSCRIPTION_FEATURE
```

---

## 💡 RELATIONSHIP EXPLANATIONS

### **One-to-One (1:1)**

-   User → Role: Each user has ONE role
-   User → Company: Each user belongs to ONE company (null for superadmin)
-   User → EmployeeProfile: Each user has ONE detailed profile
-   CompanySubscription → Subscription: Each company subscription uses ONE plan

### **One-to-Many (1:N)**

-   Company → Users: A company has MANY users
-   Company → Attendances: A company has MANY attendance records
-   Company → EmployeeProfiles: A company has MANY employee profiles
-   Company → CompanySubscriptions: A company can have MANY subscriptions (active + history)
-   Role → Users: A role has MANY users
-   Subscription → CompanySubscriptions: A subscription plan has MANY companies using it
-   Subscription → SubscriptionFeatures: A subscription plan has MANY features

### **Many-to-One (N:1)**

-   Attendance → User: Many attendances belong to ONE user
-   Attendance → Company: Many attendances belong to ONE company
-   EmployeeProfile → User: Profiles link to users
-   CompanySubscription → Company: References company
-   CompanySubscription → Subscription: References subscription plan

---

## 🔑 KEY RELATIONSHIPS FOR OPERATIONS

### **User Authentication Flow:**

```
User (with role & company) → Role (for permissions) → Company (for context)
```

### **Attendance Tracking Flow:**

```
User → Company → Attendance (linked by user_id & company_id)
```

### **Subscription Feature Access:**

```
Company → CompanySubscription → Subscription → SubscriptionFeature
         (checks if subscription is active)
```

### **Employee Management Flow:**

```
User → EmployeeProfile (extended info + employee_code)
    → Company (workplace)
    → Attendance (track work hours)
```

---

## ✅ RELATIONSHIP SUMMARY TABLE

| Model               | Relationship | Target              | Type | Required        |
| ------------------- | ------------ | ------------------- | ---- | --------------- |
| User                | belongsTo    | Role                | 1:1  | Yes             |
| User                | belongsTo    | Company             | 1:1  | No (SuperAdmin) |
| User                | hasMany      | Attendance          | 1:N  | Yes             |
| User                | hasOne       | EmployeeProfile     | 1:1  | Yes             |
| Company             | hasMany      | User                | 1:N  | Yes             |
| Company             | hasMany      | Attendance          | 1:N  | Yes             |
| Company             | hasMany      | EmployeeProfile     | 1:N  | Yes             |
| Company             | hasMany      | CompanySubscription | 1:N  | Yes             |
| Attendance          | belongsTo    | User                | N:1  | Yes             |
| Attendance          | belongsTo    | Company             | N:1  | Yes             |
| EmployeeProfile     | belongsTo    | User                | N:1  | Yes             |
| EmployeeProfile     | belongsTo    | Company             | N:1  | Yes             |
| Subscription        | hasMany      | CompanySubscription | 1:N  | Yes             |
| Subscription        | hasMany      | SubscriptionFeature | 1:N  | Yes             |
| CompanySubscription | belongsTo    | Company             | N:1  | Yes             |
| CompanySubscription | belongsTo    | Subscription        | N:1  | Yes             |
| SubscriptionFeature | belongsTo    | Subscription        | N:1  | Yes             |
