<?php
header("Content-Type: application/json");
require "config.php";

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

/* =========================
   GET ALL ROLE-PERMISSIONS
========================= */
if ($method === "GET" && !isset($_GET['role']) && !isset($_GET['permission'])) {

    $stmt = $pdo->query("
        SELECT rp.id_role, rp.id_permission,
               r.nom_role,
               p.code_permission
        FROM role_permissions rp
        JOIN roles r ON rp.id_role = r.id_role
        JOIN permissions p ON rp.id_permission = p.id_permission
    ");

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result);
    exit;
}

/* =========================
   GET PERMISSIONS BY ROLE
========================= */
if ($method === "GET" && isset($_GET['role'])) {

    $stmt = $pdo->prepare("
        SELECT p.id_permission, p.code_permission, p.description
        FROM role_permissions rp
        JOIN permissions p ON rp.id_permission = p.id_permission
        WHERE rp.id_role = ?
    ");

    $stmt->execute([$_GET['role']]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result);
    exit;
}

/* =========================
   CREATE ROLE-PERMISSION
========================= */
if ($method === "POST") {

    if (!isset($data['id_role']) || !isset($data['id_permission'])) {
        http_response_code(400);
        echo json_encode([
            "message" => "id_role and id_permission are required"
        ]);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO role_permissions (id_role, id_permission)
        VALUES (?, ?)
    ");

    try {
        $stmt->execute([
            $data['id_role'],
            $data['id_permission']
        ]);

        echo json_encode([
            "message" => "Permission assigned to role"
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
   DELETE ROLE-PERMISSION
========================= */
if ($method === "DELETE") {

    if (!isset($_GET['role']) || !isset($_GET['permission'])) {
        http_response_code(400);
        echo json_encode([
            "message" => "role and permission are required"
        ]);
        exit;
    }

    $stmt = $pdo->prepare("
        DELETE FROM role_permissions
        WHERE id_role = ? AND id_permission = ?
    ");

    $stmt->execute([
        $_GET['role'],
        $_GET['permission']
    ]);

    echo json_encode([
        "message" => "Role permission removed"
    ]);
    exit;
}

/* =========================
   INVALID METHOD
========================= */
http_response_code(405);
echo json_encode(["message" => "Method not allowed"]);