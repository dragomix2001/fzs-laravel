# FZS Laravel API Documentation

## Base URL
```
http://localhost/api/v1
```

## Authentication

All protected endpoints require Bearer token authentication.

### Login
```
POST /api/v1/auth/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com",
        "role": "admin"
    },
    "token": "1|abc123...",
    "message": "Успешна пријава"
}
```

### Logout
```
POST /api/v1/auth/logout
Authorization: Bearer {token}
```

---

## Endpoints

### Auth

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/auth/login` | Login user | No |
| POST | `/auth/logout` | Logout user | Yes |
| GET | `/auth/user` | Get current user | Yes |
| PUT | `/auth/profile` | Update profile | Yes |
| POST | `/auth/change-password` | Change password | Yes |

### Obaveštenja

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/obavestenja` | List all active obaveštenja | Yes |
| GET | `/obavestenja/{id}` | Get single obaveštenje | Yes |
| GET | `/obavestenja/javna` | List public obaveštenja | No |

**Obaveštenje Object:**
```json
{
    "id": 1,
    "naslov": "Ispitni rok",
    "sadrzaj": "Ispiti počinju...",
    "tip": "ispit",
    "aktivan": true,
    "datum_objave": "2024-01-15",
    "profesor_id": 1
}
```

### Student

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/student/profile` | Get student profile | Yes |
| GET | `/student/ispiti` | Get passed exams | Yes |
| GET | `/student/prijave` | Get exam registrations | Yes |
| GET | `/student/upis` | Get enrollment history | Yes |
| GET | `/student/stats` | Get student statistics | Yes |

**Student Stats Response:**
```json
{
    "data": {
        "polozeni_ispiti": 15,
        "prosek": 8.5,
        "espb": 180
    }
}
```

### Kandidati (CRUD)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/kandidati` | List all kandidati | Yes |
| GET | `/kandidati/{id}` | Get kandidat | Yes |
| POST | `/kandidati` | Create kandidat | Yes |
| PUT | `/kandidati/{id}` | Update kandidat | Yes |
| DELETE | `/kandidati/{id}` | Delete kandidat | Yes |

### Ispiti

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/ispiti` | List all predmeti | Yes |
| GET | `/ispiti/{id}` | Get predmet | Yes |
| POST | `/ispiti` | Create predmet | Yes |
| PUT | `/ispiti/{id}` | Update predmet | Yes |
| DELETE | `/ispiti/{id}` | Delete predmet | Yes |

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
    "error": "Unauthorized"
}
```

### 404 Not Found
```json
{
    "message": "Resource not found"
}
```

### 422 Validation Error
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email field is required."]
    }
}
```

---

## User Roles

| Role | Description |
|------|-------------|
| `admin` | Full access to all features |
| `professor` | Can manage exams,raspored, obaveštenja |
| `student` | Can view obaveštenja and own data |

---

## Mobile App Integration

1. User opens mobile app
2. User enters email and password
3. App calls `/api/v1/auth/login`
4. Server returns user data and token
5. App stores token securely
6. App includes token in header: `Authorization: Bearer {token}`
7. All subsequent requests include the token
