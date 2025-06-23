<?php
require_once __DIR__ . '/../models/PRModel.php';

class PRController {
    private $model;

    public function __construct() {
        $this->model = new PRModel();
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            switch ($action) {
                case 'list':
                    $this->list();
                    break;
                case 'create':
                    $this->create();
                    break;
                case 'delete':
                    $this->delete();
                    break;
                case 'update':
                    $this->update();
                    break;
                default:
                    $this->sendResponse(false, 'Invalid action');
            }
        } else {
            $this->sendResponse(false, 'Invalid request method');
        }
    }

    private function list() {
        try {
            $search = $_POST['search'] ?? '';
            $page = max(1, intval($_POST['page'] ?? 1));
            $limit = max(1, intval($_POST['limit'] ?? 10));
            $id = $_POST['id'] ?? null;
            
            // If ID is provided, fetch single record
            if ($id) {
                $result = $this->model->getById($id);
                if ($result) {
                    // Process image path
                    if (!empty($result['image_path'])) {
                        $result['image_url'] = '../../backend/' . $result['image_path'];
                    }
                    $this->sendResponse(true, "Record fetched successfully", ['data' => [$result]]);
                } else {
                    $this->sendResponse(false, "Record not found");
                }
                return;
            }
            
            // Get data from model
            $result = $this->model->list($search, $page, $limit);
            
            // Process image paths
            foreach ($result['data'] as &$record) {
                if (!empty($record['image_path'])) {
                    // Convert relative path to absolute URL
                    $record['image_url'] = '../../backend/' . $record['image_path'];
                }
            }
            
            $this->sendResponse(true, "Records fetched successfully", $result);
        } catch (Exception $e) {
            $this->sendResponse(false, 'Failed to fetch records: ' . $e->getMessage());
        }
    }

    private function create() {
        try {
            // Validate required fields
            $requiredFields = ['pr_number', 'pr_date', 'requested_by', 'item_description', 'estimated_cost'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Missing required field: $field");
                }
            }

            // Prepare data for model
            $data = [
                'pr_number' => $_POST['pr_number'],
                'pr_date' => $_POST['pr_date'],
                'requested_by' => $_POST['requested_by'],
                'item_description' => $_POST['item_description'],
                'estimated_cost' => floatval($_POST['estimated_cost'])
            ];

            // Create the record
            $this->model->create($data);
            $this->sendResponse(true, "Purchase request created successfully");
        } catch (Exception $e) {
            $this->sendResponse(false, 'Failed to create purchase request: ' . $e->getMessage());
        }
    }

    private function delete() {
        try {
            if (empty($_POST['id'])) {
                throw new Exception('ID is required for deletion');
            }

            $id = intval($_POST['id']);
            if ($id <= 0) {
                throw new Exception('Invalid ID provided');
            }

            $this->model->delete($id);
            $this->sendResponse(true, "Record deleted successfully");
        } catch (Exception $e) {
            $this->sendResponse(false, 'Failed to delete record: ' . $e->getMessage());
        }
    }

    private function update() {
        try {
            // Validate required fields
            $requiredFields = ['id', 'pr_number', 'pr_date', 'requested_by', 'item_description', 'estimated_cost'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Missing required field: $field");
                }
            }

            // Prepare data for model
            $data = [
                'id' => intval($_POST['id']),
                'pr_number' => $_POST['pr_number'],
                'pr_date' => $_POST['pr_date'],
                'requested_by' => $_POST['requested_by'],
                'item_description' => $_POST['item_description'],
                'estimated_cost' => floatval($_POST['estimated_cost'])
            ];

            // Update the record
            $this->model->update($data);
            $this->sendResponse(true, "Purchase request updated successfully");
        } catch (Exception $e) {
            $this->sendResponse(false, 'Failed to update purchase request: ' . $e->getMessage());
        }
    }

    private function sendResponse($success, $message, $data = null) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }
}

// Create and run the controller
$controller = new PRController();
$controller->handleRequest();

