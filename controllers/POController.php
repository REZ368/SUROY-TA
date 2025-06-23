<?php
require_once '../models/POModel.php';

class POController {
    private $model;

    public function __construct() {
        $this->model = new POModel();
    }

    // Check if PR number exists
    public function checkPrNumber() {
        // Retrieve the PR number from the request (AJAX)
        $prNumber = isset($_GET['pr_number']) ? $_GET['pr_number'] : null;

        // Call the model method to check if the PR number exists
        $exists = $this->model->checkPrNumberExists($prNumber);

        // Return JSON response
        echo json_encode(['exists' => $exists]);
    }

    public function createPurchaseOrder() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['pr_number']) || !isset($data['po_number']) || !isset($data['po_date'])) {
                throw new Exception('Missing required fields');
            }

            $prNumber = $data['pr_number'];
            $poNumber = $data['po_number'];
            $poDate = $data['po_date'];
            $mark = $data['mark'] ?? '';
            $imagePath = $data['image_path'] ?? '';
            $suppliers = $data['suppliers'] ?? [];

            // Validate PR number exists
            if (!$this->model->checkPrNumberExists($prNumber)) {
                throw new Exception('PR number does not exist');
            }

            $result = $this->model->createPurchaseOrder($prNumber, $poNumber, $poDate, $mark, $imagePath, $suppliers);
            echo json_encode(['success' => $result]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function getPurchaseOrders() {
        try {
            $orders = $this->model->getPurchaseOrders();
            echo json_encode(['success' => true, 'data' => $orders]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function getPurchaseOrder() {
        try {
            $id = isset($_GET['id']) ? $_GET['id'] : null;
            if (!$id) {
                throw new Exception('ID is required');
            }

            $order = $this->model->getPurchaseOrderById($id);
            echo json_encode(['success' => true, 'data' => $order]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function updatePurchaseOrder() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['id']) || !isset($data['po_number']) || !isset($data['po_date'])) {
                throw new Exception('Missing required fields');
            }

            $id = $data['id'];
            $poNumber = $data['po_number'];
            $poDate = $data['po_date'];
            $mark = $data['mark'] ?? '';
            $imagePath = $data['image_path'] ?? '';
            $suppliers = $data['suppliers'] ?? [];

            $result = $this->model->updatePurchaseOrder($id, $poNumber, $poDate, $mark, $imagePath, $suppliers);
            echo json_encode(['success' => $result]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function deletePurchaseOrder() {
        try {
            $id = isset($_GET['id']) ? $_GET['id'] : null;
            if (!$id) {
                throw new Exception('ID is required');
            }

            $result = $this->model->deletePurchaseOrder($id);
            echo json_encode(['success' => $result]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
