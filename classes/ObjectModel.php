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
    const TYPE_BOOL = 2;
    const TYPE_STRING = 3;
    const TYPE_FLOAT = 4;
    const TYPE_DATE = 5;
    const TYPE_HTML = 6;
    const TYPE_NOTHING = 7;

    protected $definition = [];
    protected $table = '';
    protected $primary = 'id';
    protected $id;

    function __construct($id = null){
        if($id){
            $this->id = intval($id);
            $this->get_infos();
        }
    }


    protected function get_infos(){
        $sql = "SELECT * FROM $this->table WHERE $this->primary = $this->id";
        $req = Db::query($sql);

        if($req->rowCount() >0 ){
            $res = $req->fetch(PDO::FETCH_ASSOC);
            foreach($this->definition as $row){
                $this->$row['name'] = $this->format_values($res[$row['name']],$row['type']);
            }
        }
    }

    public function delete(){
        $sql = "DELETE FROM {$this->table} WHERE {$this->primary} = {$this->id}";
        Db::exec($sql);

        echo json_encode($_POST['item_id']);
    }

    protected function format_values($value, $type, $with_quotes = false){
        switch($type){
            case PDO::PARAM_INT:
            case PDO::PARAM_BOOL:
                return (int)$value;
            case PDO::PARAM_STR:
            default :
                if ($with_quotes)
                    return '\''.Db::sanitize($value).'\'';
                return Db::sanitize($value);
        }
    }
}
