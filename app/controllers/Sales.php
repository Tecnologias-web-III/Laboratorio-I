<?php
class Sales extends Controller {
    private $saleModel;

    public function __construct() {
        if (!isLoggedIn()) {
            header('Location: ' . urlRoot . '/users/login');
            exit();
        }

        $this->saleModel = $this->model('Sale');
    }

    public function index() {
        $data = [
            'customers' => $this->saleModel->getCustomers(),
            'products' => $this->saleModel->getProducts(),
            'cart' => $_SESSION['sale_cart'] ?? [],
            'selected_customer' => $_SESSION['sale_customer'] ?? '',
            'error' => $_SESSION['sale_error'] ?? '',
            'success' => $_SESSION['sale_success'] ?? ''
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

        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
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
                'product' => $product,
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
        $cart = $_SESSION['sale_cart'] ?? [];

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

        $_SESSION['sale_error'] = 'No se pudo completar la venta. Verifique la conexión a la base de datos y la estructura de las tablas.';
        header('Location: ' . urlRoot . '/sales/index');
        exit();
    }

}
?>
