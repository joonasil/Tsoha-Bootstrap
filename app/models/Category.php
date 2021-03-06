<?php

class Category extends BaseModel {
    public $id, $name, $user_id;
    
    public function __construct($attributes) {
        parent::__construct($attributes);
        $this->validators = array('validate_name');
    }
    
    //Metodi palauttaa listana kaikki kategoriat tietokannasta.
    public static function all() {
        $query = DB::connection()->prepare('SELECT * FROM Category');
        
        $query->execute();
        
        $rows = $query->fetchAll();
        $categories = array();
        
        foreach($rows as $row){
            $categories[] = new Category(array(
                'id' => (int) $row['id'],
                'name' => $row['category_name']      
            ));
        }
        
        return $categories;
    }
    
    //Metodi etsii tietokannasta kategoriaa id:n avulla ja palauttaa sen jos
    //kategoria löytyy tietokannasta, muuten palauttaa null
    public static function find($id) {
        $query = DB::connection()->prepare('SELECT * FROM Category WHERE id = :id LIMIT 1');
        $query->execute(array('id' => $id));
        $row = $query->fetch();
        
        if($row){
            $category = new Category(array(
                'id' => (int) $row['id'],
                'name' => $row['category_name']
            ));
            
            return $category;
        }
        
        return null;
    }
    
    //palauttaa kaikki tietyn käyttäjän kategoriat listana
    public static function find_user($id) {
        $query = DB::connection()->prepare('SELECT DISTINCT category_name, Category.id FROM Category, Account WHERE Category.user_id = :id');
        $query->execute(array('id' => $id));
        $rows = $query->fetchAll();
        $categories = array();
        
        foreach($rows as $row){
            $categories[] = new Category(array(
                'id' => (int) $row['id'],
                'name' => $row['category_name']      
            ));
        }
        
        return $categories;
    }
    
    //tallettaa uuden kategorian tietokantaan
    public function save(){
        $query = DB::connection()->prepare('INSERT INTO Category (user_id, category_name) VALUES (:id, :name) RETURNING id');
        $query->execute(array('id' => $this->user_id, 'name' => $this->name));
        $row = $query->fetch();
        $this->id = $row['id'];
    }
    
    //validiointimetodi kategorian nimelle
    public function validate_name(){
        $errors = array();
        if(self::validate_empty_string($this->name)){
            $errors[] = 'Luokan nimi ei saa olla tyhjä!';
        }
        if(self::validate_string_length($this->name, 3)){
            $errors[] = 'Luokan nimen tulee olla vähintään kolme merkkiä pitkä!';
        }
        return $errors;
    }
    
    //Metodi poistaa kyseisen kategorian tietokannasta
    public static function delete($id) {
        $query = DB::connection()->prepare('DELETE FROM Category WHERE id=:id');
        $query2 = DB::connection()->prepare('DELETE FROM Categories WHERE category_id=:id');
        $query2->execute(array('id' => $id));
        $query->execute(array('id' => $id));
    }
}
