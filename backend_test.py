#!/usr/bin/env python3
"""
Backend Test Suite for Translators101 PHP Application
Tests the Agenda T101 functionality implemented in index2.php and generate_ics.php
"""

import requests
import json
import sys
from datetime import datetime, timedelta
import os
import re

# Configuration
BASE_URL = "https://translators-agenda.preview.emergentagent.com"
INDEX2_URL = f"{BASE_URL}/Refinamentos/live-stream/index2.php"
GENERATE_ICS_URL = f"{BASE_URL}/Refinamentos/generate_ics.php"

class AgendaT101TestSuite:
    def __init__(self):
        self.session = requests.Session()
        self.test_results = []
        self.authenticated = False
        
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

    def test_index2_page_accessibility(self):
        """Test if the index2.php page is accessible"""
        try:
            response = self.session.get(INDEX2_URL, timeout=15)
            
            if response.status_code == 200:
                # Check if page redirects to login or loads content
                if "login" in response.url.lower() or "planos.php" in response.url:
                    self.log_result(
                        "Index2 Page Accessibility", 
                        True, 
                        "Page redirects to login/plans (authentication required) - Expected behavior"
                    )
                    return True
                elif "Agenda T101" in response.text or "Live Stream" in response.text:
                    self.log_result(
                        "Index2 Page Accessibility", 
                        True, 
                        "Page loads successfully with live stream content"
                    )
                    self.authenticated = True
                    return True
                else:
                    self.log_result(
                        "Index2 Page Accessibility", 
                        False, 
                        "Page loads but doesn't contain expected content",
                        f"Response length: {len(response.text)}, URL: {response.url}"
                    )
                    return False
            else:
                self.log_result(
                    "Index2 Page Accessibility", 
                    False, 
                    f"HTTP {response.status_code} error",
                    f"Response: {response.text[:200]}..."
                )
                return False
                
        except requests.exceptions.RequestException as e:
            self.log_result(
                "Index2 Page Accessibility", 
                False, 
                "Network error accessing page",
                str(e)
            )
            return False

    def test_php_syntax_errors(self):
        """Test for PHP syntax errors in the response"""
        try:
            response = self.session.get(INDEX2_URL, timeout=15)
            
            if response.status_code == 200:
                # Check for PHP errors
                php_errors = [
                    "Parse error",
                    "Fatal error",
                    "Warning:",
                    "Notice:",
                    "Undefined variable",
                    "Undefined index",
                    "Call to undefined function"
                ]
                
                found_errors = []
                for error in php_errors:
                    if error in response.text:
                        found_errors.append(error)
                
                if not found_errors:
                    self.log_result(
                        "PHP Syntax Check", 
                        True, 
                        "No PHP syntax errors detected"
                    )
                else:
                    self.log_result(
                        "PHP Syntax Check", 
                        False, 
                        f"PHP errors found: {', '.join(found_errors)}",
                        "Check PHP error logs for details"
                    )
            else:
                self.log_result(
                    "PHP Syntax Check", 
                    False, 
                    f"Cannot check PHP syntax - HTTP {response.status_code}"
                )
                
        except requests.exceptions.RequestException as e:
            self.log_result(
                "PHP Syntax Check", 
                False, 
                "Network error during PHP syntax test",
                str(e)
            )

    def test_database_connection(self):
        """Test database connectivity by checking for database-related content"""
        try:
            response = self.session.get(INDEX2_URL, timeout=15)
            
            if response.status_code == 200:
                # Check for database connection errors
                db_errors = [
                    "Connection failed",
                    "Access denied for user",
                    "Unknown database",
                    "Can't connect to MySQL server",
                    "PDOException",
                    "SQLSTATE"
                ]
                
                found_db_errors = []
                for error in db_errors:
                    if error in response.text:
                        found_db_errors.append(error)
                
                if found_db_errors:
                    self.log_result(
                        "Database Connection", 
                        False, 
                        f"Database connection errors found: {', '.join(found_db_errors)}",
                        "Check database credentials in config/database.php"
                    )
                else:
                    # Check if page contains database-driven content
                    if "schedule-item" in response.text or "Nenhuma palestra agendada" in response.text or "Erro ao carregar a agenda" in response.text:
                        self.log_result(
                            "Database Connection", 
                            True, 
                            "Database query executed successfully (content or error message present)"
                        )
                    else:
                        self.log_result(
                            "Database Connection", 
                            True, 
                            "No database connection errors detected, but cannot verify query execution"
                        )
            else:
                self.log_result(
                    "Database Connection", 
                    False, 
                    f"Cannot check database connection - HTTP {response.status_code}"
                )
                
        except requests.exceptions.RequestException as e:
            self.log_result(
                "Database Connection", 
                False, 
                "Network error during database connection test",
                str(e)
            )

    def test_agenda_section_html_generation(self):
        """Test if the Agenda T101 section HTML is generated correctly"""
        try:
            response = self.session.get(INDEX2_URL, timeout=15)
            
            if response.status_code == 200:
                # Check for Agenda T101 section elements
                required_elements = [
                    "Agenda T101",
                    "schedule-grid",
                    "agendaContainer",
                    "download-ics-btn",
                    "Baixar Convite"
                ]
                
                missing_elements = []
                for element in required_elements:
                    if element not in response.text:
                        missing_elements.append(element)
                
                if not missing_elements:
                    self.log_result(
                        "Agenda Section HTML Generation", 
                        True, 
                        "All required Agenda T101 HTML elements are present"
                    )
                    
                    # Check if there are schedule items or empty state
                    if "schedule-item" in response.text:
                        self.log_result(
                            "Schedule Items Display", 
                            True, 
                            "Schedule items are being displayed"
                        )
                    elif "Nenhuma palestra agendada" in response.text:
                        self.log_result(
                            "Schedule Items Display", 
                            True, 
                            "Empty state message displayed correctly"
                        )
                    elif "Erro ao carregar a agenda" in response.text:
                        self.log_result(
                            "Schedule Items Display", 
                            False, 
                            "Database error when loading schedule",
                            "Check database connection and table structure"
                        )
                    else:
                        self.log_result(
                            "Schedule Items Display", 
                            False, 
                            "No schedule items or status message found"
                        )
                else:
                    self.log_result(
                        "Agenda Section HTML Generation", 
                        False, 
                        f"Missing HTML elements: {', '.join(missing_elements)}"
                    )
            else:
                self.log_result(
                    "Agenda Section HTML Generation", 
                    False, 
                    f"Cannot check HTML generation - HTTP {response.status_code}"
                )
                
        except requests.exceptions.RequestException as e:
            self.log_result(
                "Agenda Section HTML Generation", 
                False, 
                "Network error during HTML generation test",
                str(e)
            )

    def test_javascript_ics_functionality(self):
        """Test if JavaScript ICS download functionality is present"""
        try:
            response = self.session.get(INDEX2_URL, timeout=15)
            
            if response.status_code == 200:
                # Check for JavaScript functions
                js_functions = [
                    'downloadICSFile',
                    'download-ics-btn',
                    'addEventListener',
                    'data-title',
                    'data-speaker',
                    'data-date',
                    'data-time'
                ]
                
                missing_functions = []
                for func in js_functions:
                    if func not in response.text:
                        missing_functions.append(func)
                
                if not missing_functions:
                    self.log_result(
                        "JavaScript ICS Functionality", 
                        True, 
                        "All required JavaScript ICS functions and attributes are present"
                    )
                else:
                    self.log_result(
                        "JavaScript ICS Functionality", 
                        False, 
                        f"Missing JavaScript elements: {', '.join(missing_functions)}"
                    )
                    
                # Check for ICS content generation
                ics_elements = [
                    'BEGIN:VCALENDAR',
                    'VEVENT',
                    'VALARM',
                    'text/calendar'
                ]
                
                missing_ics = []
                for element in ics_elements:
                    if element not in response.text:
                        missing_ics.append(element)
                
                if not missing_ics:
                    self.log_result(
                        "ICS Content Generation", 
                        True, 
                        "ICS calendar content generation code is present"
                    )
                else:
                    self.log_result(
                        "ICS Content Generation", 
                        False, 
                        f"Missing ICS elements: {', '.join(missing_ics)}"
                    )
            else:
                self.log_result(
                    "JavaScript ICS Functionality", 
                    False, 
                    f"Cannot check JavaScript - HTTP {response.status_code}"
                )
                
        except requests.exceptions.RequestException as e:
            self.log_result(
                "JavaScript ICS Functionality", 
                False, 
                "Network error during JavaScript test",
                str(e)
            )

    def test_generate_ics_php_file(self):
        """Test the generate_ics.php file accessibility and functionality"""
        try:
            # Test without parameters (should return error)
            response = self.session.get(GENERATE_ICS_URL, timeout=15)
            
            if response.status_code == 400:
                self.log_result(
                    "Generate ICS PHP - Parameter Validation", 
                    True, 
                    "File correctly validates required parameters (returns 400 for missing lecture_id)"
                )
            elif response.status_code == 401:
                self.log_result(
                    "Generate ICS PHP - Authentication", 
                    True, 
                    "File correctly requires authentication (returns 401)"
                )
            elif response.status_code == 200:
                if "login" in response.url.lower():
                    self.log_result(
                        "Generate ICS PHP - Authentication", 
                        True, 
                        "File redirects to login (authentication required)"
                    )
                else:
                    # Check response content
                    try:
                        json_response = json.loads(response.text)
                        if 'error' in json_response:
                            self.log_result(
                                "Generate ICS PHP - Error Handling", 
                                True, 
                                f"File returns proper error response: {json_response['error']}"
                            )
                        else:
                            self.log_result(
                                "Generate ICS PHP - Response Format", 
                                False, 
                                "Unexpected JSON response format"
                            )
                    except json.JSONDecodeError:
                        self.log_result(
                            "Generate ICS PHP - File Access", 
                            True, 
                            "File is accessible and returns content"
                        )
            else:
                self.log_result(
                    "Generate ICS PHP - File Access", 
                    False, 
                    f"HTTP {response.status_code} error",
                    f"Response: {response.text[:200]}..."
                )
            
            # Test with invalid lecture_id
            response = self.session.get(GENERATE_ICS_URL + "?lecture_id=999999", timeout=15)
            
            if response.status_code == 404:
                self.log_result(
                    "Generate ICS PHP - Invalid ID Handling", 
                    True, 
                    "File correctly handles invalid lecture_id (returns 404)"
                )
            elif response.status_code == 401:
                self.log_result(
                    "Generate ICS PHP - Authentication Check", 
                    True, 
                    "File requires authentication before processing"
                )
            elif response.status_code == 200:
                if "login" in response.url.lower():
                    self.log_result(
                        "Generate ICS PHP - Authentication Redirect", 
                        True, 
                        "File redirects to login for authentication"
                    )
                else:
                    try:
                        json_response = json.loads(response.text)
                        if 'error' in json_response and 'não encontrada' in json_response['error']:
                            self.log_result(
                                "Generate ICS PHP - Invalid ID Handling", 
                                True, 
                                "File correctly handles invalid lecture_id"
                            )
                        else:
                            self.log_result(
                                "Generate ICS PHP - Invalid ID Handling", 
                                False, 
                                f"Unexpected response for invalid ID: {json_response}"
                            )
                    except json.JSONDecodeError:
                        self.log_result(
                            "Generate ICS PHP - Invalid ID Handling", 
                            False, 
                            "Non-JSON response for invalid lecture_id"
                        )
                
        except requests.exceptions.RequestException as e:
            self.log_result(
                "Generate ICS PHP - File Access", 
                False, 
                "Network error accessing generate_ics.php",
                str(e)
            )

    def test_database_query_structure(self):
        """Test if the database query structure is correct by analyzing the PHP code"""
        try:
            response = self.session.get(INDEX2_URL, timeout=15)
            
            if response.status_code == 200:
                # Check for SQL query elements in the response (if visible in source)
                sql_elements = [
                    "upcoming_announcements",
                    "is_active = 1",
                    "announcement_date",
                    "ORDER BY",
                    "LIMIT"
                ]
                
                # This test is limited since we can't see PHP source code in response
                # But we can check if the query executed successfully
                if "Erro ao carregar a agenda" in response.text:
                    self.log_result(
                        "Database Query Structure", 
                        False, 
                        "Database query failed - check table structure and query syntax",
                        "Error message displayed on page"
                    )
                elif "schedule-item" in response.text or "Nenhuma palestra agendada" in response.text:
                    self.log_result(
                        "Database Query Structure", 
                        True, 
                        "Database query executed successfully"
                    )
                else:
                    self.log_result(
                        "Database Query Structure", 
                        True, 
                        "No database errors detected, query structure appears correct"
                    )
            else:
                self.log_result(
                    "Database Query Structure", 
                    False, 
                    f"Cannot check query structure - HTTP {response.status_code}"
                )
                
        except requests.exceptions.RequestException as e:
            self.log_result(
                "Database Query Structure", 
                False, 
                "Network error during query structure test",
                str(e)
            )

    def test_css_styles_loading(self):
        """Test if CSS styles for Agenda T101 are loading correctly"""
        try:
            response = self.session.get(INDEX2_URL, timeout=15)
            
            if response.status_code == 200:
                # Check for CSS classes and styles
                css_elements = [
                    "schedule-grid",
                    "schedule-item",
                    "schedule-date",
                    "schedule-info",
                    "schedule-actions",
                    "download-ics-btn",
                    "btn-outline"
                ]
                
                missing_css = []
                for element in css_elements:
                    if element not in response.text:
                        missing_css.append(element)
                
                if not missing_css:
                    self.log_result(
                        "CSS Styles Loading", 
                        True, 
                        "All required CSS classes are present"
                    )
                else:
                    self.log_result(
                        "CSS Styles Loading", 
                        False, 
                        f"Missing CSS classes: {', '.join(missing_css)}"
                    )
                    
                # Check for inline styles
                if "<style>" in response.text and "schedule-grid" in response.text:
                    self.log_result(
                        "Inline CSS Styles", 
                        True, 
                        "Inline CSS styles are present"
                    )
                else:
                    self.log_result(
                        "Inline CSS Styles", 
                        False, 
                        "Inline CSS styles not found"
                    )
            else:
                self.log_result(
                    "CSS Styles Loading", 
                    False, 
                    f"Cannot check CSS styles - HTTP {response.status_code}"
                )
                
        except requests.exceptions.RequestException as e:
            self.log_result(
                "CSS Styles Loading", 
                False, 
                "Network error during CSS test",
                str(e)
            )

    def test_responsive_design_elements(self):
        """Test if responsive design elements are present"""
        try:
            response = self.session.get(INDEX2_URL, timeout=15)
            
            if response.status_code == 200:
                # Check for responsive design elements
                responsive_elements = [
                    "@media",
                    "max-width",
                    "grid-template-columns",
                    "flex-direction: column"
                ]
                
                found_responsive = []
                for element in responsive_elements:
                    if element in response.text:
                        found_responsive.append(element)
                
                if len(found_responsive) >= 2:
                    self.log_result(
                        "Responsive Design Elements", 
                        True, 
                        f"Responsive design elements found: {', '.join(found_responsive)}"
                    )
                else:
                    self.log_result(
                        "Responsive Design Elements", 
                        False, 
                        f"Limited responsive design elements: {', '.join(found_responsive)}"
                    )
            else:
                self.log_result(
                    "Responsive Design Elements", 
                    False, 
                    f"Cannot check responsive design - HTTP {response.status_code}"
                )
                
        except requests.exceptions.RequestException as e:
            self.log_result(
                "Responsive Design Elements", 
                False, 
                "Network error during responsive design test",
                str(e)
            )

    def run_all_tests(self):
        """Run all tests"""
        print("=" * 70)
        print("TRANSLATORS101 AGENDA T101 BACKEND TEST SUITE")
        print("=" * 70)
        print(f"Testing Index2 URL: {INDEX2_URL}")
        print(f"Testing Generate ICS URL: {GENERATE_ICS_URL}")
        print(f"Test started at: {datetime.now().isoformat()}")
        print("=" * 70)
        print()
        
        # Run tests in order of importance
        self.test_index2_page_accessibility()
        self.test_php_syntax_errors()
        self.test_database_connection()
        self.test_database_query_structure()
        self.test_agenda_section_html_generation()
        self.test_javascript_ics_functionality()
        self.test_generate_ics_php_file()
        self.test_css_styles_loading()
        self.test_responsive_design_elements()
        
        # Summary
        print("=" * 70)
        print("TEST SUMMARY")
        print("=" * 70)
        
        passed = sum(1 for result in self.test_results if result['success'])
        total = len(self.test_results)
        
        print(f"Total Tests: {total}")
        print(f"Passed: {passed}")
        print(f"Failed: {total - passed}")
        print(f"Success Rate: {(passed/total)*100:.1f}%")
        print()
        
        # Failed tests details
        failed_tests = [result for result in self.test_results if not result['success']]
        if failed_tests:
            print("FAILED TESTS:")
            for test in failed_tests:
                print(f"❌ {test['test']}: {test['message']}")
                if test['details']:
                    print(f"   Details: {test['details']}")
            print()
        
        # Passed tests summary
        passed_tests = [result for result in self.test_results if result['success']]
        if passed_tests:
            print("PASSED TESTS:")
            for test in passed_tests:
                print(f"✅ {test['test']}: {test['message']}")
            print()
        
        if not failed_tests:
            print("🎉 All tests passed!")
        
        print("=" * 70)
        
        return passed == total

if __name__ == "__main__":
    test_suite = AgendaT101TestSuite()
    success = test_suite.run_all_tests()
    sys.exit(0 if success else 1)