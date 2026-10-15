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

    public function reporteFacturas() {
        $data = [
            'customers' => $this->saleModel->getCustomers(),
            'selected_customer' => '',
            'start_date' => '',
            'end_date' => '',
            'error' => ''
        ];

        $this->view('sales/reporte_facturas', $data);
    }

    public function pdfFacturas() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . urlRoot . '/sales/reporteFacturas');
            exit();
        }

        $customerId = $_POST['customer_id'] ?? '';
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';
        $data = [
            'customers' => $this->saleModel->getCustomers(),
            'selected_customer' => is_string($customerId) ? trim($customerId) : '',
            'start_date' => is_string($startDate) ? trim($startDate) : '',
            'end_date' => is_string($endDate) ? trim($endDate) : '',
            'error' => ''
        ];

        $customerExists = false;
        foreach ($data['customers'] as $customer) {
            if ($customer->CustomerID === $data['selected_customer']) {
                $customerExists = true;
                break;
            }
        }

        if (!$customerExists) {
            $data['error'] = 'Seleccione un cliente válido.';
        } elseif (!$this->isValidReportDate($data['start_date']) || !$this->isValidReportDate($data['end_date'])) {
            $data['error'] = 'Ingrese fechas válidas para el rango del reporte.';
        } elseif ($data['start_date'] > $data['end_date']) {
            $data['error'] = 'La fecha inicial no puede ser posterior a la fecha final.';
        }

        if ($data['error'] !== '') {
            $this->view('sales/reporte_facturas', $data);
            return;
        }

        $rows = $this->saleModel->getCustomerInvoicesByDateRange(
            $data['selected_customer'],
            $data['start_date'],
            $data['end_date']
        );

        if ($rows === false) {
            $data['error'] = $this->saleModel->getError()
                ?: 'No se pudieron consultar las facturas. Intente nuevamente.';
            $this->view('sales/reporte_facturas', $data);
            return;
        }

        if (empty($rows)) {
            $data['error'] = 'No se encontraron facturas para ese cliente en el rango de fechas seleccionado.';
            $this->view('sales/reporte_facturas', $data);
            return;
        }

        require_once(appRoot . '/libraries/fpdf/fpdf.php');

        $invoices = [];
        foreach ($rows as $row) {
            $orderId = (int)$row->OrderID;
            if (!isset($invoices[$orderId])) {
                $invoices[$orderId] = [
                    'header' => $row,
                    'items' => [],
                    'subtotal' => 0.0,
                    'discount' => 0.0,
                    'total' => 0.0
                ];
            }

            $invoices[$orderId]['items'][] = $row;
            $lineSubtotal = round((float)$row->UnitPrice * (int)$row->Quantity, 2);
            $invoices[$orderId]['subtotal'] += $lineSubtotal;
            $invoices[$orderId]['total'] += (float)$row->LineTotal;
            $invoices[$orderId]['discount'] += $lineSubtotal - (float)$row->LineTotal;
        }

        $startDisplay = date('d/m/Y', strtotime($data['start_date']));
        $endDisplay = date('d/m/Y', strtotime($data['end_date']));
        $usuario = $_SESSION['usuario'] ?? 'Invitado';
        $logoPath = appRoot . '/../public/img/logoNorthWind.jpeg';

        $pdf = new class($usuario, $logoPath, $startDisplay, $endDisplay) extends FPDF {
            private $user;
            private $logoPath;
            private $startDate;
            private $endDate;

            public function __construct($user, $logoPath, $startDate, $endDate) {
                parent::__construct('P', 'mm', 'letter');
                $this->user = $user;
                $this->logoPath = $logoPath;
                $this->startDate = $startDate;
                $this->endDate = $endDate;
                $this->SetMargins(10, 10, 10);
                $this->SetAutoPageBreak(true, 20);
            }

            private function toPdf($value) {
                return mb_convert_encoding((string)$value, 'ISO-8859-1', 'UTF-8');
            }

            function Header() {
                if (file_exists($this->logoPath)) {
                    $this->Image($this->logoPath, 10, 8, 22, 22);
                }

                $this->SetFont('Arial', 'B', 16);
                $this->SetTextColor(15, 23, 42);
                $this->SetXY(36, 9);
                $this->Cell(0, 6, 'Northwind', 0, 1, 'L');

                $this->SetFont('Arial', '', 9);
                $this->SetTextColor(100, 116, 139);
                $this->SetX(36);
                $this->Cell(0, 4, 'Enterprise Solutions - Est. 1989', 0, 1, 'L');
                $this->SetX(36);
                $this->Cell(0, 4, 'Sample Sales Database', 0, 1, 'L');

                $this->SetY(34);
                $this->SetFont('Arial', 'B', 11);
                $this->SetTextColor(30, 41, 59);
                $this->Cell(0, 7, $this->toPdf('List of invoices by customer (date range)'), 0, 1, 'L');
                $this->SetFont('Arial', '', 8);
                $this->SetTextColor(71, 85, 105);
                $this->Cell(0, 5, $this->toPdf('Periodo consultado: ' . $this->startDate . ' - ' . $this->endDate), 0, 1, 'L');

                $this->SetDrawColor(203, 213, 225);
                $this->SetLineWidth(0.4);
                $this->Line(10, $this->GetY() + 1, 206, $this->GetY() + 1);
                $this->SetY($this->GetY() + 4);
            }

            function Footer() {
                $this->SetY(-17);
                $this->SetDrawColor(226, 232, 240);
                $this->Line(10, $this->GetY(), 206, $this->GetY());
                $this->SetY(-14);
                $this->SetFont('Arial', '', 8);
                $this->SetTextColor(100, 116, 139);
                $printedBy = $this->toPdf('Impreso por: ' . $this->user . ' | ' . date('d/m/Y H:i'));
                $this->Cell(140, 5, $printedBy, 0, 0, 'L');
                $this->Cell(56, 5, $this->toPdf('Página: ' . $this->PageNo() . '/{nb}'), 0, 0, 'R');
            }

            public function invoiceTableHeader() {
                $this->SetFont('Arial', 'B', 8);
                $this->SetFillColor(226, 232, 240);
                $this->SetTextColor(51, 65, 85);
                $this->SetDrawColor(148, 163, 184);

                $columns = [
                    [18, 'Código', 'C'],
                    [60, 'Nombre', 'L'],
                    [22, 'Cantidad', 'C'],
                    [31, 'Precio Uni.', 'R'],
                    [25, 'Descuento', 'R'],
                    [40, 'Total', 'R']
                ];

                foreach ($columns as $column) {
                    $this->Cell($column[0], 8, $this->toPdf($column[1]), 1, 0, $column[2], true);
                }
                $this->Ln();
                $this->SetFont('Arial', '', 8);
                $this->SetTextColor(30, 41, 59);
            }

            private function lineCount($width, $text) {
                $words = preg_split('/\s+/', trim($this->toPdf($text)));
                $lines = 1;
                $line = '';

                foreach ($words as $word) {
                    $candidate = $line === '' ? $word : $line . ' ' . $word;
                    if ($line !== '' && $this->GetStringWidth($candidate) > $width - 2) {
                        $lines++;
                        $line = $word;
                    } else {
                        $line = $candidate;
                    }
                }

                return $lines;
            }

            public function invoiceRow($item) {
                $productName = $item->ProductName ?: 'Producto ' . $item->ProductID;
                $lineHeight = 6;
                $rowHeight = max($lineHeight, $this->lineCount(60, $productName) * $lineHeight);

                if ($this->GetY() + $rowHeight > 258) {
                    $this->AddPage();
                    $this->invoiceTableHeader();
                }

                $y = $this->GetY();
                $this->Cell(18, $rowHeight, (string)$item->ProductID, 1, 0, 'C');
                $nameX = $this->GetX();
                $this->MultiCell(60, $lineHeight, $this->toPdf($productName), 1, 'L');
                $this->SetXY($nameX + 60, $y);
                $this->Cell(22, $rowHeight, (string)$item->Quantity, 1, 0, 'C');
                $this->Cell(31, $rowHeight, '$' . number_format((float)$item->UnitPrice, 2), 1, 0, 'R');
                $this->Cell(25, $rowHeight, number_format((float)$item->Discount * 100, 2) . '%', 1, 0, 'R');
                $this->Cell(40, $rowHeight, '$' . number_format((float)$item->LineTotal, 2), 1, 1, 'R');
            }

            public function invoiceTotals($subtotal, $discount, $total) {
                $this->Ln(2);
                $this->SetFont('Arial', '', 8);
                $this->SetX(116);
                $this->Cell(53, 6, $this->toPdf('Subtotal'), 1, 0, 'R');
                $this->Cell(37, 6, '$' . number_format($subtotal, 2), 1, 1, 'R');
                $this->SetX(116);
                $this->Cell(53, 6, $this->toPdf('Descuento'), 1, 0, 'R');
                $this->Cell(37, 6, '$' . number_format($discount, 2), 1, 1, 'R');
                $this->SetFont('Arial', 'B', 9);
                $this->SetX(116);
                $this->Cell(53, 7, $this->toPdf('Total factura'), 1, 0, 'R');
                $this->Cell(37, 7, '$' . number_format($total, 2), 1, 1, 'R');
            }

            public function reportTotal($invoiceCount, $total) {
                if ($this->GetY() + 27 > 258) {
                    $this->AddPage();
                }

                $y = $this->GetY() + 7;
                $this->SetFillColor(241, 245, 249);
                $this->SetDrawColor(148, 163, 184);
                $this->Rect(10, $y, 196, 19, 'DF');
                $this->SetXY(13, $y + 2);
                $this->SetFont('Arial', '', 8);
                $this->SetTextColor(71, 85, 105);
                $this->Cell(190, 5, $this->toPdf('Facturas incluidas: ' . $invoiceCount), 0, 1, 'L');
                $this->SetX(13);
                $this->SetFont('Arial', 'B', 11);
                $this->SetTextColor(30, 41, 59);
                $this->Cell(140, 8, $this->toPdf('Total de Compras Acumuladas'), 0, 0, 'L');
                $this->Cell(50, 8, '$' . number_format($total, 2), 0, 1, 'R');
            }
        };

        $pdf->AliasNbPages();
        $grandTotal = 0.0;

        foreach ($invoices as $invoice) {
            $header = $invoice['header'];
            $pdf->AddPage();
            $pdf->SetY(49);

            $location = implode(', ', array_filter([
                $header->Country,
                $header->City,
                $header->PostalCode
            ]));
            $contact = trim(($header->ContactName ?? '') . ' - ' . ($header->ContactTitle ?? ''), ' -');

            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(25, 5, mb_convert_encoding('Cliente:', 'ISO-8859-1', 'UTF-8'), 0, 0);
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(171, 5, mb_convert_encoding($header->CompanyName, 'ISO-8859-1', 'UTF-8'), 0, 1);
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(25, 5, mb_convert_encoding('Contacto:', 'ISO-8859-1', 'UTF-8'), 0, 0);
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(171, 5, mb_convert_encoding($contact, 'ISO-8859-1', 'UTF-8'), 0, 1);
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(25, 5, mb_convert_encoding('Ubicación:', 'ISO-8859-1', 'UTF-8'), 0, 0);
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(171, 5, mb_convert_encoding($location, 'ISO-8859-1', 'UTF-8'), 0, 1);

            $bandY = $pdf->GetY() + 3;
            $pdf->SetFillColor(226, 232, 240);
            $pdf->SetDrawColor(203, 213, 225);
            $pdf->Rect(10, $bandY, 196, 18, 'DF');
            $pdf->SetXY(12, $bandY + 1);
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(91, 5, mb_convert_encoding('Factura #: ' . $header->OrderID, 'ISO-8859-1', 'UTF-8'), 0, 0);
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(91, 5, mb_convert_encoding('Fecha Facturación: ' . date('d/m/Y', strtotime($header->OrderDate)), 'ISO-8859-1', 'UTF-8'), 0, 1);
            $pdf->SetX(12);
            $employee = trim(($header->FirstName ?? '') . ' ' . ($header->LastName ?? ''));
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(91, 5, mb_convert_encoding('Empleado: ' . ($employee ?: 'No asignado'), 'ISO-8859-1', 'UTF-8'), 0, 0);
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(91, 5, mb_convert_encoding('Requerida: ' . date('d/m/Y', strtotime($header->RequiredDate)), 'ISO-8859-1', 'UTF-8'), 0, 1);
            $pdf->SetX(103);
            $pdf->Cell(91, 5, mb_convert_encoding('Despachada: ' . date('d/m/Y', strtotime($header->ShippedDate)), 'ISO-8859-1', 'UTF-8'), 0, 1);
            $pdf->SetY($bandY + 21);

            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(0, 6, mb_convert_encoding('Detalle de productos de esta factura:', 'ISO-8859-1', 'UTF-8'), 0, 1);
            $pdf->invoiceTableHeader();

            foreach ($invoice['items'] as $item) {
                $pdf->invoiceRow($item);
            }

            $pdf->invoiceTotals($invoice['subtotal'], $invoice['discount'], $invoice['total']);
            $grandTotal += $invoice['total'];
        }

        $pdf->reportTotal(count($invoices), $grandTotal);
        $pdf->Output('I', 'Facturas_por_cliente.pdf');
        exit();
    }

    private function isValidReportDate($date) {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }

        $parts = array_map('intval', explode('-', $date));
        return checkdate($parts[1], $parts[2], $parts[0]);
    }
}
?>