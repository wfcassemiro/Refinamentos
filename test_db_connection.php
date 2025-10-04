<?php
// Simple database connection test
require_once '/app/Refinamentos/config/database.php';

echo "Testing database connection...\n";

try {
    // Test basic connection
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    
    if ($result && $result['test'] == 1) {
        echo "✅ Database connection successful!\n";
    } else {
        echo "❌ Database connection failed - no result\n";
        exit(1);
    }
    
    // Test if upcoming_announcements table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'upcoming_announcements'");
    $table_exists = $stmt->fetch();
    
    if ($table_exists) {
        echo "✅ Table 'upcoming_announcements' exists!\n";
        
        // Test table structure
        $stmt = $pdo->query("DESCRIBE upcoming_announcements");
        $columns = $stmt->fetchAll();
        
        echo "📋 Table structure:\n";
        foreach ($columns as $column) {
            echo "   - {$column['Field']} ({$column['Type']})\n";
        }
        
        // Test data retrieval
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM upcoming_announcements");
        $count_result = $stmt->fetch();
        echo "📊 Total records: {$count_result['count']}\n";
        
        // Test the actual query used in index2.php
        $stmt = $pdo->query("
            SELECT id, title, speaker, announcement_date, lecture_time, description, image_path
            FROM upcoming_announcements 
            WHERE is_active = 1 
            AND announcement_date >= CURDATE()
            ORDER BY announcement_date ASC
            LIMIT 10
        ");
        $lectures = $stmt->fetchAll();
        echo "🎯 Active future lectures: " . count($lectures) . "\n";
        
        if (count($lectures) > 0) {
            echo "📅 Sample lecture data:\n";
            $sample = $lectures[0];
            echo "   - Title: {$sample['title']}\n";
            echo "   - Speaker: {$sample['speaker']}\n";
            echo "   - Date: {$sample['announcement_date']}\n";
            echo "   - Time: {$sample['lecture_time']}\n";
        }
        
    } else {
        echo "❌ Table 'upcoming_announcements' does not exist!\n";
        
        // Show available tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll();
        echo "📋 Available tables:\n";
        foreach ($tables as $table) {
            echo "   - " . array_values($table)[0] . "\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✅ Database connectivity test completed successfully!\n";
?>