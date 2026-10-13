<?php
class Product {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Todos los productos en orden alfabetico, con el nombre de la categoria
    public function getProducts() {
        $this->db->query('SELECT p.ProductID, p.ProductName, p.UnitsInStock, p.UnitPrice,
                                 c.CategoryName
                          FROM products p
                          LEFT JOIN categories c ON p.CategoryID = c.CategoryID
                          ORDER BY p.ProductName ASC');
        return $this->db->resultSet();
    }

    // Solo los productos de una categoria
    public function getProductsByCategory($categoryId) {
        $this->db->query('SELECT p.ProductID, p.ProductName, p.UnitsInStock, p.UnitPrice,
                                 c.CategoryName
                          FROM products p
                          LEFT JOIN categories c ON p.CategoryID = c.CategoryID
                          WHERE p.CategoryID = :catid
                          ORDER BY p.ProductName ASC');
        $this->db->bind(':catid', $categoryId);
        return $this->db->resultSet();
    }

    // Un producto por ID (para el formulario de modificar)
    public function getProductById($id) {
        $this->db->query('SELECT * FROM products WHERE ProductID = :id');
        $this->db->bind(':id', $id);
        return $this->db->singleRow();
    }

    // Agregar producto
    public function addProduct($data) {
        $this->db->query('INSERT INTO products (ProductName, CategoryID, UnitPrice, UnitsInStock)
                          VALUES (:name, :category, :price, :stock)');
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':category', $data['category']);
        $this->db->bind(':price', $data['price']);
        $this->db->bind(':stock', $data['stock']);

        return $this->db->execute();
    }

    // Modificar producto
    public function updateProduct($data) {
        $this->db->query('UPDATE products SET ProductName = :name, CategoryID = :category,
                          UnitPrice = :price, UnitsInStock = :stock
                          WHERE ProductID = :id');
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':category', $data['category']);
        $this->db->bind(':price', $data['price']);
        $this->db->bind(':stock', $data['stock']);

        return $this->db->execute();
    }

    // Borrar producto (puede fallar si ya esta en una orden)
    public function deleteProduct($id) {
        try {
            $this->db->query('DELETE FROM products WHERE ProductID = :id');
            $this->db->bind(':id', $id);
            return $this->db->execute();
        } catch (Exception $e) {
            return false;
        }
    }
}
?>