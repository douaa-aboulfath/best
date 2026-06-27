<?php
header("Content-Type: application/json");
require "config.php";

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

/* =========================
   GET ALL LOGS
========================= */
if ($method === "GET" && !isset($_GET['id'])) {

    $stmt = $pdo->query("
        SELECT *
        FROM audit_log
        ORDER BY date_action DESC
    ");

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($rows);
    exit;
}

/* =========================
   GET LOG BY ID
========================= */
if ($method === "GET" && isset($_GET['id'])) {

    $stmt = $pdo->prepare("
        SELECT *
        FROM audit_log
        WHERE id_log = ?
    ");

    $stmt->execute([$_GET['id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo json_encode($row);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Log not found"]);
    }

    exit;
}

/* =========================
   CREATE LOG
========================= */
if ($method === "POST") {

    if (!isset($data['utilisateur'], $data['table_concernee'], $data['action_effectuee'])) {
        http_response_code(400);
        echo json_encode(["message" => "Missing required fields"]);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO audit_log (
            utilisateur,
            table_concernee,
            action_effectuee
        )
        VALUES (?, ?, ?)
    ");

    try {
        $stmt->execute([
            $data['utilisateur'],
            $data['table_concernee'],
            $data['action_effectuee']
        ]);

        echo json_encode([
            "message" => "Log created",
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
   DELETE LOG
========================= */
if ($method === "DELETE") {

    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["message" => "ID is required"]);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM audit_log WHERE id_log = ?");
    $stmt->execute([$_GET['id']]);

    echo json_encode(["message" => "Log deleted"]);
    exit;
}

/* =========================
   INVALID METHOD
========================= */
http_response_code(405);
echo json_encode(["message" => "Method not allowed"]);