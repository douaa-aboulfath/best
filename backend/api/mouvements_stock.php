<?php
header("Content-Type: application/json");
require "config.php";

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

/* =========================
   GET ALL MOVEMENTS
========================= */
if ($method === "GET" && !isset($_GET['id'])) {

    $stmt = $pdo->query("
        SELECT ms.*,
               p.designation AS produit,
               p.reference_produit
        FROM mouvements_stock ms
        JOIN produits p ON ms.id_produit = p.id_produit
        ORDER BY ms.id_mouvement DESC
    ");

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($rows);
    exit;
}

/* =========================
   GET MOVEMENT BY ID
========================= */
if ($method === "GET" && isset($_GET['id'])) {

    $stmt = $pdo->prepare("
        SELECT ms.*,
               p.designation AS produit,
               p.reference_produit
        FROM mouvements_stock ms
        JOIN produits p ON ms.id_produit = p.id_produit
        WHERE ms.id_mouvement = ?
    ");

    $stmt->execute([$_GET['id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo json_encode($row);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Movement not found"]);
    }

    exit;
}

/* =========================
   CREATE MOVEMENT
========================= */
if ($method === "POST") {

    if (!isset($data['id_produit'], $data['type_mouvement'], $data['quantite'])) {
        http_response_code(400);
        echo json_encode(["message" => "Missing required fields"]);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO mouvements_stock (
            id_produit,
            type_mouvement,
            quantite,
            commentaire
        )
        VALUES (?, ?, ?, ?)
    ");

    try {
        $stmt->execute([
            $data['id_produit'],
            $data['type_mouvement'],
            $data['quantite'],
            $data['commentaire'] ?? null
        ]);

        echo json_encode([
            "message" => "Stock movement created",
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
   UPDATE MOVEMENT
========================= */
if ($method === "PUT") {

    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["message" => "ID is required"]);
        exit;
    }

    $stmt = $pdo->prepare("
        UPDATE mouvements_stock SET
            id_produit = ?,
            type_mouvement = ?,
            quantite = ?,
            commentaire = ?
        WHERE id_mouvement = ?
    ");

    $stmt->execute([
        $data['id_produit'],
        $data['type_mouvement'],
        $data['quantite'],
        $data['commentaire'] ?? null,
        $_GET['id']
    ]);

    echo json_encode(["message" => "Stock movement updated"]);
    exit;
}

/* =========================
   DELETE MOVEMENT
========================= */
if ($method === "DELETE") {

    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["message" => "ID is required"]);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM mouvements_stock WHERE id_mouvement = ?");
    $stmt->execute([$_GET['id']]);

    echo json_encode(["message" => "Stock movement deleted"]);
    exit;
}

/* =========================
   INVALID METHOD
========================= */
http_response_code(405);
echo json_encode(["message" => "Method not allowed"]);