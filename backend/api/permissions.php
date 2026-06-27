<?php
header("Content-Type: application/json");
require "config.php";

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

/* =========================
   GET ALL PERMISSIONS
========================= */
if ($method === "GET" && !isset($_GET['id'])) {

    $stmt = $pdo->query("SELECT * FROM permissions");
    $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($permissions);
    exit;
}

/* =========================
   GET BY ID
========================= */
if ($method === "GET" && isset($_GET['id'])) {

    $stmt = $pdo->prepare("SELECT * FROM permissions WHERE id_permission = ?");
    $stmt->execute([$_GET['id']]);
    $permission = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($permission) {
        echo json_encode($permission);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Permission not found"]);
    }
    exit;
}

/* =========================
   CREATE PERMISSION
========================= */
if ($method === "POST") {

    if (!isset($data['code_permission'])) {
        http_response_code(400);
        echo json_encode(["message" => "code_permission is required"]);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO permissions (code_permission, description)
        VALUES (?, ?)
    ");

    try {
        $stmt->execute([
            $data['code_permission'],
            $data['description'] ?? null
        ]);

        echo json_encode([
            "message" => "Permission created",
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
   UPDATE PERMISSION
========================= */
if ($method === "PUT") {

    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["message" => "ID is required"]);
        exit;
    }

    $stmt = $pdo->prepare("
        UPDATE permissions
        SET code_permission = ?, description = ?
        WHERE id_permission = ?
    ");

    $stmt->execute([
        $data['code_permission'],
        $data['description'] ?? null,
        $_GET['id']
    ]);

    echo json_encode(["message" => "Permission updated"]);
    exit;
}

/* =========================
   DELETE PERMISSION
========================= */
if ($method === "DELETE") {

    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["message" => "ID is required"]);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM permissions WHERE id_permission = ?");
    $stmt->execute([$_GET['id']]);

    echo json_encode(["message" => "Permission deleted"]);
    exit;
}

/* =========================
   INVALID METHOD
========================= */
http_response_code(405);
echo json_encode(["message" => "Method not allowed"]);