<?php
header("Content-Type: application/json");
require "config.php";

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

/* =========================
   GET ALL CATEGORIES
========================= */
if ($method === "GET" && !isset($_GET['id'])) {

    $stmt = $pdo->query("SELECT * FROM categories ORDER BY id_categorie DESC");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($rows);
    exit;
}

/* =========================
   GET CATEGORY BY ID
========================= */
if ($method === "GET" && isset($_GET['id'])) {

    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id_categorie = ?");
    $stmt->execute([$_GET['id']]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo json_encode($row);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Category not found"]);
    }

    exit;
}

/* =========================
   CREATE CATEGORY
========================= */
if ($method === "POST") {

    if (!isset($data['nom'])) {
        http_response_code(400);
        echo json_encode(["message" => "nom is required"]);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO categories (nom, description)
        VALUES (?, ?)
    ");

    try {
        $stmt->execute([
            $data['nom'],
            $data['description'] ?? null
        ]);

        echo json_encode([
            "message" => "Category created",
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
   UPDATE CATEGORY
========================= */
if ($method === "PUT") {

    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["message" => "ID is required"]);
        exit;
    }

    $stmt = $pdo->prepare("
        UPDATE categories
        SET nom = ?, description = ?
        WHERE id_categorie = ?
    ");

    $stmt->execute([
        $data['nom'],
        $data['description'] ?? null,
        $_GET['id']
    ]);

    echo json_encode(["message" => "Category updated"]);
    exit;
}

/* =========================
   DELETE CATEGORY
========================= */
if ($method === "DELETE") {

    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["message" => "ID is required"]);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM categories WHERE id_categorie = ?");
    $stmt->execute([$_GET['id']]);

    echo json_encode(["message" => "Category deleted"]);
    exit;
}

/* =========================
   INVALID METHOD
========================= */
http_response_code(405);
echo json_encode(["message" => "Method not allowed"]);