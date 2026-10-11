<?php
class Order {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Obtener lista de Empleados
    public function getEmployees() {
        $this->db->query('SELECT EmployeeID, FirstName, LastName FROM employees ORDER BY LastName ASC');
        return $this->db->resultSet();
    }

    // Obtener lista de Clientes
    public function getCustomers() {
        $this->db->query('SELECT CustomerID, CompanyName FROM customers ORDER BY CompanyName ASC');
        return $this->db->resultSet();
    }

    // Obtener lista de Productos disponibles
    public function getProducts() {
        $this->db->query('SELECT ProductID, ProductName, UnitPrice, UnitsInStock FROM products WHERE Discontinued = 0 AND UnitsInStock > 0 ORDER BY ProductName ASC');
        return $this->db->resultSet();
    }

    // Obtener lista de Transportistas (Shippers / Encomiendas)
    public function getShippers() {
        $this->db->query('SELECT ShipperID, CompanyName FROM shippers ORDER BY CompanyName ASC');
        return $this->db->resultSet();
    }

    // Registrar el Encabezado de la Venta Asistida
    public function createOrder($data) {
        $this->db->query('INSERT INTO orders (CustomerID, EmployeeID, ShipVia, Freight, TipoVenta) VALUES (:cust, :emp, :ship, :freight, "Asistida")');
        $this->db->bind(':cust', $data['customer_id']);
        $this->db->bind(':emp', $data['employee_id']);
        $this->db->bind(':ship', $data['shipper_id']);
        $this->db->bind(':freight', $data['freight']);

        if ($this->db->execute()) {
            // Obtiene el ID de la orden recién creada
            return $this->db->singleRow('SELECT LAST_INSERT_ID() as id')->id ?? null;
        }
        return false;
    }

    // Registrar el Detalle de la Venta
    public function createOrderDetail($orderId, $data) {
        $this->db->query('INSERT INTO order_details (OrderID, ProductID, UnitPrice, Quantity, Discount) VALUES (:order_id, :prod_id, :price, :qty, :disc)');
        $this->db->bind(':order_id', $orderId);
        $this->db->bind(':prod_id', $data['product_id']);
        $this->db->bind(':price', $data['unit_price']);
        $this->db->bind(':qty', $data['quantity']);
        $this->db->bind(':disc', $data['discount']);

        return $this->db->execute();
    }
}
?>