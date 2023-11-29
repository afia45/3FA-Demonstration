<?php
ob_start();
session_start();
require "vendor/autoload.php";
require "Classes/User.php";
require "Classes/GoogleAuth.php";

use Classes\User;
use Classes\GoogleAuth;

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Database configuration
$dbHost = 'db';
$dbUser = 'root';
$dbPassword = 'awesomemanu';
$dbName = 'test';

// Establish database connection
$dbConnection = new mysqli($dbHost, $dbUser, $dbPassword, $dbName);

if ($dbConnection->connect_error) {
    die("Error: " . $dbConnection->connect_error);
}

// Registration logic
if (!empty($_POST['register_email']) && !empty($_POST['register_password']) && !empty($_POST['register_name']) && !empty($_POST['security_question']) && !empty($_POST['security_answer'])) {
    $registerEmail = $_POST['register_email'];
    $registerPassword = password_hash($_POST['register_password'], PASSWORD_DEFAULT);
    $registerName = $_POST['register_name'];
    $securityQuestion = $_POST['security_question'];
    $securityAnswer = $_POST['security_answer'];

    // Automatically collect the current timestamp for created_at
    $createdAt = date('Y-m-d H:i:s'); // Current timestamp

    // Insert new user into the database with an empty google_key and automatic created_at
    $googleKey = ''; // Default empty value
    $registerQuery = $dbConnection->prepare("INSERT INTO users (email, password, name, google_key, created_at, question, answer) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $registerQuery->bind_param("sssssss", $registerEmail, $registerPassword, $registerName, $googleKey, $createdAt, $securityQuestion, $securityAnswer);
    $registerQuery->execute();
    $registerQuery->close();

    // Redirect to index.php after successful registration
    header("Location: index.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #17a2b8;
            height: 100vh;
        }

        #login .container #login-row #login-column #login-box {
            margin-top: 120px;
            max-width: 600px;
            height: 580px; /* Adjusted height to fit the new fields */
            border: 1px solid #9C9C9C;
            background-color: #EAEAEA;
        }

        #login .container #login-row #login-column #login-box #register-form {
            padding: 20px;
        }
    </style>
</head>
<body>
    <div id="login">
        <div class="container">
            <div id="login-row" class="row justify-content-center align-items-center">
                <div id="login-column" class="col-md-6">
                    <div id="login-box" class="col-md-12">
                        <!-- Registration Form -->
                        <form id="register-form" class="form" action="signup.php" method="post">
                            <h3 class="text-center text-info">Register</h3>
                            <div class="form-group">
                                <label for="register_name" class="text-info">Name:</label><br>
                                <input type="text" name="register_name" id="register_name" class="form-control" required>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="register_email" class="text-info">Email:</label><br>
                                <input type="text" name="register_email" id="register_email" class="form-control" required>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="register_password" class="text-info">Password:</label><br>
                                <input type="password" name="register_password" id="register_password" class="form-control" pattern=".{8,}" title="Password must be at least 8 characters long" required>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="security_question" class="text-info">Security Question:</label><br>
                                <select name="security_question" id="security_question" class="form-control">
                                    <option value="---Choose Option---">---Choose Option---</option>
                                    <option value="Which university did your Dad graduate from?">Which university did your Dad graduate from?</option>
                                    <option value="What is your Grandpa's middle name?">What is your Grandpa's middle name?</option>
                                    <option value="What was the name of your first stuffed animal?">What was the name of your first stuffed animal?</option>
                                </select>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="security_answer" class="text-info">Security Answer:</label><br>
                                <input type="text" name="security_answer" id="security_answer" class="form-control" required>
                            </div>
                            <br>
                            <div class="mb-3">
                                <input type="submit" name="submit" class="btn btn-info btn-md" value="Register">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php
ob_end_flush();
?>
