# Assignment Management System

A comprehensive assignment creation, submission, and grading system built with PHP and MySQL.

## 🚀 Features

### For Teachers/Administrators
- **Assignment Creation**: Create assignments with detailed instructions, due dates, and grading criteria
- **File Upload Support**: Support for various file types with configurable size limits
- **Submission Management**: View all student submissions with detailed tracking
- **Grading Interface**: Grade submissions with detailed feedback and scoring
- **Statistics & Analytics**: View submission rates, grade distributions, and performance metrics
- **Late Submission Handling**: Configurable late submission policies with automatic penalties
- **Multiple Attempts**: Allow students to resubmit assignments with attempt tracking

### For Students
- **Assignment Viewing**: View all available assignments with clear deadlines and requirements
- **Easy Submission**: Submit assignments via text input or file upload
- **Submission Tracking**: Monitor submission status and grades
- **File Management**: Upload multiple file types with drag-and-drop support
- **Draft Saving**: Auto-save submission drafts to prevent data loss
- **Grade Viewing**: View grades and teacher feedback

### System Features
- **Role-based Access Control**: Separate interfaces for teachers and students
- **Security**: Input validation, SQL injection protection, and secure file uploads
- **Responsive Design**: Mobile-friendly interface using Bootstrap
- **Real-time Updates**: Dynamic content updates and validation
- **Database Optimization**: Indexed tables and optimized queries for performance

## 📋 Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- PHP Extensions: PDO, PDO_MySQL, fileinfo

## 🛠️ Installation

### 1. Database Setup

1. Import the main database schema:
```sql
mysql -u username -p database_name < database_schema.sql
```

2. Install the assignment system tables:
```sql
mysql -u username -p database_name < database/install_assignments.sql
```

### 2. File System Setup

1. Create the upload directories:
```bash
mkdir -p uploads/assignments
chmod 755 uploads/assignments
```

2. Set proper permissions:
```bash
chmod 755 uploads/
chmod 755 uploads/assignments/
```

### 3. Configuration

1. Update the database configuration in `config/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_username');
define('DB_PASS', 'your_database_password');
```

2. Ensure the Assignment class is properly included in your project.

### 4. User Roles

Make sure you have users in your database with appropriate roles:
- **admin**: Can create and manage all assignments
- **employee**: Students who can submit assignments

## 📁 File Structure

```
pages/assignments/
├── index.php          # Assignment listing and overview
├── create.php         # Create new assignments (teachers only)
├── view.php          # View assignment details and submissions
├── submit.php        # Submit assignments (students only)
└── grade.php         # Grade submissions (teachers only)

includes/
├── class.Assignment.php # Assignment management class

database/
├── install_assignments.sql # Database installation script
└── assignments_schema.sql  # Complete schema definition
```

## 🎯 Usage Guide

### For Teachers

#### Creating an Assignment
1. Navigate to **Assignments → Create Assignment**
2. Fill in the basic information:
   - Title and description
   - Assignment type (Homework, Project, Exam, Quiz, Essay, Presentation)
   - Detailed instructions
3. Set grading parameters:
   - Total points
   - Maximum attempts
4. Configure deadline settings:
   - Due date and time
   - Late submission policy
   - Late penalty percentage
5. Set file upload requirements:
   - Require file upload (yes/no)
   - Maximum file size
   - Allowed file types
6. Publish the assignment

#### Grading Submissions
1. Go to **Assignments → Grade Submissions**
2. View all student submissions in a list
3. Click "Grade Submission" on any ungraded work
4. Enter the score and feedback
5. Use quick grade buttons for common letter grades
6. Save the grade

#### Managing Assignments
- **View Assignment**: See detailed assignment information and student progress
- **Edit Assignment**: Modify assignment details (before submissions)
- **Delete Assignment**: Remove assignment (with confirmation)
- **View Statistics**: Monitor submission rates and grade distributions

### For Students

#### Viewing Assignments
1. Navigate to **Assignments** to see all available assignments
2. Filter by type or search by keywords
3. Click on any assignment to view details
4. Check submission status and deadlines

#### Submitting Assignments
1. Go to the assignment details page
2. Click **Submit Assignment** (if allowed)
3. Choose submission method:
   - **Text Submission**: Type your answer directly
   - **File Upload**: Upload required documents
4. Add any additional comments
5. Review and submit

#### Tracking Submissions
- View submission status (Submitted, Under Review, Graded)
- See grades and teacher feedback
- Check if resubmission is allowed
- Monitor deadlines and time remaining

## ⚙️ Configuration Options

### Assignment Types
- **Homework**: Regular assignments
- **Project**: Complex multi-part assignments
- **Exam**: Timed assessments
- **Quiz**: Quick knowledge checks
- **Essay**: Written assignments
- **Presentation**: Oral/visual presentations

