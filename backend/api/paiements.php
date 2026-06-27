<?php
header("Content-Type: application/json");
require "config.php";

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

/* =========================
   GET ALL PAYMENTS
========================= */
if ($method === "GET" && !isset($_GET['id'])) {

    $stmt = $pdo->query("
        SELECT p.*,
               f.numero_facture
        FROM paiements p
        JOIN factures f ON p.id_facture = f.id_facture
        ORDER BY p.id_paiement DESC
    ");

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($rows);
    exit;
}

/* =========================
   GET PAYMENT BY ID
========================= */
if ($method === "GET" && isset($_GET['id'])) {

    $stmt = $pdo->prepare("
        SELECT p.*,
               f.numero_facture
        FROM paiements p
        JOIN factures f ON p.id_facture = f.id_facture
        WHERE p.id_paiement = ?
    ");

    $stmt->execute([$_GET['id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo json_encode($row);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Payment not found"]);
    }

    exit;
}

/* =========================
   CREATE PAYMENT
========================= */
if ($method === "POST") {

    if (!isset($data['id_facture'], $data['montant'], $data['mode_paiement'])) {
        http_response_code(400);
        echo json_encode(["message" => "Missing required fields"]);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO paiements (
            id_facture,
            montant,
            mode_paiement,
            reference_paiement
        )
        VALUES (?, ?, ?, ?)
    ");

    try {
        $stmt->execute([
            $data['id_facture'],
            $data['montant'],
            $data['mode_paiement'],
            $data['reference_paiement'] ?? null
        ]);

        echo json_encode([
            "message" => "Payment created",
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
   UPDATE PAYMENT
========================= */
if ($method === "PUT") {

    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["message" => "ID is required"]);
        exit;
    }

    $stmt = $pdo->prepare("
        UPDATE paiements SET
            id_facture = ?,
            montant = ?,
            mode_paiement = ?,
            reference_paiement = ?
        WHERE id_paiement = ?
    ");

    $stmt->execute([
        $data['id_facture'],
        $data['montant'],
        $data['mode_paiement'],
        $data['reference_paiement'] ?? null,
        $_GET['id']
    ]);

    echo json_encode(["message" => "Payment updated"]);
    exit;
}

/* =========================
   DELETE PAYMENT
========================= */
if ($method === "DELETE") {

    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["message" => "ID is required"]);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM paiements WHERE id_paiement = ?");
    $stmt->execute([$_GET['id']]);

    echo json_encode(["message" => "Payment deleted"]);
    exit;
}

/* =========================
   INVALID METHOD
========================= */
http_response_code(405);
echo json_encode(["message" => "Method not allowed"]);
