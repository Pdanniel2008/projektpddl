<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'ibox';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die(json_encode(['error' => 'Adatbázis hiba']));
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
        if ($filter === 'all') {
            $result = $conn->query("SELECT * FROM ideas ORDER BY created_at DESC");
        } else {
            $stmt = $conn->prepare("SELECT * FROM ideas WHERE category = ? ORDER BY created_at DESC");
            $stmt->bind_param('s', $filter);
            $stmt->execute();
            $result = $stmt->get_result();
        }
        $ideas = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($ideas);
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $conn->prepare("INSERT INTO ideas (category, problem, solution, email) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $data['category'], $data['problem'], $data['solution'], $data['email']);
        if ($stmt->execute()) {
            echo json_encode(['id' => $conn->insert_id, 'success' => true]);
        } else {
            echo json_encode(['error' => 'Mentés sikertelen']);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];
        $completed = $data['completed'] ? 1 : 0;
        $stmt = $conn->prepare("UPDATE ideas SET completed = ? WHERE id = ?");
        $stmt->bind_param('ii', $completed, $id);
        $stmt->execute();
        echo json_encode(['success' => true]);
        break;

    case 'DELETE':
        $id = $_GET['id'];
        $stmt = $conn->prepare("DELETE FROM ideas WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        echo json_encode(['success' => true]);
        break;
}
?>