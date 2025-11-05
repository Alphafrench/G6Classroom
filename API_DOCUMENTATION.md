# API Documentation - Google Classroom Clone

## Table of Contents
1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Response Format](#response-format)
4. [Error Handling](#error-handling)
5. [Rate Limiting](#rate-limiting)
6. [User Management API](#user-management-api)
7. [Authentication API](#authentication-api)
8. [Course Management API](#course-management-api)
9. [Assignment API](#assignment-api)
10. [Attendance API](#attendance-api)
11. [File Upload API](#file-upload-api)
12. [Reporting API](#reporting-api)

## Overview

The Google Classroom Clone provides a RESTful API for all core functionality. The API follows REST principles and returns JSON responses.

### Base URL
```
Development: http://localhost:8080/api/v1
Production: https://yourdomain.com/api/v1
```

### API Versioning
The API uses URL versioning: `/api/v1/`, `/api/v2/`, etc.

### Content Type
All API endpoints accept and return `application/json` content.

### HTTP Methods
- `GET` - Retrieve data
- `POST` - Create new resource
- `PUT` - Update existing resource
- `DELETE` - Delete resource

## Authentication

### JWT Token Authentication
Most endpoints require authentication using JWT tokens.

#### Token Format
```
Authorization: Bearer <jwt_token>
```

#### Obtaining a Token
```http
POST /api/v1/auth/login
Content-Type: application/json

{
    "username": "john_doe",
    "password": "secure_password"
}
```

#### Response
```json
{
    "success": true,
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "user": {
        "id": 123,
        "username": "john_doe",
        "email": "john@example.com",
        "role": "student"
    },
    "expires_at": "2025-11-06T22:44:46Z"
}
```

#### Token Refresh
```http
POST /api/v1/auth/refresh
Authorization: Bearer <expired_token>
```

#### Logout
```http
POST /api/v1/auth/logout
Authorization: Bearer <valid_token>
```

### Session-Based Authentication
Some endpoints use PHP sessions.

#### Session Login
```http
POST /api/v1/auth/session-login
Content-Type: application/json

{
    "username": "john_doe",
    "password": "secure_password"
}
```

### API Key Authentication (Future)
```http
X-API-Key: your_api_key_here
```

## Response Format

### Success Response
```json
{
    "success": true,
    "data": {
        // Response data
    },
    "message": "Operation completed successfully",
    "pagination": {
        "current_page": 1,
        "total_pages": 5,
        "per_page": 20,
        "total_records": 95,
        "has_next": true,
        "has_prev": false
    },
    "timestamp": "2025-11-05T22:44:46Z"
}
```

### Error Response
```json
{
    "success": false,
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "The given data was invalid",
        "details": {
            "email": ["The email field is required"],
            "password": ["The password must be at least 8 characters"]
        }
    },
    "timestamp": "2025-11-05T22:44:46Z"
}
```

## Error Handling

### HTTP Status Codes
- `200` - OK
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `409` - Conflict
- `422` - Unprocessable Entity
- `429` - Too Many Requests
- `500` - Internal Server Error

### Error Codes
- `AUTHENTICATION_FAILED` - Invalid credentials
- `TOKEN_EXPIRED` - JWT token has expired
- `ACCESS_DENIED` - Insufficient permissions
- `VALIDATION_ERROR` - Input validation failed
- `RESOURCE_NOT_FOUND` - Requested resource doesn't exist
- `DUPLICATE_ENTRY` - Resource already exists
- `RATE_LIMIT_EXCEEDED` - Too many requests
- `SERVER_ERROR` - Internal server error

### Error Response Examples

#### Validation Error (422)
```json
{
    "success": false,
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "The given data was invalid",
        "details": {
            "email": ["The email must be a valid email address"],
            "first_name": ["The first name field is required"]
        }
    }
}
```

#### Unauthorized Error (401)
```json
{
    "success": false,
    "error": {
        "code": "AUTHENTICATION_FAILED",
        "message": "Invalid authentication credentials"
    }
}
```

#### Rate Limit Error (429)
```json
{
    "success": false,
    "error": {
        "code": "RATE_LIMIT_EXCEEDED",
        "message": "Too many requests. Try again later",
        "retry_after": 60
    }
}
```

## Rate Limiting

### Rate Limits
- **Authentication**: 5 requests per minute
- **General API**: 100 requests per hour
- **File Upload**: 10 requests per hour
- **Export**: 5 requests per hour

### Rate Limit Headers
```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1641234567
```

### Rate Limit Response
```json
{
    "success": false,
    "error": {
        "code": "RATE_LIMIT_EXCEEDED",
        "message": "Too many requests",
        "retry_after": 3600
    }
}
```

## User Management API

### Get All Users
```http
GET /api/v1/users
Authorization: Bearer <token>
```

#### Query Parameters
- `page` (int) - Page number (default: 1)
- `per_page` (int) - Records per page (default: 20, max: 100)
- `role` (string) - Filter by role (admin, teacher, student, parent)
- `status` (string) - Filter by status (active, inactive, suspended)
- `search` (string) - Search term for name or email
- `sort_by` (string) - Sort field (first_name, last_name, email, created_at)
- `sort_order` (string) - Sort order (ASC, DESC)

#### Response
```json
{
    "success": true,
    "data": {
        "users": [
            {
                "id": 123,
                "username": "john_doe",
                "email": "john@example.com",
                "first_name": "John",
                "last_name": "Doe",
                "role": "student",
                "status": "active",
                "created_at": "2025-01-15T10:30:00Z",
                "last_login": "2025-11-05T22:44:46Z"
            }
        ]
    },
    "pagination": {
        "current_page": 1,
        "total_pages": 5,
        "per_page": 20,
        "total_records": 95,
        "has_next": true,
        "has_prev": false
    }
}
```

### Get User by ID
```http
GET /api/v1/users/{user_id}
Authorization: Bearer <token>
```

#### Response
```json
{
    "success": true,
    "data": {
        "id": 123,
        "username": "john_doe",
        "email": "john@example.com",
        "first_name": "John",
        "last_name": "Doe",
        "role": "student",
        "status": "active",
        "phone": "+1234567890",
        "date_of_birth": "2000-05-15",
        "created_at": "2025-01-15T10:30:00Z",
        "updated_at": "2025-11-05T22:44:46Z",
        "last_login": "2025-11-05T22:44:46Z"
    }
}
```

### Create User
```http
POST /api/v1/users
Authorization: Bearer <token>
Content-Type: application/json

{
    "username": "jane_doe",
    "email": "jane@example.com",
    "password": "secure_password",
    "first_name": "Jane",
    "last_name": "Doe",
    "role": "teacher",
    "phone": "+1234567890",
    "date_of_birth": "1985-03-20"
}
```

#### Response (201)
```json
{
    "success": true,
    "data": {
        "user_id": 124,
        "username": "jane_doe",
        "email": "jane@example.com"
    },
    "message": "User created successfully"
}
```

### Update User
```http
PUT /api/v1/users/{user_id}
Authorization: Bearer <token>
Content-Type: application/json

{
    "first_name": "Jane",
    "last_name": "Smith",
    "phone": "+1234567890"
}
```

#### Response (200)
```json
{
    "success": true,
    "message": "User updated successfully",
    "data": {
        "user_id": 124,
        "updated_fields": ["first_name", "last_name", "phone"]
    }
}
```

### Delete User
```http
DELETE /api/v1/users/{user_id}
Authorization: Bearer <token>
```

#### Response (200)
```json
{
    "success": true,
    "message": "User deleted successfully"
}
```

### Change Password
```http
PUT /api/v1/users/{user_id}/password
Authorization: Bearer <token>
Content-Type: application/json

{
    "current_password": "old_password",
    "new_password": "new_secure_password"
}
```

### Change User Status
```http
PUT /api/v1/users/{user_id}/status
Authorization: Bearer <token>
Content-Type: application/json

{
    "status": "suspended"
}
```

### Get User Statistics
```http
GET /api/v1/users/statistics
Authorization: Bearer <token>
```

#### Response
```json
{
    "success": true,
    "data": {
        "total_users": 150,
        "by_status": {
            "active": 140,
            "inactive": 8,
            "suspended": 2
        },
        "by_role": {
            "admin": 3,
            "teacher": 25,
            "student": 120,
            "parent": 2
        },
        "recent_registrations": 12,
        "active_users": 95
    }
}
```

## Authentication API

### User Login
```http
POST /api/v1/auth/login
Content-Type: application/json

{
    "username": "john_doe",
    "password": "secure_password"
}
```

#### Response (200)
```json
{
    "success": true,
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "refresh_token": "refresh_token_here",
    "user": {
        "id": 123,
        "username": "john_doe",
        "email": "john@example.com",
        "role": "student",
        "first_name": "John",
        "last_name": "Doe"
    },
    "expires_at": "2025-11-06T22:44:46Z"
}
```

### User Registration
```http
POST /api/v1/auth/register
Content-Type: application/json

{
    "username": "new_user",
    "email": "newuser@example.com",
    "password": "secure_password",
    "password_confirmation": "secure_password",
    "first_name": "New",
    "last_name": "User",
    "role": "student"
}
```

#### Response (201)
```json
{
    "success": true,
    "message": "User registered successfully",
    "user_id": 125,
    "verification_required": true
}
```

### Logout
```http
POST /api/v1/auth/logout
Authorization: Bearer <token>
```

#### Response (200)
```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

### Refresh Token
```http
POST /api/v1/auth/refresh
Content-Type: application/json

{
    "refresh_token": "refresh_token_here"
}
```

### Forgot Password
```http
POST /api/v1/auth/forgot-password
Content-Type: application/json

{
    "email": "user@example.com"
}
```

#### Response (200)
```json
{
    "success": true,
    "message": "Password reset email sent"
}
```

### Reset Password
```http
POST /api/v1/auth/reset-password
Content-Type: application/json

{
    "token": "reset_token_here",
    "password": "new_secure_password",
    "password_confirmation": "new_secure_password"
}
```

### Verify Email
```http
POST /api/v1/auth/verify-email
Content-Type: application/json

{
    "token": "verification_token_here"
}
```

### Resend Verification
```http
POST /api/v1/auth/resend-verification
Content-Type: application/json

{
    "email": "user@example.com"
}
```

## Course Management API

### Get All Courses
```http
GET /api/v1/courses
Authorization: Bearer <token>
```

#### Query Parameters
- `page` (int) - Page number
- `per_page` (int) - Records per page
- `status` (string) - Course status (active, inactive, completed)
- `teacher_id` (int) - Filter by teacher
- `search` (string) - Search in course name or code

#### Response
```json
{
    "success": true,
    "data": {
        "courses": [
            {
                "id": 45,
                "name": "Introduction to Programming",
                "code": "CS101",
                "description": "Learn the basics of programming",
                "teacher_id": 12,
                "teacher_name": "Prof. Smith",
                "status": "active",
                "student_count": 25,
                "created_at": "2025-01-15T10:30:00Z"
            }
        ]
    },
    "pagination": {
        "current_page": 1,
        "total_pages": 3,
        "per_page": 20,
        "total_records": 45
    }
}
```

### Get Course by ID
```http
GET /api/v1/courses/{course_id}
Authorization: Bearer <token>
```

#### Response
```json
{
    "success": true,
    "data": {
        "id": 45,
        "name": "Introduction to Programming",
        "code": "CS101",
        "description": "Learn the basics of programming",
        "teacher_id": 12,
        "teacher_name": "Prof. Smith",
        "status": "active",
        "room": "Room 101",
        "schedule": "MWF 10:00-11:00",
        "credits": 3,
        "created_at": "2025-01-15T10:30:00Z",
        "updated_at": "2025-11-05T22:44:46Z"
    }
}
```

### Create Course
```http
POST /api/v1/courses
Authorization: Bearer <token>
Content-Type: application/json

{
    "name": "Advanced Mathematics",
    "code": "MATH201",
    "description": "Advanced mathematical concepts",
    "teacher_id": 12,
    "room": "Room 205",
    "schedule": "TTh 14:00-15:30",
    "credits": 4
}
```

#### Response (201)
```json
{
    "success": true,
    "data": {
        "course_id": 46,
        "name": "Advanced Mathematics",
        "code": "MATH201"
    },
    "message": "Course created successfully"
}
```

### Update Course
```http
PUT /api/v1/courses/{course_id}
Authorization: Bearer <token>
Content-Type: application/json

{
    "name": "Advanced Mathematics II",
    "room": "Room 206",
    "schedule": "TTh 14:00-15:30, F 10:00-11:00"
}
```

### Delete Course
```http
DELETE /api/v1/courses/{course_id}
Authorization: Bearer <token>
```

### Enroll Student
```http
POST /api/v1/courses/{course_id}/enroll
Authorization: Bearer <token>
Content-Type: application/json

{
    "student_id": 123
}
```

#### Response (201)
```json
{
    "success": true,
    "data": {
        "enrollment_id": 789,
        "course_id": 45,
        "student_id": 123,
        "enrolled_at": "2025-11-05T22:44:46Z"
    },
    "message": "Student enrolled successfully"
}
```

### Unenroll Student
```http
DELETE /api/v1/courses/{course_id}/enroll/{student_id}
Authorization: Bearer <token>
```

### Get Course Students
```http
GET /api/v1/courses/{course_id}/students
Authorization: Bearer <token>
```

#### Response
```json
{
    "success": true,
    "data": {
        "students": [
            {
                "id": 123,
                "username": "student1",
                "first_name": "John",
                "last_name": "Doe",
                "email": "john@example.com",
                "enrolled_at": "2025-01-20T10:30:00Z"
            }
        ]
    }
}
```

### Get Teacher Courses
```http
GET /api/v1/teachers/{teacher_id}/courses
Authorization: Bearer <token>
```

## Assignment API

### Get All Assignments
```http
GET /api/v1/assignments
Authorization: Bearer <token>
```

#### Query Parameters
- `course_id` (int) - Filter by course
- `type` (string) - Filter by type (homework, quiz, exam, project)
- `status` (string) - Filter by status (draft, published, closed)
- `due_date_from` (date) - Filter by due date range
- `due_date_to` (date) - Filter by due date range

#### Response
```json
{
    "success": true,
    "data": {
        "assignments": [
            {
                "id": 67,
                "title": "Programming Assignment 1",
                "description": "Create a simple calculator",
                "course_id": 45,
                "course_name": "Introduction to Programming",
                "type": "homework",
                "points": 100,
                "due_date": "2025-11-15T23:59:59Z",
                "status": "published",
                "submission_count": 23,
                "created_at": "2025-11-01T10:30:00Z"
            }
        ]
    }
}
```

### Get Assignment by ID
```http
GET /api/v1/assignments/{assignment_id}
Authorization: Bearer <token>
```

#### Response
```json
{
    "success": true,
    "data": {
        "id": 67,
        "title": "Programming Assignment 1",
        "description": "Create a simple calculator",
        "instructions": "Build a calculator that can perform basic operations...",
        "course_id": 45,
        "course_name": "Introduction to Programming",
        "type": "homework",
        "points": 100,
        "due_date": "2025-11-15T23:59:59Z",
        "status": "published",
        "attachments": [
            {
                "id": 12,
                "filename": "assignment_details.pdf",
                "size": 1024000,
                "uploaded_at": "2025-11-01T10:30:00Z"
            }
        ],
        "created_at": "2025-11-01T10:30:00Z",
        "updated_at": "2025-11-05T22:44:46Z"
    }
}
```

### Create Assignment
```http
POST /api/v1/assignments
Authorization: Bearer <token>
Content-Type: application/json

{
    "title": "Final Project",
    "description": "Build a complete web application",
    "instructions": "Create a full-stack web application using PHP and MySQL...",
    "course_id": 45,
    "type": "project",
    "points": 200,
    "due_date": "2025-12-15T23:59:59Z",
    "status": "draft"
}
```

#### Response (201)
```json
{
    "success": true,
    "data": {
        "assignment_id": 68,
        "title": "Final Project",
        "course_id": 45
    },
    "message": "Assignment created successfully"
}
```

### Update Assignment
```http
PUT /api/v1/assignments/{assignment_id}
Authorization: Bearer <token>
Content-Type: application/json

{
    "title": "Final Project - Updated",
    "points": 250,
    "due_date": "2025-12-20T23:59:59Z",
    "status": "published"
}
```

### Delete Assignment
```http
DELETE /api/v1/assignments/{assignment_id}
Authorization: Bearer <token>
```

### Submit Assignment
```http
POST /api/v1/assignments/{assignment_id}/submit
Authorization: Bearer <token>
Content-Type: application/json

{
    "submission_text": "Here is my solution to the assignment...",
    "submission_file": "base64_encoded_file_content"
}
```

#### Response (201)
```json
{
    "success": true,
    "data": {
        "submission_id": 234,
        "assignment_id": 67,
        "student_id": 123,
        "submitted_at": "2025-11-10T15:30:00Z",
        "late_submission": false
    },
    "message": "Assignment submitted successfully"
}
```

### Get Assignment Submissions
```http
GET /api/v1/assignments/{assignment_id}/submissions
Authorization: Bearer <token>
```

#### Query Parameters
- `graded` (bool) - Filter by graded/ungraded submissions
- `late` (bool) - Filter by late submissions

#### Response
```json
{
    "success": true,
    "data": {
        "submissions": [
            {
                "id": 234,
                "student_id": 123,
                "student_name": "John Doe",
                "submitted_at": "2025-11-10T15:30:00Z",
                "late_submission": false,
                "grade": 95,
                "feedback": "Excellent work!",
                "graded_at": "2025-11-12T10:30:00Z"
            }
        ],
        "statistics": {
            "total_submissions": 25,
            "graded_submissions": 20,
            "late_submissions": 2,
            "average_grade": 87.5
        }
    }
}
```

### Grade Assignment
```http
PUT /api/v1/assignments/{assignment_id}/submissions/{submission_id}/grade
Authorization: Bearer <token>
Content-Type: application/json

{
    "grade": 95,
    "feedback": "Excellent work! Your solution is well-structured and efficient.",
    "points_earned": 95
}
```

### Get Student Submissions
```http
GET /api/v1/students/{student_id}/submissions
Authorization: Bearer <token>
```

#### Query Parameters
- `course_id` (int) - Filter by course
- `assignment_id` (int) - Filter by assignment
- `status` (string) - Filter by submission status

## Attendance API

### Check In
```http
POST /api/v1/attendance/checkin
Authorization: Bearer <token>
Content-Type: application/json

{
    "employee_id": 123,
    "timestamp": "2025-11-05T08:30:00Z"
}
```

#### Response (200)
```json
{
    "success": true,
    "data": {
        "record_id": 456,
        "employee_id": 123,
        "check_in_time": "2025-11-05T08:30:00Z",
        "status": "present"
    },
    "message": "Successfully checked in"
}
```

### Check Out
```http
POST /api/v1/attendance/checkout
Authorization: Bearer <token>
Content-Type: application/json

{
    "employee_id": 123,
    "timestamp": "2025-11-05T17:30:00Z"
}
```

#### Response (200)
```json
{
    "success": true,
    "data": {
        "record_id": 456,
        "employee_id": 123,
        "check_in_time": "2025-11-05T08:30:00Z",
        "check_out_time": "2025-11-05T17:30:00Z",
        "total_hours": 9.0,
        "status": "present"
    },
    "message": "Successfully checked out"
}
```

### Get Today's Attendance
```http
GET /api/v1/attendance/today?employee_id=123
Authorization: Bearer <token>
```

#### Response
```json
{
    "success": true,
    "data": {
        "employee_id": 123,
        "date": "2025-11-05",
        "check_in_time": "08:30:00",
        "check_out_time": null,
        "total_hours": null,
        "status": "present",
        "break_time": null
    }
}
```

### Get Attendance Records
```http
GET /api/v1/attendance/records?employee_id=123&start_date=2025-11-01&end_date=2025-11-30
Authorization: Bearer <token>
```

#### Query Parameters
- `employee_id` (int) - Employee ID (required for students, optional for teachers to see all)
- `start_date` (date) - Start date (YYYY-MM-DD)
- `end_date` (date) - End date (YYYY-MM-DD)
- `status` (string) - Filter by status (present, absent, late, half_day)

#### Response
```json
{
    "success": true,
    "data": {
        "employee_id": 123,
        "records": [
            {
                "date": "2025-11-05",
                "check_in_time": "08:30:00",
                "check_out_time": "17:30:00",
                "total_hours": 9.0,
                "break_time": 60,
                "status": "present",
                "notes": null
            }
        ],
        "summary": {
            "total_days": 5,
            "present_days": 5,
            "absent_days": 0,
            "late_days": 1,
            "total_hours": 45.0,
            "average_hours": 9.0
        }
    }
}
```

### Get Attendance Statistics
```http
GET /api/v1/attendance/stats/{employee_id}?year=2025&month=11
Authorization: Bearer <token>
```

#### Response
```json
{
    "success": true,
    "data": {
        "employee_id": 123,
        "year": 2025,
        "month": 11,
        "statistics": {
            "total_days": 22,
            "working_days": 20,
            "present_days": 19,
            "absent_days": 1,
            "late_days": 2,
            "half_days": 1,
            "total_hours": 171.0,
            "overtime_hours": 3.0,
            "attendance_rate": 95.0,
            "punctuality_rate": 90.0
        }
    }
}
```

### Bulk Check In
```http
POST /api/v1/attendance/bulk-checkin
Authorization: Bearer <token>
Content-Type: application/json

{
    "employee_ids": [123, 124, 125],
    "timestamp": "2025-11-05T08:30:00Z"
}
```

#### Response
```json
{
    "success": true,
    "data": {
        "total_processed": 3,
        "successful": 3,
        "failed": 0,
        "results": [
            {
                "employee_id": 123,
                "status": "success",
                "record_id": 456
            },
            {
                "employee_id": 124,
                "status": "success",
                "record_id": 457
            },
            {
                "employee_id": 125,
                "status": "success",
                "record_id": 458
            }
        ]
    }
}
```

## File Upload API

### Upload File
```http
POST /api/v1/files/upload
Authorization: Bearer <token>
Content-Type: multipart/form-data

file: [binary file data]
type: avatar|assignment|resource
```

#### Query Parameters
- `type` (string) - File type (avatar, assignment, resource)
- `course_id` (int) - Course ID (for course-specific files)
- `assignment_id` (int) - Assignment ID (for assignment files)

#### Response (201)
```json
{
    "success": true,
    "data": {
        "file_id": 789,
        "filename": "homework1.zip",
        "original_name": "My Homework.zip",
        "size": 2048576,
        "mime_type": "application/zip",
        "type": "assignment",
        "course_id": 45,
        "assignment_id": 67,
        "uploaded_at": "2025-11-05T22:44:46Z",
        "url": "/uploads/assignments/homework1.zip"
    },
    "message": "File uploaded successfully"
}
```

### Download File
```http
GET /api/v1/files/{file_id}/download
Authorization: Bearer <token>
```

#### Response
Returns the file as a binary download with appropriate headers.

### Delete File
```http
DELETE /api/v1/files/{file_id}
Authorization: Bearer <token>
```

#### Response (200)
```json
{
    "success": true,
    "message": "File deleted successfully"
}
```

### Get File Info
```http
GET /api/v1/files/{file_id}
Authorization: Bearer <token>
```

#### Response
```json
{
    "success": true,
    "data": {
        "file_id": 789,
        "filename": "homework1.zip",
        "original_name": "My Homework.zip",
        "size": 2048576,
        "mime_type": "application/zip",
        "type": "assignment",
        "uploaded_by": 123,
        "uploaded_at": "2025-11-05T22:44:46Z",
        "download_count": 5,
        "url": "/uploads/assignments/homework1.zip"
    }
}
```

### List Files
```http
GET /api/v1/files
Authorization: Bearer <token>
```

#### Query Parameters
- `type` (string) - Filter by file type
- `course_id` (int) - Filter by course
- `assignment_id` (int) - Filter by assignment
- `uploaded_by` (int) - Filter by uploader

#### Response
```json
{
    "success": true,
    "data": {
        "files": [
            {
                "file_id": 789,
                "filename": "homework1.zip",
                "original_name": "My Homework.zip",
                "size": 2048576,
                "type": "assignment",
                "uploaded_by": 123,
                "uploaded_at": "2025-11-05T22:44:46Z"
            }
        ]
    }
}
```

## Reporting API

### Generate Attendance Report
```http
GET /api/v1/reports/attendance?course_id=45&start_date=2025-11-01&end_date=2025-11-30&format=csv
Authorization: Bearer <token>
```

#### Query Parameters
- `course_id` (int) - Course ID (required)
- `student_id` (int) - Student ID (optional, for individual report)
- `start_date` (date) - Start date (required)
- `end_date` (date) - End date (required)
- `format` (string) - Output format (csv, pdf, json)
- `include_details` (bool) - Include detailed records

#### Response
Returns the report in the requested format:
- CSV: Returns CSV file
- PDF: Returns PDF document
- JSON: Returns JSON data

### Generate Grade Report
```http
GET /api/v1/reports/grades?course_id=45&format=pdf
Authorization: Bearer <token>
```

#### Query Parameters
- `course_id` (int) - Course ID (required)
- `student_id` (int) - Student ID (optional)
- `format` (string) - Output format
- `include_statistics` (bool) - Include grade statistics

#### Response
Returns grade report in the requested format.

### Get Course Statistics
```http
GET /api/v1/reports/course/{course_id}/statistics
Authorization: Bearer <token>
```

#### Response
```json
{
    "success": true,
    "data": {
        "course_id": 45,
        "course_name": "Introduction to Programming",
        "enrollment": {
            "total_students": 30,
            "active_students": 28,
            "graduated": 0,
            "dropped": 2
        },
        "attendance": {
            "average_attendance_rate": 92.5,
            "low_attendance_students": 2
        },
        "assignments": {
            "total_assignments": 8,
            "completed_assignments": 6,
            "average_grade": 85.2,
            "submission_rate": 88.9
        },
        "participation": {
            "forum_posts": 145,
            "average_posts_per_student": 4.8
        }
    }
}
```

### Get System Statistics
```http
GET /api/v1/reports/system/statistics
Authorization: Bearer <token>
```

#### Response
```json
{
    "success": true,
    "data": {
        "overview": {
            "total_users": 500,
            "total_courses": 45,
            "total_assignments": 280,
            "total_file_uploads": 1250
        },
        "users": {
            "by_role": {
                "admin": 5,
                "teacher": 25,
                "student": 465,
                "parent": 5
            },
            "by_status": {
                "active": 480,
                "inactive": 15,
                "suspended": 5
            }
        },
        "activity": {
            "daily_logins": {
                "today": 245,
                "yesterday": 267,
                "this_week": 1820
            },
            "assignment_submissions": {
                "today": 15,
                "this_week": 89,
                "this_month": 320
            }
        },
        "system_health": {
            "database_size": "2.5 GB",
            "storage_used": "15.2 GB",
            "api_response_time": "120ms",
            "error_rate": 0.02
        }
    }
}
```

---

This API documentation covers all the major endpoints available in the Google Classroom Clone system. For more specific implementation details, refer to the actual API endpoint files in the `/api/` directory.
