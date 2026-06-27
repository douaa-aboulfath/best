<?php
header("Content-Type: application/json");
require "config.php";

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

/* =========================
   GET ALL CLIENTS
========================= */
if ($method === "GET" && !isset($_GET['id'])) {

    $stmt = $pdo->query("SELECT * FROM clients ORDER BY id_client DESC");
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($clients);
    exit;
}

/* =========================
   GET CLIENT BY ID
========================= */
if ($method === "GET" && isset($_GET['id'])) {

    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id_client = ?");
    $stmt->execute([$_GET['id']]);

    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($client) {
        echo json_encode($client);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Client not found"]);
    }

    exit;
}

/* =========================
   CREATE CLIENT
========================= */
if ($method === "POST") {

    if (!isset($data['nom'])) {
        http_response_code(400);
        echo json_encode(["message" => "nom is required"]);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO clients (nom, prenom, telephone, email, adresse, ville)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    try {
        $stmt->execute([
            $data['nom'],
            $data['prenom'] ?? null,
            $data['telephone'] ?? null,
            $data['email'] ?? null,
            $data['adresse'] ?? null,
            $data['ville'] ?? null
        ]);

        echo json_encode([
            "message" => "Client created",
            "id" => $pdo->lastInsertId()
        ]);

    } catch (PDOException $e) {
        http_response_code(400);
        echo json_encode([
            "error" => "Insert failed",
            "message" => $e->getMessage()
        ]);
    }

    exit;
}

/* =========================
   UPDATE CLIENT
========================= */
if ($method === "PUT") {

    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["message" => "ID is required"]);
        exit;
    }

    $stmt = $pdo->prepare("
        UPDATE clients
        SET nom = ?, prenom = ?, telephone = ?, email = ?, adresse = ?, ville = ?
        WHERE id_client = ?
    ");

    $stmt->execute([
        $data['nom'],
        $data['prenom'] ?? null,
        $data['telephone'] ?? null,
        $data['email'] ?? null,
        $data['adresse'] ?? null,
        $data['ville'] ?? null,
        $_GET['id']
    ]);

    echo json_encode(["message" => "Client updated"]);
    exit;
}

/* =========================
   DELETE CLIENT
========================= */
if ($method === "DELETE") {

    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["message" => "ID is required"]);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM clients WHERE id_client = ?");
    $stmt->execute([$_GET['id']]);

    echo json_encode(["message" => "Client deleted"]);
    exit;
}

/* =========================
   INVALID METHOD
========================= */
http_response_code(405);
echo json_encode(["message" => "Method not allowed"]);