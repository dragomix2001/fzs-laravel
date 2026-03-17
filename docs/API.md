# FZS Laravel API Documentation

Base URL: `http://localhost:8000/api/v1`

## Authentication

All protected routes require a Bearer token. Obtain token via login endpoint.

```
Authorization: Bearer <your-token>
```

---

## Endpoints

### Authentication

#### POST /api/v1/auth/login
Login with credentials.

**Request:**
```json
{
    "email": "user@example.com",
    "password": "password"
}
```

**Response:**
```json
{
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "Bearer",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com"
    }
}
```

#### POST /api/v1/auth/logout
Logout (invalidate token). Requires authentication.

**Response:**
```json
{
    "message": "Successfully logged out"
}
```

#### GET /api/v1/auth/user
Get current authenticated user. Requires authentication.

**Response:**
```json
{
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com"
}
```

#### PUT /api/v1/auth/profile
Update user profile. Requires authentication.

**Request:**
```json
{
    "name": "John Doe",
    "email": "newemail@example.com"
}
```

**Response:**
```json
{
    "message": "Profile updated successfully",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "newemail@example.com"
    }
}
```

#### POST /api/v1/auth/change-password
Change user password. Requires authentication.

**Request:**
```json
{
    "current_password": "oldpassword",
    "password": "newpassword",
    "password_confirmation": "newpassword"
}
```

**Response:**
```json
{
    "message": "Password changed successfully"
}
```

---

### Kandidati (Candidates)

#### GET /api/v1/kandidati
List all candidates. Requires authentication.

**Response:**
```json
[
    {
        "id": 1,
        "ime": "John",
        "prezime": "Doe",
        "email": "john@example.com",
        "brojIndeksa": "12345"
    }
]
```

#### POST /api/v1/kandidati
Create a new candidate. Requires authentication.

**Request:**
```json
{
    "ime": "John",
    "prezime": "Doe",
    "email": "john@example.com",
    "jmbg": "1234567890123",
    "datumRodjenja": "2000-01-01"
}
```

**Response:**
```json
{
    "id": 1,
    "ime": "John",
    "prezime": "Doe",
    "email": "john@example.com"
}
```

#### GET /api/v1/kandidati/{id}
Get a specific candidate. Requires authentication.

#### PUT /api/v1/kandidati/{id}
Update a candidate. Requires authentication.

#### DELETE /api/v1/kandidati/{id}
Delete a candidate. Requires authentication.

---

### Ispiti (Exams)

#### GET /api/v1/ispiti
List all exams. Requires authentication.

**Response:**
```json
[
    {
        "id": 1,
        "naziv": "Mathematics",
        "predmet_id": 1
    }
]
```

#### POST /api/v1/ispiti
Create a new exam. Requires authentication.

#### GET /api/v1/ispiti/{id}
Get a specific exam. Requires authentication.

#### PUT /api/v1/ispiti/{id}
Update an exam. Requires authentication.

#### DELETE /api/v1/ispiti/{id}
Delete an exam. Requires authentication.

---

### Obavestenja (Notices)

#### GET /api/v1/obavestenja/javna
Get public notices. No authentication required.

**Response:**
```json
[
    {
        "id": 1,
        "naslov": "Notice Title",
        "sadrzaj": "Notice content",
        "datum": "2024-01-01"
    }
]
```

#### GET /api/v1/obavestenja
List all notices. Requires authentication.

#### GET /api/v1/obavestenja/{id}
Get a specific notice. Requires authentication.

---

### Student

All student endpoints require authentication.

#### GET /api/v1/student/profile
Get student profile.

**Response:**
```json
{
    "id": 1,
    "ime": "John",
    "prezime": "Doe",
    "brojIndeksa": "12345",
    "godinaStudija": 2
}
```

#### GET /api/v1/student/ispiti
Get student's passed exams.

#### GET /api/v1/student/prijave
Get student's exam registrations.

#### GET /api/v1/student/upis
Get student's enrollment information.

#### GET /api/v1/student/stats
Get student statistics.

---

### Raspored (Schedule)

#### GET /api/v1/raspored
Get full schedule. No authentication required.

#### GET /api/v1/raspored/today
Get today's schedule. No authentication required.

#### GET /api/v1/raspored/{id}
Get specific schedule item. No authentication required.

---

### Aktivnost (Activity)

#### GET /api/v1/aktivnost
List all activities. No authentication required.

#### GET /api/v1/aktivnost/today
Get today's activities. No authentication required.

#### GET /api/v1/aktivnost/{id}
Get specific activity. No authentication required.

#### GET /api/v1/aktivnost/moje
Get my activities. Requires authentication.

---

## Error Responses

### 401 Unauthorized
```json
{
    "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
    "message": "This action is unauthorized."
}
```

### 404 Not Found
```json
{
    "message": "Resource not found."
}
```

### 422 Validation Error
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": [
            "The email field is required."
        ]
    }
}
```

---

## Rate Limiting

The API is rate limited to 60 requests per minute per user.

---

## Versioning

The API uses URL versioning. Current version is `v1`.

Example: `/api/v1/kandidati`
