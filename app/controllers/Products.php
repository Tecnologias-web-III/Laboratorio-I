<?php
class Products extends Controller {

    private $productModel;
    private $categoryModel;

    public function __construct() {
       
        // Si no esta logueado lo mando al login
        if (!isLoggedIn()) {
            header('Location: ' . urlRoot . '/users/login');
            exit();
        }
        $this->productModel = $this->model('Product');
        $this->categoryModel = $this->model('Category');
    }
 
    // Lista general, o por categoria si viene el id en la url (/products/index/3)
    public function index($categoryId = null) {
        if ($categoryId) {
            $category = $this->categoryModel->getCategoryById($categoryId);

            // Si la categoria no existe vuelvo a la lista general
            if (!$category) {
                header('Location: ' . urlRoot . '/products');
                exit();
            }

            $products = $this->productModel->getProductsByCategory($categoryId);
            $title = 'Lista de productos por la categoría ' . $category->CategoryName;
        } else {
            $products = $this->productModel->getProducts();
            $title = 'Lista general de productos';
        }

        $data = [
            'title' => $title,
            'products' => $products,
            'filtered' => !empty($categoryId),
            'error' => ''
        ];
        $this->view('products/index', $data);
    }
        // Vista y procesamiento para crear producto
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'name' => trim($_POST['txtNombre']),
                'category' => $_POST['cboCategoria'],
                'price' => trim($_POST['txtPrecio']),
                'stock' => trim($_POST['txtStock']),
                'categories' => $this->categoryModel->getCategories(),
                'name_err' => '',
                'price_err' => '',
                'stock_err' => ''
            ];

            if (empty($data['name'])) {
                $data['name_err'] = 'Por favor ingrese el nombre del producto';
            }
            if (!is_numeric($data['price']) || $data['price'] < 0) {
                $data['price_err'] = 'El precio debe ser un número mayor o igual a 0';
            }
            if (!is_numeric($data['stock']) || $data['stock'] < 0) {
                $data['stock_err'] = 'El stock debe ser un número mayor o igual a 0';
            }

            if (empty($data['name_err']) && empty($data['price_err']) && empty($data['stock_err'])) {
                if ($this->productModel->addProduct($data)) {
                    header('Location: ' . urlRoot . '/products');
                    exit();
                } else {
                    die('Error al guardar el producto.');
                }
            } else {
                $this->view('products/insprod', $data);
            }
        } else {
            $data = [
                'name' => '',
                'category' => '',
                'price' => '',
                'stock' => '',
                'categories' => $this->categoryModel->getCategories(),
                'name_err' => '',
                'price_err' => '',
                'stock_err' => ''
            ];
            $this->view('products/insprod', $data);
        }
    }
        // Vista y procesamiento para modificar producto
    public function edit($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'id' => $id,
                'name' => trim($_POST['txtNombre']),
                'category' => $_POST['cboCategoria'],
                'price' => trim($_POST['txtPrecio']),
                'stock' => trim($_POST['txtStock']),
                'categories' => $this->categoryModel->getCategories(),
                'name_err' => '',
                'price_err' => '',
                'stock_err' => ''
            ];

            if (empty($data['name'])) {
                $data['name_err'] = 'El nombre no puede estar vacío';
            }
            if (!is_numeric($data['price']) || $data['price'] < 0) {
                $data['price_err'] = 'El precio debe ser un número mayor o igual a 0';
            }
            if (!is_numeric($data['stock']) || $data['stock'] < 0) {
                $data['stock_err'] = 'El stock debe ser un número mayor o igual a 0';
            }

            if (empty($data['name_err']) && empty($data['price_err']) && empty($data['stock_err'])) {
                if ($this->productModel->updateProduct($data)) {
                    header('Location: ' . urlRoot . '/products');
                    exit();
                } else {
                    die('Error al actualizar.');
                }
            } else {
                $this->view('products/modprod', $data);
            }
        } else {
            $product = $this->productModel->getProductById($id);

            // Si no existe el producto vuelvo a la lista
            if (!$product) {
                header('Location: ' . urlRoot . '/products');
                exit();
            }

            $data = [
                'id' => $product->ProductID,
                'name' => $product->ProductName,
                'category' => $product->CategoryID,
                'price' => $product->UnitPrice,
                'stock' => $product->UnitsInStock,
                'categories' => $this->categoryModel->getCategories(),
                'name_err' => '',
                'price_err' => '',
                'stock_err' => ''
            ];
            $this->view('products/modprod', $data);
        }
    }
    // Eliminar producto
    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->productModel->deleteProduct($id)) {
                header('Location: ' . urlRoot . '/products');
                exit();
            } else {
                
                $data = [
                    'title' => 'Lista general de productos',
                    'products' => $this->productModel->getProducts(),
                    'filtered' => false,
                    'error' => 'No se puede borrar el producto porque ya está asociado a una orden.'
                ];
                $this->view('products/index', $data);
            }
        }
    }
}
?>