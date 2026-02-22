<?php
require 'db.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$body = json_decode(file_get_contents('php://input'), true);
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($method === 'GET') {
    $rows = $db->query("SELECT * FROM applications ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows);

} elseif ($method === 'POST') {
    $s = $db->prepare("INSERT INTO applications (company, role, status, next_action, notes) VALUES (?,?,?,?,?)");
    $s->execute([$body['company'], $body['role'], $body['status'], $body['next_action'], $body['notes']]);
    echo json_encode(['id' => $db->lastInsertId()]);

} elseif ($method === 'PUT' && $id) {
    $s = $db->prepare("UPDATE applications SET company=?, role=?, status=?, next_action=?, notes=? WHERE id=?");
    $s->execute([$body['company'], $body['role'], $body['status'], $body['next_action'], $body['notes'], $id]);
    echo json_encode(['success' => true]);

} elseif ($method === 'DELETE' && $id) {
    $db->prepare("DELETE FROM applications WHERE id=?")->execute([$id]);
    echo json_encode(['success' => true]);
}