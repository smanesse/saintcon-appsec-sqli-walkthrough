<?php
ob_start();
session_start();
include("db_config.php");
ini_set('display_errors', 1);

// Initialize variables
$username = "";
$pass = "";
$queryType = "vulnerable"; // Default query type
$message = "";
$executedQuery = "";

// Check if the form was submitted
if (!empty($_POST['uid'])) {
    $username = ($_REQUEST['uid']);
    $pass = $_REQUEST['password'];
    $queryType = $_POST['query_type']; // Update query type based on form submission

    $sqlQuery = "";

    if ($queryType === "vulnerable") {
        // Vulnerable SQL query (unsafe)
        $sqlQuery = "SELECT * FROM users where username='" . $username . "' AND password = '" . md5($pass) . "'";
        $executedQuery = $sqlQuery;
    } elseif ($queryType === "prepared") {
        // Secure prepared statement query
        $sqlQuery = "SELECT * FROM users where username=? AND password = ?";
        $executedQuery = $sqlQuery . "\n";
        $executedQuery .= "\$stmt = \$con->prepare(\$sqlQuery);\n";
        $executedQuery .= "\$stmt->bind_param(\"ss\", \"$username\", md5(\"$pass\"));\n";
        $executedQuery .= "\$stmt->execute();\n";
        $executedQuery .= "\$result = \$stmt->get_result();";
    }

    if ($queryType === "vulnerable") {
        // Vulnerable SQL query (unsafe)
        $result = mysqli_query($con, $sqlQuery);

        if ($result) {
            $row = mysqli_fetch_array($result);
            if ($row) {
                $message = "Login Successful!";
                $_SESSION["username"] = $row[1];
                $_SESSION["name"] = $row[3];
                if ($_SESSION['next'] === "searchproducts.php") {
                    header('Location: searchproducts.php');
                } elseif ($_SESSION['next'] === "blindsqli.php") {
                    header('Location: blindsqli.php?user=' . $_SESSION["username"]);
                } elseif ($_SESSION['next'] === "os_sqli.php") {
                    header('Location: os_sqli.php?user=' . $_SESSION["username"]);
                }
            } else {
                $message = "Invalid password!";
            }
        } else {
            echo 'Error: ' . mysqli_error($con);
        }
    } elseif ($queryType === "prepared") {
        // Secure prepared statement query
        $stmt = $con->prepare($sqlQuery);
        $stmt->bind_param("ss", $username, md5($pass));
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result) {
            $row = mysqli_fetch_array($result);
            if ($row) {
                $message = "Login Successful!";
                $_SESSION["username"] = $row[1];
                $_SESSION["name"] = $row[3];
                if ($_SESSION['next'] === "searchproducts.php") {
                    header('Location: searchproducts.php');
                } elseif ($_SESSION['next'] === "blindsqli.php") {
                    header('Location: blindsqli.php?user=' . $_SESSION["username"]);
                } elseif ($_SESSION['next'] === "os_sqli.php") {
                    header('Location: os_sqli.php?user=' . $_SESSION["username"]);
                }
            } else {
                $message = "Invalid password!";
            }
        } else {
            echo 'Error: ' . mysqli_error($con);
        }
    }
}
?>

<!-- HTML code for the form -->
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Login Page - SQL Injection Training App</title>
    <link href="./css/htmlstyles.css" rel="stylesheet">
</head>

<body>
<div class="container-narrow">
    <div class="jumbotron">
        <p class="lead" style="color:white">
            Login Page - Choose SQL Query Type
            <?php
            if (!empty($_REQUEST['msg'])) {
                if ($_REQUEST['msg'] === "1") {
                    $_SESSION['next'] = 'searchproducts.php';
                    echo "<br />Please login to continue to Search Products";
                } elseif ($_REQUEST['msg'] === "2") {
                    $_SESSION['next'] = 'blindsqli.php';
                    echo "<br />Please login to continue to Blind SQL Injection Page";
                } elseif ($_REQUEST['msg'] === "3") {
                    $_SESSION['next'] = 'os_sqli.php';
                    echo "<br />Please login to continue to OS Command Injection Page";
                } else {
                    $_SESSION['next'] = 'searchproducts.php';
                }
            }
            ?>
        </p>
    </div>

    <div class="response">
        <form method="POST" autocomplete="off">
            <p style="color:white">
                Username: <input type="text" id="uid" name="uid" value="<?php echo $username; ?>"><br/><br/>
                Password: <input type="password" id="password" name="password" value="<?php echo $pass; ?>"><br/><br/>
                Choose SQL Query Type:
                <input type="radio" name="query_type" value="vulnerable" id="radio_vulnerable"
                       <?php if ($queryType === "vulnerable") echo "checked"; ?>> Vulnerable SQL Injection
                <input type="radio" name="query_type" value="prepared" id="radio_prepared"
                       <?php if ($queryType === "prepared") echo "checked"; ?>> Prepared Statement
            </p>
            <p>
                <input type="submit" value="Submit"/>
                <input type="reset" value="Reset" onclick="clearData()"/>
            </p>
        </form>
    </div>

    <!-- Display login message -->
    <?php if (!empty($message)) : ?>
        <div id="login_message" style="color:<?php echo ($message === 'Login Successful!') ? '#008000' : '#FF0000'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <!-- Display executed query -->
    <?php if (!empty($executedQuery)) : ?>
        <div id="executed_query" style="border:1px solid #4CAF50; padding: 10px">
            <h3>Executed Query</h3>
            <pre><?php echo htmlspecialchars($executedQuery); ?></pre>
        </div>
    <?php endif; ?>

    <!-- Code snippets for each query type -->
    <div id="vulnerable_code" style="display:none;">
        <h3>Vulnerable SQL Injection Code</h3>
        <textarea rows='15' cols='120' readonly style='border:1px solid #4CAF50; padding: 10px'>
            $sqlQuery = "SELECT * FROM users where username='" . $username . "' AND password = '" . md5($pass) . "'";
            
            // ...
        </textarea>
    </div>

    <div id="prepared_code" style="display:none;">
        <h3>Prepared Statement Code</h3>
        <textarea rows='15' cols='120' readonly style='border:1px solid #4CAF50; padding: 10px'>
            $sqlQuery = "SELECT * FROM users where username=? AND password = ?";
            $stmt = $con->prepare($sqlQuery);
            $stmt->bind_param("ss", $username, md5($pass));
            $stmt->execute();
            $result = $stmt->get_result();
            
            // ...
        </textarea>
    </div>

    <script>
        // JavaScript to toggle code snippets based on radio button selection
        document.getElementById("radio_vulnerable").addEventListener("change", function () {
            document.getElementById("vulnerable_code").style.display = "block";
            document.getElementById("prepared_code").style.display = "none";
        });

        document.getElementById("radio_prepared").addEventListener("change", function () {
            document.getElementById("vulnerable_code").style.display = "none";
            document.getElementById("prepared_code").style.display = "block";
        });

        // JavaScript to show code snippet based on selected radio button
        if (<?php echo ($queryType === "vulnerable") ? "true" : "false"; ?>) {
            document.getElementById("vulnerable_code").style.display = "block";
        } else {
            document.getElementById("prepared_code").style.display = "block";
        }
    </script>
</body>
</html>
