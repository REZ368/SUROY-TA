<?php
class PRModel {
    private $db;
    private $uploadDir;

    public function __construct() {
        try {
            // Connect to the supply_db database
            $this->db = new mysqli('localhost', 'root', '', 'supply_db');
            
            if ($this->db->connect_error) {
                throw new Exception('Database connection failed: ' . $this->db->connect_error);
            }
            
            // Set character set to utf8mb4 for proper handling of special characters
            $this->db->set_charset("utf8mb4");
            
            // Set upload directory
            $this->uploadDir = __DIR__ . '/../uploads/';
            if (!file_exists($this->uploadDir)) {
                mkdir($this->uploadDir, 0777, true);
            }
        } catch (Exception $e) {
            throw new Exception('Database initialization failed: ' . $e->getMessage());
        }
    }

    public function list($search = '', $page = 1, $limit = 10) {
        try {
            $offset = ($page - 1) * $limit;
            $where = '';
            $params = [];
            $types = '';

            if (!empty($search)) {
                $where = "WHERE pr_number LIKE ? OR requested_by LIKE ? OR item_description LIKE ?";
                $searchTerm = "%$search%";
                $params = [$searchTerm, $searchTerm, $searchTerm];
                $types = 'sss';
            }

            // Get total count
            $countQuery = "SELECT COUNT(*) as total FROM purchase_requests $where";
            $countStmt = $this->db->prepare($countQuery);
            if (!empty($params)) {
                $countStmt->bind_param($types, ...$params);
            }
            $countStmt->execute();
            $total = $countStmt->get_result()->fetch_assoc()['total'];
            $countStmt->close();

            // Get paginated data with fixed limit of 10
            $query = "SELECT * FROM purchase_requests $where ORDER BY pr_date DESC LIMIT 10 OFFSET ?";
            $stmt = $this->db->prepare($query);
            
            if (!empty($params)) {
                $params[] = $offset;
                $types .= 'i';
                $stmt->bind_param($types, ...$params);
            } else {
                $stmt->bind_param('i', $offset);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return [
                'data' => $data,
                'total' => $total,
                'page' => $page,
                'limit' => 10 // Always return 10 as the limit
            ];
        } catch (Exception $e) {
            if (isset($stmt)) $stmt->close();
            throw $e;
        }
    }

    public function delete($id) {
        try {
            // First get the image path to delete the file
            $stmt = $this->db->prepare("SELECT image_path FROM purchase_requests WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            // Delete the record
            $stmt = $this->db->prepare("DELETE FROM purchase_requests WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to delete purchase request: ' . $stmt->error);
            }
            
            // Delete the image file if exists
            if ($row && $row['image_path']) {
                $imagePath = __DIR__ . '/../' . $row['image_path'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            $stmt->close();
            return true;
        } catch (Exception $e) {
            if (isset($stmt)) $stmt->close();
            throw $e;
        }
    }

    public function create($data) {
        try {
            $imagePath = null;
            
            // Handle image upload if present
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['image'];
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $maxSize = 10 * 1024 * 1024; // 10MB

                // Validate file type
                if (!in_array($file['type'], $allowedTypes)) {
                    throw new Exception('Invalid file type. Only JPG, PNG, and GIF are allowed.');
                }

                // Validate file size
                if ($file['size'] > $maxSize) {
                    throw new Exception('File size exceeds 10MB limit.');
                }

                // Generate unique filename
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $extension;
                $imagePath = 'uploads/' . $filename;

                // Move uploaded file
                if (!move_uploaded_file($file['tmp_name'], $this->uploadDir . $filename)) {
                    throw new Exception('Failed to move uploaded file.');
                }
            }

            // Prepare and execute the insert query
            $stmt = $this->db->prepare("
                INSERT INTO purchase_requests 
                (pr_number, pr_date, requested_by, item_description, estimated_cost, image_path) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "ssssds",
                $data['pr_number'],
                $data['pr_date'],
                $data['requested_by'],
                $data['item_description'],
                $data['estimated_cost'],
                $imagePath
            );

            if (!$stmt->execute()) {
                // If insert fails, delete the uploaded image if it exists
                if ($imagePath && file_exists($this->uploadDir . basename($imagePath))) {
                    unlink($this->uploadDir . basename($imagePath));
                }
                throw new Exception('Failed to create purchase request: ' . $stmt->error);
            }

            $stmt->close();
            return true;
        } catch (Exception $e) {
            if (isset($stmt)) $stmt->close();
            throw $e;
        }
    }

    public function getById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM purchase_requests WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            $stmt->close();
            return $data;
        } catch (Exception $e) {
            if (isset($stmt)) $stmt->close();
            throw $e;
        }
    }

    public function update($data) {
        try {
            $imagePath = null;
            
            // Handle image upload if present
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['image'];
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $maxSize = 10 * 1024 * 1024; // 10MB

                // Validate file type
                if (!in_array($file['type'], $allowedTypes)) {
                    throw new Exception('Invalid file type. Only JPG, PNG, and GIF are allowed.');
                }

                // Validate file size
                if ($file['size'] > $maxSize) {
                    throw new Exception('File size exceeds 10MB limit.');
                }

                // Generate unique filename
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $extension;
                $imagePath = 'uploads/' . $filename;

                // Move uploaded file
                if (!move_uploaded_file($file['tmp_name'], $this->uploadDir . $filename)) {
                    throw new Exception('Failed to move uploaded file.');
                }

                // Delete old image if exists
                $stmt = $this->db->prepare("SELECT image_path FROM purchase_requests WHERE id = ?");
                $stmt->bind_param("i", $data['id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $oldImage = $result->fetch_assoc()['image_path'];
                $stmt->close();

                if ($oldImage && file_exists(__DIR__ . '/../' . $oldImage)) {
                    unlink(__DIR__ . '/../' . $oldImage);
                }
            }

            // Prepare and execute the update query
            if ($imagePath) {
                $stmt = $this->db->prepare("
                    UPDATE purchase_requests 
                    SET pr_number = ?, pr_date = ?, requested_by = ?, 
                        item_description = ?, estimated_cost = ?, image_path = ?
                    WHERE id = ?
                ");
                $stmt->bind_param(
                    "ssssdsi",
                    $data['pr_number'],
                    $data['pr_date'],
                    $data['requested_by'],
                    $data['item_description'],
                    $data['estimated_cost'],
                    $imagePath,
                    $data['id']
                );
            } else {
                $stmt = $this->db->prepare("
                    UPDATE purchase_requests 
                    SET pr_number = ?, pr_date = ?, requested_by = ?, 
                        item_description = ?, estimated_cost = ?
                    WHERE id = ?
                ");
                $stmt->bind_param(
                    "ssssdi",
                    $data['pr_number'],
                    $data['pr_date'],
                    $data['requested_by'],
                    $data['item_description'],
                    $data['estimated_cost'],
                    $data['id']
                );
            }

            if (!$stmt->execute()) {
                // If update fails, delete the uploaded image if it exists
                if ($imagePath && file_exists($this->uploadDir . basename($imagePath))) {
                    unlink($this->uploadDir . basename($imagePath));
                }
                throw new Exception('Failed to update purchase request: ' . $stmt->error);
            }

            $stmt->close();
            return true;
        } catch (Exception $e) {
            if (isset($stmt)) $stmt->close();
            throw $e;
        }
    }

    public function __destruct() {
        if ($this->db) {
            $this->db->close();
        }
    }
}

