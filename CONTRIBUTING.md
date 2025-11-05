# Contributing to Classroom Management System

First off, thank you for considering contributing to the Classroom Management System! It's people like you that make this project better.

## Code of Conduct

This project and everyone participating in it is governed by our Code of Conduct. By participating, you are expected to uphold this code.

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check the issue list as you might find out that you don't need to create one. When you are creating a bug report, please include as many details as possible:

**Bug Report Template:**
```markdown
**Describe the bug**
A clear and concise description of what the bug is.

**To Reproduce**
Steps to reproduce the behavior:
1. Go to '...'
2. Click on '....'
3. Scroll down to '....'
4. See error

**Expected behavior**
A clear and concise description of what you expected to happen.

**Screenshots**
If applicable, add screenshots to help explain your problem.

**Environment:**
 - OS: [e.g. Ubuntu 20.04]
 - PHP Version: [e.g. 8.2]
 - Database: [e.g. MySQL 8.0]
 - Browser: [e.g. Chrome 91]

**Additional context**
Add any other context about the problem here.
```

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When creating an enhancement suggestion, please include:

**Enhancement Template:**
```markdown
**Is your enhancement related to a problem?**
A clear and concise description of what the problem is.

**Describe the solution you'd like**
A clear and concise description of what you want to happen.

**Describe alternatives you've considered**
A clear and concise description of any alternative solutions or features you've considered.

**Additional context**
Add any other context or screenshots about the enhancement request here.
```

## Development Setup

### Prerequisites

- PHP 8.2 or higher
- MySQL 8.0 or higher
- Apache 2.4+ or Nginx
- Composer
- Git

### Getting Started

1. **Fork the repository**
   ```bash
   # Clone your fork
   git clone https://github.com/your-username/classroom-management.git
   cd classroom-management
   ```

2. **Add upstream remote**
   ```bash
   git remote add upstream https://github.com/original-username/classroom-management.git
   ```

3. **Create a feature branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

4. **Install dependencies**
   ```bash
   composer install
   ```

5. **Set up development environment**
   ```bash
   # Copy environment template
   cp config/environment.example.php config/environment.php
   
   # Configure database
   # Edit config/environment.php with your development database credentials
   ```

6. **Set up database**
   ```bash
   # Create development database
   mysql -u root -p -e "CREATE DATABASE classroom_clone_dev;"
   
   # Import schema
   mysql -u root -p classroom_clone_dev < database/schema.sql
   
   # Import sample data
   mysql -u root -p classroom_clone_dev < database/sample_data.sql
   ```

7. **Start development server**
   ```bash
   # Using PHP built-in server
   php -S localhost:8080 -t .
   
   # Or using Docker
   docker-compose up -d
   ```

## Coding Standards

### PHP Standards

We follow PSR-12 coding standards. Please ensure your code adheres to these standards.

**Code Style Tools:**
```bash
# Install PHP_CodeSniffer
composer require --dev squizlabs/php_codesniffer

# Check code style
vendor/bin/phpcs --standard=PSR12 --extensions=php --ignore=*/vendor/*,*/node_modules/* .

# Auto-fix code style
vendor/bin/phpcbf --standard=PSR12 --extensions=php --ignore=*/vendor/*,*/node_modules/* .
```

### Naming Conventions

- **Classes**: PascalCase (e.g., `UserManager`, `ClassroomController`)
- **Methods and Variables**: camelCase (e.g., `getUser()`, `$userName`)
- **Constants**: UPPER_SNAKE_CASE (e.g., `MAX_FILE_SIZE`)
- **Database Tables**: snake_case (e.g., `user_profiles`, `class_assignments`)
- **Database Columns**: snake_case (e.g., `first_name`, `created_at`)

### File Organization

```
project/
├── api/                 # API endpoints
├── assets/             # Static assets (CSS, JS, images)
├── config/             # Configuration files
├── includes/           # Core PHP classes
├── pages/              # Web pages and controllers
├── database/           # Database files and migrations
├── tests/              # Test files
├── docs/               # Documentation
└── scripts/            # Utility scripts
```

## Writing Code

### PHP Best Practices

1. **Use Type Declarations**
   ```php
   // Good
   public function getUser(int $userId): ?User
   {
       return $this->userRepository->find($userId);
   }
   
   // Avoid
   public function getUser($userId)
   {
       // ...
   }
   ```

2. **Use PHPDoc Comments**
   ```php
   /**
    * Create a new assignment
    *
    * @param string $title The assignment title
    * @param string $description The assignment description
    * @param int $courseId The course ID
    * @param string $dueDate The due date in Y-m-d format
    * @param int $maxPoints Maximum points possible
    * @return Assignment The created assignment
    * @throws ValidationException If validation fails
    */
   public function createAssignment(
       string $title,
       string $description,
       int $courseId,
       string $dueDate,
       int $maxPoints
   ): Assignment {
       // Implementation
   }
   ```

3. **Use Prepared Statements**
   ```php
   // Good
   $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
   $stmt->execute([$email]);
   
   // Avoid
   $query = "SELECT * FROM users WHERE email = '$email'";
   $result = mysqli_query($connection, $query);
   ```

