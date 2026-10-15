<?php
class Sale {
    private $db;
    private $error = '';

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

    // Mensaje de error entendible para mostrar al usuario (vacio si no hubo error)
    public function getError() {
        return $this->error;
    }

    // Guarda la venta completa (encabezado + detalle + rebaja de stock) en UNA transaccion:
    // o se guarda todo, o no se guarda nada. Devuelve el OrderID o false.
    public function createOrder($customerId, $cart) {
        $this->error = '';

        if (empty($customerId) || empty($cart)) {
            $this->error = 'Debe seleccionar un cliente y al menos un producto.';
            return false;
        }

        try {
            $this->db->beginTransaction();

            // Cliente: orders exige ShipName/ShipAddress/ShipCity/ShipCountry (NOT NULL),
            // se copian desde los datos del cliente.
            $this->db->query('SELECT CompanyName, Address, City, Region, PostalCode, Country
                              FROM customers WHERE CustomerID = :id');
            $this->db->bind(':id', $customerId);
            $customer = $this->db->singleRow();
            if (!$customer) {
                throw new RuntimeException('El cliente seleccionado no existe.');
            }

            // orders.EmployeeID y orders.ShipVia son obligatorios y llaves foraneas:
            // en la venta de carrito se usa el primer empleado y el primer transportista.
            $this->db->query('SELECT MIN(EmployeeID) AS id FROM employees');
            $employeeId = (int)($this->db->singleRow()->id ?? 0);
            $this->db->query('SELECT MIN(ShipperID) AS id FROM shippers');
            $shipperId = (int)($this->db->singleRow()->id ?? 0);
            if (!$employeeId || !$shipperId) {
                throw new RuntimeException('Faltan empleados o transportistas en la base de datos.');
            }

            // Se vuelve a leer cada producto (con bloqueo) para usar el precio y el stock reales,
            // no los que se guardaron en la sesion cuando se agrego al carrito.
            $lines = [];
            $total = 0.0;
            foreach ($cart as $item) {
                $productId = (int)($item['product']->ProductID ?? 0);
                $quantity  = (int)($item['quantity'] ?? 0);
                if ($productId <= 0 || $quantity <= 0) {
                    continue;
                }

                $this->db->query('SELECT ProductID, ProductName, UnitPrice, UnitsInStock
                                  FROM products WHERE ProductID = :id FOR UPDATE');
                $this->db->bind(':id', $productId);
                $product = $this->db->singleRow();

                if (!$product) {
                    throw new RuntimeException('Uno de los productos del carrito ya no existe.');
                }
                if ((int)$product->UnitsInStock < $quantity) {
                    throw new RuntimeException('Stock insuficiente para "' . $product->ProductName
                        . '" (disponible: ' . (int)$product->UnitsInStock . ').');
                }

                $lines[] = ['product' => $product, 'quantity' => $quantity];
                $total  += (float)$product->UnitPrice * $quantity;
            }

            if (empty($lines)) {
                throw new RuntimeException('El carrito no tiene productos validos.');
            }

            // Encabezado. ShippedDate es NOT NULL en esta base: se usa la fecha de la orden.
            $now      = time();
            $orderDt  = date('Y-m-d H:i:s', $now);
            $requiredDt = date('Y-m-d H:i:s', strtotime('+7 days', $now));

            $this->db->query('INSERT INTO orders
                                (CustomerID, EmployeeID, OrderDate, RequiredDate, ShippedDate, ShipVia, Freight,
                                 ShipName, ShipAddress, ShipCity, ShipRegion, ShipPostalCode, ShipCountry)
                              VALUES
                                (:customer, :employee, :orderDate, :requiredDate, :shippedDate, :shipVia, :freight,
                                 :shipName, :shipAddress, :shipCity, :shipRegion, :shipPostal, :shipCountry)');
            $this->db->bind(':customer', $customerId);
            $this->db->bind(':employee', $employeeId);
            $this->db->bind(':orderDate', $orderDt);
            $this->db->bind(':requiredDate', $requiredDt);
            $this->db->bind(':shippedDate', $orderDt);
            $this->db->bind(':shipVia', $shipperId);
            $this->db->bind(':freight', number_format($total * 0.05, 2, '.', ''));
            $this->db->bind(':shipName', $customer->CompanyName);
            $this->db->bind(':shipAddress', $customer->Address);
            $this->db->bind(':shipCity', $customer->City);
            $this->db->bind(':shipRegion', $customer->Region);
            $this->db->bind(':shipPostal', $customer->PostalCode);
            $this->db->bind(':shipCountry', $customer->Country);
            $this->db->execute();

            $orderId = (int)$this->db->lastInsertId();

            foreach ($lines as $line) {
                // la tabla se llama order_details (con guion bajo)
                $this->db->query('INSERT INTO order_details (OrderID, ProductID, UnitPrice, Quantity, Discount)
                                  VALUES (:orderId, :productId, :price, :quantity, 0)');
                $this->db->bind(':orderId', $orderId);
                $this->db->bind(':productId', (int)$line['product']->ProductID);
                $this->db->bind(':price', $line['product']->UnitPrice);
                $this->db->bind(':quantity', $line['quantity']);
                $this->db->execute();

                $this->db->query('UPDATE products SET UnitsInStock = UnitsInStock - :qty WHERE ProductID = :id');
                $this->db->bind(':qty', $line['quantity']);
                $this->db->bind(':id', (int)$line['product']->ProductID);
                $this->db->execute();
            }

            $this->db->commit();
            return $orderId;

        } catch (PDOException $e) {
            // PDOException hereda de RuntimeException, por eso se captura primero
            $this->db->rollBack();
            error_log('Sale::createOrder -> ' . $e->getMessage());
            $this->error = 'No se pudo completar la venta. Verifique la conexión a la base de datos y la estructura de las tablas.';
            return false;
        } catch (RuntimeException $e) {
            $this->db->rollBack();
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function getCustomerInvoicesByDateRange($customerId, $startDate, $endDate) {
        $this->error = '';

        try {
            $this->db->query('SELECT
                                o.OrderID, o.OrderDate, o.RequiredDate, o.ShippedDate,
                                c.CustomerID, c.CompanyName, c.ContactName, c.ContactTitle,
                                c.City, c.Country, c.PostalCode,
                                e.FirstName, e.LastName,
                                od.ProductID, p.ProductName, od.Quantity, od.UnitPrice, od.Discount,
                                ROUND(od.UnitPrice * od.Quantity * (1 - od.Discount), 2) AS LineTotal
                              FROM orders o
                              INNER JOIN customers c ON c.CustomerID = o.CustomerID
                              LEFT JOIN employees e ON e.EmployeeID = o.EmployeeID
                              INNER JOIN order_details od ON od.OrderID = o.OrderID
                              LEFT JOIN products p ON p.ProductID = od.ProductID
                              WHERE o.CustomerID = :customer_id
                                AND o.OrderDate BETWEEN :start_date AND :end_date
                              ORDER BY o.OrderDate ASC, o.OrderID ASC, od.ProductID ASC');
            $this->db->bind(':customer_id', $customerId);
            $this->db->bind(':start_date', $startDate . ' 00:00:00');
            $this->db->bind(':end_date', $endDate . ' 23:59:59');

            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log('Sale::getCustomerInvoicesByDateRange -> ' . $e->getMessage());
            $this->error = 'No se pudieron consultar las facturas del cliente.';
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