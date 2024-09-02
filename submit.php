<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = filter_var($_POST["name"], FILTER_SANITIZE_STRING);
    if (empty($name)) {
        // Handle empty or invalid input
        http_response_code(400);
        echo "Invalid input";
        exit;
    }

    // Perform some action with the input data
    echo "Hello, $name!";
}
?>