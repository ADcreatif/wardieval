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
    const TYPE_JSON = 8;

    protected static $definition = []; //définition de la table [name, type, [length, nullValues]]
    protected $def; // contient la définition courante de l'objet
    protected $table = ''; // nom de la table
    protected $primary = 'id'; // clé primaire ('id' par défaut)
    public $id; // id de la ligne

    function __construct($id = null) {
        global $errors;

        $class = get_class($this);
        $this->def = $class::$definition;

        foreach ($this->def as $row)
            $this->{$row['name']} = null;

        if ($id) {
            $this->id = intval($id);
            if ($this->exists_in_database($this->primary, $this->id, $this->table))
                $this->get_infos();
            else
                $errors[] = "impossible de trouver l'élément dans notre base";
        }
    }

    public function hydrate($attributes) {
        /*foreach($this->def as $row){
            if(isset($attributes[$row['name']])){
                $value = $attributes[$row['name']];
                print_r( $value); echo '-';
                if($value != null && $value != '' && (!isset($row['nullValues']) OR $row['nullValues'] )){
                    print_r ($value);echo '<br>';
                    $this->$row['name'] = $value;
                }
            }
        }*/
        foreach ($attributes as $name => $value) {
            if (property_exists($this, $name))
                $this->$name = $value;
            elseif (_DEBUG_) {
                echo '<b>Erreur, la propriété ' . $name . ' n\'as pas été définie dans ' . get_class($this) . '</b><pre>';
                debug_print_backtrace();
                echo '</pre>';
                exit;
            }
        }
    }

    protected function get_infos() {
        $sql = "SELECT * FROM $this->table WHERE $this->primary = $this->id";
        $req = Db::query($sql);

        if ($req->rowCount() > 0) {
            $res = $req->fetch(PDO::FETCH_ASSOC);

            foreach ($this->def as $row) {
                $this->$row['name'] = $this->format_values($res[$row['name']], $row['type']);
            }
        }
    }

    public static function exists_in_database($row, $value, $table) {
        $sql = "SELECT id,$row FROM $table WHERE $row = :value";
        $req = Db::prepare($sql);
        $req->execute([':value' => $value]);

        return $req->rowCount() > 0;
    }

    /****************************************
     **********       CRUD          *********
     ****************************************/

    public function save() {
        return (int)$this->id > 0 ? $this->update() : $this->add();
    }

    public function update() {
        $fields = $this->format_request();
        //$fields = $this->format_fields();

        $sql = "UPDATE $this->table SET {$fields['sql_params']} WHERE id $this->id";
        $req = Db::prepare($sql);

        foreach ($fields['bind_params'] as $field)
            $req->bindParam($field['name'], $field['value'], $field['type']);

        if ($req->execute())
            return Db::getLastInsertId();
        return false;
    }

    public function add() {
        $fields = $this->format_request();

        $sql = "INSERT INTO $this->table SET {$fields['sql_params']}";
        $req = Db::prepare($sql);

        foreach ($fields['bind_params'] as $field)
            $req->bindParam($field['name'], $field['value'], $field['type']);

        if ($req->execute())
            return $this->id = Db::getLastInsertId();

        throw new Exception ("L'insertion de l'objet n'as pas fonctionné");
    }

    /** Met à jour une valeur de la table
     * @param $row_name
     * @param $value
     * @return mixed la nouvelle valeur mise à jour si la modification à fonctionné
     * @throws Exception
     */
    public function update_value($row_name, $value) {
        foreach ($this->def as $row) {
            if ($row['name'] == $row_name) {
                $this->$row_name = $value;
                $sql = "UPDATE $this->table SET $row_name = :value WHERE id = $this->id;";
                $req = Db::prepare($sql);
                $req->bindParam(':value', $value, $row['type']);
                $req->execute();
                return $this->$row_name;
            }
        }
        throw new Exception ("le champ $row_name est introuvable dans {getClass($this)}");
    }

    public function delete() {
        $sql = "DELETE FROM $this->table WHERE $this->primary = {$this->id}";
        Db::exec($sql);

    }


    /****************************************
     *********       FINDERS        *********
     ****************************************/
    /**
     * @param String $where
     */
    public static function get_all_where($where) {
        //print_r(property_exists(self, $where));

    }

    /****************************************
     *********      FORMATING       *********
     ****************************************/

    // formate les champs avant leur enregistrement dans la base
    protected function format_request() {
        $fields = [];
        $sql_part = '';

        foreach ($this->def as $field) {

            if (property_exists($this, $field['name']))
                $value = $this->$field['name'];
            elseif (_DEBUG_) {
                echo '<b>Erreur, la propriété ' . $field['name'] . ' n\'as pas été définie dans ' . get_class($this) . '</b><pre>';
                debug_print_backtrace();
                echo '</pre>';
                exit;
            } else throw new Exception('Un problème est survenu. Essayez de recharger la page');

            // si la valeur est nulle, vide ou qu'on autorise pas les valeurs nulles on passe
            if (isset($field['nullValues']) && $field['nullValues'] && ($value == null || $value == ''))
                continue;

            // si la valeur doit être tronquée (max_lenght dans la définition)
            if (isset($field['length']) && strlen($value) >= $field['length'])
                $value = trim(substr($value, 0, $field['length']) . '...');

            // si la valeur est de type JSON on l'encode
            if ($field['type'] == self::TYPE_JSON)
                $value = json_encode($value, JSON_FORCE_OBJECT); //on force l'encodage sous forme object  (pour les clés égales à 0 et tableau vides)

            //création de la chaine pour le bind de PDO (:row_name)
            $row_name = ':' . $field['name'];

            // on converti nos propres types en ceux de PDO
            $pdo_type = $this->get_pdo_types($field['type']);

            $fields[] = [
                'name'  => $row_name,
                'value' => $value,
                'type'  => $pdo_type
            ];

            // création de la chaine en SQL "SET (row_name = value,...)"
            $sql_part .= " {$field['name']} = $row_name, ";

        }
        // on enlève la dernière virgule
        $sql_part = substr($sql_part, 0, strripos($sql_part, ','));

        return ['sql_params' => $sql_part, 'bind_params' => $fields];
    }

    /* avant c'était dans la méthode update en cas particulier pour add_fleet et sa contrainte d'unicité
    protected function format_fields() {
        $fields = [];

        foreach ($this->def as $row){
            if(isset($this->$row['name']) OR (isset($row['nullValues']) AND $row['nullValues'] )){
                $nullValues = isset($row['nullValues']) ? $row['nullValues'] : false;
                $fields[$row['name']] = $this->format_values($this->$row['name'], $row['type'] , $nullValues);
            } elseif(_DEBUG_) {
                echo '<b>Erreur, la propriété '.$row['name'].' n\'as pas été définie dans '.get_class($this).'</b><pre>';
                debug_print_backtrace();
                echo '</pre>';
                exit;
            }
        }
        return $fields;
    } */

    // formate à leur sortie de la base
    private function format_values($value, $type, $nullValues = false, $with_quotes = false) {
        if ($nullValues && ($value = '' || is_null($value)))
            return 'NULL';

        switch ($type) {
            case self::TYPE_INT:

            case self::TYPE_BOOL:
                return (int)$value;

            case self::TYPE_DATE:
                if (!$value)
                    return '0000-00-00 00:00:00';
                return $value;

            case self::TYPE_JSON:
                return json_decode($value, true);

            case self::TYPE_FLOAT:
                return (float)str_replace(',', '.', $value);

            case self::TYPE_HTML;
                return '\'' . Db::sanitize($value, true) . '\'';

            case self::TYPE_STRING:

            default :
                if ($with_quotes)
                    return '\'' . Db::sanitize($value) . '\'';
                return Db::sanitize($value);
        }
    }

    private function get_pdo_types($type) {
        switch ($type) {
            case self::TYPE_INT:
                return PDO::PARAM_INT;

            case self::TYPE_BOOL:
                return PDO::PARAM_BOOL;

            case self::TYPE_STRING:
            case self::TYPE_DATE:
            case self::TYPE_HTML:
            case self::TYPE_FLOAT:
            case self::TYPE_NOTHING:
            case self::TYPE_JSON:
            default:
                return PDO::PARAM_STR;
        }
    }
}
