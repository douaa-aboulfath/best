<?php
header("Content-Type: application/json");
require "config.php";

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

/* =========================
   GET ALL USER-ROLES
========================= */
if ($method === "GET" && !isset($_GET['user'])) {

    $stmt = $pdo->query("
        SELECT ur.id_utilisateur, ur.id_role,
               u.nom AS utilisateur_nom,
               r.nom_role
        FROM utilisateur_roles ur
        JOIN utilisateurs u ON ur.id_utilisateur = u.id_utilisateur
        JOIN roles r ON ur.id_role = r.id_role
    ");

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result);
    exit;
}

/* =========================
   GET ROLES OF ONE USER
========================= */
if ($method === "GET" && isset($_GET['user'])) {

    $stmt = $pdo->prepare("
        SELECT r.id_role, r.nom_role, r.description
        FROM utilisateur_roles ur
        JOIN roles r ON ur.id_role = r.id_role
        WHERE ur.id_utilisateur = ?
    ");

    $stmt->execute([$_GET['user']]);
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($roles);
    exit;
}

/* =========================
   ASSIGN ROLE TO USER
========================= */
if ($method === "POST") {

    if (!isset($data['id_utilisateur']) || !isset($data['id_role'])) {
        http_response_code(400);
        echo json_encode([
            "message" => "id_utilisateur and id_role are required"
        ]);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO utilisateur_roles (id_utilisateur, id_role)
        VALUES (?, ?)
    ");

    try {
        $stmt->execute([
            $data['id_utilisateur'],
            $data['id_role']
        ]);

        echo json_encode([
            "message" => "Role assigned to user"
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
   DELETE USER ROLE
========================= */
if ($method === "DELETE") {

    if (!isset($_GET['user']) || !isset($_GET['role'])) {
        http_response_code(400);
        echo json_encode([
            "message" => "user and role are required"
        ]);
        exit;
    }

    $stmt = $pdo->prepare("
        DELETE FROM utilisateur_roles
        WHERE id_utilisateur = ? AND id_role = ?
    ");

    $stmt->execute([
        $_GET['user'],
        $_GET['role']
    ]);

    echo json_encode([
        "message" => "Role removed from user"
    ]);
    exit;
}

/* =========================
   INVALID METHOD
========================= */
http_response_code(405);
echo json_encode(["message" => "Method not allowed"]);