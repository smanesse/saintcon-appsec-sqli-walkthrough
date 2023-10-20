<?php
if (!empty($_POST['query']) && !empty($_POST['values'])) {
    // Include your database connection configuration here
    include("db_config.php");

    $query = $_POST['query'];
    $values = explode(',', $_POST['values']);

    // Generate PHP code for prepared statement
    $code = "<?php\n";
    $code .= '$stmt = $con->prepare("' . $query . '");' . "\n";
    
    foreach ($values as $param) {
        $code .= '$stmt->bind_param("s", ' . trim($param) . ");\n";
    }

    $code .= '$stmt->execute();' . "\n";
    $code .= '$result = $stmt->get_result();' . "\n";
    $code .= '?>';

    // Display the generated PHP code
    echo $code;
} else {
    // Handle the case where form fields are empty
    echo "Please enter both the query and placeholder values.";
}
?>
