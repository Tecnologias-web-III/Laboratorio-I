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