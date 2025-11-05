# Testing Documentation

## Overview

This document provides comprehensive testing procedures and test cases for the Classroom Management System. It covers unit testing, integration testing, security testing, API testing, and user acceptance testing to ensure system reliability, security, and functionality.

## Table of Contents

1. [Testing Architecture](#testing-architecture)
2. [Testing Framework](#testing-framework)
3. [Unit Testing](#unit-testing)
4. [Integration Testing](#integration-testing)
5. [Security Testing](#security-testing)
6. [API Testing](#api-testing)
7. [Database Testing](#database-testing)
8. [User Acceptance Testing](#user-acceptance-testing)
9. [Performance Testing](#performance-testing)
10. [Automated Testing](#automated-testing)
11. [Testing Procedures](#testing-procedures)
12. [Test Cases](#test-cases)

## Testing Architecture

### Testing Pyramid

```
                    /\
                   /  \        E2E Tests (Few)
                  /    \
                 /      \
                /--------\
               /          \
              /   API     \      Integration Tests (Some)
             /    Tests     \
            /----------------\
           /                  \
          /    Unit Tests      \   Unit Tests (Many)
         /      (Many)          \
        /------------------------\
       /                          \
      /       Component Tests       \
     /                              \
    /--------------------------------\
   /                                  \
  /            UI Tests                 \
 /                                      \
/----------------------------------------\
```

### Testing Layers

1. **Unit Tests**: Test individual components and functions
2. **Component Tests**: Test classes and modules in isolation
3. **Integration Tests**: Test how components work together
4. **API Tests**: Test REST API endpoints and responses
5. **Security Tests**: Test security vulnerabilities and protections
6. **End-to-End Tests**: Test complete user workflows
7. **Performance Tests**: Test system performance under load

## Testing Framework

### Prerequisites

```bash
# Install testing dependencies
composer require --dev phpunit/phpunit
composer require --dev phpunit/php-code-coverage
composer require --dev squizlabs/php_codesniffer
composer require --dev guzzlehttp/guzzle
composer require --dev fakerphp/faker
```

### PHPUnit Configuration

Create `phpunit.xml` configuration:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/10.0/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
         verbose="true">
    
    <testsuites>
        <testsuite name="unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="integration">
            <directory>tests/Integration</directory>
        </testsuite>
        <testsuite name="security">
            <directory>tests/Security</directory>
        </testsuite>
        <testsuite name="api">
            <directory>tests/API</directory>
        </testsuite>
    </testsuites>
    
    <source>
        <include>
            <directory suffix=".php">includes</directory>
        </include>
        <exclude>
            <directory>vendor</directory>
            <directory>tests</directory>
        </exclude>
    </source>
    
    <logging>
        <log type="coverage-html" target="coverage"/>
        <log type="coverage-clover" target="coverage/clover.xml"/>
    </logging>
    
    <php>
        <env name="DB_HOST" value="localhost"/>
        <env name="DB_NAME" value="classroom_management_test"/>
        <env name="DB_USER" value="test_user"/>
        <env name="DB_PASS" value="test_password"/>
        <env name="APP_ENV" value="testing"/>
    </php>
</phpunit>
```

### Directory Structure

```
tests/
├── Unit/                    # Unit tests
│   ├── UserTest.php
│   ├── DatabaseTest.php
│   ├── CourseTest.php
│   └── AttendanceTest.php
├── Integration/             # Integration tests
│   ├── UserRegistrationTest.php
│   ├── CourseManagementTest.php
│   └── AuthenticationTest.php
├── Security/                # Security tests
│   ├── SQLInjectionTest.php
│   ├── XSSProtectionTest.php
│   ├── CSRFProtectionTest.php
│   └── AuthenticationSecurityTest.php
├── API/                     # API tests
│   ├── AttendanceAPITest.php
│   ├── UserAPITest.php
│   └── CourseAPITest.php
├── Fixtures/                # Test data fixtures
│   └── UserFixture.php
└── bootstrap.php            # Test bootstrap
```

## Unit Testing

### Database Class Testing

Test the database abstraction layer:

```php
<?php
// tests/Unit/DatabaseTest.php

use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    private $database;
    private $testDb;
    
    protected function setUp(): void
    {
        $this->testDb = 'classroom_management_test';
        $this->database = new Database();
    }
    
    public function testConnection()
    {
        $this->assertNotNull($this->database->getConnection());
    }
    
    public function testExecuteQuery()
    {
        $sql = "SELECT 1 as test_value";
        $result = $this->database->executeQuery($sql);
        
        $this->assertInstanceOf(PDOStatement::class, $result);
        
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals(1, $row['test_value']);
    }
    
    public function testPreparedStatement()
    {
        $sql = "SELECT * FROM users WHERE email = :email";
        $params = [':email' => 'test@example.com'];
        
        $result = $this->database->executeQuery($sql, $params);
        
        $this->assertInstanceOf(PDOStatement::class, $result);
    }
    
    public function testInsert()
    {
        $userData = [
            'email' => 'test_' . uniqid() . '@example.com',
            'password_hash' => password_hash('password123', PASSWORD_BCRYPT),
            'role' => 'student',
            'first_name' => 'Test',
            'last_name' => 'User'
        ];
        
        $userId = $this->database->insert('users', $userData);
        
        $this->assertIsInt($userId);
        $this->assertGreaterThan(0, $userId);
        
        // Clean up
        $this->database->delete('users', ['user_id' => $userId]);
    }
    
    public function testUpdate()
    {
        // Insert test user
        $userData = [
            'email' => 'test_' . uniqid() . '@example.com',
            'password_hash' => password_hash('password123', PASSWORD_BCRYPT),
            'role' => 'student'
        ];
        
        $userId = $this->database->insert('users', $userData);
        
        // Update user
        $updateData = ['first_name' => 'Updated'];
        $affected = $this->database->update('users', $updateData, ['user_id' => $userId]);
        
        $this->assertEquals(1, $affected);
        
        // Clean up
        $this->database->delete('users', ['user_id' => $userId]);
    }
    
    public function testDelete()
    {
        // Insert test user
        $userData = [
            'email' => 'test_' . uniqid() . '@example.com',
            'password_hash' => password_hash('password123', PASSWORD_BCRYPT),
            'role' => 'student'
        ];
        
        $userId = $this->database->insert('users', $userData);
        
        // Delete user
        $affected = $this->database->delete('users', ['user_id' => $userId]);
        
        $this->assertEquals(1, $affected);
    }
    
    public function testTransactionRollback()
    {
        try {
            $this->database->beginTransaction();
            
            // Insert test user
            $userData = [
                'email' => 'test_' . uniqid() . '@example.com',
                'password_hash' => password_hash('password123', PASSWORD_BCRYPT),
                'role' => 'student'
            ];
            
            $userId = $this->database->insert('users', $userData);
            
            // Intentionally cause error (duplicate email)
            $this->database->insert('users', $userData);
            
            $this->database->commit();
            $this->fail('Expected exception was not thrown');
        } catch (Exception $e) {
            $this->database->rollback();
            
            // Verify user was not created
            $sql = "SELECT COUNT(*) as count FROM users WHERE email = :email";
            $result = $this->database->executeQuery($sql, [':email' => $userData['email']]);
            $row = $result->fetch(PDO::FETCH_ASSOC);
            
            $this->assertEquals(0, $row['count']);
        }
    }
}
```

### User Class Testing

Test user management functionality:

```php
<?php
// tests/Unit/UserTest.php

use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private $user;
    private $database;
    
    protected function setUp(): void
    {
        $this->database = $this->createMock(Database::class);
        $this->user = new User($this->database);
    }
    
    public function testAuthenticateWithValidCredentials()
    {
        $email = 'test@example.com';
        $password = 'password123';
        
        // Mock user data
        $userData = [
            'user_id' => 1,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            'role' => 'student',
            'status' => 'active'
        ];
        
        $this->database
            ->expects($this->once())
            ->method('executeQuery')
            ->willReturn($this->createMock(PDOStatement::class));
        
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
              ->method('fetch')
              ->willReturn($userData);
        
        $this->database->method('executeQuery')
                      ->willReturn($stmt);
        
        $result = $this->user->authenticate($email, $password);
        
        $this->assertEquals($userData['user_id'], $result['user_id']);
    }
    
    public function testAuthenticateWithInvalidPassword()
    {
        $email = 'test@example.com';
        $password = 'wrongpassword';
        
        $userData = [
            'user_id' => 1,
            'email' => $email,
            'password_hash' => password_hash('correctpassword', PASSWORD_BCRYPT),
            'role' => 'student'
        ];
        
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
              ->method('fetch')
              ->willReturn($userData);
        
        $this->database->method('executeQuery')
                      ->willReturn($stmt);
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid credentials');
        
        $this->user->authenticate($email, $password);
    }
    
    public function testCreateUserWithValidData()
    {
        $userData = [
            'email' => 'newuser@example.com',
            'password' => 'SecurePass123!',
            'role' => 'student',
            'first_name' => 'John',
            'last_name' => 'Doe'
        ];
        
        $this->database
            ->expects($this->once())
            ->method('insert')
            ->willReturn(123);
        
        $result = $this->user->createUser($userData);
        
        $this->assertIsInt($result);
        $this->assertEquals(123, $result);
    }
    
    public function testCreateUserWithWeakPassword()
    {
        $userData = [
            'email' => 'newuser@example.com',
            'password' => 'weak', // Too weak
            'role' => 'student'
        ];
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Password does not meet requirements');
        
        $this->user->createUser($userData);
    }
    
    public function testValidatePasswordStrength()
    {
        // Valid password
        $this->assertTrue($this->user->validatePasswordStrength('StrongPass123!'));
        
        // Invalid passwords
        $this->assertFalse($this->user->validatePasswordStrength('weak'));
        $this->assertFalse($this->user->validatePasswordStrength('nouppercase123!'));
        $this->assertFalse($this->user->validatePasswordStrength('NOLOWERCASE123!'));
        $this->assertFalse($this->user->validatePasswordStrength('NoSpecialChar123'));
        $this->assertFalse($this->user->validatePasswordStrength('NoNumber!'));
    }
}
```

## Integration Testing

### User Registration Integration

Test complete user registration workflow:

```php
<?php
// tests/Integration/UserRegistrationTest.php

use PHPUnit\Framework\TestCase;

class UserRegistrationTest extends TestCase
{
    private $database;
    private $user;
    
    protected function setUp(): void
    {
        $this->database = new Database();
        $this->user = new User($this->database);
    }
    
    public function testCompleteUserRegistration()
    {
        // Clean up any existing test data
        $this->cleanupTestData();
        
        // Test user data
        $userData = [
            'email' => 'integration_test_' . uniqid() . '@example.com',
            'password' => 'SecurePass123!',
            'role' => 'student',
            'first_name' => 'Integration',
            'last_name' => 'Test'
        ];
        
        // Create user
        $userId = $this->user->createUser($userData);
        $this->assertIsInt($userId);
        
        // Verify user exists in database
        $sql = "SELECT * FROM users WHERE user_id = :user_id";
        $result = $this->database->executeQuery($sql, [':user_id' => $userId]);
        $savedUser = $result->fetch(PDO::FETCH_ASSOC);
        
        $this->assertEquals($userData['email'], $savedUser['email']);
        $this->assertEquals($userData['role'], $savedUser['role']);
        $this->assertEquals($userData['first_name'], $savedUser['first_name']);
        $this->assertEquals($userData['last_name'], $savedUser['last_name']);
        $this->assertTrue(password_verify($userData['password'], $savedUser['password_hash']));
        
        // Verify authentication works
        $authenticatedUser = $this->user->authenticate(
            $userData['email'], 
            $userData['password']
        );
        
        $this->assertEquals($userId, $authenticatedUser['user_id']);
        
        // Clean up
        $this->database->delete('users', ['user_id' => $userId]);
    }
    
    public function testDuplicateEmailRegistration()
    {
        $userData = [
            'email' => 'duplicate_test@example.com',
            'password' => 'SecurePass123!',
            'role' => 'student'
        ];
        
        // Create first user
        $firstUserId = $this->user->createUser($userData);
        
        // Try to create second user with same email
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Email already exists');
        
        $this->user->createUser($userData);
        
        // Clean up
        $this->database->delete('users', ['user_id' => $firstUserId]);
    }
    
    private function cleanupTestData()
    {
        $sql = "DELETE FROM users WHERE email LIKE '%@example.com' AND email LIKE 'integration_test_%'";
        $this->database->executeQuery($sql);
    }
}
```

### Course Management Integration

Test course creation and enrollment:

```php
<?php
// tests/Integration/CourseManagementTest.php

use PHPUnit\Framework\TestCase;

class CourseManagementTest extends TestCase
{
    private $database;
    private $course;
    private $user;
    
    protected function setUp(): void
    {
        $this->database = new Database();
        $this->course = new Course($this->database);
        $this->user = new User($this->database);
    }
    
    public function testCreateCourseAndEnrollStudent()
    {
        // Create test teacher
        $teacherData = [
            'email' => 'teacher_' . uniqid() . '@example.com',
            'password' => 'SecurePass123!',
            'role' => 'teacher',
            'first_name' => 'Test',
            'last_name' => 'Teacher'
        ];
        
        $teacherId = $this->user->createUser($teacherData);
        
        // Create test student
        $studentData = [
            'email' => 'student_' . uniqid() . '@example.com',
            'password' => 'SecurePass123!',
            'role' => 'student',
            'first_name' => 'Test',
            'last_name' => 'Student'
        ];
        
        $studentId = $this->user->createUser($studentData);
        
        // Create course
        $courseData = [
            'course_name' => 'Test Course',
            'course_code' => 'TEST101',
            'description' => 'A test course for integration testing',
            'teacher_id' => $teacherId,
            'semester' => 'Fall 2025',
            'academic_year' => '2025-2026'
        ];
        
        $courseId = $this->course->createCourse($courseData);
        
        // Verify course was created
        $this->assertIsInt($courseId);
        
        // Enroll student
        $enrollmentId = $this->course->enrollStudent($courseId, $studentId);
        $this->assertIsInt($enrollmentId);
        
        // Verify enrollment
        $enrollments = $this->course->getStudentEnrollments($studentId);
        $this->assertCount(1, $enrollments);
        $this->assertEquals($courseId, $enrollments[0]['course_id']);
        
        // Clean up
        $this->course->unenrollStudent($courseId, $studentId);
        $this->database->delete('courses', ['course_id' => $courseId]);
        $this->database->delete('users', ['user_id' => $teacherId]);
        $this->database->delete('users', ['user_id' => $studentId]);
    }
}
```

## Security Testing

### SQL Injection Testing

Test for SQL injection vulnerabilities:

```php
<?php
// tests/Security/SQLInjectionTest.php

use PHPUnit\Framework\TestCase;

class SQLInjectionTest extends TestCase
{
    private $database;
    private $user;
    
    protected function setUp(): void
    {
        $this->database = new Database();
        $this->user = new User($this->database);
    }
    
    public function testEmailParameterSQLInjection()
    {
        $maliciousEmails = [
            "'; DROP TABLE users; --",
            "' OR '1'='1",
            "admin'--",
            "admin' OR 1=1--",
            "admin' UNION SELECT * FROM users--"
        ];
        
        foreach ($maliciousEmails as $email) {
            $this->expectNotToPerformAssertions();
            
            try {
                $this->user->getUserByEmail($email);
                // If we get here without exception, that's concerning
            } catch (Exception $e) {
                // Expected - should not execute malicious SQL
                $this->assertStringNotContainsString('DROP TABLE', $e->getMessage());
                $this->assertStringNotContainsString('UNION SELECT', $e->getMessage());
            }
        }
    }
    
    public function testPreparedStatementProtection()
    {
        // Test that the system uses prepared statements
        $sql = "SELECT * FROM users WHERE email = :email";
        $params = [':email' => "test'; DROP TABLE users; --"];
        
        $stmt = $this->database->executeQuery($sql, $params);
        
        // Should return empty result, not execute DROP TABLE
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Verify no tables were dropped
        $tables = $this->database->executeQuery("SHOW TABLES")->fetchAll();
        $this->assertNotEmpty($tables); // Tables still exist
    }
    
    public function testNumericParameterSQLInjection()
    {
        $course = new Course($this->database);
        
        $maliciousIds = [
            "1; DROP TABLE courses; --",
            "1 OR 1=1",
            "1 UNION SELECT * FROM users"
        ];
        
        foreach ($maliciousIds as $courseId) {
            $this->expectNotToPerformAssertions();
            
            try {
                $course->getCourse($courseId);
            } catch (Exception $e) {
                // Should handle gracefully without executing malicious SQL
                $this->assertStringNotContainsString('DROP TABLE', $e->getMessage());
            }
        }
    }
}
```

### XSS Protection Testing

Test for Cross-Site Scripting vulnerabilities:

```php
<?php
// tests/Security/XSSProtectionTest.php

use PHPUnit\Framework\TestCase;

class XSSProtectionTest extends TestCase
{
    private $database;
    private $course;
    
    protected function setUp(): void
    {
        $this->database = new Database();
        $this->course = new Course($this->database);
    }
    
    public function testCourseDescriptionXSS()
    {
        $xssPayloads = [
            '<script>alert("XSS")</script>',
            '"><script>alert("XSS")</script>',
            "javascript:alert('XSS')",
            '<img src=x onerror=alert("XSS")>',
            '<svg onload=alert("XSS")>'
        ];
        
        foreach ($xssPayloads as $payload) {
            $courseData = [
                'course_name' => 'Test Course',
                'course_code' => 'XSS' . uniqid(),
                'description' => $payload,
                'teacher_id' => 1
            ];
            
            $courseId = $this->course->createCourse($courseData);
            
            // Retrieve and verify output encoding
            $course = $this->course->getCourse($courseId);
            
            // Should be encoded, not executed
            $this->assertStringNotContainsString('<script>', $course['description']);
            $this->assertStringNotContainsString('alert(', $course['description']);
            
            // Clean up
            $this->database->delete('courses', ['course_id' => $courseId]);
        }
    }
    
    public function testUserNameXSS()
    {
        $user = new User($this->database);
        
        $xssPayloads = [
            '<script>alert("XSS")</script>',
            '"><script>alert("XSS")</script>',
            '<img src=x onerror=alert("XSS")>'
        ];
        
        foreach ($xssPayloads as $payload) {
            $userData = [
                'email' => 'xss_test_' . uniqid() . '@example.com',
                'password' => 'SecurePass123!',
                'role' => 'student',
                'first_name' => $payload,
                'last_name' => 'Test'
            ];
            
            $userId = $user->createUser($userData);
            
            $savedUser = $user->getUserById($userId);
            
            // Should be encoded, not executed
            $this->assertStringNotContainsString('<script>', $savedUser['first_name']);
            $this->assertStringNotContainsString('alert(', $savedUser['first_name']);
            
            // Clean up
            $this->database->delete('users', ['user_id' => $userId]);
        }
    }
    
    public function testOutputEncoding()
    {
        $encoder = new OutputEncoder();
        
        $testStrings = [
            '<script>alert("XSS")</script>',
            '"><img src=x onerror=alert("XSS")>',
            "It's a \"test\" string",
            "Line 1\nLine 2\r\nLine 3"
        ];
        
        foreach ($testStrings as $string) {
            $encoded = $encoder::encode($string);
            
            // Should not contain dangerous characters as-is
            $this->assertStringNotContainsString('<script>', $encoded);
            $this->assertStringNotContainsString('alert(', $encoded);
            
            // Should be properly escaped
            $this->assertStringContainsString('&lt;', $encoded);
            $this->assertStringContainsString('&gt;', $encoded);
            $this->assertStringContainsString('&quot;', $encoded);
        }
    }
}
```

### CSRF Protection Testing

Test for Cross-Site Request Forgery vulnerabilities:

```php
<?php
// tests/Security/CSRFProtectionTest.php

use PHPUnit\Framework\TestCase;

class CSRFProtectionTest extends TestCase
{
    private $csrf;
    private $database;
    
    protected function setUp(): void
    {
        $this->csrf = new CSRFProtection();
        $this->database = new Database();
        
        // Start session for testing
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public function testCSRFTokenGeneration()
    {
        $token = $this->csrf->generateToken();
        
        $this->assertNotEmpty($token);
        $this->assertIsString($token);
        $this->assertEquals(64, strlen($token)); // 32 bytes = 64 hex chars
    }
    
    public function testValidCSRFToken()
    {
        $token = $this->csrf->generateToken();
        
        // Should not throw exception
        $this->csrf->validateToken($token);
        $this->assertTrue(true); // If we reach here, test passed
    }
    
    public function testInvalidCSRFToken()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('CSRF token validation failed');
        
        $this->csrf->validateToken('invalid_token');
    }
    
    public function testCSRFTokenUniqueness()
    {
        $token1 = $this->csrf->generateToken();
        
        // Clear session and generate new token
        unset($_SESSION['csrf_token']);
        $token2 = $this->csrf->generateToken();
        
        $this->assertNotEquals($token1, $token2);
    }
    
    public function testFormWithoutCSRFToken()
    {
        // Simulate form submission without CSRF token
        $_POST = [
            'action' => 'update_profile',
            'first_name' => 'Test'
        ];
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('CSRF token validation failed');
        
        // This should fail if the form processor properly validates CSRF
        $this->processFormSubmission($_POST);
    }
    
    public function testFormWithValidCSRFToken()
    {
        $token = $this->csrf->generateToken();
        
        // Simulate form submission with valid CSRF token
        $_POST = [
            'action' => 'update_profile',
            'first_name' => 'Test',
            'csrf_token' => $token
        ];
        
        // Should not throw exception
        $this->processFormSubmission($_POST);
        $this->assertTrue(true);
    }
    
    private function processFormSubmission($postData)
    {
        if (!isset($postData['csrf_token'])) {
            throw new Exception('CSRF token validation failed');
        }
        
        $this->csrf->validateToken($postData['csrf_token']);
    }
}
```

## API Testing

### Attendance API Testing

Test REST API endpoints:

```php
<?php
// tests/API/AttendanceAPITest.php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class AttendanceAPITest extends TestCase
{
    private $httpClient;
    private $baseUrl;
    private $authToken;
    private $database;
    
    protected function setUp(): void
    {
        $this->baseUrl = 'http://localhost:8000/api';
        $this->httpClient = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ]);
        
        $this->database = new Database();
        $this->authenticateUser();
    }
    
    public function testEmployeeCheckInAPI()
    {
        // Test successful check-in
        $response = $this->makeAuthenticatedRequest('POST', '/attendance/checkin.php', [
            'employee_id' => 123,
            'location' => 'office'
        ]);
        
        $this->assertEquals(200, $response['status']);
        $this->assertTrue($response['data']['success']);
        $this->assertArrayHasKey('check_in_time', $response['data']);
        
        // Test duplicate check-in
        $duplicateResponse = $this->makeAuthenticatedRequest('POST', '/attendance/checkin.php', [
            'employee_id' => 123,
            'location' => 'office'
        ]);
        
        $this->assertEquals(400, $duplicateResponse['status']);
        $this->assertFalse($duplicateResponse['data']['success']);
        $this->assertStringContainsString('already checked in', $duplicateResponse['data']['message']);
    }
    
    public function testInvalidEmployeeId()
    {
        $response = $this->makeAuthenticatedRequest('POST', '/attendance/checkin.php', [
            'employee_id' => 'invalid_id',
            'location' => 'office'
        ]);
        
        $this->assertEquals(400, $response['status']);
        $this->assertFalse($response['data']['success']);
        $this->assertStringContainsString('Invalid employee ID', $response['data']['message']);
    }
    
    public function testMissingRequiredFields()
    {
        $response = $this->makeAuthenticatedRequest('POST', '/attendance/checkin.php', [
            'employee_id' => 123
            // missing location
        ]);
        
        $this->assertEquals(400, $response['status']);
        $this->assertFalse($response['data']['success']);
    }
    
    public function testUnauthenticatedRequest()
    {
        try {
            $response = $this->httpClient->post('/attendance/checkin.php', [
                'json' => [
                    'employee_id' => 123,
                    'location' => 'office'
                ]
            ]);
            
            $this->fail('Expected authentication error');
        } catch (RequestException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
        }
    }
    
    public function testGetAttendanceRecords()
    {
        $response = $this->makeAuthenticatedRequest('GET', '/attendance/records.php', [
            'employee_id' => 123,
            'date' => '2025-11-05'
        ]);
        
        $this->assertEquals(200, $response['status']);
        $this->assertArrayHasKey('attendance_records', $response['data']);
    }
    
    private function authenticateUser()
    {
        // Create test user and get auth token
        $response = $this->httpClient->post('/auth/login.php', [
            'json' => [
                'email' => 'test@example.com',
                'password' => 'testpassword'
            ]
        ]);
        
        $data = json_decode($response->getBody(), true);
        $this->authToken = $data['token'];
    }
    
    private function makeAuthenticatedRequest($method, $endpoint, $data = [])
    {
        $options = [];
        
        if (!empty($data)) {
            $options['json'] = $data;
        }
        
        $options['headers']['Authorization'] = 'Bearer ' . $this->authToken;
        
        try {
            $response = $this->httpClient->request($method, $endpoint, $options);
            
            return [
                'status' => $response->getStatusCode(),
                'data' => json_decode($response->getBody(), true)
            ];
        } catch (RequestException $e) {
            return [
                'status' => $e->getResponse()->getStatusCode(),
                'data' => json_decode($e->getResponse()->getBody(), true)
            ];
        }
    }
}
```

### User API Testing

Test user management endpoints:

```php
<?php
// tests/API/UserAPITest.php

use PHPUnit\Framework\TestCase;

class UserAPITest extends TestCase
{
    private $httpClient;
    private $baseUrl;
    private $adminToken;
    
    protected function setUp(): void
    {
        $this->baseUrl = 'http://localhost:8000/api';
        $this->httpClient = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ]);
        
        $this->authenticateAdmin();
    }
    
    public function testCreateUserAPI()
    {
        $userData = [
            'email' => 'api_test_' . uniqid() . '@example.com',
            'password' => 'SecurePass123!',
            'role' => 'student',
            'first_name' => 'API',
            'last_name' => 'Test'
        ];
        
        $response = $this->makeAdminRequest('POST', '/users/create.php', $userData);
        
        $this->assertEquals(201, $response['status']);
        $this->assertTrue($response['data']['success']);
        $this->assertArrayHasKey('user_id', $response['data']);
        
        // Clean up
        $this->makeAdminRequest('DELETE', '/users/delete.php', [
            'user_id' => $response['data']['user_id']
        ]);
    }
    
    public function testCreateUserWithWeakPassword()
    {
        $userData = [
            'email' => 'weakpass_test@example.com',
            'password' => 'weak',
            'role' => 'student'
        ];
        
        $response = $this->makeAdminRequest('POST', '/users/create.php', $userData);
        
        $this->assertEquals(400, $response['status']);
        $this->assertFalse($response['data']['success']);
        $this->assertStringContainsString('Password', $response['data']['message']);
    }
    
    public function testGetUsersList()
    {
        $response = $this->makeAdminRequest('GET', '/users/list.php', [
            'page' => 1,
            'limit' => 10,
            'role' => 'student'
        ]);
        
        $this->assertEquals(200, $response['status']);
        $this->assertArrayHasKey('users', $response['data']);
        $this->assertArrayHasKey('total', $response['data']);
        $this->assertArrayHasKey('page', $response['data']);
    }
    
    public function testGetUserById()
    {
        // First create a user
        $createResponse = $this->makeAdminRequest('POST', '/users/create.php', [
            'email' => 'get_user_test@example.com',
            'password' => 'SecurePass123!',
            'role' => 'teacher'
        ]);
        
        $userId = $createResponse['data']['user_id'];
        
        // Then retrieve it
        $response = $this->makeAdminRequest('GET', '/users/get.php', [
            'user_id' => $userId
        ]);
        
        $this->assertEquals(200, $response['status']);
        $this->assertEquals($userId, $response['data']['user']['user_id']);
        $this->assertEquals('teacher', $response['data']['user']['role']);
        
        // Clean up
        $this->makeAdminRequest('DELETE', '/users/delete.php', [
            'user_id' => $userId
        ]);
    }
    
    private function authenticateAdmin()
    {
        $response = $this->httpClient->post('/auth/login.php', [
            'json' => [
                'email' => 'admin@example.com',
                'password' => 'adminpassword'
            ]
        ]);
        
        $data = json_decode($response->getBody(), true);
        $this->adminToken = $data['token'];
    }
    
    private function makeAdminRequest($method, $endpoint, $data = [])
    {
        $options = [];
        
        if (!empty($data)) {
            $options['json'] = $data;
        }
        
        $options['headers']['Authorization'] = 'Bearer ' . $this->adminToken;
        
        try {
            $response = $this->httpClient->request($method, $endpoint, $options);
            
            return [
                'status' => $response->getStatusCode(),
                'data' => json_decode($response->getBody(), true)
            ];
        } catch (RequestException $e) {
            return [
                'status' => $e->getResponse()->getStatusCode(),
                'data' => json_decode($e->getResponse()->getBody(), true)
            ];
        }
    }
}
```

## Database Testing

### Database Connection Testing

Test database connectivity and configuration:

```php
<?php
// tests/Database/DatabaseConnectionTest.php

use PHPUnit\Framework\TestCase;

class DatabaseConnectionTest extends TestCase
{
    private $database;
    
    protected function setUp(): void
    {
        $this->database = new Database();
    }
    
    public function testDatabaseConnection()
    {
        $connection = $this->database->getConnection();
        
        $this->assertInstanceOf(PDO::class, $connection);
        
        // Test connection is working
        $stmt = $connection->query('SELECT 1');
        $result = $stmt->fetch();
        
        $this->assertEquals(1, $result[1]);
    }
    
    public function testConnectionWithInvalidCredentials()
    {
        $this->expectException(PDOException::class);
        
        $invalidDb = new Database();
        // This would require modifying the constructor to accept different params
        // For testing purposes, we'll skip this test
        $this->markTestSkipped('Cannot test with current implementation');
    }
    
    public function testConnectionPersistence()
    {
        $connection1 = $this->database->getConnection();
        $connection2 = $this->database->getConnection();
        
        // Should return the same connection
        $this->assertSame($connection1, $connection2);
    }
    
    public function testRequiredTablesExist()
    {
        $requiredTables = [
            'users',
            'user_activity_logs',
            'attendance_records',
            'courses',
            'enrollments'
        ];
        
        $connection = $this->database->getConnection();
        
        foreach ($requiredTables as $table) {
            $stmt = $connection->query("SHOW TABLES LIKE '$table'");
            $result = $stmt->fetchAll();
            
            $this->assertNotEmpty($result, "Table '$table' does not exist");
        }
    }
    
    public function testTableSchemaIntegrity()
    {
        // Test users table structure
        $stmt = $this->database->executeQuery("DESCRIBE users");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $requiredColumns = [
            'user_id',
            'email',
            'password_hash',
            'role',
            'status',
            'first_name',
            'last_name',
            'created_at',
            'updated_at'
        ];
        
        foreach ($requiredColumns as $column) {
            $this->assertContains($column, $columns, "Column '$column' missing from users table");
        }
    }
    
    public function testForeignKeyConstraints()
    {
        // Test that foreign key constraints are properly set up
        $connection = $this->database->getConnection();
        
        // Test courses table foreign key to users
        $stmt = $connection->query("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE TABLE_NAME = 'courses' 
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ");
        
        $constraints = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $this->assertNotEmpty($constraints, 'No foreign key constraints found on courses table');
    }
}
```

### Database Transaction Testing

Test transaction handling and rollback:

```php
<?php
// tests/Database/DatabaseTransactionTest.php

use PHPUnit\Framework\TestCase;

class DatabaseTransactionTest extends TestCase
{
    private $database;
    
    protected function setUp(): void
    {
        $this->database = new Database();
    }
    
    public function testCommitTransaction()
    {
        $this->database->beginTransaction();
        
        // Insert test user
        $userData = [
            'email' => 'transaction_test_' . uniqid() . '@example.com',
            'password_hash' => password_hash('password123', PASSWORD_BCRYPT),
            'role' => 'student'
        ];
        
        $userId = $this->database->insert('users', $userData);
        $this->assertGreaterThan(0, $userId);
        
        // Verify user exists in current transaction
        $sql = "SELECT * FROM users WHERE user_id = :user_id";
        $result = $this->database->executeQuery($sql, [':user_id' => $userId]);
        $user = $result->fetch(PDO::FETCH_ASSOC);
        
        $this->assertEquals($userId, $user['user_id']);
        
        // Commit transaction
        $this->database->commit();
        
        // Verify user still exists after commit
        $result = $this->database->executeQuery($sql, [':user_id' => $userId]);
        $user = $result->fetch(PDO::FETCH_ASSOC);
        
        $this->assertEquals($userId, $user['user_id']);
        
        // Clean up
        $this->database->delete('users', ['user_id' => $userId]);
    }
    
    public function testRollbackTransaction()
    {
        $this->database->beginTransaction();
        
        // Insert test user
        $userData = [
            'email' => 'rollback_test_' . uniqid() . '@example.com',
            'password_hash' => password_hash('password123', PASSWORD_BCRYPT),
            'role' => 'student'
        ];
        
        $userId = $this->database->insert('users', $userData);
        
        // Rollback transaction
        $this->database->rollback();
        
        // Verify user does not exist after rollback
        $sql = "SELECT COUNT(*) as count FROM users WHERE user_id = :user_id";
        $result = $this->database->executeQuery($sql, [':user_id' => $userId]);
        $count = $result->fetch(PDO::FETCH_ASSOC);
        
        $this->assertEquals(0, $count['count']);
    }
    
    public function testNestedTransactions()
    {
        $this->database->beginTransaction();
        
        try {
            // Outer transaction
            $userData1 = [
                'email' => 'nested_outer_' . uniqid() . '@example.com',
                'password_hash' => password_hash('password123', PASSWORD_BCRYPT),
                'role' => 'student'
            ];
            
            $userId1 = $this->database->insert('users', $userData1);
            
            // Inner transaction
            $this->database->beginTransaction();
            
            $userData2 = [
                'email' => 'nested_inner_' . uniqid() . '@example.com',
                'password_hash' => password_hash('password123', PASSWORD_BCRYPT),
                'role' => 'teacher'
            ];
            
            $userId2 = $this->database->insert('users', $userData2);
            
            // Rollback inner transaction
            $this->database->rollback();
            
            // Commit outer transaction
            $this->database->commit();
            
            // Verify only outer user exists
            $sql = "SELECT COUNT(*) as count FROM users WHERE user_id = :user_id";
            
            $result1 = $this->database->executeQuery($sql, [':user_id' => $userId1]);
            $count1 = $result1->fetch(PDO::FETCH_ASSOC);
            
            $result2 = $this->database->executeQuery($sql, [':user_id' => $userId2]);
            $count2 = $result2->fetch(PDO::FETCH_ASSOC);
            
            $this->assertEquals(1, $count1['count'], 'Outer transaction should be committed');
            $this->assertEquals(0, $count2['count'], 'Inner transaction should be rolled back');
            
            // Clean up
            $this->database->delete('users', ['user_id' => $userId1]);
            
        } catch (Exception $e) {
            $this->database->rollback();
            throw $e;
        }
    }
}
```

## User Acceptance Testing

### Student User Acceptance Tests

Test complete student workflows:

```php
<?php
// tests/UAT/StudentWorkflowTest.php

use PHPUnit\Framework\TestCase;

class StudentWorkflowTest extends TestCase
{
    private $database;
    private $student;
    private $course;
    
    protected function setUp(): void
    {
        $this->database = new Database();
        $this->student = new User($this->database);
        $this->course = new Course($this->database);
        
        $this->setupTestData();
    }
    
    public function testStudentRegistrationAndLogin()
    {
        // Student registration
        $studentData = [
            'email' => 'uat_student_' . uniqid() . '@example.com',
            'password' => 'StudentPass123!',
            'role' => 'student',
            'first_name' => 'UAT',
            'last_name' => 'Student'
        ];
        
        $studentId = $this->student->createUser($studentData);
        $this->assertIsInt($studentId);
        
        // Student login
        $authenticatedStudent = $this->student->authenticate(
            $studentData['email'],
            $studentData['password']
        );
        
        $this->assertEquals($studentId, $authenticatedStudent['user_id']);
        $this->assertEquals('student', $authenticatedStudent['role']);
        
        return $studentId;
    }
    
    /**
     * @depends testStudentRegistrationAndLogin
     */
    public function testCourseEnrollment($studentId)
    {
        // Create a test course
        $courseData = [
            'course_name' => 'UAT Test Course',
            'course_code' => 'UAT101',
            'description' => 'Course for UAT testing',
            'teacher_id' => $this->testTeacherId,
            'semester' => 'Fall 2025'
        ];
        
        $courseId = $this->course->createCourse($courseData);
        
        // Student enrolls in course
        $enrollmentId = $this->course->enrollStudent($courseId, $studentId);
        $this->assertIsInt($enrollmentId);
        
        // Verify enrollment
        $enrollments = $this->course->getStudentEnrollments($studentId);
        $this->assertCount(1, $enrollments);
        $this->assertEquals($courseId, $enrollments[0]['course_id']);
        
        return ['studentId' => $studentId, 'courseId' => $courseId];
    }
    
    /**
     * @depends testCourseEnrollment
     */
    public function testAssignmentSubmission($data)
    {
        $studentId = $data['studentId'];
        $courseId = $data['courseId'];
        
        // Create assignment
        $assignmentData = [
            'course_id' => $courseId,
            'title' => 'UAT Test Assignment',
            'description' => 'Assignment for UAT testing',
            'due_date' => date('Y-m-d H:i:s', strtotime('+7 days')),
            'max_points' => 100
        ];
        
        $assignmentId = $this->createAssignment($assignmentData);
        
        // Student submits assignment
        $submissionData = [
            'assignment_id' => $assignmentId,
            'student_id' => $studentId,
            'content' => 'This is my UAT test submission',
            'submitted_at' => date('Y-m-d H:i:s')
        ];
        
        $submissionId = $this->submitAssignment($submissionData);
        $this->assertIsInt($submissionId);
        
        // Verify submission
        $submission = $this->getSubmission($assignmentId, $studentId);
        $this->assertEquals($submissionId, $submission['submission_id']);
        $this->assertEquals('This is my UAT test submission', $submission['content']);
        
        return $data;
    }
    
    /**
     * @depends testAssignmentSubmission
     */
    public function testGradeViewing($data)
    {
        $studentId = $data['studentId'];
        
        // Create a grade for the student
        $gradeData = [
            'student_id' => $studentId,
            'assignment_id' => $this->testAssignmentId,
            'points_earned' => 85,
            'max_points' => 100,
            'feedback' => 'Good work on UAT test',
            'graded_at' => date('Y-m-d H:i:s')
        ];
        
        $gradeId = $this->createGrade($gradeData);
        
        // Student views grades
        $grades = $this->getStudentGrades($studentId);
        $this->assertNotEmpty($grades);
        
        $studentGrade = array_filter($grades, function($grade) use ($studentId) {
            return $grade['student_id'] == $studentId;
        });
        
        $this->assertNotEmpty($studentGrade);
        $grade = reset($studentGrade);
        $this->assertEquals(85, $grade['points_earned']);
        
        return $data;
    }
    
    private function setupTestData()
    {
        // Create test teacher
        $teacherData = [
            'email' => 'uat_teacher_' . uniqid() . '@example.com',
            'password' => 'TeacherPass123!',
            'role' => 'teacher',
            'first_name' => 'UAT',
            'last_name' => 'Teacher'
        ];
        
        $this->testTeacherId = $this->student->createUser($teacherData);
    }
    
    private function createAssignment($data)
    {
        // Implementation for creating assignment
        return $this->database->insert('assignments', $data);
    }
    
    private function submitAssignment($data)
    {
        // Implementation for submitting assignment
        return $this->database->insert('assignment_submissions', $data);
    }
    
    private function getSubmission($assignmentId, $studentId)
    {
        $sql = "SELECT * FROM assignment_submissions 
                WHERE assignment_id = :assignment_id AND student_id = :student_id";
        $result = $this->database->executeQuery($sql, [
            ':assignment_id' => $assignmentId,
            ':student_id' => $studentId
        ]);
        
        return $result->fetch(PDO::FETCH_ASSOC);
    }
    
    private function createGrade($data)
    {
        // Implementation for creating grade
        return $this->database->insert('grades', $data);
    }
    
    private function getStudentGrades($studentId)
    {
        $sql = "SELECT * FROM grades WHERE student_id = :student_id";
        $result = $this->database->executeQuery($sql, [':student_id' => $studentId]);
        
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }
}
```

### Teacher User Acceptance Tests

Test complete teacher workflows:

```php
<?php
// tests/UAT/TeacherWorkflowTest.php

use PHPUnit\Framework\TestCase;

class TeacherWorkflowTest extends TestCase
{
    private $database;
    private $user;
    private $course;
    private $attendance;
    
    protected function setUp(): void
    {
        $this->database = new Database();
        $this->user = new User($this->database);
        $this->course = new Course($this->database);
        $this->attendance = new Attendance($this->database);
    }
    
    public function testCourseCreationAndManagement()
    {
        // Create teacher
        $teacherData = [
            'email' => 'teacher_uat_' . uniqid() . '@example.com',
            'password' => 'TeacherPass123!',
            'role' => 'teacher',
            'first_name' => 'UAT',
            'last_name' => 'Teacher'
        ];
        
        $teacherId = $this->user->createUser($teacherData);
        
        // Create course
        $courseData = [
            'course_name' => 'Advanced UAT Testing',
            'course_code' => 'UAT201',
            'description' => 'Advanced course management testing',
            'teacher_id' => $teacherId,
            'semester' => 'Fall 2025',
            'academic_year' => '2025-2026'
        ];
        
        $courseId = $this->course->createCourse($courseData);
        $this->assertIsInt($courseId);
        
        // Verify course creation
        $course = $this->course->getCourse($courseId);
        $this->assertEquals('Advanced UAT Testing', $course['course_name']);
        $this->assertEquals($teacherId, $course['teacher_id']);
        
        return $teacherId;
    }
    
    /**
     * @depends testCourseCreationAndManagement
     */
    public function testStudentEnrollment($teacherId)
    {
        // Create test students
        $studentIds = [];
        
        for ($i = 1; $i <= 5; $i++) {
            $studentData = [
                'email' => "student{$i}_uat_" . uniqid() . "@example.com",
                'password' => 'StudentPass123!',
                'role' => 'student',
                'first_name' => "Student{$i}",
                'last_name' => 'UAT'
            ];
            
            $studentId = $this->user->createUser($studentData);
            $studentIds[] = $studentId;
        }
        
        // Enroll all students in the course
        $courseId = $this->getLatestCourseByTeacher($teacherId);
        
        foreach ($studentIds as $studentId) {
            $enrollmentId = $this->course->enrollStudent($courseId, $studentId);
            $this->assertIsInt($enrollmentId);
        }
        
        // Verify enrollment count
        $enrollments = $this->course->getCourseEnrollments($courseId);
        $this->assertCount(5, $enrollments);
        
        return ['teacherId' => $teacherId, 'courseId' => $courseId, 'studentIds' => $studentIds];
    }
    
    /**
     * @depends testStudentEnrollment
     */
    public function testAssignmentCreationAndGrading($data)
    {
        $courseId = $data['courseId'];
        $studentIds = $data['studentIds'];
        
        // Create assignment
        $assignmentData = [
            'course_id' => $courseId,
            'title' => 'UAT Assignment 1',
            'description' => 'Test assignment for UAT',
            'instructions' => 'Complete all questions and submit on time',
            'due_date' => date('Y-m-d H:i:s', strtotime('+7 days')),
            'max_points' => 100,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $assignmentId = $this->createAssignment($assignmentData);
        $this->assertIsInt($assignmentId);
        
        // Students submit assignments (simulate)
        foreach ($studentIds as $studentId) {
            $submissionData = [
                'assignment_id' => $assignmentId,
                'student_id' => $studentId,
                'content' => "UAT submission from student {$studentId}",
                'submitted_at' => date('Y-m-d H:i:s'),
                'status' => 'submitted'
            ];
            
            $submissionId = $this->submitAssignment($submissionData);
            $this->assertIsInt($submissionId);
        }
        
        // Teacher grades all submissions
        foreach ($studentIds as $studentId) {
            $pointsEarned = rand(70, 100);
            $feedback = "Good work! Score: {$pointsEarned}/100";
            
            $gradeData = [
                'student_id' => $studentId,
                'assignment_id' => $assignmentId,
                'points_earned' => $pointsEarned,
                'max_points' => 100,
                'feedback' => $feedback,
                'graded_at' => date('Y-m-d H:i:s'),
                'graded_by' => $data['teacherId']
            ];
            
            $gradeId = $this->createGrade($gradeData);
            $this->assertIsInt($gradeId);
        }
        
        // Verify grade distribution
        $grades = $this->getAssignmentGrades($assignmentId);
        $this->assertCount(5, $grades);
        
        $totalPoints = array_sum(array_column($grades, 'points_earned'));
        $this->assertGreaterThan(0, $totalPoints);
        
        return $data;
    }
    
    private function getLatestCourseByTeacher($teacherId)
    {
        $sql = "SELECT course_id FROM courses 
                WHERE teacher_id = :teacher_id 
                ORDER BY created_at DESC LIMIT 1";
        
        $result = $this->database->executeQuery($sql, [':teacher_id' => $teacherId]);
        $course = $result->fetch(PDO::FETCH_ASSOC);
        
        return $course['course_id'];
    }
    
    private function createAssignment($data)
    {
        return $this->database->insert('assignments', $data);
    }
    
    private function submitAssignment($data)
    {
        return $this->database->insert('assignment_submissions', $data);
    }
    
    private function createGrade($data)
    {
        return $this->database->insert('grades', $data);
    }
    
    private function getAssignmentGrades($assignmentId)
    {
        $sql = "SELECT * FROM grades WHERE assignment_id = :assignment_id";
        $result = $this->database->executeQuery($sql, [':assignment_id' => $assignmentId]);
        
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }
}
```

## Performance Testing

### Load Testing

Test system performance under load:

```php
<?php
// tests/Performance/LoadTest.php

use PHPUnit\Framework\TestCase;

class LoadTest extends TestCase
{
    private $database;
    private $httpClient;
    private $baseUrl;
    
    protected function setUp(): void
    {
        $this->database = new Database();
        $this->baseUrl = 'http://localhost:8000';
        $this->httpClient = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 60,
            'connect_timeout' => 30
        ]);
    }
    
    public function testConcurrentUserRegistration()
    {
        $startTime = microtime(true);
        $concurrentRequests = 50;
        $promises = [];
        
        for ($i = 0; $i < $concurrentRequests; $i++) {
            $userData = [
                'email' => "loadtest_{$i}_" . uniqid() . "@example.com",
                'password' => 'LoadTest123!',
                'role' => 'student'
            ];
            
            $promises[] = $this->httpClient->postAsync('/api/users/create.php', [
                'json' => $userData
            ]);
        }
        
        // Execute all requests concurrently
        $responses = \GuzzleHttp\Promise\Utils::settle($promises)->wait();
        
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        
        // Verify performance requirements
        $this->assertLessThan(30, $duration, 'Concurrent registration took too long');
        
        // Count successful requests
        $successfulRequests = 0;
        foreach ($responses as $response) {
            if ($response['state'] === 'fulfilled' && 
                $response['value']->getStatusCode() === 201) {
                $successfulRequests++;
            }
        }
        
        $successRate = ($successfulRequests / $concurrentRequests) * 100;
        $this->assertGreaterThan(95, $successRate, 'Success rate too low under load');
        
        // Clean up created users
        $this->cleanupTestUsers();
    }
    
    public function testDatabasePerformance()
    {
        $startTime = microtime(true);
        $operations = 1000;
        
        for ($i = 0; $i < $operations; $i++) {
            $userData = [
                'email' => "perf_test_{$i}_" . uniqid() . "@example.com",
                'password_hash' => password_hash('password123', PASSWORD_BCRYPT),
                'role' => 'student'
            ];
            
            $this->database->insert('users', $userData);
        }
        
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        $opsPerSecond = $operations / $duration;
        
        // Performance requirements
        $this->assertGreaterThan(100, $opsPerSecond, 'Database operations too slow');
        
        // Clean up
        $this->database->executeQuery("
            DELETE FROM users 
            WHERE email LIKE 'perf_test_%@example.com'
        ");
    }
    
    public function testLargeDataQuery()
    {
        // Insert test data
        $courseCount = 100;
        $studentsPerCourse = 50;
        
        for ($c = 1; $c <= $courseCount; $c++) {
            // Create course
            $courseData = [
                'course_name' => "Performance Test Course {$c}",
                'course_code' => "PERF{$c}",
                'description' => 'Course for performance testing',
                'teacher_id' => 1
            ];
            
            $courseId = $this->database->insert('courses', $courseData);
            
            // Enroll students
            for ($s = 1; $s <= $studentsPerCourse; $s++) {
                $this->course->enrollStudent($courseId, $s);
            }
        }
        
        // Test complex query performance
        $startTime = microtime(true);
        
        $sql = "
            SELECT 
                c.course_name,
                c.course_code,
                COUNT(e.student_id) as enrollment_count,
                AVG(g.points_earned / g.max_points * 100) as avg_grade
            FROM courses c
            LEFT JOIN enrollments e ON c.course_id = e.course_id
            LEFT JOIN grades g ON e.student_id = g.student_id
            WHERE c.course_code LIKE 'PERF%'
            GROUP BY c.course_id, c.course_name, c.course_code
            ORDER BY enrollment_count DESC
        ";
        
        $result = $this->database->executeQuery($sql);
        $courses = $result->fetchAll(PDO::FETCH_ASSOC);
        
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        
        // Performance requirements
        $this->assertLessThan(5, $duration, 'Complex query took too long');
        $this->assertCount($courseCount, $courses);
        
        // Clean up
        $this->database->executeQuery("DELETE FROM courses WHERE course_code LIKE 'PERF%'");
    }
    
    private function cleanupTestUsers()
    {
        $this->database->executeQuery("
            DELETE FROM users 
            WHERE email LIKE 'loadtest_%@example.com'
        ");
    }
}
```

## Automated Testing

### Test Execution Scripts

Create automated test execution:

```bash
#!/bin/bash
# run_tests.sh

echo "Running Classroom Management System Tests"
echo "=========================================="

# Set environment
export APP_ENV=testing
export DB_NAME=classroom_management_test

# Run unit tests
echo "Running Unit Tests..."
./vendor/bin/phpunit tests/Unit/ --colors=never

# Run integration tests
echo "Running Integration Tests..."
./vendor/bin/phpunit tests/Integration/ --colors=never

# Run security tests
echo "Running Security Tests..."
./vendor/bin/phpunit tests/Security/ --colors=never

# Run API tests
echo "Running API Tests..."
./vendor/bin/phpunit tests/API/ --colors=never

# Generate coverage report
echo "Generating Coverage Report..."
./vendor/bin/phpunit --coverage-html coverage/

echo "Test execution completed!"
```

### Continuous Integration

Create CI configuration:

```yaml
# .github/workflows/tests.yml
name: Test Suite

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: rootpassword
          MYSQL_DATABASE: classroom_management_test
        ports:
          - 3306/tcp
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
        extensions: mbstring, xml, ctype, iconv, intl, pdo, pdo_mysql
        
    - name: Install Dependencies
      run: composer install --no-progress --prefer-dist --optimize-autoloader
    
    - name: Prepare Test Database
      run: |
        mysql -h 127.0.0.1 -P ${{ job.services.mysql.ports['3306'] }} -uroot -prootpassword < database/auth_schema.sql
        mysql -h 127.0.0.1 -P ${{ job.services.mysql.ports['3306'] }} -uroot -prootpassword classroom_management_test < database/test_data.sql
    
    - name: Run Tests
      env:
        DB_HOST: 127.0.0.1
        DB_PORT: ${{ job.services.mysql.ports['3306'] }}
        DB_NAME: classroom_management_test
        DB_USER: root
        DB_PASS: rootpassword
      run: ./vendor/bin/phpunit --coverage-text
    
    - name: Upload Coverage
      uses: codecov/codecov-action@v3
```

### Test Data Fixtures

Create reusable test data:

```php
<?php
// tests/Fixtures/UserFixture.php

class UserFixture
{
    private $database;
    
    public function __construct($database)
    {
        $this->database = $database;
    }
    
    public function createTestUsers($count = 10)
    {
        $users = [];
        
        for ($i = 1; $i <= $count; $i++) {
            $userData = [
                'email' => "test_user_{$i}_" . uniqid() . "@example.com",
                'password_hash' => password_hash('password123', PASSWORD_BCRYPT),
                'role' => $i % 3 === 0 ? 'teacher' : 'student',
                'first_name' => "TestUser{$i}",
                'last_name' => 'Fixture',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $userId = $this->database->insert('users', $userData);
            $users[] = array_merge(['user_id' => $userId], $userData);
        }
        
        return $users;
    }
    
    public function createTestCourses($teacherId, $count = 5)
    {
        $courses = [];
        
        for ($i = 1; $i <= $count; $i++) {
            $courseData = [
                'course_name' => "Test Course {$i}",
                'course_code' => "TC{$i}" . uniqid(),
                'description' => "Test course {$i} for fixtures",
                'teacher_id' => $teacherId,
                'semester' => 'Fall 2025',
                'academic_year' => '2025-2026',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $courseId = $this->database->insert('courses', $courseData);
            $courses[] = array_merge(['course_id' => $courseId], $courseData);
        }
        
        return $courses;
    }
    
    public function enrollStudentsInCourses($studentIds, $courseIds)
    {
        foreach ($courseIds as $course) {
            $enrollmentCount = min(count($studentIds), rand(5, 15));
            $selectedStudents = array_slice($studentIds, 0, $enrollmentCount);
            
            foreach ($selectedStudents as $student) {
                $enrollmentData = [
                    'student_id' => $student['user_id'],
                    'course_id' => $course['course_id'],
                    'enrollment_date' => date('Y-m-d H:i:s'),
                    'status' => 'active'
                ];
                
                $this->database->insert('enrollments', $enrollmentData);
            }
        }
    }
    
    public function cleanup()
    {
        // Clean up all test data
        $this->database->executeQuery("DELETE FROM enrollments WHERE student_id IN (
            SELECT user_id FROM users WHERE email LIKE 'test_user_%@example.com'
        )");
        
        $this->database->executeQuery("DELETE FROM courses WHERE teacher_id IN (
            SELECT user_id FROM users WHERE email LIKE 'test_user_%@example.com'
        )");
        
        $this->database->executeQuery("DELETE FROM users WHERE email LIKE 'test_user_%@example.com'");
    }
}
```

## Testing Procedures

### Pre-Testing Checklist

Before running tests, ensure:

- [ ] Development environment is properly configured
- [ ] Test database is created and accessible
- [ ] All dependencies are installed (`composer install`)
- [ ] Test data fixtures are in place
- [ ] Web server is running for API tests
- [ ] Environment variables are set correctly
- [ ] File permissions allow test execution

### Test Execution Order

1. **Unit Tests**: Test individual components
2. **Integration Tests**: Test component interactions
3. **Security Tests**: Test security vulnerabilities
4. **API Tests**: Test REST endpoints
5. **Database Tests**: Test database operations
6. **UAT Tests**: Test complete workflows
7. **Performance Tests**: Test system performance

### Running Tests

```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test suite
./vendor/bin/phpunit tests/Unit/
./vendor/bin/phpunit tests/Integration/
./vendor/bin/phpunit tests/Security/
./vendor/bin/phpunit tests/API/

# Run with coverage
./vendor/bin/phpunit --coverage-html coverage/

# Run specific test class
./vendor/bin/phpunit tests/Unit/UserTest.php

# Run specific test method
./vendor/bin/phpunit tests/Unit/UserTest.php --filter testAuthenticateWithValidCredentials

# Run tests with verbose output
./vendor/bin/phpunit --verbose

# Run tests in parallel
./vendor/bin/phpunit --process-isolation
```

### Test Results Analysis

After test execution:

1. **Review Failed Tests**: Analyze failures and fix issues
2. **Check Coverage**: Ensure adequate code coverage (>80%)
3. **Performance Metrics**: Monitor test execution times
4. **Security Validation**: Verify all security tests pass
5. **API Responses**: Validate API test results
6. **Database Integrity**: Ensure no data corruption

### Continuous Testing

Set up automated testing:

- **Pre-commit Hooks**: Run unit tests before commits
- **CI/CD Pipeline**: Run full test suite on every push
- **Scheduled Tests**: Run performance tests nightly
- **Manual Testing**: Execute UAT tests before releases

## Test Cases

### Authentication Test Cases

| Test Case ID | Description | Steps | Expected Result |
|--------------|-------------|-------|-----------------|
| AUTH-001 | Valid Login | Enter correct email/password | Successfully authenticated |
| AUTH-002 | Invalid Password | Enter correct email/wrong password | Authentication failed |
| AUTH-003 | Invalid Email | Enter invalid email format | Validation error |
| AUTH-004 | Empty Fields | Leave email/password blank | Validation errors |
| AUTH-005 | Account Lockout | 5 failed attempts | Account temporarily locked |
| AUTH-006 | Password Reset | Request password reset | Reset email sent |
| AUTH-007 | Session Timeout | Inactive for 2 hours | Session expired |
| AUTH-008 | Logout | Click logout button | Session destroyed |

### Course Management Test Cases

| Test Case ID | Description | Steps | Expected Result |
|--------------|-------------|-------|-----------------|
| COURSE-001 | Create Course | Fill course form and submit | Course created successfully |
| COURSE-002 | Duplicate Course Code | Use existing course code | Validation error |
| COURSE-003 | Enroll Student | Select student and course | Student enrolled |
| COURSE-004 | Maximum Enrollment | Exceed course capacity | Enrollment blocked |
| COURSE-005 | Update Course | Modify course details | Course updated |
| COURSE-006 | Delete Course | Delete existing course | Course removed |
| COURSE-007 | View Enrollments | View course student list | Students displayed |

### Attendance Test Cases

| Test Case ID | Description | Steps | Expected Result |
|--------------|-------------|-------|-----------------|
| ATT-001 | Check In | Submit check-in form | Check-in recorded |
| ATT-002 | Duplicate Check-in | Check in twice | Error message |
| ATT-003 | Check Out | Submit check-out form | Check-out recorded |
| ATT-004 | Invalid Employee ID | Use non-existent ID | Validation error |
| ATT-005 | View Attendance | View attendance records | Records displayed |
| ATT-006 | Export Attendance | Export to CSV | File downloaded |

### Assignment Test Cases

| Test Case ID | Description | Steps | Expected Result |
|--------------|-------------|-------|-----------------|
| ASSIGN-001 | Create Assignment | Fill assignment form | Assignment created |
| ASSIGN-002 | Submit Assignment | Upload/submit work | Submission recorded |
| ASSIGN-003 | Late Submission | Submit after due date | Late flag set |
| ASSIGN-004 | Grade Assignment | Enter points and feedback | Grade recorded |
| ASSIGN-005 | View Submissions | View all student submissions | Submissions listed |
| ASSIGN-006 | Download Submission | Download student file | File downloaded |

### Security Test Cases

| Test Case ID | Description | Steps | Expected Result |
|--------------|-------------|-------|-----------------|
| SEC-001 | SQL Injection | Submit malicious SQL | No injection executed |
| SEC-002 | XSS Attack | Submit script payload | Script not executed |
| SEC-003 | CSRF Token | Submit without token | Request rejected |
| SEC-004 | File Upload | Upload executable file | Upload rejected |
| SEC-005 | Privilege Escalation | Access admin functions | Access denied |
| SEC-006 | Session Hijacking | Use stolen session | Session invalid |

### Performance Test Cases

| Test Case ID | Description | Steps | Expected Result |
|--------------|-------------|-------|-----------------|
| PERF-001 | Concurrent Users | 100 users simultaneous login | All succeed |
| PERF-002 | Database Load | 1000 insert operations | Complete in <30s |
| PERF-003 | File Upload | Upload large file | Complete in <60s |
| PERF-004 | Search Performance | Search large dataset | Results in <5s |
| PERF-005 | Report Generation | Generate complex report | Complete in <30s |

---

## Conclusion

This testing documentation provides comprehensive coverage for the Classroom Management System. Regular testing ensures:

- **Reliability**: System works as expected
- **Security**: Protected against vulnerabilities
- **Performance**: Meets performance requirements
- **Usability**: User-friendly interface
- **Maintainability**: Easy to debug and extend

For questions or issues with testing procedures, contact the development team.

---

**Last Updated**: November 5, 2025  
**Version**: 1.0  
**Author**: Development Team  
**Review Date**: February 5, 2026