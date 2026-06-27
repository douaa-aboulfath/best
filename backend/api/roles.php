<?php
header("Content-Type: application/json");
require "config.php";

$method = $_SERVER['REQUEST_METHOD'];

/**
 * Read JSON input
 */
$data = json_decode(file_get_contents("php://input"), true);

/* =========================
   GET ALL ROLES
========================= */
if ($method === "GET" && !isset($_GET['id'])) {

    $stmt = $pdo->query("SELECT * FROM roles");
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($roles);
    exit;
}

/* =========================
   GET ROLE BY ID
========================= */
if ($method === "GET" && isset($_GET['id'])) {

    $stmt = $pdo->prepare("SELECT * FROM roles WHERE id_role = ?");
    $stmt->execute([$_GET['id']]);
    $role = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($role) {
        echo json_encode($role);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Role not found"]);
    }
    exit;
}

/* =========================
   CREATE ROLE
========================= */
if ($method === "POST") {

    if (!isset($data['nom_role'])) {
        http_response_code(400);
        echo json_encode(["message" => "nom_role is required"]);
        exit;
    }

    $stmt = $pdo->prepare(
        "INSERT INTO roles (nom_role, description) VALUES (?, ?)"
    );

    try {
        $stmt->execute([
            $data['nom_role'],
            $data['description'] ?? null
        ]);

        echo json_encode([
            "message" => "Role created",
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
   UPDATE ROLE
========================= */
if ($method === "PUT") {

    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["message" => "ID is required"]);
        exit;
    }

    $stmt = $pdo->prepare(
        "UPDATE roles 
         SET nom_role = ?, description = ? 
         WHERE id_role = ?"
    );

    $stmt->execute([
        $data['nom_role'],
        $data['description'] ?? null,
        $_GET['id']
    ]);

    echo json_encode(["message" => "Role updated"]);
    exit;
}

/* =========================
   DELETE ROLE
========================= */
if ($method === "DELETE") {

    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["message" => "ID is required"]);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM roles WHERE id_role = ?");
    $stmt->execute([$_GET['id']]);

    echo json_encode(["message" => "Role deleted"]);
    exit;
}

/* =========================
   INVALID METHOD
========================= */
http_response_code(405);
echo json_encode(["message" => "Method not allowed"]);