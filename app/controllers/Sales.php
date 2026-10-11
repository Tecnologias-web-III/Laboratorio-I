<?php
class Sales extends Controller {
    private $orderModel;

    public function __construct() {
        // Verificar que el usuario haya iniciado sesión
        if (!isLoggedIn()) {
            header('Location: ' . urlRoot . '/users/login');
            exit();
        }
        $this->orderModel = $this->model('Order');
    }

    public function asistida() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'employee_id' => trim($_POST['cmbEmpleado']),
                'customer_id' => trim($_POST['cmbCliente']),
                'shipper_id'  => trim($_POST['cmbShipper']),
                'freight'     => !empty($_POST['txtFlete']) ? trim($_POST['txtFlete']) : 0.00,
                'product_id'  => trim($_POST['cmbProducto']),
                'unit_price'  => trim($_POST['txtPrecio']),
                'quantity'    => trim($_POST['txtCantidad']),
                'discount'    => !empty($_POST['txtDescuento']) ? trim($_POST['txtDescuento']) : 0,
                'err'         => ''
            ];

            // Validaciones básicas de campos
            if (empty($data['employee_id']) || empty($data['customer_id']) || empty($data['product_id']) || empty($data['quantity'])) {
                $data['err'] = 'Por favor complete todos los campos obligatorios del formulario.';
            }

            if (empty($data['err'])) {
                // 1. Guardar encabezado de la orden
                $orderId = $this->orderModel->createOrder($data);
                
                if ($orderId) {
                    // 2. Guardar detalle del producto
                    if ($this->orderModel->createOrderDetail($orderId, $data)) {
                        header('Location: ' . urlRoot . '/sales/asistida?success=1');
                        exit();
                    }
                }
                $data['err'] = 'Ocurrió un error al procesar la venta en la base de datos.';
            }

            // Si hay error, recargar catálogos y re-mostrar vista
            $data['employees'] = $this->orderModel->getEmployees();
            $data['customers'] = $this->orderModel->getCustomers();
            $data['products']  = $this->orderModel->getProducts();
            $data['shippers']  = $this->orderModel->getShippers();
            $this->view('sales/asistida', $data);

        } else {
            // Datos locales costarricenses por defecto en caso de que la BD no responda
            $employees = $this->orderModel->getEmployees();
            if (empty($employees)) {
                $employees = [
                    (object)['EmployeeID' => 1, 'FirstName' => 'Kevin', 'LastName' => 'Alvarado'],
                    (object)['EmployeeID' => 2, 'FirstName' => 'Mariana', 'LastName' => 'Solano'],
                    (object)['EmployeeID' => 3, 'FirstName' => 'Bryan', 'LastName' => 'Brenes'],
                    (object)['EmployeeID' => 4, 'FirstName' => 'Yuliana', 'LastName' => 'Castro']
                ];
            }

            $customers = $this->orderModel->getCustomers();
            if (empty($customers)) {
                $customers = [
                    (object)['CustomerID' => 'ABAST', 'CompanyName' => 'Distribuidora El Abastecedor S.A.'],
                    (object)['CustomerID' => 'TROP',  'CompanyName' => 'Supermercado La Tropicana'],
                    (object)['CustomerID' => 'MARP',  'CompanyName' => 'Comercializadora El Mar de Puntarenas'],
                    (object)['CustomerID' => 'PULP',  'CompanyName' => 'Pulpería La Central (Orotina)']
                ];
            }

            $products = $this->orderModel->getProducts();
            if (empty($products)) {
                $products = [
                    (object)['ProductID' => 1, 'ProductName' => 'Café Tarrazú Molido 500g', 'UnitPrice' => 3500.00, 'UnitsInStock' => 45],
                    (object)['ProductID' => 2, 'ProductName' => 'Salsa Lizano 135ml', 'UnitPrice' => 1200.00, 'UnitsInStock' => 120],
                    (object)['ProductID' => 3, 'ProductName' => 'Queso Turrialba 1kg', 'UnitPrice' => 4800.00, 'UnitsInStock' => 25],
                    (object)['ProductID' => 4, 'ProductName' => 'Chicas Avena y Galletas', 'UnitPrice' => 850.00, 'UnitsInStock' => 80]
                ];
            }

            $shippers = $this->orderModel->getShippers();
            if (empty($shippers)) {
                $shippers = [
                    (object)['ShipperID' => 1, 'CompanyName' => 'Correos de Costa Rica'],
                    (object)['ShipperID' => 2, 'CompanyName' => 'Encomiendas Caribe Express'],
                    (object)['ShipperID' => 3, 'CompanyName' => 'Transportes Moín & Pacífico']
                ];
            }

            $data = [
                'employees' => $employees,
                'customers' => $customers,
                'products'  => $products,
                'shippers'  => $shippers,
                'err'       => ''
            ];
            $this->view('sales/asistida', $data);
        }
    }
}
?>