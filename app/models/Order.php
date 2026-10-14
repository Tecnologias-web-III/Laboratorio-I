<?php
class Order {
    private $db;
    private $error = '';

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

    // Mensaje de error entendible para mostrar al usuario (vacio si no hubo error)
    public function getError() {
        return $this->error;
    }

    // Venta asistida completa (encabezado + detalle + rebaja de stock) en UNA transaccion.
    // Devuelve true si se guardo todo, false si no (y deja el motivo en getError()).
    public function createAssistedSale($data) {
        $this->error = '';

        try {
            $this->db->beginTransaction();

            // Cliente: orders exige ShipName/ShipAddress/ShipCity/ShipCountry (NOT NULL)
            $this->db->query('SELECT CompanyName, Address, City, Region, PostalCode, Country
                              FROM customers WHERE CustomerID = :id');
            $this->db->bind(':id', $data['customer_id']);
            $customer = $this->db->singleRow();
            if (!$customer) {
                throw new RuntimeException('El cliente seleccionado no existe.');
            }

            // Producto con bloqueo: el precio y el stock salen de la base, no del formulario
            $this->db->query('SELECT ProductID, ProductName, UnitPrice, UnitsInStock, Discontinued
                              FROM products WHERE ProductID = :id FOR UPDATE');
            $this->db->bind(':id', (int)$data['product_id']);
            $product = $this->db->singleRow();
            if (!$product) {
                throw new RuntimeException('El producto seleccionado no existe.');
            }
            if ((int)$product->Discontinued !== 0) {
                throw new RuntimeException('El producto "' . $product->ProductName . '" está descontinuado.');
            }

            $quantity = (int)$data['quantity'];
            if ((int)$product->UnitsInStock < $quantity) {
                throw new RuntimeException('Stock insuficiente para "' . $product->ProductName
                    . '" (disponible: ' . (int)$product->UnitsInStock . ').');
            }

            // Encabezado. ShippedDate es NOT NULL en esta base: se usa la fecha de la orden.
            $now        = time();
            $orderDt    = date('Y-m-d H:i:s', $now);
            $requiredDt = date('Y-m-d H:i:s', strtotime('+7 days', $now));

            $this->db->query('INSERT INTO orders
                                (CustomerID, EmployeeID, OrderDate, RequiredDate, ShippedDate, ShipVia, Freight,
                                 ShipName, ShipAddress, ShipCity, ShipRegion, ShipPostalCode, ShipCountry)
                              VALUES
                                (:customer, :employee, :orderDate, :requiredDate, :shippedDate, :shipVia, :freight,
                                 :shipName, :shipAddress, :shipCity, :shipRegion, :shipPostal, :shipCountry)');
            $this->db->bind(':customer', $data['customer_id']);
            $this->db->bind(':employee', (int)$data['employee_id']);
            $this->db->bind(':orderDate', $orderDt);
            $this->db->bind(':requiredDate', $requiredDt);
            $this->db->bind(':shippedDate', $orderDt);
            $this->db->bind(':shipVia', (int)$data['shipper_id']);
            $this->db->bind(':freight', number_format((float)$data['freight'], 2, '.', ''));
            $this->db->bind(':shipName', $customer->CompanyName);
            $this->db->bind(':shipAddress', $customer->Address);
            $this->db->bind(':shipCity', $customer->City);
            $this->db->bind(':shipRegion', $customer->Region);
            $this->db->bind(':shipPostal', $customer->PostalCode);
            $this->db->bind(':shipCountry', $customer->Country);
            $this->db->execute();

            // OJO: antes se llamaba singleRow() con un SELECT, pero singleRow() NO recibe
            // parametros: volvia a ejecutar el INSERT y reventaba con error 500.
            $orderId = (int)$this->db->lastInsertId();

            // Detalle. El formulario captura el descuento en %, Northwind lo guarda como fraccion (10% -> 0.10)
            $this->db->query('INSERT INTO order_details (OrderID, ProductID, UnitPrice, Quantity, Discount)
                              VALUES (:order_id, :prod_id, :price, :qty, :disc)');
            $this->db->bind(':order_id', $orderId);
            $this->db->bind(':prod_id', (int)$product->ProductID);
            $this->db->bind(':price', $product->UnitPrice);
            $this->db->bind(':qty', $quantity);
            $this->db->bind(':disc', round(((float)$data['discount']) / 100, 2));
            $this->db->execute();

            // Rebajar inventario
            $this->db->query('UPDATE products SET UnitsInStock = UnitsInStock - :qty WHERE ProductID = :id');
            $this->db->bind(':qty', $quantity);
            $this->db->bind(':id', (int)$product->ProductID);
            $this->db->execute();

            $this->db->commit();
            return true;

        } catch (PDOException $e) {
            // PDOException hereda de RuntimeException, por eso se captura primero
            $this->db->rollBack();
            error_log('Order::createAssistedSale -> ' . $e->getMessage());
            $this->error = 'Ocurrió un error al procesar la venta en la base de datos.';
            return false;
        } catch (RuntimeException $e) {
            $this->db->rollBack();
            $this->error = $e->getMessage();
            return false;
        }
    }
}
?>