<?php
require $_SERVER['DOCUMENT_ROOT'] . '/assets/config/config.php';
require $_SERVER['DOCUMENT_ROOT'] . "/assets/config/database.php";

// Database connection code here

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"];
    $enr = $_POST["enr"];

    // check if there is a existing enr with this name.
    $sql = "SELECT * FROM intra_edivi WHERE enr = :enr";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":enr", $enr);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    // get amount of results
    $count = $stmt->rowCount();

    if ($action === "openOrCreate") {
        if ($count > 0) {
            $redirectUrl = "/enotf/bridge/prot/index.php?enr=$enr";
            echo $redirectUrl;
            exit();
        } else {
            try {
                $sql2 = "INSERT INTO intra_edivi (enr) VALUES (:enr)";

                $stmt2 = $pdo->prepare($sql2);
                $stmt2->bindParam(":enr", $enr);
                $stmt2->execute();
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage(); // Output the error message for debugging
            }

            $redirectUrl = "/enotf/bridge/prot/index.php?enr=$enr";
            echo $redirectUrl;
            exit();
        }
    }
}
