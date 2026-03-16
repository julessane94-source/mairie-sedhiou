<?php
require_once "database/config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nom = htmlspecialchars($_POST['nom']);
    $email = htmlspecialchars($_POST['email']);
    $formation = htmlspecialchars($_POST['formation']);

    $stmt = $conn->prepare("INSERT INTO formations (nom, email, formation) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nom, $email, $formation);

    if ($stmt->execute()) {
        header("Location: success.php");
        exit();
    } else {
        echo "Erreur lors de l'enregistrement.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Accès non autorisé.";
}
?>