<?php
/**
 * Created by PhpStorm.
 * User: Alan
 * Date: 26/08/15
 * Time: 15:05
 */

class ObjectModel {

    // liste des types
    const TYPE_INT = 1;
    const TYPE_STRING = 2;
    const TYPE_DATE = 3;
    const TYPE_FLOAT = 4;
    const TYPE_BOOL = 5;
    const TYPE_HTML = 6;
    const TYPE_NOTHING = 7;

    protected static $definition = [];
    protected $table = '';
    protected $primary = 'id';
    protected $id;

    function __construct($id = null){
        global $errors;

        if($id){
            $this->id = intval($id);
            if ($this->exists_in_database($this->primary, $this->id, $this->table))
                $this->get_infos();
            else
                $errors[] = "impossible de trouver l'élément dans notre base";
        }
    }


    protected function get_infos(){
        $sql = "SELECT * FROM $this->table WHERE $this->primary = $this->id";
        $req = Db::query($sql);

        if ($req->rowCount() > 0) {
            $res = $req->fetch(PDO::FETCH_ASSOC);
            $class = get_class($this);
            foreach ($class::$definition as $row) {
                $this->$row['name'] = $this->format_values($res[$row['name']],$row['type']);
            }
        }
    }

    public function add() {
    }

    /**
     * Met à jour une valeur de la table
     *
     * @param $row_name string
     * @param $value
     */
    public function update($row_name, $value) {
        $class = get_class($this);
        foreach ($class::$definition as $row) {
            if ($row['name'] == $row_name) {
                $sql = "UPDATE $this->table SET $row_name = :value WHERE id = $this->id";
                $req = Db::prepare($sql);
                $value = $this->format_values($value, $row['type']);
                $req->execute([':value' => $value]);
                break;
            }

        }
    }

    public function delete() {
        $sql = "DELETE FROM $this->table WHERE $this->primary = {$this->id}";
        Db::exec($sql);

    }

    protected function format_values($value, $type, $with_quotes = false){
        switch($type) {
            case self::TYPE_INT:

            case self::TYPE_BOOL:
                return (int)$value;

            case self::TYPE_FLOAT:
                return (float)str_replace(',', '.', $value);

            case self::TYPE_HTML;
                return Db::sanitize($value, true);

            case self::TYPE_STRING:

            default :
                if ($with_quotes)
                    return '\''.Db::sanitize($value).'\'';
                return Db::sanitize($value);
        }
    }

    public static function exists_in_database($row, $value, $table) {
        $sql = "SELECT id,$row FROM $table WHERE $row = :value";
        $req = Db::prepare($sql);
        $req->execute([':value' => $value]);

        return $req->rowCount() > 0;
    }
}