4. **Input Validation and Sanitization**
   ```php
   public function createUser(array $data): User
   {
       // Validate required fields
       $required = ['first_name', 'last_name', 'email', 'password'];
       foreach ($required as $field) {
           if (empty($data[$field])) {
               throw new ValidationException("Field {$field} is required");
           }
       }
       
       // Sanitize inputs
       $data['email'] = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
       $data['first_name'] = htmlspecialchars($data['first_name']);
       
       // Additional validation rules
       if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
           throw new ValidationException('Invalid email format');
       }
       
       // Create user
       return $this->userRepository->create($data);
   }
   ```

### Database Design

1. **Use Foreign Key Constraints**
   ```sql
   CREATE TABLE assignments (
       id INT AUTO_INCREMENT PRIMARY KEY,
       title VARCHAR(255) NOT NULL,
       course_id INT NOT NULL,
       due_date DATETIME NOT NULL,
       FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
   );
   ```

2. **Use Indexes for Performance**
   ```sql
   CREATE INDEX idx_assignments_course_id ON assignments(course_id);
   CREATE INDEX idx_assignments_due_date ON assignments(due_date);
   CREATE INDEX idx_users_email ON users(email);
   ```

3. **Use ENUM for Fixed Values**
   ```sql
   CREATE TABLE users (
       id INT AUTO_INCREMENT PRIMARY KEY,
       role ENUM('admin', 'teacher', 'student') NOT NULL,
       status ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active'
   );
   ```

### Frontend Standards

1. **Use Semantic HTML**
   ```html
   <!-- Good -->
   <main>
       <section>
           <h2>Course Overview</h2>
           <p>Course description here</p>
       </section>
   </main>
   
   <!-- Avoid -->
   <div>
       <div>
           <h2>Course Overview</h2>
           <p>Course description here</p>
       </div>
   </div>
   ```

2. **Use CSS Custom Properties**
   ```css
   :root {
       --primary-color: #007bff;
       --secondary-color: #6c757d;
       --border-radius: 0.375rem;
   }
   
   .btn {
       background-color: var(--primary-color);
       border-radius: var(--border-radius);
   }
   ```

3. **Use JavaScript ES6+ Features**
   ```javascript
   // Good - Using modern JavaScript
   const getCourses = async () => {
       try {
           const response = await fetch('/api/courses');
           const courses = await response.json();
           return courses;
       } catch (error) {
           console.error('Error fetching courses:', error);
       }
   };
   
   // Avoid - Using old-style functions
   var getCourses = function() {
       // ...
   };
   ```

## Testing

### Unit Tests

Write unit tests for all new functionality. We use PHPUnit for testing.

**Test Structure:**
```php
<?php

use PHPUnit\Framework\TestCase;
use App\Models\User;

class UserTest extends TestCase
{
    private User $user;
    
    protected function setUp(): void
    {
        $this->user = new User();
    }
    
    public function testUserCreation()
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123'
        ];
        
        $user = $this->user->create($data);
        
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John', $user->getFirstName());
        $this->assertEquals('john@example.com', $user->getEmail());
    }
    
    public function testInvalidEmail()
    {
        $this->expectException(ValidationException::class);
        
        $this->user->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'invalid-email',
            'password' => 'password123'
        ]);
    }
}
```

**Running Tests:**
```bash
# Run all tests
./vendor/bin/phpunit

# Run tests with coverage
./vendor/bin/phpunit --coverage-html coverage/

# Run specific test file
./vendor/bin/phpunit tests/Models/UserTest.php
```

### Integration Tests

Test the integration between components:

```php
class CourseControllerTest extends TestCase
{
    public function testCreateCourse()
    {
        $controller = new CourseController();
        $request = new Request([
            'name' => 'Mathematics 101',
            'description' => 'Introduction to Algebra',
            'code' => 'MATH101'
        ]);
        
        $response = $controller->create($request);
        
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertNotNull($response->getData()['id']);
    }
}
```

### Manual Testing Checklist

Before submitting a pull request, manually test:

- [ ] User registration and login
- [ ] Course creation and enrollment
- [ ] Assignment creation and submission
- [ ] Grade management
- [ ] File upload/download
- [ ] API endpoints (if applicable)
- [ ] Mobile responsiveness
- [ ] Security features (CSRF, XSS protection)
- [ ] Error handling

## Security

### Security Guidelines

1. **Never commit sensitive data**
   ```bash
   # Add to .gitignore
   .env
   config/database.php
   uploads/private/
   ```

2. **Use CSRF protection**
   ```php
   // Generate CSRF token
   $token = $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
   
   // Verify CSRF token
   if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
       throw new SecurityException('CSRF token mismatch');
   }
   ```

3. **Sanitize all outputs**
   ```php
   // When outputting user data
   echo htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');
   ```

4. **Use secure session settings**
   ```php
   ini_set('session.cookie_httponly', 1);
   ini_set('session.cookie_secure', 1); // HTTPS only
   ini_set('session.use_strict_mode', 1);
   ```

## Performance

### Performance Guidelines

