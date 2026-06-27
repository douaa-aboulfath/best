<?php
header("Content-Type: application/json");
require "config.php";

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

/* =========================
   GET ALL FACTURES
========================= */
if ($method === "GET" && !isset($_GET['id'])) {

    $stmt = $pdo->query("
        SELECT f.*,
               c.nom AS client_nom,
               c.prenom AS client_prenom,
               u.nom AS utilisateur_nom
        FROM factures f
        JOIN clients c ON f.id_client = c.id_client
        JOIN utilisateurs u ON f.id_utilisateur = u.id_utilisateur
        ORDER BY f.id_facture DESC
    ");

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($rows);
    exit;
}

/* =========================
   GET FACTURE BY ID
========================= */
if ($method === "GET" && isset($_GET['id'])) {

    $stmt = $pdo->prepare("
        SELECT f.*,
               c.nom AS client_nom,
               c.prenom AS client_prenom,
               u.nom AS utilisateur_nom
        FROM factures f
        JOIN clients c ON f.id_client = c.id_client
        JOIN utilisateurs u ON f.id_utilisateur = u.id_utilisateur
        WHERE f.id_facture = ?
    ");

    $stmt->execute([$_GET['id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo json_encode($row);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Facture not found"]);
    }

    exit;
}

/* =========================
   CREATE FACTURE
========================= */
if ($method === "POST") {

    if (!isset($data['numero_facture'], $data['id_client'], $data['id_utilisateur'])) {
        http_response_code(400);
        echo json_encode(["message" => "Missing required fields"]);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO factures (
            numero_facture,
            id_client,
            id_utilisateur,
            montant_ht,
            montant_tva,
            montant_ttc,
            statut
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    try {
        $stmt->execute([
            $data['numero_facture'],
            $data['id_client'],
            $data['id_utilisateur'],
            $data['montant_ht'] ?? 0,
            $data['montant_tva'] ?? 0,
            $data['montant_ttc'] ?? 0,
            $data['statut'] ?? 'BROUILLON'
        ]);

        echo json_encode([
            "message" => "Facture created",
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
   UPDATE FACTURE
========================= */
if ($method === "PUT") {

    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["message" => "ID is required"]);
        exit;
    }

    $stmt = $pdo->prepare("
        UPDATE factures SET
            numero_facture = ?,
            id_client = ?,
            id_utilisateur = ?,
            montant_ht = ?,
            montant_tva = ?,
            montant_ttc = ?,
            statut = ?
        WHERE id_facture = ?
    ");

    $stmt->execute([
        $data['numero_facture'],
        $data['id_client'],
        $data['id_utilisateur'],
        $data['montant_ht'] ?? 0,
        $data['montant_tva'] ?? 0,
        $data['montant_ttc'] ?? 0,
        $data['statut'] ?? 'BROUILLON',
        $_GET['id']
    ]);

    echo json_encode(["message" => "Facture updated"]);
    exit;
}

/* =========================
   DELETE FACTURE
========================= */
if ($method === "DELETE") {

    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["message" => "ID is required"]);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM factures WHERE id_facture = ?");
    $stmt->execute([$_GET['id']]);

    echo json_encode(["message" => "Facture deleted"]);
    exit;
}

/* =========================
   INVALID METHOD
========================= */
http_response_code(405);
echo json_encode(["message" => "Method not allowed"]);