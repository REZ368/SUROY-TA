<?php
session_start();

class AuthController {
    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            switch ($action) {
                case 'logout':
                    $this->logout();
                    break;
                default:
                    $this->sendResponse(false, 'Invalid action');
            }
        } else {
            $this->sendResponse(false, 'Invalid request method');
        }
    }

    private function logout() {
        try {
            // Clear all session variables
            $_SESSION = array();

            // Destroy the session
            if (session_destroy()) {
                $this->sendResponse(true, "Logged out successfully");
            } else {
                throw new Exception('Failed to destroy session');
            }
        } catch (Exception $e) {
            $this->sendResponse(false, 'Logout failed: ' . $e->getMessage());
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
$controller = new AuthController();
$controller->handleRequest(); 