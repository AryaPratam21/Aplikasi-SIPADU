<?php
$password = "admin12345";
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Password: " . $password . "<br>";
echo "Hash: " . $hash . "<br>";

// Verify test
if (password_verify($password, $hash)) {
    echo "Password verification successful!";
} else {
    echo "Password verification failed!";
}
