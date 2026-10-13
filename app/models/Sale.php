<?php
class Sale {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getCustomers() {
        try {
            $this->db->query('SELECT CustomerID, CompanyName, ContactName, City FROM customers ORDER BY CompanyName ASC');
            return $this->db->resultSet();
        } catch (Exception $e) {
            return [];
        }
    }

    public function getProducts() {
        try {
            $this->db->query('SELECT ProductID, ProductName, UnitPrice, UnitsInStock, CategoryID FROM products ORDER BY ProductName ASC');
            return $this->db->resultSet();
        } catch (Exception $e) {
            return [];
        }
    }

    public function getProductById($id) {
        if (empty($id)) {
            return false;
        }

        try {
            $this->db->query('SELECT ProductID, ProductName, UnitPrice, UnitsInStock, CategoryID FROM products WHERE ProductID = :id');
            $this->db->bind(':id', (int)$id);
            return $this->db->singleRow();
        } catch (Exception $e) {
            return false;
        }
    }

    public function createOrder($customerId, $cart) {
        if (empty($customerId) || empty($cart)) {
            return false;
        }

        $total = 0.0;
        foreach ($cart as $item) {
            $quantity = (int)($item['quantity'] ?? 0);
            $price = (float)($item['product']->UnitPrice ?? 0);
            if ($quantity > 0 && $price > 0) {
                $total += $price * $quantity;
            }
        }

        try {
            $orderDate = date('Y-m-d H:i:s');
            $this->db->query('INSERT INTO orders (CustomerID, OrderDate, RequiredDate, Freight) VALUES (:customer, :orderDate, DATE_ADD(:orderDate, INTERVAL 7 DAY), :freight)');
            $this->db->bind(':customer', $customerId);
            $this->db->bind(':orderDate', $orderDate);
            $this->db->bind(':freight', number_format((float)$total * 0.05, 2, '.', ''));

            if (!$this->db->execute()) {
                return false;
            }

            $orderId = $this->db->lastInsertId();

            foreach ($cart as $item) {
                $product = $item['product'] ?? null;
                $quantity = (int)($item['quantity'] ?? 0);
                if (!$product || $quantity <= 0) {
                    continue;
                }

                $this->db->query('INSERT INTO `order details` (OrderID, ProductID, UnitPrice, Quantity, Discount) VALUES (:orderId, :productId, :price, :quantity, 0)');
                $this->db->bind(':orderId', (int)$orderId);
                $this->db->bind(':productId', (int)($product->ProductID ?? 0));
                $this->db->bind(':price', (float)($product->UnitPrice ?? 0));
                $this->db->bind(':quantity', $quantity);

                if (!$this->db->execute()) {
                    return false;
                }
            }

            return $orderId;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getOrderSummary($orderId) {
        if (empty($orderId)) {
            return false;
        }

        try {
            $this->db->query('SELECT o.OrderID, o.CustomerID, o.OrderDate, o.Freight, c.CompanyName, c.ContactName FROM orders o LEFT JOIN customers c ON c.CustomerID = o.CustomerID WHERE o.OrderID = :id');
            $this->db->bind(':id', (int)$orderId);
            $order = $this->db->singleRow();

            if (!$order) {
                return false;
            }

            $this->db->query('SELECT od.OrderID, od.ProductID, od.Quantity, od.UnitPrice, p.ProductName FROM `order details` od LEFT JOIN products p ON p.ProductID = od.ProductID WHERE od.OrderID = :id ORDER BY od.ProductID ASC');
            $this->db->bind(':id', (int)$orderId);
            $items = $this->db->resultSet();

            $order->Items = $items ?: [];
            $order->Total = 0.0;
            foreach ($order->Items as $item) {
                $order->Total += ((float)$item->UnitPrice * (int)$item->Quantity);
            }

            return $order;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
