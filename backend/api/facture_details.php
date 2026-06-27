<?php
header("Content-Type: application/json");
require "config.php";

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

/* =========================
   GET ALL DETAILS
========================= */
if ($method === "GET" && !isset($_GET['facture'])) {

    $stmt = $pdo->query("
        SELECT fd.*,
               f.numero_facture,
               p.designation AS produit
        FROM facture_details fd
        JOIN factures f ON fd.id_facture = f.id_facture
        JOIN produits p ON fd.id_produit = p.id_produit
        ORDER BY fd.id_detail DESC
    ");

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($rows);
    exit;
}

/* =========================
   GET DETAILS BY FACTURE
========================= */
if ($method === "GET" && isset($_GET['facture'])) {

    $stmt = $pdo->prepare("
        SELECT fd.*,
               p.designation AS produit
        FROM facture_details fd
        JOIN produits p ON fd.id_produit = p.id_produit
        WHERE fd.id_facture = ?
    ");

    $stmt->execute([$_GET['facture']]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($rows);
    exit;
}

/* =========================
   CREATE DETAIL LINE
========================= */
if ($method === "POST") {

    if (!isset($data['id_facture'], $data['id_produit'], $data['quantite'], $data['prix_unitaire'])) {
        http_response_code(400);
        echo json_encode(["message" => "Missing required fields"]);
        exit;
    }

    // calculate total line
    $total = ($data['quantite'] * $data['prix_unitaire']) - ($data['remise'] ?? 0);

    $stmt = $pdo->prepare("
        INSERT INTO facture_details (
            id_facture,
            id_produit,
            quantite,
            prix_unitaire,
            remise,
            total_ligne
        )
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    try {
        $stmt->execute([
            $data['id_facture'],
            $data['id_produit'],
            $data['quantite'],
            $data['prix_unitaire'],
            $data['remise'] ?? 0,
            $total
        ]);

        echo json_encode([
            "message" => "Facture detail created",
            "id" => $pdo->lastInsertId(),
            "total_ligne" => $total
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
   UPDATE DETAIL
========================= */
if ($method === "PUT") {

    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["message" => "ID is required"]);
        exit;
    }

    $total = ($data['quantite'] * $data['prix_unitaire']) - ($data['remise'] ?? 0);

    $stmt = $pdo->prepare("
        UPDATE facture_details SET
            id_facture = ?,
            id_produit = ?,
            quantite = ?,
            prix_unitaire = ?,
            remise = ?,
            total_ligne = ?
        WHERE id_detail = ?
    ");

    $stmt->execute([
        $data['id_facture'],
        $data['id_produit'],
        $data['quantite'],
        $data['prix_unitaire'],
        $data['remise'] ?? 0,
        $total,
        $_GET['id']
    ]);

    echo json_encode([
        "message" => "Facture detail updated",
        "total_ligne" => $total
    ]);

    exit;
}

/* =========================
   DELETE DETAIL
========================= */
if ($method === "DELETE") {

    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["message" => "ID is required"]);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM facture_details WHERE id_detail = ?");
    $stmt->execute([$_GET['id']]);

    echo json_encode(["message" => "Facture detail deleted"]);
    exit;
}

/* =========================
   INVALID METHOD
========================= */
http_response_code(405);
echo json_encode(["message" => "Method not allowed"]);