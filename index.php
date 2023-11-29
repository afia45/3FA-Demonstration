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

// Login logic
if (!empty($_POST['email']) && !empty($_POST['password'])) {
    $loginEmail = $_POST['email'];
    $loginPassword = $_POST['password'];

    

    // Fetch hashed password from the database based on the email
    $fetchUserDataQuery = $dbConnection->prepare("SELECT password, question, answer FROM users WHERE email = ?");
    $fetchUserDataQuery->bind_param("s", $loginEmail);
    $fetchUserDataQuery->execute();
    $fetchUserDataQuery->bind_result($hashedPassword, $securityQuestion, $storedAnswer);
    $fetchUserDataQuery->fetch();

    // Verify the entered password with the hashed password from the database
    if (password_verify($loginPassword, $hashedPassword)) {
        // Password is correct
        
        if (!(new User())->authenticateUser($_POST)) {
            die("Unauthorised User");
        }

        new GoogleAuth($_POST);
       
    } else {
        // Password is incorrect
        die("Incorrect Password");
    }

    $fetchUserDataQuery->close();
}



if($_GET)
{
    if(isset($_GET["code"]) && $_GET["code"] != "")
    {
        (new GoogleAuth)->verifyFromGoogle($_GET["code"]);
        return;
    }

    session_destroy();
    echo "Logged Out Scuccessfuly";
    header("location: index.php");
    exit;
}


?>

<!DOCTYPE html>
<html lang="eng">
<head>
    <title>3 Factor Authentication</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: -webkit-linear-gradient(left, #17a2b8, #077c8f);
            height: 100vh;
        }
        #login .container #login-row #login-column #login-box {
            margin-top: 120px;
            max-width: 600px;
            height: 500px;
            border: 1px solid #9C9C9C;
            background-color: #EAEAEA;
        }
        #login .container #login-row #login-column #login-box #login-form {
            padding: 20px;
        }
        #login .container #login-row #login-column #login-box #login-form #register-link {
            margin-top: -85px;
        }
        .text-info {
            color: maroon; /* Change text color to maroon */
        }

        .text-center {
            color: maroon; /* Change text color to maroon */
        }

        .btn-info:hover {
            background-color: gray; /* Change button background color on hover to darkred */
            border-color: black; /* Change button border color on hover to darkred */
        }
    </style>
</head>
<body>
    <div id="login">
        <div class="container">
            <?php 
            
            if(isset($_SESSION["userKey"])){
            ?>
                <h3 class="text-center text-black pt-5">App key: <?php echo $_SESSION["userKey"];
             ?></h3>
                <h3 class="text-center text-black pt-5">
                    <?php
                    echo (new GoogleAuth)->getOtp();
                    ?>
                </h3>
            <?php
            }


            if(!empty($_SESSION["message"])){
                ?><br>
                <center>
                <h3 class="alert alert-<?php echo $_SESSION["status"]?>">
                    <?php echo $_SESSION["message"]; ?>
                </h3>
                </center>
                <?php
            }
            
            if(isset($_SESSION["userKey"])){
                // Html for collecting google authenticator code.
                ?>
                <div id="login-row" class="row justify-content-center align-items-center">
                <div id="login-column" class="col-md-6">
                    <div id="login-box" class="col-md-12">
                        <form id="login-form" class="form" action="index.php" method="get">
                        <h3 class="text-center text-info">Security Verification</h3>
                                    <div class="form-group">
                                        <label for="security_question" class="text-info">Security Question:</label><br>
                                        <p><?php echo $securityQuestion; ?></p>
                                    </div>
                                    <div class="form-group">
                                        <label for="security_answer" class="text-info">Security Answer:</label><br>
                                        <input type="text" name="security_answer" id="security_answer" class="form-control">
                                    </div><br>
                            <h3 class="text-center text-info">Check Google Authenticator</h3>
                            <div class="form-group">
                                <label for="code" class="text-info">Google code:</label><br>
                                <input type="text" name="code" id="code" class="form-control">
                            </div>
                            <br />
                            <div class="form-group">
                                <input type="submit" name="submit" class="btn btn-info btn-md" value="Submit">
                            </div>
                            <br />
                            <div class="form-group">
                                <input type="submit" name="submit" class="btn btn-danger btn-md" value="Logout">
                            </div>
                        </form>
                    </div>
                </div>
            </div>


            <?php } else {
                
                // Html for login email and password.
                ?>
                <div id="login-row" class="row justify-content-center align-items-center">
                <div id="login-column" class="col-md-6">
                    <div id="login-box" class="col-md-12">
                        <form id="login-form" class="form" action="index.php" method="post">
                            <br><br>
                            <h3 class="text-center text-info">Login</h3><br>
                            <div class="form-group">
                                <label for="username" class="text-info">Email:</label><br>
                                <input type="text" name="email" id="username" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="password" class="text-info">Password:</label><br>
                                <input type="password" name="password" id="password" class="form-control" pattern=".{8,}" title="Password must be at least 8 characters long" required> 
                            </div>
                            <br />
                            <div class="form-group">
                                <input type="submit" name="submit" class="btn btn-info btn-md" value="Submit">
                            </div>
                            <br>
                            <a href="signup.php">Don't have an account? Sign up here.</a>
                        </form>
                    </div>
                </div>
            </div>
            <?php }?>


        </div>
    </div>
</body>
</html>

<?php
    ob_end_flush();
?>
