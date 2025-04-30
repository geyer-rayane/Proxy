<?php

// === CONFIGURATION ===
$appToken = 'WMwUQFvhdxLE6bBHBBFHA53cQO4dkjDUoSYD0FKe';
$userToken = 'GRs14Za3o2tVPaq2H3j4RCDgmrSaPNFTOtvARh7u';
$apiUrl = 'https://poc-glpi-dlog.vaucluse.fr/apirest.php/Ticket/';

// === RÉCUPÉRATION DES DONNÉES BRUTES ===
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

// === LOGGING ===
$logFile = __DIR__ . '/log_debug.txt';
$logData = "==== NOUVELLE REQUÊTE [" . date('Y-m-d H:i:s') . "] ====\n";
$logData .= "RAW INPUT:\n$rawInput\n\n";
$logData .= "JSON DECODE:\n" . print_r($input, true) . "\n";

file_put_contents($logFile, $logData, FILE_APPEND);

// === VÉRIFICATION ===
if (!$input || !isset($input['title'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Données invalides']);
    exit;
}

// === PRÉPARATION DE LA REQUÊTE API ===
$data = [
    'input' => [
        'name' => $input['title'],
        'content' => $input['description'] ?? 'Ticket envoyé depuis DLOG',
        'itilcategories_id' => 1,
        'type' => 1,
        'requesttypes_id' => 1
    ]
];

// === ENVOI VERS L'API GLPI BS ===
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "App-Token: $appToken",
    "Authorization: user_token $userToken",
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

// === LOG DE LA RÉPONSE ===
$logResponse = "HTTP RESPONSE CODE: $httpCode\n";
$logResponse .= "API RESPONSE:\n$response\n\n";
file_put_contents($logFile, $logResponse, FILE_APPEND);

// === RÉPONSE VERS LE CLIENT ===
http_response_code($httpCode);
header('Content-Type: application/json');
echo $response;
?>
