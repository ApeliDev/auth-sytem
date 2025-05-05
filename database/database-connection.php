<?php
/**
 * Database Connection
 * 
 * This file establishes a connection to the MySQL database and provides
 * helper functions for database operations.
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');     
define('DB_PASS', '');         
define('DB_NAME', 'mildred');

// Create connection
function connectDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Close database connection
function closeDB($conn) {
    $conn->close();
}

// Execute a query and return result
function executeQuery($sql, $params = []) {
    $conn = connectDB();
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } elseif (is_string($param)) {
                $types .= 's';
            } else {
                $types .= 'b';
            }
        }
        
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    closeDB($conn);
    
    return $result;
}

// Fetch a single row
function fetchRow($sql, $params = []) {
    $result = executeQuery($sql, $params);
    return $result->fetch_assoc();
}

// Fetch all rows
function fetchAll($sql, $params = []) {
    $result = executeQuery($sql, $params);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Insert data and return inserted ID
function insert($sql, $params = []) {
    $conn = connectDB();
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } elseif (is_string($param)) {
                $types .= 's';
            } else {
                $types .= 'b';
            }
        }
        
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $insertId = $conn->insert_id;
    $stmt->close();
    closeDB($conn);
    
    return $insertId;
}

function update($sql, $params = []) {
    $conn = connectDB();
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } elseif (is_string($param)) {
                $types .= 's';
            } else {
                $types .= 'b';
            }
        }
        
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $affectedRows = $stmt->affected_rows;
    $stmt->close();
    closeDB($conn);
    
    return $affectedRows;
}

?>