### File Upload Settings
- **Required Upload**: Students must upload a file
- **Optional Upload**: Students can choose to upload or submit text
- **File Size Limit**: Configurable (1MB to 50MB)
- **File Types**: PDF, Word, Text, ZIP, HTML, CSS, JavaScript, etc.

### Late Submission Policy
- **Allow Late Submissions**: Yes/No
- **Late Penalty**: Percentage reduction per day (default 5%)
- **Grace Period**: Additional time before penalty applies

### Grading System
- **Total Points**: Configurable maximum score
- **Letter Grades**: A (90-100%), B (80-89%), C (70-79%), D (60-69%), F (0-59%)
- **Feedback**: Teachers can provide detailed written feedback
- **Quick Grading**: Pre-set scores for common letter grades

## 🔧 Advanced Features

### Database Views
- **assignment_overview**: Complete assignment statistics
- **student_assignment_status**: Individual student progress

### Stored Procedures
- **GetAssignmentStats()**: Retrieve comprehensive assignment analytics

### Automatic Triggers
- **Letter Grade Calculation**: Automatic letter grade assignment
- **Late Submission Detection**: Automatic late flagging
- **Timestamps**: Automatic created/updated tracking

### Security Features
- **Input Validation**: All user inputs are sanitized
- **SQL Injection Protection**: Prepared statements throughout
- **File Upload Security**: File type and size validation
- **Access Control**: Role-based permission checking
- **CSRF Protection**: Form token validation

## 📊 Analytics & Reporting

### Teacher Dashboard
- Total assignments created
- Submission statistics
- Grading progress
- Average scores
- Grade distribution charts

### Student Dashboard
- Assignment completion status
- Grade history
- Upcoming deadlines
- Submission progress

### Assignment Statistics
- Submission rate
- On-time vs late submissions
- Average scores
- Grade distribution
- Student engagement metrics

## 🐛 Troubleshooting

### Common Issues

#### File Upload Problems
- Check upload directory permissions (755)
- Verify PHP upload limits in php.ini
- Ensure allowed file types are configured correctly
- Check available disk space

#### Database Connection Issues
- Verify database credentials in config.php
- Check MySQL service status
- Ensure database exists and user has proper permissions

#### Permission Errors
- Teachers can only grade their own assignments
- Students can only view their own submissions
- Admin users have full access to all features

#### Performance Issues
- Ensure database indexes are created
- Monitor query performance
- Check for large file uploads affecting server load

### Error Messages
- **"Assignment not found"**: Invalid assignment ID or insufficient permissions
- **"Late submissions not allowed"**: Assignment deadline has passed
- **"Maximum attempts exceeded"**: Student has used all allowed attempts
- **"File upload required"**: Assignment requires file submission

## 🔒 Security Considerations

### File Upload Security
- Only allow specific file extensions
- Validate file content using MIME type checking
- Store uploads outside web root when possible
- Scan uploaded files for malicious content

### Data Protection
- Sanitize all user inputs
- Use prepared statements for database queries
- Implement proper session management
- Log security events for monitoring

### Access Control
- Verify user roles before allowing actions
- Check ownership of assignments before modifications
- Implement rate limiting for form submissions
- Use HTTPS in production environments

## 📈 Future Enhancements

### Planned Features
- **Rubric-based Grading**: Detailed grading criteria
- **Peer Review**: Student-to-student assignment review
- **Assignment Templates**: Reusable assignment formats
- **Email Notifications**: Automatic deadline reminders
- **Mobile App**: Native mobile application
- **API Integration**: RESTful API for third-party tools

### Technical Improvements
- **Caching**: Redis/Memcached for improved performance
- **Search**: Full-text search across assignments and submissions
- **Bulk Operations**: Mass grading and assignment management
- **Real-time Updates**: WebSocket for live notifications
- **Offline Support**: Progressive Web App capabilities

## 🤝 Contributing

This assignment system is designed to be extensible. To add new features:

1. Follow the existing code structure and naming conventions
2. Add proper database migrations for schema changes
3. Include comprehensive error handling and logging
4. Write clear documentation for new features
5. Test thoroughly with both teacher and student user types

## 📞 Support

For technical support or questions about the assignment system:

1. Check this documentation first
2. Review the error logs in `logs/error.log`
3. Test with the included sample data
4. Verify database schema matches the installation script

## 📄 License

This assignment management system is provided as-is for educational and development purposes. Modify and adapt as needed for your specific requirements.

---

**Version**: 1.0  
**Last Updated**: November 2024  
**Compatible with**: PHP 7.4+, MySQL 5.7+