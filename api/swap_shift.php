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
        // TODO : vérifier que post_id et requester_id existent dans les tables correspondantes
        // TODO : vérifier que le post_id n'est pas déjà occupé par un autre swap        
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
        $data = json_decode(file_get_contents('php://input'), true);

        // quelle demande d'échange ?
        $id = $data['swap'] ?? null;
        if ($id === null) {
            http_response_code(400);
            echo json_encode(['error' => 'Champ swap obligatoire']);
            exit;
        }

        try {
            // Vérifier que la demande existe
            $stmt = $pdo->prepare("SELECT * FROM swap_shift WHERE id = ?");
            $stmt->execute([$id]);
            $request = $stmt->fetch();
            if (!$request) {
                http_response_code(404);
                echo json_encode(['error' => 'Demande non trouvée']);
                exit;
            }

            // Préparation des parties à mettre à jour
            $fields = [];
            $params = [];

            // selon les champs reçus, on prépare la requête de mise à jour

            if (isset($data['candidat'])) {
                $fields[] = 'receiver_id = :receiver_id';
                $params[':receiver_id'] = $data['candidat'];
            }

            if (isset($data['superviseur']) && isset($data['statut'])) {
                $fields[] = 'validator_id = :validator_id';
                $params[':validator_id'] = $data['superviseur'];
                $fields[] = 'status = :status';
                $params[':status'] = $data['statut'];
                $fields[] = 'validated_at = NOW()';
            }

            $params[':id'] = $id;
            $sql = "UPDATE swap_shift SET " . implode(', ', $fields) . " WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            // Retourne la demande mise à jour
            $stmt = $pdo->prepare("SELECT * FROM swap_shift WHERE id = ?");
            $stmt->execute([$id]);
            $updatedRequest = $stmt->fetch();

            echo json_encode($updatedRequest);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la mise à jour', 'message' => $e->getMessage()]);
        }
        break;


    case 'DELETE':
        // suppression en base, mais il serait préférable de faire un soft delete (ex : etat = 'deleted')
        if (!isset($_GET['swap'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Champ swap oligatoire']);
            break;
        }
        $swapId = intval($_GET['swap']);

        if ($swapId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Identifiant "swap" invalide.']);
            break;
        }

        try {
            $stmt = $pdo->prepare("SELECT id FROM swap_shift WHERE id = :id");
            $stmt->execute([':id' => $swapId]);
            $result = $stmt->fetch();

            if (!$result) {
                http_response_code(404);
                echo json_encode(['error' => 'Échange introuvable.']);
                break;
            }

            $stmt = $pdo->prepare("DELETE FROM swap_shift WHERE id = :id");
            $stmt->execute([':id' => $swapId]);

            http_response_code(204);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la suppression.']);
        }

        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Méthode non autorisée']);
        break;
}
