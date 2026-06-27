<?php
header("Content-Type: application/json");
require "config.php";

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

/* =========================
   GET ALL FOURNISSEURS
========================= */
if ($method === "GET" && !isset($_GET['id'])) {

    $stmt = $pdo->query("SELECT * FROM fournisseurs ORDER BY id_fournisseur DESC");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($rows);
    exit;
}

/* =========================
   GET BY ID
========================= */
if ($method === "GET" && isset($_GET['id'])) {

    $stmt = $pdo->prepare("SELECT * FROM fournisseurs WHERE id_fournisseur = ?");
    $stmt->execute([$_GET['id']]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo json_encode($row);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Fournisseur not found"]);
    }

    exit;
}

/* =========================
   CREATE FOURNISSEUR
========================= */
if ($method === "POST") {

    if (!isset($data['raison_sociale'])) {
        http_response_code(400);
        echo json_encode(["message" => "raison_sociale is required"]);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO fournisseurs (raison_sociale, telephone, email, adresse)
        VALUES (?, ?, ?, ?)
    ");

    try {
        $stmt->execute([
            $data['raison_sociale'],
            $data['telephone'] ?? null,
            $data['email'] ?? null,
            $data['adresse'] ?? null
        ]);

        echo json_encode([
            "message" => "Fournisseur created",
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
   UPDATE FOURNISSEUR
========================= */
if ($method === "PUT") {

    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["message" => "ID is required"]);
        exit;
    }

    $stmt = $pdo->prepare("
        UPDATE fournisseurs
        SET raison_sociale = ?, telephone = ?, email = ?, adresse = ?
        WHERE id_fournisseur = ?
    ");

    $stmt->execute([
        $data['raison_sociale'],
        $data['telephone'] ?? null,
        $data['email'] ?? null,
        $data['adresse'] ?? null,
        $_GET['id']
    ]);

    echo json_encode(["message" => "Fournisseur updated"]);
    exit;
}

/* =========================
   DELETE FOURNISSEUR
========================= */
if ($method === "DELETE") {

    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["message" => "ID is required"]);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM fournisseurs WHERE id_fournisseur = ?");
    $stmt->execute([$_GET['id']]);

    echo json_encode(["message" => "Fournisseur deleted"]);
    exit;
}

/* =========================
   INVALID METHOD
========================= */
http_response_code(405);
echo json_encode(["message" => "Method not allowed"]);