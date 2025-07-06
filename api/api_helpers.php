<?php

/**
 * Envoie une réponse JSON avec code HTTP.
 */
function sendResponse(int $code, $data = null): void
{
    http_response_code($code);
    header('Content-Type: application/json');
    if ($data !== null) {
        echo json_encode($data);
    }
}

/**
 * Lit et décode le JSON reçu en entrée.
 */
function getJsonInput(): array
{
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendResponse(400, ['error' => 'JSON invalide']);
        exit;
    }
    return $data;
}