1. **Use Caching**
   ```php
   // Cache frequently accessed data
   $cacheKey = "user_courses_{$userId}";
   $courses = $cache->get($cacheKey);
   
   if ($courses === null) {
       $courses = $courseRepository->getUserCourses($userId);
       $cache->set($cacheKey, $courses, 3600); // Cache for 1 hour
   }
   
   return $courses;
   ```

2. **Optimize Database Queries**
   ```php
   // Use specific columns instead of SELECT *
   $stmt = $pdo->prepare('SELECT id, name, code FROM courses WHERE status = ?');
   $stmt->execute(['active']);
   
   // Use LIMIT for pagination
   $stmt = $pdo->prepare('SELECT * FROM assignments LIMIT ? OFFSET ?');
   $stmt->bindValue(1, $limit, PDO::PARAM_INT);
   $stmt->bindValue(2, $offset, PDO::PARAM_INT);
   ```

3. **Minimize HTTP Requests**
   ```javascript
   // Bundle CSS and JS files
   // Use CSS sprites for icons
   // Optimize images
   ```

## Documentation

### Code Documentation

- Document all public methods with PHPDoc
- Include parameter types and return types
- Add examples for complex methods
- Document any side effects or dependencies

### API Documentation

Document all API endpoints:

```php
/**
 * Create a new course
 * 
 * @api {post} /api/v1/courses Create Course
 * @apiName CreateCourse
 * @apiGroup Courses
 * @apiVersion 1.0.0
 * 
 * @apiParam {String} name Course name (required)
 * @apiParam {String} description Course description
 * @apiParam {String} course_code Unique course code (required)
 * @apiParam {Integer} teacher_id Teacher ID (required)
 * 
 * @apiSuccess {Integer} id Course ID
 * @apiSuccess {String} name Course name
 * @apiSuccess {String} course_code Course code
 * @apiSuccess {Integer} teacher_id Teacher ID
 * @apiSuccess {String} created_at Creation timestamp
 * 
 * @apiError (400) ValidationError Invalid input data
 * @apiError (409) ConflictError Course code already exists
 */
```

### User Documentation

Update relevant documentation files when adding features:

- `README.md` - Overview and quick start
- `docs/user-guide.md` - End user instructions
- `docs/admin-guide.md` - Administrator instructions
- `docs/developer-guide.md` - Developer documentation
- `CHANGELOG.md` - Version history

## Pull Request Process

### Before Submitting

1. **Run Code Quality Checks**
   ```bash
   # Check code style
   vendor/bin/phpcs --standard=PSR12 --extensions=php --ignore=*/vendor/* .
   
   # Run tests
   ./vendor/bin/phpunit
   
   # Security audit
   composer audit
   ```

2. **Update Documentation**
   - Update README.md if needed
   - Update CHANGELOG.md
   - Add PHPDoc for new methods

3. **Test Thoroughly**
   - Run all existing tests
   - Add tests for new functionality
   - Test in different browsers
   - Test on mobile devices

### Pull Request Guidelines

1. **Use Descriptive Titles**
   ```
   ✅ "Add assignment grading system with rubric support"
   ❌ "Fix bug" or "Update code"
   ```

2. **Provide Clear Descriptions**
   ```markdown
   ## What this PR does
   - Implements assignment grading system
   - Adds rubric support for detailed feedback
   - Updates grade calculation logic
   
   ## Screenshots
   [Add screenshots if UI is modified]
   
   ## Testing
   - All existing tests pass
   - Added 15 new unit tests
   - Manually tested grading workflows
   ```

3. **Keep PRs Focused**
   - One feature/fix per PR
   - Related changes in separate commits
   - Rebase if needed to keep history clean

### Review Process

1. **Automated Checks**
   - CI/CD pipeline runs automatically
   - All tests must pass
   - Code style must be correct

2. **Code Review**
   - At least one maintainer review required
   - Address all review comments
   - Make requested changes

3. **Manual Testing**
   - Reviewer tests the functionality
   - Check for edge cases
   - Verify security implications

## Release Process

### Version Numbering

We follow [Semantic Versioning](https://semver.org/):

- **MAJOR**: Breaking changes
- **MINOR**: New features (backward compatible)
- **PATCH**: Bug fixes (backward compatible)

### Release Checklist

- [ ] Update version in `config/config.php`
- [ ] Update CHANGELOG.md with all changes
- [ ] Run full test suite
- [ ] Test in staging environment
- [ ] Update documentation
- [ ] Create GitHub release with tag
- [ ] Deploy to production
- [ ] Monitor for issues

## Communication

### Channels

- **GitHub Issues**: Bug reports and feature requests
- **GitHub Discussions**: General questions and ideas
- **Email**: For security issues and private matters

### Response Times

- **Bug Reports**: Initial response within 48 hours
- **Feature Requests**: Initial response within 1 week
- **Security Issues**: Initial response within 24 hours

## Recognition

Contributors will be recognized in:

- `CONTRIBUTORS.md` file
- GitHub contributors page
- Release notes for significant contributions
- Annual contributor appreciation

## License

By contributing, you agree that your contributions will be licensed under the same MIT License that covers the project.

---

Thank you for contributing to the Classroom Management System! 🎓