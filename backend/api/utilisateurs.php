<?php

session_start();

header('Content-Type: application/json');

/*
|--------------------------------------------------------------------------
| CONFIG DATABASE
|--------------------------------------------------------------------------
*/

$host = "localhost";
$dbname = "decoration_shop";
$user = "root";
$password = "";

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $password
    );

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

} catch(PDOException $e) {

    die(json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]));
}

/*
|--------------------------------------------------------------------------
| FONCTIONS
|--------------------------------------------------------------------------
*/

function responseJson($success, $message = '', $data = null)
{
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ]);
    exit;
}

function isAdmin()
{
    return isset($_SESSION['role'])
        && $_SESSION['role'] === 'ADMIN';
}

function isLogged()
{
    return isset($_SESSION['id_utilisateur']);
}

/*
|--------------------------------------------------------------------------
| ACTION
|--------------------------------------------------------------------------
*/

$action = $_REQUEST['action'] ?? '';

/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/

if ($action == 'login')
{
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $sql = "
        SELECT *
        FROM utilisateurs
        WHERE email = ?
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);

    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userData)
    {
        responseJson(false, "Utilisateur introuvable");
    }

    if (!password_verify(
        $password,
        $userData['mot_de_passe']
    ))
    {
        responseJson(false, "Mot de passe incorrect");
    }

    $_SESSION['id_utilisateur']
        = $userData['id_utilisateur'];

    $_SESSION['nom']
        = $userData['nom'];

    $_SESSION['role']
        = $userData['role'];

    responseJson(true, "Connexion réussie", [
        "id" => $userData['id_utilisateur'],
        "nom" => $userData['nom'],
        "role" => $userData['role']
    ]);
}

/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
*/

if ($action == 'logout')
{
    session_destroy();

    responseJson(true, "Déconnexion effectuée");
}

/*
|--------------------------------------------------------------------------
| SECURITE
|--------------------------------------------------------------------------
*/

if (!isLogged())
{
    responseJson(false, "Non connecté");
}

/*
|--------------------------------------------------------------------------
| LISTE
|--------------------------------------------------------------------------
*/

if ($action == 'list')
{
    $stmt = $pdo->query("
        SELECT
            id_utilisateur,
            nom,
            prenom,
            email,
            role,
            actif,
            date_creation
        FROM utilisateurs
        ORDER BY id_utilisateur DESC
    ");

    responseJson(
        true,
        "Liste utilisateurs",
        $stmt->fetchAll(PDO::FETCH_ASSOC)
    );
}

/*
|--------------------------------------------------------------------------
| DETAIL
|--------------------------------------------------------------------------
*/

if ($action == 'get')
{
    $id = $_GET['id'] ?? 0;

    $stmt = $pdo->prepare("
        SELECT
            id_utilisateur,
            nom,
            prenom,
            email,
            role,
            actif
        FROM utilisateurs
        WHERE id_utilisateur=?
    ");

    $stmt->execute([$id]);

    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userData)
    {
        responseJson(false, "Utilisateur introuvable");
    }

    responseJson(true, "", $userData);
}

/*
|--------------------------------------------------------------------------
| CREATE
|--------------------------------------------------------------------------
*/

if ($action == 'create')
{
    if (!isAdmin())
    {
        responseJson(false, "Accès refusé");
    }

    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'VENDEUR';

    if (
        empty($nom) ||
        empty($email) ||
        empty($password)
    )
    {
        responseJson(false, "Champs obligatoires");
    }

    $hash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    $stmt = $pdo->prepare("
        INSERT INTO utilisateurs
        (
            nom,
            prenom,
            email,
            mot_de_passe,
            role
        )
        VALUES
        (
            ?,?,?,?,?
        )
    ");

    $stmt->execute([
        $nom,
        $prenom,
        $email,
        $hash,
        $role
    ]);

    responseJson(true, "Utilisateur créé");
}

/*
|--------------------------------------------------------------------------
| UPDATE
|--------------------------------------------------------------------------
*/

if ($action == 'update')
{
    if (!isAdmin())
    {
        responseJson(false, "Accès refusé");
    }

    $stmt = $pdo->prepare("
        UPDATE utilisateurs
        SET
            nom=?,
            prenom=?,
            email=?,
            role=?,
            actif=?
        WHERE id_utilisateur=?
    ");

    $stmt->execute([
        $_POST['nom'],
        $_POST['prenom'],
        $_POST['email'],
        $_POST['role'],
        $_POST['actif'],
        $_POST['id_utilisateur']
    ]);

    responseJson(true, "Utilisateur modifié");
}

/*
|--------------------------------------------------------------------------
| PASSWORD
|--------------------------------------------------------------------------
*/

if ($action == 'change_password')
{
    $id = $_POST['id_utilisateur'];

    $hash = password_hash(
        $_POST['password'],
        PASSWORD_DEFAULT
    );

    $stmt = $pdo->prepare("
        UPDATE utilisateurs
        SET mot_de_passe=?
        WHERE id_utilisateur=?
    ");

    $stmt->execute([
        $hash,
        $id
    ]);

    responseJson(
        true,
        "Mot de passe modifié"
    );
}

/*
|--------------------------------------------------------------------------
| DELETE
|--------------------------------------------------------------------------
*/

if ($action == 'delete')
{
    if (!isAdmin())
    {
        responseJson(false, "Accès refusé");
    }

    $stmt = $pdo->prepare("
        DELETE FROM utilisateurs
        WHERE id_utilisateur=?
    ");

    $stmt->execute([
        $_POST['id_utilisateur']
    ]);

    responseJson(true, "Utilisateur supprimé");
}

responseJson(false, "Action inconnue");