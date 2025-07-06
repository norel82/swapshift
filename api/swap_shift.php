<?php

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

try {
    $db = new Database();
    $pdo = $db->getConnection();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de connexion à la base']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;

switch ($method) {
    case 'GET':
        if ($id === null) {
            // Récupérer toutes les demandes
            try {
                $stmt = $pdo->query("SELECT * FROM swap_shift ORDER BY created_at DESC");
                $requests = $stmt->fetchAll();
                echo json_encode($requests);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur lors de la récupération des demandes']);
            }
        } else {
            // Récupérer une demande précise par ID
            try {
                $stmt = $pdo->prepare("SELECT * FROM swap_shift WHERE id = ?");
                $stmt->execute([$id]);
                $request = $stmt->fetch();
                if ($request === false) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Demande non trouvée']);
                } else {
                    echo json_encode($request);
                }
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur lors de la récupération de la demande']);
            }
        }
        break;

    case 'POST':

        $data = json_decode(file_get_contents('php://input'), true);

        // Validation des champs obligatoires
        if (
            empty($data['post_id']) ||
            empty($data['requester_id'])
        ) {
            http_response_code(400);
            echo json_encode(['error' => 'Les champs "post_id" et "requester_id" sont obligatoires.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("
            INSERT INTO swap_shift (post_id, requester_id, status)
            VALUES (:post_id, :requester_id, 'pending')
        ");

            $stmt->execute([
                ':post_id' => $data['post_id'],
                ':requester_id' => $data['requester_id'],
            ]);

            http_response_code(201);
            echo json_encode(['success' => 'Demande créée avec succès.']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la création de la demande']);
        }
        break;

    case 'PUT':
        // TODO : mise à jour
        http_response_code(501);
        echo json_encode(['error' => 'PUT non encore implémenté']);
        break;

    case 'DELETE':
        // TODO : suppression
        http_response_code(501);
        echo json_encode(['error' => 'DELETE non encore implémenté']);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Méthode non autorisée']);
        break;
}
