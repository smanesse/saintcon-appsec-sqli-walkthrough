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

if ($_REQUEST["reset"]) {
	$flag = "";
	$flag1 = "";
}

// Check if the form was submitted
if (!empty($_REQUEST['uid'])) {
    $username = ($_REQUEST['uid']);
    $pass = $_REQUEST['password'];
    $queryType = $_REQUEST['query_type']; // Update query type based on form submission

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
		    if (strpos($username, "'") !== false) {
			$_SESSION["flag1"] = true;
		    }
            } else {
                $message = "Invalid password!";
            }
        } else {
	    $err = mysqli_error($con);
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
            } else {
                $message = "Invalid password!";
            }
		    if ($_SESSION["flag1"]) {
			$flag = "ifixedphp";
		    }
        } else {
            $err = mysqli_error($con);
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
<div style="display:flex;">
	<div style="flex-grow:1">
	</div>
	<div style="">
		<div style="background-color:#3d3d4d; color:white; max-width: 500px; margin-right:100px; padding: 10px;" class="rounded">
			<h2>Instructions</h2>
			<p>Remember <a href="https://xkcd.com/327/" target="_blank" style="color:#ddddff">Bobby Tables</a>? Yeah, not here. This page will walk you through fixing the classic SQL injection in a login page. Your reward will be a flag for the Hacker's Challenge.</p>
			<p>Instructions</p>
			<ol>
			<li>Log in as the user "bob" using SQL injection (string concatentation)
				<ul>
					<li>Enter "bob" as the username and some random password.</li>
					<li>Note the "Executed Query" listed below - see the single quotes around bob? What happens when you try again with a single quote in your username?</li>
					<li>Hijack the query by entering the following username with any password: <pre>bob' -- -</pre></li>
					<li>Why does this work? The single quote allows you to "escape" out of the username part of the query and put your own code. The double-dash (--) is a comment, which means the rest of the query is ignored.</li>
					<li>You can look at the "Executed Query" to see that everything before the -- is executed (with the rest ignored), resulting in returning the user "bob" without checking the password.</li>
				</ul>
			</li>
			<li>Attempt the same exploit against a prepared statement
				<ul>
				<li>Notice the same exploit is impossible. Any data you enter doesn't get treated as SQL code.</li>
				</ul>
			</li>
			<li>Claim your flag</li>
			</ol>
		</div>
	</div>
		<div style="width:900px">
		<div>

			<form method="GET" autocomplete="off">
		    <div class="jumbotron rounded">
			<p class="lead" style="color:white; text-align: center;">
			    Login Page
			</p>
			<div style="display:flex">
			    <span style="color:white; margin: auto">
				Username: <input type="text" id="uid" name="uid" value="<?php echo $username; ?>"><br/>
				Password: <input type="password" id="password" name="password" value="<?php echo $pass; ?>"><br/>
				<div style="display:flex">
				<input type="submit" value="Submit" style="margin:auto"/>
				</div>
			    </span>
			    </div>
		    </div>
				Query mode:
				<br/>
				<input type="radio" name="query_type" value="vulnerable" id="radio_vulnerable"
				       <?php if ($queryType === "vulnerable") echo "checked"; ?>/> String concatenation (vulnerable)
				       <br/>
				<input type="radio" name="query_type" value="prepared" id="radio_prepared"
				       <?php if ($queryType === "prepared") echo "checked"; ?>/> Prepared Statement (not vulnerable)
			    <p>
			    </p>
			</form>
			<div>
			<form method="GET">
				<input type="hidden" name="reset" value="true">
				<input type="submit" value="Reset" style="float:right">
			</form>
			<br/>
			</div>

		    <!-- Display login message -->
		    <?php if (!empty($message)) : ?>
			<div id="login_message" style="color:<?php echo ($message === 'Login Successful!') ? '#008000' : '#FF0000'; ?>">
			    <?php echo $message; ?>
			</div>
		    <?php endif; ?>

		    <!-- Display executed query -->
		    <?php if (!empty($executedQuery)) : ?>
			<div id="executed_query" style="border:1px solid #4CAF50; padding: 10px" class="rounded">
			    <h3>Executed Query</h3>
			    <pre style="text-wrap: wrap;"><?php echo htmlspecialchars($executedQuery); ?></pre>
			</div>
			<br/>
		    <?php endif; ?>

		    <?php if (!empty($err)) : ?>
			<div id="err" style="border:1px solid red; padding: 10px" class="rounded">
			    <h3>Error</h3>
			    <pre style="text-wrap: wrap;"><?php echo htmlspecialchars($err); ?></pre>
			</div>
			<br/>
		    <?php endif; ?>

		    <?php if (!empty($flag)) : ?>
			<div id="flag" style="border:1px solid gree; padding: 10px" class="rounded">
			    <h3>Flag</h3>
			    <pre style="text-wrap: wrap;"><?php echo htmlspecialchars($flag); ?></pre>
			</div>
			<br/>
		    <?php endif; ?>

		    <!-- Code snippets for each query type -->
		    <div id="vulnerable_code" style="display:none;">
			<h3>Vulnerable SQL Injection Code</h3>
			<div class="rounded" style="border:1px solid blue; padding: 10px">
			<pre style="text-wrap: wrap;">
$sqlQuery = "SELECT * FROM users where username='" . $username . "' AND password = '" . md5($pass) . "'";
			    
// ...
			</pre>
			</div>

		    </div>

		    <div id="prepared_code" style="display:none;" class="rounded">
			<h3>Prepared Statement Code</h3>
			<div class="rounded" style="border:1px solid #4CAF50; padding: 10px">
			<pre>
$sqlQuery = "SELECT * FROM users where username=? AND password = ?";
$stmt = $con->prepare($sqlQuery);
$stmt->bind_param("ss", $username, md5($pass));
$stmt->execute();
$result = $stmt->get_result();
			    
// ...
			</pre>
			</div>
		    </div>
		</div>
	</div>
	<div style="flex-grow:1"></div>
	</div>
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
