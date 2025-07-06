<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../api/api_helpers.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de connexion à la base']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];


switch ($method) {
    case 'GET':
        handleGet($pdo);
        break;
    case 'POST':
        handlePost($pdo);
        break;
    case 'PUT':
        handlePut($pdo);
        break;
    case 'DELETE':
        handleDelete($pdo);
        break;
    default:
        sendResponse(405, ['error' => 'Méthode non autorisée']);
        break;
}


function handleGet(PDO $pdo): void
{
    $id = $_GET['id'] ?? null;

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
}

function handlePost(PDO $pdo): void
{
    $data = getJsonInput();

    if (empty($data['post_id']) || empty($data['requester_id'])) {
        sendResponse(400, ['error' => 'Les champs "post_id" et "requester_id" sont obligatoires.']);
        return;
    }
    // TODO: vérifier l'existence de post_id et requester_id, vérifier que post_id n'est pas déjà occupé

    try {
        $stmt = $pdo->prepare("
            INSERT INTO swap_shift (post_id, requester_id, status)
            VALUES (:post_id, :requester_id, 'pending')
        ");
        $stmt->execute([
            ':post_id' => $data['post_id'],
            ':requester_id' => $data['requester_id'],
        ]);
        sendResponse(201, ['success' => 'Demande créée avec succès.']);
    } catch (PDOException $e) {
        sendResponse(500, ['error' => 'Erreur lors de la création de la demande']);
    }
}


function handlePut(PDO $pdo): void
{
    $data = getJsonInput();
    $id = $data['swap'] ?? null;

    if ($id === null) {
        sendResponse(400, ['error' => 'Champ swap obligatoire']);
        return;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM swap_shift WHERE id = ?");
        $stmt->execute([$id]);
        $request = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$request) {
            sendResponse(404, ['error' => 'Demande non trouvée']);
            return;
        }

        $fields = [];
        $params = [];

        if (isset($data['candidat'])) {
            $fields[] = 'receiver_id = :receiver_id';
            $params[':receiver_id'] = $data['candidat'];
        }

        if (isset($data['superviseur'], $data['statut'])) {
            $fields[] = 'validator_id = :validator_id';
            $params[':validator_id'] = $data['superviseur'];
            $fields[] = 'status = :status';
            $params[':status'] = $data['statut'];
            $fields[] = 'validated_at = NOW()';
        }

        if (empty($fields)) {
            sendResponse(400, ['error' => 'Aucune donnée valide à mettre à jour']);
            return;
        }

        $params[':id'] = $id;
        $sql = "UPDATE swap_shift SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Retourne la demande mise à jour
        $stmt = $pdo->prepare("SELECT * FROM swap_shift WHERE id = ?");
        $stmt->execute([$id]);
        $updatedRequest = $stmt->fetch(PDO::FETCH_ASSOC);

        sendResponse(200, $updatedRequest);
    } catch (PDOException $e) {
        sendResponse(500, ['error' => 'Erreur lors de la mise à jour', 'message' => $e->getMessage()]);
    }
}

function handleDelete(PDO $pdo): void
{
    $swapId = $_GET['swap'] ?? null;

    if ($swapId === null) {
        sendResponse(400, ['error' => 'Champ swap oligatoire']);
        return;
    }

    $swapId = intval($swapId);
    if ($swapId <= 0) {
        sendResponse(400, ['error' => 'Identifiant "swap" invalide.']);
        return;
    }

    try {
        $stmt = $pdo->prepare("SELECT id FROM swap_shift WHERE id = :id");
        $stmt->execute([':id' => $swapId]);
        if (!$stmt->fetch()) {
            sendResponse(404, ['error' => 'Échange introuvable.']);
            return;
        }

        // Soft delete: mettre à jour le statut au lieu de supprimer
        $stmt = $pdo->prepare("UPDATE swap_shift SET status = 'deleted' WHERE id = :id");
        $stmt->execute([':id' => $swapId]);
        sendResponse(204);
    } catch (PDOException $e) {
        sendResponse(500, ['error' => 'Erreur lors de la suppression.']);
    }
}
