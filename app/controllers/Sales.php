<?php
class Sales extends Controller {
    private $orderModel;
    private $saleModel;

    public function __construct() {
        // Verificar que el usuario haya iniciado sesión
        if (!isLoggedIn()) {
            header('Location: ' . urlRoot . '/users/login');
            exit();
        }

        $this->orderModel = $this->model('Order');
        $this->saleModel  = $this->model('Sale');
    }

    public function index() {
        $data = [
            'customers'         => $this->saleModel->getCustomers(),
            'products'          => $this->saleModel->getProducts(),
            'cart'              => $_SESSION['sale_cart'] ?? [],
            'selected_customer' => $_SESSION['sale_customer'] ?? '',
            'error'             => $_SESSION['sale_error'] ?? '',
            'success'           => $_SESSION['sale_success'] ?? ''
        ];

        unset($_SESSION['sale_error']);
        unset($_SESSION['sale_success']);

        $this->view('sales/index', $data);
    }

    public function addToCart() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . urlRoot . '/sales/index');
            exit();
        }

        $productId  = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $quantity   = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
        $customerId = filter_input(INPUT_POST, 'customer_id', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (!$productId || !$quantity || $quantity <= 0) {
            $_SESSION['sale_error'] = 'Debe indicar un producto y una cantidad válida.';
            header('Location: ' . urlRoot . '/sales/index');
            exit();
        }

        $product = $this->saleModel->getProductById($productId);
        if (!$product) {
            $_SESSION['sale_error'] = 'El producto seleccionado no existe.';
            header('Location: ' . urlRoot . '/sales/index');
            exit();
        }

        // No permitir poner en el carrito mas unidades de las que hay en stock
        $inCart = (int)($_SESSION['sale_cart'][(int)$productId]['quantity'] ?? 0);
        if (($inCart + $quantity) > (int)$product->UnitsInStock) {
            $_SESSION['sale_error'] = 'Stock insuficiente para "' . $product->ProductName
                . '" (disponible: ' . (int)$product->UnitsInStock . ', en el carrito: ' . $inCart . ').';
            header('Location: ' . urlRoot . '/sales/index');
            exit();
        }

        if (!empty($customerId)) {
            $_SESSION['sale_customer'] = $customerId;
        }

        if (!isset($_SESSION['sale_cart'])) {
            $_SESSION['sale_cart'] = [];
        }

        $key = (int)$productId;
        if (isset($_SESSION['sale_cart'][$key])) {
            $_SESSION['sale_cart'][$key]['quantity'] += $quantity;
        } else {
            $_SESSION['sale_cart'][$key] = [
                'product'  => $product,
                'quantity' => $quantity
            ];
        }

        header('Location: ' . urlRoot . '/sales/index');
        exit();
    }

    public function clearCart() {
        unset($_SESSION['sale_cart']);
        unset($_SESSION['sale_customer']);
        header('Location: ' . urlRoot . '/sales/index');
        exit();
    }

    public function checkout() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . urlRoot . '/sales/index');
            exit();
        }

        $customerId = filter_input(INPUT_POST, 'customer_id', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $cart       = $_SESSION['sale_cart'] ?? [];

        if (empty($customerId) || empty($cart)) {
            $_SESSION['sale_error'] = 'Debe seleccionar un cliente y al menos un producto antes de confirmar la venta.';
            header('Location: ' . urlRoot . '/sales/index');
            exit();
        }

        $orderId = $this->saleModel->createOrder($customerId, $cart);

        if ($orderId) {
            unset($_SESSION['sale_cart']);
            unset($_SESSION['sale_customer']);
            $_SESSION['sale_success'] = 'La venta se registró correctamente.';
            header('Location: ' . urlRoot . '/sales/index');
            exit();
        }

        $_SESSION['sale_error'] = $this->saleModel->getError()
            ?: 'No se pudo completar la venta. Verifique la conexión a la base de datos y la estructura de las tablas.';
        header('Location: ' . urlRoot . '/sales/index');
        exit();
    }

    public function asistida() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'employee_id' => trim($_POST['cmbEmpleado'] ?? ''),
                'customer_id' => trim($_POST['cmbCliente'] ?? ''),
                'shipper_id'  => trim($_POST['cmbShipper'] ?? ''),
                'freight'     => !empty($_POST['txtFlete']) ? trim($_POST['txtFlete']) : 0.00,
                'product_id'  => trim($_POST['cmbProducto'] ?? ''),
                'unit_price'  => trim($_POST['txtPrecio'] ?? ''),
                'quantity'    => trim($_POST['txtCantidad'] ?? ''),
                'discount'    => !empty($_POST['txtDescuento']) ? trim($_POST['txtDescuento']) : 0,
                'err'         => ''
            ];

            // Validaciones de campos obligatorios y de formato
            $quantityOk = filter_var($data['quantity'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false;

            if (empty($data['employee_id']) || empty($data['customer_id']) || empty($data['product_id'])
                || empty($data['shipper_id']) || !$quantityOk) {
                $data['err'] = 'Por favor complete todos los campos obligatorios con valores válidos.';
            } elseif (!is_numeric($data['freight']) || $data['freight'] < 0) {
                $data['err'] = 'El costo de envío debe ser un número mayor o igual a 0.';
            } elseif (!is_numeric($data['discount']) || $data['discount'] < 0 || $data['discount'] > 100) {
                $data['err'] = 'El descuento debe estar entre 0 y 100.';
            }

            if (empty($data['err'])) {
                // Ejecutar la venta asistida mediante transacción en el modelo para mayor seguridad
                if ($this->orderModel->createAssistedSale($data)) {
                    header('Location: ' . urlRoot . '/sales/asistida?success=1');
                    exit();
                } else {
                    $data['err'] = $this->orderModel->getError() ?: 'Ocurrió un error al procesar la venta en la base de datos.';
                }
            }

            // Si hay error, recargar catálogos y re-mostrar vista con los datos actuales
            $data['employees'] = $this->orderModel->getEmployees();
            $data['customers'] = $this->orderModel->getCustomers();
            $data['products']  = $this->orderModel->getProducts();
            $data['shippers']  = $this->orderModel->getShippers();
            
            $this->view('sales/asistida', $data);

        } else {
            // Carga inicial del formulario (GET)
            $employees = $this->orderModel->getEmployees();
            $customers = $this->orderModel->getCustomers();
            $products  = $this->orderModel->getProducts();
            $shippers  = $this->orderModel->getShippers();

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