# Course Management System - Complete Guide

## Overview

The Course Management System is a comprehensive web-based solution for managing educational courses, student enrollments, course materials, announcements, and discussions. Built on PHP with MySQL database, it provides role-based access for teachers and students.

## Features

### Core Features
- **Course Creation & Management**: Teachers can create and manage courses with detailed information
- **Student Enrollment**: Students can browse and enroll in courses using class codes
- **Course Announcements**: Post and view course announcements with priority levels
- **Discussion Forums**: Interactive discussion forums for each course
- **Course Materials**: Upload and share course materials and resources
- **Role-based Access**: Separate interfaces for teachers, students, and administrators
- **CRUD Operations**: Complete create, read, update, and delete functionality
- **Validation & Security**: Input validation, CSRF protection, and secure authentication

### User Roles
- **Admin**: Full system access, user management, system configuration
- **Teacher**: Create and manage courses, post announcements, moderate discussions
- **Student**: Browse courses, enroll using class codes, participate in discussions
- **Employee**: Legacy role maintained for backward compatibility

## Installation

### 1. Database Setup
1. Ensure MySQL database is configured and running
2. The system uses the existing user management system and database
3. Run the installation script: `http://your-domain/pages/courses/install.php`
4. Follow the installation steps to create course management tables

### 2. User Setup
The system extends the existing user roles to include:
- **Teacher**: Can create and manage courses
- **Student**: Can enroll in courses and participate in discussions

Sample users are created automatically during installation:
- Username: `teacher1` / Password: `password123`
- Username: `student1` / Password: `password123`
- Username: `teacher2` / Password: `password123`
- Username: `student2` / Password: `password123`

### 3. File Structure
```
pages/courses/
├── index.php          # Course listing (teachers see their courses, students see available courses)
├── create.php         # Course creation form for teachers
├── edit.php           # Course editing interface for teachers
├── view.php           # Course detail page with announcements, discussions, materials
├── enroll.php         # Student enrollment with class codes
├── install.php        # Installation and setup script
├── discussion.php     # Individual discussion thread (if needed)
└── discussion.php     # Discussion viewing and posting (if needed)

includes/
├── class.Course.php   # Course management class with all CRUD operations
└── class.Database.php # Database connection and operations

course_schema.sql      # Complete database schema for course management
```

## User Guide

### For Administrators

#### Accessing the Installation
1. Navigate to `http://your-domain/pages/courses/install.php`
2. Login as admin user
3. Follow the installation steps:
   - Install Database Schema
   - Verify Installation
   - Sample data is automatically created

#### Managing Users
- Use the existing admin panel at `/pages/admin/users.php`
- Add teachers by creating users with role 'teacher'
- Students get role 'student' for course access

### For Teachers

#### Creating a Course
1. Login with teacher credentials
2. Navigate to Courses → "Create New Course"
3. Fill in course details:
   - Course Title
   - Course Code (unique identifier)
   - Description
   - Semester and Year
   - Credits and Max Students
   - Start/End dates
4. Click "Create Course"

#### Managing Courses
- **Edit Course**: Click "Edit" on any course to modify details
- **Course Dashboard**: View enrollment statistics, announcements, discussions
- **Post Announcements**: Use the announcements tab to communicate with students
- **Create Discussions**: Start new discussion topics for student interaction

#### Course Materials
- Upload documents, videos, and other materials
- Set visibility (public/teacher only)
- Students can download and view materials

### For Students

#### Browsing Courses
1. Navigate to Courses page
2. View available courses with details:
   - Course descriptions
   - Instructor information
   - Enrollment status
   - Credit information

#### Enrolling in Courses
1. Click "Enroll" on any available course
2. Enter class code if required (provided by instructor)
3. Confirm enrollment
4. Access course materials and participate in discussions

#### Course Participation
- View announcements and course updates
- Participate in discussion forums
- Download course materials
- View course information and schedule

## Database Schema

### Core Tables

#### `courses`
Stores course information including title, description, course code, teacher, dates, etc.

#### `course_enrollments`
Manages student enrollments in courses with status tracking

#### `course_announcements`
Course announcements with priority levels and publication scheduling

#### `course_discussions`
Discussion forum topics for each course

#### `discussion_posts`
Individual posts within discussion topics with threading support

#### `course_materials`
File uploads and course materials with metadata

#### `course_assignments`
Assignment creation and management (extensible feature)

#### `assignment_submissions`
Student assignment submissions with grading (extensible feature)

### Relationships
- One teacher can have many courses
- Many students can enroll in many courses
- Many announcements per course
- Many discussions per course
- Many posts per discussion
- Many materials per course

## Security Features

### Authentication & Authorization
- Secure session-based authentication
- Role-based access control (admin, teacher, student)
- CSRF token protection for all forms
- Input validation and sanitization

### Data Protection
- SQL injection prevention using prepared statements
- XSS protection with output encoding
- File upload validation and sanitization
- Secure file handling for course materials

### Session Security
- Session timeout management
- Session fixation prevention
- IP address validation (optional)
- Failed login attempt tracking

## API & Integration

### Course Management API
The `Course` class provides methods for:
- `createCourse()` - Create new courses
- `updateCourse()` - Modify existing courses
- `getCourse()` - Retrieve course details
- `getAllCourses()` - List courses with filtering
- `enrollStudent()` - Student enrollment processing

### Access Control
- `isCourseTeacher()` - Verify teacher ownership
- `verifyEnrollment()` - Check student enrollment
- `has_role()` - Role verification

## Customization

### Styling
- Bootstrap 5 framework integration
- Custom CSS for course-specific elements
- Responsive design for mobile devices
- Color-coded course status indicators

### Extensions
The system is designed for extensibility:
- Additional course fields
- Grade management system
- Calendar integration
- Video conferencing integration
- Mobile app API

### Database Extensions
- Add custom fields to existing tables
- Create new relationship tables
- Implement additional reporting views
- Add custom indexes for performance

## Troubleshooting

### Common Issues

#### Database Connection Errors
- Verify MySQL service is running
- Check database credentials in `database_config.php`
- Ensure database exists and user has permissions

#### Permission Denied Errors
- Check user roles in database
- Verify session authentication
- Confirm CSRF tokens are being generated

#### File Upload Issues
- Check upload directory permissions (`uploads/`)
- Verify PHP upload settings
- Ensure sufficient disk space

#### Course Creation Problems
- Verify course code uniqueness
- Check teacher user exists
- Validate date ranges

### Logging
- Error logs: PHP error_log() function
- Activity logs: Database activity_logs table
- Security logs: Database security_events table

## Performance

### Optimization Tips
- Use database indexes on frequently queried columns
- Implement pagination for large course lists
- Optimize images and course materials
- Use CDN for static assets

### Monitoring
- Database query performance
- User session activity
- File upload success rates
- Error frequency tracking

## Support

### Documentation
- Inline code comments
- Function documentation in classes
- Database schema documentation
- User guide and tutorials

### Maintenance
- Regular database backups
- Log file rotation
- User activity monitoring
- Security update notifications

## Version History

### Version 1.0 (Current)
- Initial course management system
- Core CRUD operations
- Role-based access control
- Announcement system
- Discussion forums
- Course materials
- Student enrollment system

### Future Enhancements
- Grade management system
- Assignment and quiz functionality
- Calendar integration
- Mobile application
- Advanced reporting
- Multi-language support
- Integration with external systems

## Conclusion

The Course Management System provides a solid foundation for educational course management with comprehensive features for teachers and students. The modular design allows for easy extension and customization to meet specific institutional needs while maintaining security and performance standards.