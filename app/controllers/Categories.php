<?php
class Categories extends Controller {

    private $categoryModel;
    private $maxImageSize = 2097152;

    public function __construct() {
        // Redirigir a login si no está autenticado
        if (!isLoggedIn()) {
            header('Location: ' . urlRoot . '/users/login');
            exit();
        }
        $this->categoryModel = $this->model('Category');
    }

    // Muestra la lista principal de categorías
    public function index() {
        $categories = $this->categoryModel->getCategories();
        $data = [
            'title' => 'Gestión de Categorías',
            'categories' => $categories
        ];
        $this->view('categories/index', $data);
    }

    // Vista y procesamiento para crear categoría
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'name' => trim($_POST['txtNombre']),
                'description' => trim($_POST['txtDescrip']),
                'imagen' => null,
                'mime' => null,
                'name_err' => '',
                'image_err' => ''
            ];

            if (empty($data['name'])) {
                $data['name_err'] = 'Por favor ingrese el nombre de la categoría';
            }

            // Se obtiene y valida la imagen seleccionada
            if (isset($_FILES['txtArchi'])) {
                $imageData = $this->validateImage($_FILES['txtArchi']);
                $data['imagen'] = $imageData['imagen'];
                $data['mime'] = $imageData['mime'];
                $data['image_err'] = $imageData['error'];
            }

            if (empty($data['name_err']) && empty($data['image_err'])) {
                if ($this->categoryModel->addCategory($data)) {
                    header('Location: ' . urlRoot . '/categories');
                    exit();
                } else {
                    die('Error al guardar la categoría.');
                }
            } else {
                $this->view('categories/inscateg', $data);
            }
        } else {
            $data = [
                'name' => '',
                'description' => '',
                'name_err' => '',
                'image_err' => ''
            ];
            $this->view('categories/inscateg', $data);
        }
    }

    // Vista y procesamiento para modificar categoría
    public function edit($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'id' => $id,
                'name' => trim($_POST['txtNombre']),
                'description' => trim($_POST['txtDescrip']),
                'imagen' => null,
                'mime' => null,
                'name_err' => '',
                'image_err' => ''
            ];

            if (empty($data['name'])) {
                $data['name_err'] = 'El nombre no puede estar vacío';
            }

            // Se cambia la imagen solamente si el usuario selecciona otra
            if (isset($_FILES['txtArchi'])) {
                $imageData = $this->validateImage($_FILES['txtArchi']);
                $data['imagen'] = $imageData['imagen'];
                $data['mime'] = $imageData['mime'];
                $data['image_err'] = $imageData['error'];
            }

            if (empty($data['name_err']) && empty($data['image_err'])) {
                if ($this->categoryModel->updateCategory($data)) {
                    header('Location: ' . urlRoot . '/categories');
                    exit();
                } else {
                    die('Error al actualizar.');
                }
            } else {
                $this->view('categories/modcateg', $data);
            }
        } else {
            $category = $this->categoryModel->getCategoryById($id);
            $data = [
                'id' => $category->CategoryID,
                'name' => $category->CategoryName,
                'description' => $category->Description,
                'has_image' => !empty($category->Imagen),
                'name_err' => '',
                'image_err' => ''
            ];
            $this->view('categories/modcateg', $data);
        }
    }

    // Método para servir la imagen BLOB directamente con sus cabeceras HTTP
    public function image($id) {
        $category = $this->categoryModel->getCategoryById($id);
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if ($category && !empty($category->Imagen) && in_array($category->Mime, $allowedTypes, true)) {
            header('Content-Type: ' . $category->Mime);
            header('X-Content-Type-Options: nosniff');
            echo $category->Imagen;
            exit();
        } else {
            // Imagen por defecto si no existe BLOB almacenado
            header('Content-Type: image/svg+xml');
            echo '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" fill="gray" class="bi bi-image" viewBox="0 0 16 16"><path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/><path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2h-12zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1h12z"/></svg>';
            exit();
        }
    }

    // Se verifica que el archivo seleccionado sea una imagen valida
    private function validateImage($file) {
        $result = [
            'imagen' => null,
            'mime' => null,
            'error' => ''
        ];

        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return $result;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $result['error'] = 'No se pudo cargar la imagen. Intente nuevamente.';
            return $result;
        }

        if ($file['size'] > $this->maxImageSize) {
            $result['error'] = 'La imagen no debe superar los 2 MB.';
            return $result;
        }

        $imageInfo = @getimagesize($file['tmp_name']);
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if ($imageInfo === false || !in_array($imageInfo['mime'], $allowedTypes, true)) {
            $result['error'] = 'Seleccione una imagen valida en formato JPG, PNG, GIF o WEBP.';
            return $result;
        }

        $imageContent = file_get_contents($file['tmp_name']);
        if ($imageContent === false) {
            $result['error'] = 'No se pudo leer la imagen seleccionada.';
            return $result;
        }

        $result['imagen'] = $imageContent;
        $result['mime'] = $imageInfo['mime'];
        return $result;
    }

    // Eliminar categoría
    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->categoryModel->deleteCategory($id)) {
                header('Location: ' . urlRoot . '/categories');
                exit();
            }
        }
    }

    // Imprime la lista de categorías en formato PDF
    public function pdfCategorias() {
        require_once(appRoot . '/libraries/fpdf/fpdf.php');

        // Obtiene los datos desde la base de datos vía Modelo (PDO)
        $categorias = $this->categoryModel->getCategories();

        // Obtiene los datos del usuario desde la variable de sesión (o fallback a 'Invitado')
        $usuario = $_SESSION['usuario'] ?? 'Invitado';

        // Crea instancia personalizada de FPDF para manejar encabezado y pie de página
        $pdf = new class($usuario) extends FPDF {
            private $user;

            public function __construct($user) {
                parent::__construct('P', 'mm', 'letter');
                $this->user = $user;
            }

            // Encabezado de la página
            function Header() {
                // Logotipo (ubicado en mvc2/public/img/logoNorthWind.png)
                // Se escala automáticamente de 1024x1024 a 22x22 mm
                $imgPath = appRoot . '/../public/img/logoNorthWind.jpeg';
                if (file_exists($imgPath)) {
                    $this->Image($imgPath, 10, 8, 22, 22);
                }

                // Título principal / Marca
                $this->SetFont('Arial', 'B', 16);
                $this->SetTextColor(15, 23, 42); // Slate 900
                $this->SetXY(35, 10);
                $this->Cell(0, 6, 'Northwind', 0, 1, 'L');

                // Subtítulos institucionales
                $this->SetFont('Arial', '', 9);
                $this->SetTextColor(100, 116, 139); // Slate 500
                $this->SetX(35);
                $this->Cell(0, 4, 'Enterprise Solutions - Est. 1989', 0, 1, 'L');
                $this->SetX(35);
                $this->Cell(0, 4, 'Sample Sales Database', 0, 1, 'L');

                $this->Ln(6);

                // Título de la sección del reporte
                $this->SetFont('Arial', 'B', 12);
                $this->SetTextColor(30, 41, 59); // Slate 800
                $this->Cell(0, 8, 'List of Available Categories', 0, 1, 'L');

                // Línea divisora
                $this->SetDrawColor(226, 232, 240); // Slate 200
                $this->SetLineWidth(0.4);
                $this->Line(10, $this->GetY(), 200, $this->GetY());
                $this->Ln(4);

                // Encabezados de la tabla
                $this->SetFont('Arial', 'B', 9);
                $this->SetFillColor(241, 245, 249); // Slate 100
                $this->SetTextColor(51, 65, 85); // Slate 700
                $this->SetDrawColor(203, 213, 225); // Slate 300

                $this->Cell(15, 8, 'ID', 1, 0, 'C', true);
                $this->Cell(45, 8, 'Name', 1, 0, 'L', true);
                $this->Cell(130, 8, 'Description', 1, 1, 'L', true);
            }

            // Pie de página
            function Footer() {
                $this->SetY(-18);
                $this->SetFont('Arial', '', 8);
                $this->SetTextColor(100, 116, 139); // Slate 500

                // Línea superior del pie de página
                $this->SetDrawColor(226, 232, 240);
                $this->Line(10, $this->GetY(), 200, $this->GetY());
                $this->Ln(2);

                // Información de impresión
                $fechaImpresion = date('Y/M/d H:i');
                $this->Cell(0, 4, mb_convert_encoding("Printing by: " . $this->user, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
                $this->Cell(0, 4, mb_convert_encoding("Printing date: " . strtolower($fechaImpresion), 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');

                // Número de página
                $this->SetY(-14);
                $this->Cell(0, 4, mb_convert_encoding('Page: ' . $this->PageNo() . '/{nb}', 'ISO-8859-1', 'UTF-8'), 0, 0, 'R');
            }
        };

        // Configura el documento PDF
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(30, 41, 59);

        // Imprime los datos de las categorías
        foreach ($categorias as $cat) {
            $pdf->Cell(15, 7, $cat->CategoryID, 1, 0, 'C');
            $pdf->Cell(45, 7, mb_convert_encoding($cat->CategoryName, 'ISO-8859-1', 'UTF-8'), 1, 0, 'L');
            $pdf->Cell(130, 7, mb_convert_encoding($cat->Description, 'ISO-8859-1', 'UTF-8'), 1, 1, 'L');
        }

        // Genera salida al navegador
        $pdf->Output('I', 'rptCategories.pdf');
        exit();
    }
}
?>