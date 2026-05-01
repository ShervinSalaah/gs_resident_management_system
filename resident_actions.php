<?php
session_start();

// Protect - only logged in users can access
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once 'resident.class.php';
$resident = new Resident();

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'full_name' => $_POST['full_name'],
                'dob' => $_POST['dob'],
                'nic' => $_POST['nic'],
                'address' => $_POST['address'],
                'phone' => $_POST['phone'],
                'email' => $_POST['email'],
                'occupation' => $_POST['occupation'] ?? '',
                'gender' => $_POST['gender']
            ];
            
            // Check if NIC already exists
            if ($resident->nicExists($data['nic'])) {
                echo json_encode(['success' => false, 'error' => 'NIC already exists!']);
                exit();
            }
            
            $result = $resident->add($data);
            echo json_encode($result);
        }
        break;

    case 'update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $data = [
                'full_name' => $_POST['full_name'],
                'dob' => $_POST['dob'],
                'nic' => $_POST['nic'],
                'address' => $_POST['address'],
                'phone' => $_POST['phone'],
                'email' => $_POST['email'],
                'occupation' => $_POST['occupation'] ?? '',
                'gender' => $_POST['gender']
            ];
            
            // Check if NIC exists (excluding current record)
            if ($resident->nicExists($data['nic'], $id)) {
                echo json_encode(['success' => false, 'error' => 'NIC already exists!']);
                exit();
            }
            
            $result = $resident->update($id, $data);
            echo json_encode(['success' => $result]);
        }
        break;

    case 'delete':
        $id = $_GET['id'];
        $result = $resident->delete($id);
        header("Location: residents.php?msg=deleted");
        exit();
        break;

    case 'get':
        $id = $_GET['id'];
        $data = $resident->getById($id);
        if ($data) {
            echo json_encode($data);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Resident not found']);
        }
        break;
}
?>