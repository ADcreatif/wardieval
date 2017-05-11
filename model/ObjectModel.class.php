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
        $class = get_class($this);
        $this->def = $class::$definition;

        foreach ($this->def as $row)
            $this->{$row['name']} = null;

        if ($id) {
            $this->id = intval($id);
            if ($this->exists_in_database($this->primary, $this->id, $this->table))
                $this->get_infos();
            else
                throw new DomainException("impossible de trouver l'élément dans notre base (id $id introuvable)");
        }
    }

    public function hydrate($attributes) {
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
        $db = new Db();
        $sql = "SELECT * FROM $this->table WHERE $this->primary = ? ";
        $res = $db->queryOne($sql, [$this->id]);

        if (count($res) > 0) {
            foreach ($this->def as $row) {
                $this->$row['name'] = $this->format_values($res[$row['name']], $row['type']);
            }
        }
    }

    public static function exists_in_database($row, $value, $table) {
        $db = new Db();
        $res = $db->query("SELECT id,$row FROM $table WHERE $row = ?", [$value]);
        return count($res) > 0;
    }

    /****************************************
     **********       CRUD          *********
     ****************************************/

    public function save() {
        return (int)$this->id > 0 ? $this->update() : $this->add();
    }

    public function update() {
        $fields = $this->format_request();

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
        $sql = "INSERT INTO $this->table ({$fields['params']}) VALUES ({$fields['binds']})";
        $db = new Db();
        return $db->exec($sql, $fields['values']);
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

                $db = new Db();
                $sql = "UPDATE $this->table SET $row_name = ? WHERE id = ?";
                $db->exec($sql, [$value, $this->id]);

                return $this->$row_name = $value;
            }
        }
        throw new DomainException ("le champ $row_name est introuvable dans {getClass($this)}");
    }

    public function delete() {
        $db = new Db();
        $db->exec("DELETE FROM $this->table WHERE $this->primary = {$this->id}");
    }


    /****************************************
     *********      FORMATING       *********
     ****************************************/

    // formate les champs avant leur enregistrement dans la base
    protected function format_request() {

        $params = '';
        $binds = '';
        $values = [];


        foreach ($this->def as $field) {

            if (property_exists($this, $field['name']))
                $value = $this->$field['name'];

            // si la valeur est nulle, vide ou qu'on autorise pas les valeurs nulles on passe
            if (isset($field['nullValues']) && $field['nullValues'] && ($value == null || $value == ''))
                continue;

            // si la valeur doit être tronquée (max_length dans la définition)
            if (isset($field['length']) && strlen($value) >= $field['length'])
                $value = trim(substr($value, 0, $field['length']) . '...');

            // si la valeur est de type JSON on l'encode
            if ($field['type'] == self::TYPE_JSON)
                $value = json_encode($value, JSON_FORCE_OBJECT); //on force l'encodage sous forme object  (pour les clés égales à 0 et tableau vides)

            $params .= $field['name'] . ",";
            $binds .= "?,";
            $values[] = $value;

        }

        // on enlève la dernière virgule
        $params = substr($params, 0, strripos($params, ','));
        $binds = substr($binds, 0, strripos($binds, ','));

        return [
            'params' => $params,
            'binds' => $binds,
            'values' => $values
        ];
    }

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
}
