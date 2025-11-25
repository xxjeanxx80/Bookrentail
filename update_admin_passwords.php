<?php
// Script to update admin passwords to MD5 hash
// Run this file once to update existing admin passwords in database

require('connection.php');

// Update admin passwords to MD5
$sql = "SELECT id, email, password FROM admin";
$res = pg_query($con, $sql);

if ($res) {
    while ($row = pg_fetch_assoc($res)) {
        $id = $row['id'];
        $email = $row['email'];
        $password = $row['password'];
        
        // Check if password is already hashed (MD5 hash is 32 characters)
        if (strlen($password) != 32) {
            // Hash the password
            $hashed_password = md5($password);
            
            // Update in database
            $update_sql = "UPDATE admin SET password = '$hashed_password' WHERE id = '$id'";
            if (pg_query($con, $update_sql)) {
                echo "Updated password for admin ID: $id ($email)<br>";
            } else {
                echo "Error updating password for admin ID: $id<br>";
            }
        } else {
            echo "Password for admin ID: $id ($email) is already hashed<br>";
        }
    }
    echo "<br>Update completed!";
} else {
    echo "Error: " . pg_last_error($con);
}

pg_close($con);
?>

