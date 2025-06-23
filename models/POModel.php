<?php
require_once 'Database.php';

class POModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function checkPrNumberExists($prNumber) {
        $query = "SELECT COUNT(*) as count FROM purchase_requests WHERE pr_number = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $prNumber);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }

    public function createPurchaseOrder($prNumber, $poNumber, $poDate, $mark, $imagePath, $suppliers) {
        try {
            $this->db->beginTransaction();

            // Insert into purchase_orders
            $query = "INSERT INTO purchase_orders (pr_number, po_number, po_date, mark, image_path) 
                     VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("sssss", $prNumber, $poNumber, $poDate, $mark, $imagePath);
            $stmt->execute();
            $poId = $this->db->insert_id;

            // Insert purchase order items
            $query = "INSERT INTO purchase_order_items (po_id, supplier_name, item_description, amount) 
                     VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);

            foreach ($suppliers as $supplier) {
                foreach ($supplier['items'] as $item) {
                    $stmt->bind_param("issd", $poId, $supplier['name'], $item['item'], $item['amount']);
                    $stmt->execute();
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function getPurchaseOrders() {
        $query = "SELECT po.*, GROUP_CONCAT(DISTINCT poi.supplier_name) as suppliers 
                 FROM purchase_orders po 
                 LEFT JOIN purchase_order_items poi ON po.id = poi.po_id 
                 GROUP BY po.id 
                 ORDER BY po.created_at DESC";
        $result = $this->db->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getPurchaseOrderById($id) {
        $query = "SELECT po.*, poi.supplier_name, poi.item_description, poi.amount 
                 FROM purchase_orders po 
                 LEFT JOIN purchase_order_items poi ON po.id = poi.po_id 
                 WHERE po.id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function updatePurchaseOrder($id, $poNumber, $poDate, $mark, $imagePath, $suppliers) {
        try {
            $this->db->beginTransaction();

            // Update purchase_orders
            $query = "UPDATE purchase_orders 
                     SET po_number = ?, po_date = ?, mark = ?, image_path = ? 
                     WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ssssi", $poNumber, $poDate, $mark, $imagePath, $id);
            $stmt->execute();

            // Delete existing items
            $query = "DELETE FROM purchase_order_items WHERE po_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();

            // Insert new items
            $query = "INSERT INTO purchase_order_items (po_id, supplier_name, item_description, amount) 
                     VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);

            foreach ($suppliers as $supplier) {
                foreach ($supplier['items'] as $item) {
                    $stmt->bind_param("issd", $id, $supplier['name'], $item['item'], $item['amount']);
                    $stmt->execute();
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function deletePurchaseOrder($id) {
        $query = "DELETE FROM purchase_orders WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>
