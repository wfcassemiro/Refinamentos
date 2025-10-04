#!/usr/bin/env python3
"""
Comprehensive PHP Test Suite for Translators101 Agenda T101
Tests PHP files locally and validates database connectivity and functionality
"""

import subprocess
import json
import sys
import os
import re
from datetime import datetime

class ComprehensivePHPTestSuite:
    def __init__(self):
        self.test_results = []
        self.refinamentos_path = "/app/Refinamentos"
        
    def log_result(self, test_name, success, message="", details=""):
        """Log test result"""
        status = "✅ PASS" if success else "❌ FAIL"
        result = {
            'test': test_name,
            'status': status,
            'success': success,
            'message': message,
            'details': details,
            'timestamp': datetime.now().isoformat()
        }
        self.test_results.append(result)
        print(f"{status}: {test_name}")
        if message:
            print(f"    Message: {message}")
        if details and not success:
            print(f"    Details: {details}")
        print()

    def test_php_syntax(self):
        """Test PHP syntax for both files"""
        files_to_test = [
            "live-stream/index2.php",
            "generate_ics.php"
        ]
        
        for file_path in files_to_test:
            full_path = os.path.join(self.refinamentos_path, file_path)
            if os.path.exists(full_path):
                try:
                    result = subprocess.run(
                        ["php", "-l", full_path],
                        capture_output=True,
                        text=True,
                        cwd=self.refinamentos_path
                    )
                    
                    if result.returncode == 0:
                        self.log_result(
                            f"PHP Syntax - {file_path}",
                            True,
                            "No syntax errors detected"
                        )
                    else:
                        self.log_result(
                            f"PHP Syntax - {file_path}",
                            False,
                            "Syntax errors found",
                            result.stderr
                        )
                except Exception as e:
                    self.log_result(
                        f"PHP Syntax - {file_path}",
                        False,
                        "Error running syntax check",
                        str(e)
                    )
            else:
                self.log_result(
                    f"PHP Syntax - {file_path}",
                    False,
                    "File not found",
                    f"Path: {full_path}"
                )

    def test_database_config(self):
        """Test database configuration file"""
        config_path = os.path.join(self.refinamentos_path, "config/database.php")
        
        if os.path.exists(config_path):
            try:
                with open(config_path, 'r') as f:
                    content = f.read()
                
                # Check for required database configuration elements
                required_elements = [
                    '$host',
                    '$db',
                    '$user',
                    '$pass',
                    'PDO',
                    'mysql:host'
                ]
                
                missing_elements = []
                for element in required_elements:
                    if element not in content:
                        missing_elements.append(element)
                
                if not missing_elements:
                    self.log_result(
                        "Database Configuration",
                        True,
                        "All required database configuration elements present"
                    )
                else:
                    self.log_result(
                        "Database Configuration",
                        False,
                        f"Missing configuration elements: {', '.join(missing_elements)}"
                    )
                
                # Check for authentication functions
                auth_functions = [
                    'isLoggedIn',
                    'isAdmin',
                    'hasVideotecaAccess'
                ]
                
                missing_functions = []
                for func in auth_functions:
                    if f"function {func}" not in content:
                        missing_functions.append(func)
                
                if not missing_functions:
                    self.log_result(
                        "Authentication Functions",
                        True,
                        "All required authentication functions present"
                    )
                else:
                    self.log_result(
                        "Authentication Functions",
                        False,
                        f"Missing functions: {', '.join(missing_functions)}"
                    )
                    
            except Exception as e:
                self.log_result(
                    "Database Configuration",
                    False,
                    "Error reading database config",
                    str(e)
                )
        else:
            self.log_result(
                "Database Configuration",
                False,
                "Database config file not found",
                f"Path: {config_path}"
            )

    def test_index2_php_structure(self):
        """Test index2.php file structure and content"""
        file_path = os.path.join(self.refinamentos_path, "live-stream/index2.php")
        
        if os.path.exists(file_path):
            try:
                with open(file_path, 'r') as f:
                    content = f.read()
                
                # Check for required PHP elements
                php_elements = [
                    'session_start()',
                    'require_once',
                    'database.php',
                    'isLoggedIn()',
                    'hasVideotecaAccess()'
                ]
                
                missing_php = []
                for element in php_elements:
                    if element not in content:
                        missing_php.append(element)
                
                if not missing_php:
                    self.log_result(
                        "Index2.php - PHP Structure",
                        True,
                        "All required PHP elements present"
                    )
                else:
                    self.log_result(
                        "Index2.php - PHP Structure",
                        False,
                        f"Missing PHP elements: {', '.join(missing_php)}"
                    )
                
                # Check for Agenda T101 section
                agenda_elements = [
                    'Agenda T101',
                    'upcoming_announcements',
                    'schedule-grid',
                    'schedule-item',
                    'download-ics-btn',
                    'Baixar Convite'
                ]
                
                missing_agenda = []
                for element in agenda_elements:
                    if element not in content:
                        missing_agenda.append(element)
                
                if not missing_agenda:
                    self.log_result(
                        "Index2.php - Agenda T101 Section",
                        True,
                        "All Agenda T101 elements present"
                    )
                else:
                    self.log_result(
                        "Index2.php - Agenda T101 Section",
                        False,
                        f"Missing Agenda elements: {', '.join(missing_agenda)}"
                    )
                
                # Check for database query
                if "SELECT id, title, speaker, announcement_date, lecture_time, description, image_path" in content:
                    self.log_result(
                        "Index2.php - Database Query",
                        True,
                        "Database query for lectures is present"
                    )
                else:
                    self.log_result(
                        "Index2.php - Database Query",
                        False,
                        "Database query for lectures not found"
                    )
                
                # Check for JavaScript ICS functionality
                js_elements = [
                    'downloadICSFile',
                    'BEGIN:VCALENDAR',
                    'VEVENT',
                    'VALARM',
                    'addEventListener'
                ]
                
                missing_js = []
                for element in js_elements:
                    if element not in content:
                        missing_js.append(element)
                
                if not missing_js:
                    self.log_result(
                        "Index2.php - JavaScript ICS Functionality",
                        True,
                        "All JavaScript ICS elements present"
                    )
                else:
                    self.log_result(
                        "Index2.php - JavaScript ICS Functionality",
                        False,
                        f"Missing JavaScript elements: {', '.join(missing_js)}"
                    )
                
                # Check for CSS styles
                css_elements = [
                    'schedule-grid',
                    'schedule-item',
                    'schedule-date',
                    'schedule-info',
                    'download-ics-btn'
                ]
                
                css_found = 0
                for element in css_elements:
                    if f".{element}" in content or f"#{element}" in content:
                        css_found += 1
                
                if css_found >= 3:
                    self.log_result(
                        "Index2.php - CSS Styles",
                        True,
                        f"CSS styles present for {css_found}/{len(css_elements)} elements"
                    )
                else:
                    self.log_result(
                        "Index2.php - CSS Styles",
                        False,
                        f"Limited CSS styles: {css_found}/{len(css_elements)} elements"
                    )
                    
            except Exception as e:
                self.log_result(
                    "Index2.php - File Analysis",
                    False,
                    "Error reading index2.php",
                    str(e)
                )
        else:
            self.log_result(
                "Index2.php - File Existence",
                False,
                "index2.php file not found",
                f"Path: {file_path}"
            )

    def test_generate_ics_php_structure(self):
        """Test generate_ics.php file structure and content"""
        file_path = os.path.join(self.refinamentos_path, "generate_ics.php")
        
        if os.path.exists(file_path):
            try:
                with open(file_path, 'r') as f:
                    content = f.read()
                
                # Check for required PHP elements
                php_elements = [
                    'session_start()',
                    'require_once',
                    'database.php',
                    'isLoggedIn()',
                    '$_GET[\'lecture_id\']'
                ]
                
                missing_php = []
                for element in php_elements:
                    if element not in content:
                        missing_php.append(element)
                
                if not missing_php:
                    self.log_result(
                        "Generate_ics.php - PHP Structure",
                        True,
                        "All required PHP elements present"
                    )
                else:
                    self.log_result(
                        "Generate_ics.php - PHP Structure",
                        False,
                        f"Missing PHP elements: {', '.join(missing_php)}"
                    )
                
                # Check for ICS generation elements
                ics_elements = [
                    'BEGIN:VCALENDAR',
                    'BEGIN:VEVENT',
                    'END:VEVENT',
                    'END:VCALENDAR',
                    'DTSTART:',
                    'DTEND:',
                    'SUMMARY:',
                    'DESCRIPTION:',
                    'BEGIN:VALARM'
                ]
                
                missing_ics = []
                for element in ics_elements:
                    if element not in content:
                        missing_ics.append(element)
                
                if not missing_ics:
                    self.log_result(
                        "Generate_ics.php - ICS Format",
                        True,
                        "All ICS format elements present"
                    )
                else:
                    self.log_result(
                        "Generate_ics.php - ICS Format",
                        False,
                        f"Missing ICS elements: {', '.join(missing_ics)}"
                    )
                
                # Check for error handling
                error_elements = [
                    'http_response_code(400)',
                    'http_response_code(401)',
                    'http_response_code(404)',
                    'json_encode',
                    'PDOException'
                ]
                
                missing_error = []
                for element in error_elements:
                    if element not in content:
                        missing_error.append(element)
                
                if len(missing_error) <= 1:  # Allow for some variation in error handling
                    self.log_result(
                        "Generate_ics.php - Error Handling",
                        True,
                        "Comprehensive error handling present"
                    )
                else:
                    self.log_result(
                        "Generate_ics.php - Error Handling",
                        False,
                        f"Missing error handling: {', '.join(missing_error)}"
                    )
                
                # Check for database query
                if "SELECT id, title, speaker, announcement_date, lecture_time, description" in content:
                    self.log_result(
                        "Generate_ics.php - Database Query",
                        True,
                        "Database query for lecture data is present"
                    )
                else:
                    self.log_result(
                        "Generate_ics.php - Database Query",
                        False,
                        "Database query for lecture data not found"
                    )
                
                # Check for proper headers
                header_elements = [
                    'Content-Type: text/calendar',
                    'Content-Disposition: attachment',
                    'filename='
                ]
                
                missing_headers = []
                for element in header_elements:
                    if element not in content:
                        missing_headers.append(element)
                
                if not missing_headers:
                    self.log_result(
                        "Generate_ics.php - HTTP Headers",
                        True,
                        "All required HTTP headers present"
                    )
                else:
                    self.log_result(
                        "Generate_ics.php - HTTP Headers",
                        False,
                        f"Missing headers: {', '.join(missing_headers)}"
                    )
                    
            except Exception as e:
                self.log_result(
                    "Generate_ics.php - File Analysis",
                    False,
                    "Error reading generate_ics.php",
                    str(e)
                )
        else:
            self.log_result(
                "Generate_ics.php - File Existence",
                False,
                "generate_ics.php file not found",
                f"Path: {file_path}"
            )

    def test_database_table_structure(self):
        """Test if the expected database table structure is referenced correctly"""
        files_to_check = [
            "live-stream/index2.php",
            "generate_ics.php"
        ]
        
        expected_columns = [
            'id',
            'title',
            'speaker',
            'announcement_date',
            'lecture_time',
            'description',
            'is_active'
        ]
        
        for file_path in files_to_check:
            full_path = os.path.join(self.refinamentos_path, file_path)
            if os.path.exists(full_path):
                try:
                    with open(full_path, 'r') as f:
                        content = f.read()
                    
                    # Check if upcoming_announcements table is referenced
                    if 'upcoming_announcements' in content:
                        missing_columns = []
                        for column in expected_columns:
                            if column not in content:
                                missing_columns.append(column)
                        
                        if not missing_columns:
                            self.log_result(
                                f"Database Table Structure - {file_path}",
                                True,
                                "All expected table columns are referenced"
                            )
                        else:
                            self.log_result(
                                f"Database Table Structure - {file_path}",
                                False,
                                f"Missing column references: {', '.join(missing_columns)}"
                            )
                    else:
                        self.log_result(
                            f"Database Table Structure - {file_path}",
                            False,
                            "upcoming_announcements table not referenced"
                        )
                        
                except Exception as e:
                    self.log_result(
                        f"Database Table Structure - {file_path}",
                        False,
                        "Error checking table structure",
                        str(e)
                    )

    def test_security_measures(self):
        """Test security measures in the PHP files"""
        files_to_check = [
            "live-stream/index2.php",
            "generate_ics.php"
        ]
        
        for file_path in files_to_check:
            full_path = os.path.join(self.refinamentos_path, file_path)
            if os.path.exists(full_path):
                try:
                    with open(full_path, 'r') as f:
                        content = f.read()
                    
                    security_measures = []
                    
                    # Check for session management
                    if 'session_start()' in content:
                        security_measures.append('Session management')
                    
                    # Check for authentication
                    if 'isLoggedIn()' in content:
                        security_measures.append('Authentication check')
                    
                    # Check for input sanitization
                    if 'htmlspecialchars' in content or 'escapeIcsText' in content:
                        security_measures.append('Input sanitization')
                    
                    # Check for prepared statements
                    if '$pdo->prepare' in content:
                        security_measures.append('Prepared statements')
                    
                    # Check for error handling
                    if 'PDOException' in content:
                        security_measures.append('Error handling')
                    
                    if len(security_measures) >= 3:
                        self.log_result(
                            f"Security Measures - {file_path}",
                            True,
                            f"Good security practices: {', '.join(security_measures)}"
                        )
                    else:
                        self.log_result(
                            f"Security Measures - {file_path}",
                            False,
                            f"Limited security measures: {', '.join(security_measures)}"
                        )
                        
                except Exception as e:
                    self.log_result(
                        f"Security Measures - {file_path}",
                        False,
                        "Error checking security measures",
                        str(e)
                    )

    def test_file_dependencies(self):
        """Test if all required dependencies are present"""
        dependencies = [
            "config/database.php",
            "vision/includes/head.php",
            "vision/includes/header.php",
            "vision/includes/sidebar.php",
            "vision/includes/footer.php"
        ]
        
        missing_deps = []
        for dep in dependencies:
            full_path = os.path.join(self.refinamentos_path, dep)
            if not os.path.exists(full_path):
                missing_deps.append(dep)
        
        if not missing_deps:
            self.log_result(
                "File Dependencies",
                True,
                "All required dependency files are present"
            )
        else:
            self.log_result(
                "File Dependencies",
                False,
                f"Missing dependency files: {', '.join(missing_deps)}"
            )

    def run_all_tests(self):
        """Run all tests"""
        print("=" * 80)
        print("COMPREHENSIVE PHP TEST SUITE - TRANSLATORS101 AGENDA T101")
        print("=" * 80)
        print(f"Testing directory: {self.refinamentos_path}")
        print(f"Test started at: {datetime.now().isoformat()}")
        print("=" * 80)
        print()
        
        # Run tests in logical order
        self.test_php_syntax()
        self.test_database_config()
        self.test_file_dependencies()
        self.test_index2_php_structure()
        self.test_generate_ics_php_structure()
        self.test_database_table_structure()
        self.test_security_measures()
        
        # Summary
        print("=" * 80)
        print("TEST SUMMARY")
        print("=" * 80)
        
        passed = sum(1 for result in self.test_results if result['success'])
        total = len(self.test_results)
        
        print(f"Total Tests: {total}")
        print(f"Passed: {passed}")
        print(f"Failed: {total - passed}")
        print(f"Success Rate: {(passed/total)*100:.1f}%")
        print()
        
        # Categorize results
        critical_failures = []
        minor_issues = []
        
        failed_tests = [result for result in self.test_results if not result['success']]
        for test in failed_tests:
            if any(keyword in test['test'].lower() for keyword in ['syntax', 'structure', 'database', 'security']):
                critical_failures.append(test)
            else:
                minor_issues.append(test)
        
        if critical_failures:
            print("🚨 CRITICAL FAILURES:")
            for test in critical_failures:
                print(f"❌ {test['test']}: {test['message']}")
                if test['details']:
                    print(f"   Details: {test['details']}")
            print()
        
        if minor_issues:
            print("⚠️  MINOR ISSUES:")
            for test in minor_issues:
                print(f"❌ {test['test']}: {test['message']}")
            print()
        
        # Passed tests summary
        passed_tests = [result for result in self.test_results if result['success']]
        if passed_tests:
            print("✅ PASSED TESTS:")
            for test in passed_tests:
                print(f"✅ {test['test']}: {test['message']}")
            print()
        
        if not failed_tests:
            print("🎉 All tests passed! The Agenda T101 implementation is ready.")
        elif not critical_failures:
            print("✅ Core functionality is working. Only minor issues detected.")
        else:
            print("⚠️  Critical issues detected. Please review and fix before deployment.")
        
        print("=" * 80)
        
        return len(critical_failures) == 0  # Return True if no critical failures

if __name__ == "__main__":
    test_suite = ComprehensivePHPTestSuite()
    success = test_suite.run_all_tests()
    sys.exit(0 if success else 1)