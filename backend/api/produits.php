<?php
header("Content-Type: application/json");
require "config.php";

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

/* =========================
   GET ALL PRODUCTS
========================= */
if ($method === "GET" && !isset($_GET['id'])) {

    $stmt = $pdo->query("
        SELECT p.*,
               c.nom AS categorie,
               f.raison_sociale AS fournisseur
        FROM produits p
        LEFT JOIN categories c ON p.id_categorie = c.id_categorie
        LEFT JOIN fournisseurs f ON p.id_fournisseur = f.id_fournisseur
        ORDER BY p.id_produit DESC
    ");

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($rows);
    exit;
}

/* =========================
   GET PRODUCT BY ID
========================= */
if ($method === "GET" && isset($_GET['id'])) {

    $stmt = $pdo->prepare("
        SELECT p.*,
               c.nom AS categorie,
               f.raison_sociale AS fournisseur
        FROM produits p
        LEFT JOIN categories c ON p.id_categorie = c.id_categorie
        LEFT JOIN fournisseurs f ON p.id_fournisseur = f.id_fournisseur
        WHERE p.id_produit = ?
    ");

    $stmt->execute([$_GET['id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo json_encode($row);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Product not found"]);
    }

    exit;
}

/* =========================
   CREATE PRODUCT
========================= */
if ($method === "POST") {

    if (!isset($data['reference_produit']) || !isset($data['designation'])) {
        http_response_code(400);
        echo json_encode(["message" => "reference_produit and designation are required"]);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO produits (
            reference_produit,
            designation,
            description,
            prix_achat,
            prix_vente,
            stock_actuel,
            stock_minimum,
            image,
            id_categorie,
            id_fournisseur
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    try {
        $stmt->execute([
            $data['reference_produit'],
            $data['designation'],
            $data['description'] ?? null,
            $data['prix_achat'] ?? 0,
            $data['prix_vente'] ?? 0,
            $data['stock_actuel'] ?? 0,
            $data['stock_minimum'] ?? 5,
            $data['image'] ?? null,
            $data['id_categorie'] ?? null,
            $data['id_fournisseur'] ?? null
        ]);

        echo json_encode([
            "message" => "Product created",
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
   UPDATE PRODUCT
========================= */
if ($method === "PUT") {

    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["message" => "ID is required"]);
        exit;
    }

    $stmt = $pdo->prepare("
        UPDATE produits SET
            reference_produit = ?,
            designation = ?,
            description = ?,
            prix_achat = ?,
            prix_vente = ?,
            stock_actuel = ?,
            stock_minimum = ?,
            image = ?,
            id_categorie = ?,
            id_fournisseur = ?
        WHERE id_produit = ?
    ");

    $stmt->execute([
        $data['reference_produit'],
        $data['designation'],
        $data['description'] ?? null,
        $data['prix_achat'] ?? 0,
        $data['prix_vente'] ?? 0,
        $data['stock_actuel'] ?? 0,
        $data['stock_minimum'] ?? 5,
        $data['image'] ?? null,
        $data['id_categorie'] ?? null,
        $data['id_fournisseur'] ?? null,
        $_GET['id']
    ]);

    echo json_encode(["message" => "Product updated"]);
    exit;
}

/* =========================
   DELETE PRODUCT
========================= */
if ($method === "DELETE") {

    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["message" => "ID is required"]);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM produits WHERE id_produit = ?");
    $stmt->execute([$_GET['id']]);

    echo json_encode(["message" => "Product deleted"]);
    exit;
}

/* =========================
   INVALID METHOD
========================= */
http_response_code(405);
echo json_encode(["message" => "Method not allowed"]);