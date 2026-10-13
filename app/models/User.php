<?php
class User{
    private $db;

    //create database object
    public function __construct(){
        $this->db = new Database;
    }

    //retrive all user data
    public function getUsers(){
        $this->db->query('select * from usuarios');
        $regis = $this->db->resultSet();
        return $regis;
    }

    // Verifica si el nombre de usuario ya existe
    public function userExists($usuario){
        $this->db->query('select usuario from usuarios where usuario = :nomb');
        $this->db->bind(':nomb',$usuario);
        return $this->db->singleRow() !== false;
    }

    //retrieve an user data, loging function
    public function login($data){
        //create retrieve sentence
        $this->db->query('select * from usuarios where usuario = :nomb');

        //bind sentence with variable
        $this->db->bind(':nomb',$data['usuario']);

        //retrieve a single datarow
        $tupla = $this->db->singleRow();

        if($tupla === false){
            return false;
        }

        //update local variable with contra field from single row
        $contra = $tupla->contra;

        //validate passwords (retrieved pass with param pass)
        if(password_verify($data['contra'], $contra)){
            return $tupla;    
        }else{
            return false;
        }
    }

    // register new user function
    public function register($data){
        //create insert sentence
        $this->db->query('insert into usuarios(usuario,contra)values(:nomb,:cont)');

        //bind sentence with variable
        $this->db->bind(':nomb',$data['usuario']);
        $this->db->bind(':cont',$data['contra']);

        //execute SQL sentence
        if($this->db->execute()){
            return true;
        }else{
            return false;
        }
    }// end register user function
}
?>
