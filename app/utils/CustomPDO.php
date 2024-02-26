<?php


/**
 * Helper from the distant past
 */



namespace app\utils;

use Exception;

class CustomPDO
{
    private $charset;

    private $lastQuery;

    private $params;

    private $mysqli;

    private $host;
    private $database;
    private $username;
    private $password;


    public function setConnect($host, $database, $username, $password, $charset = 'utf8mb4')
    {
        $this->host = $host;
        $this->database = $database;
        $this->username = $username;
        $this->password = $password;
        $this->charset = $charset;
    }

    public function getLastError()
    {
        if (!$this->mysqli)
            return 'MySQL connection not opened';

        return 'MySQL error: (' . mysqli_errno($this->mysqli) . ') ' . mysqli_error($this->mysqli) .
            ($this->lastQuery ? 'Query: ' . $this->lastQuery : '');
    }

    public function open()
    {
        $this->mysqli = @mysqli_connect($this->host, $this->username, $this->password);
        $this->isMySQLInit();

        if (!@mysqli_select_db($this->mysqli, $this->database))
            throw new Exception($this->getLastError(), 500);

        return ($this->charset ? $this->query('SET NAMES ?', $this->charset) : true);
    }

    public function close()
    {
        if ($this->mysqli) {
            @mysqli_close($this->mysqli);
            unset($this->mysqli);
        }
    }

    public function select($table, $fields = '*', $filter = '', $values = array(), $order = '', $limit = '', $group = '')
    {
        return $this->query(
            "SELECT $fields FROM $table" .
                ($filter ? " WHERE $filter" : '') .
                ($group ? " GROUP BY $group" : '') .
                ($order ? " ORDER BY $order" : '') .
                ($limit ? " LIMIT $limit" : ''),
            $values
        );
    }

    public function insert($table, $filedsAndValues, $fields = '', $replace = false)
    {
        $filedsAndValues = $this->filter($filedsAndValues, $fields);

        if (count($filedsAndValues) == 0)
            return 0;
        return $this->query(
            ($replace ? 'REPLACE' : 'INSERT') . " INTO $table (?#) VALUES (?a)",
            array(array_keys($filedsAndValues), array_values($filedsAndValues))
        );
    }

    public function replace($table, $filedsAndValues, $fields = '')
    {
        return $this->insert($table, $filedsAndValues, $fields, true);
    }

    public function update($table, $filedsAndValues, $fields = '', $filter = '', $values = array())
    {
        $filedsAndValues = $this->filter($filedsAndValues, $fields);
        if (count($filedsAndValues) == 0)
            return 0;

        $values = $this->toArray($values);

        array_unshift($values, $filedsAndValues);
        return $this->query(
            "UPDATE $table SET ?a WHERE $filter",
            $values
        );
    }

    public function delete($table, $filter = '', $values = array(), $order = '', $limit = '')
    {
        return $this->query(
            "DELETE FROM $table" .
                ($filter ? " WHERE $filter" : '') .
                ($order ? " ORDER BY $order" : '') .
                ($limit ? " LIMIT $limit" : ''),
            $values
        );
    }

    public function count($table, $filter = '', $values = array(), $field = '')
    {
        return 0 + $this->fetch1(
            $this->select(
                $table,
                'COUNT(' . ($field ? $this->field($field) : '*') . ') AS _cnt_',
                $filter,
                $values
            )
        );
    }

    public function save($table, $filedsAndValues, $fields = '', $id_field = '_ID_')
    {
        $id = intval(@$filedsAndValues[$id_field]);
        if ($id > 0) {
            $this->update($table, $filedsAndValues, $fields, "$id_field=?d", array($id));
            return $id;
        } else
            return $this->insert($table, $filedsAndValues, $fields);
    }

    public function fetch($queryResult)
    {
        if (!is_object($queryResult))
            return false;
        return @mysqli_fetch_assoc($queryResult);
    }

    public function fetch1Row($queryResult)
    {
        $res = $this->fetch($queryResult);
        $this->freeQuery($queryResult);

        return (is_array($res) ? $res : array());
    }

    public function fetch1($queryResult)
    {
        $res = $this->fetch1Row($queryResult);

        return (is_array($res) ? reset($res) : null);
    }

    public function fetchRows($queryResult)
    {
        if (!is_object($queryResult))
            return false;

        $res = array();
        while ($r = $this->fetch($queryResult))
            $res[] = $r;

        $this->freeQuery($queryResult);
        return $res;
    }





    private function query($query, $values = array())
    {
        $this->isMySQLInit();
        $this->params = array_reverse($this->toArray($values));

        $pattern = '{(\?)([\?\#dfail%]?)|(\#)(\#|\!|[\w_]+)}';
        $query = preg_replace_callback($pattern, array(&$this, 'replaceCallback'), $query);
        $queryResult = $this->makeQuery($query);



        if ($queryResult === false)
            throw new Exception($this->getLastError(), 500);
        if (is_object($queryResult))
            return $queryResult;
        if (preg_match('/^\s*INSERT\s+/six', $query))
            return $this->newID();
        return $this->affCount();
    }

    private function makeQuery($query)
    {
        $this->lastQuery = $query;
        return @mysqli_query($this->mysqli, $query);
    }

    private function freeQuery($queryResult)
    {
        if (is_object($queryResult))
            @mysqli_free_result($queryResult);
    }



    private function isMySQLInit()
    {
        if ($this->mysqli)
            return true;

        throw new Exception($this->getLastError(), 500);
    }

    private function replaceCallback($m)
    {
        if ($m[2] == $m[1])
            return $m[1];
        switch ($m[1]) {
            case '#':
                if ($m[2] == '!')
                    return ' AS ' . '_ID_';
                else
                    return $this->field($m[2]);
            case '?':
                $v = array_pop($this->params);
                switch ($m[2]) {
                    case '#':
                        return $this->field($v);
                    case 'd':
                        return intval($v);
                    case 'f':
                        return str_replace(',', '.', floatval($v));
                    case 'a':
                        return $this->value($v, '', true);
                    case 'i':
                        if (is_array($v) and (count($v) > 0))
                            return 'IN (' . $this->value($v, '', true) . ')';
                        else
                            return 'IS NULL';
                    case 'l':
                    case '%':
                        if (!is_string($v) or ($v === ''))
                            return 'NOT IS NULL';
                        if ($m[2] == '%')
                            $v = '%' . $v . '%';
                        return 'LIKE ' . $this->value($v);
                }
                return $this->value($v);
        }
    }

    private function escapeString($value, $fields = '', $asArray = false)
    {

        if (!$asArray or !is_array($value))
            return "'" . mysqli_real_escape_string($this->mysqli, strval($value)) . "'";
        $result = array();

        foreach ($this->filter($value, $fields) as $f => $v) {
            if (substr($f, -1) === '=')
                $f = trim(substr($f, 0, -1));
            else
                $v = $this->escapeString($v);
            if (!is_int($f))
                $f = $this->field($f);
            $result[$f] = $v;
        }

        return $result;
    }

    private function value($value, $fields = '', $asArray = false)
    {
        $value = $this->escapeString($value, $fields, $asArray);

        if (!$asArray or !is_array($value))
            return $value;

        $result = array();
        foreach ($value as $f => $v)
            if (!is_int($f))
                $result[] = "$f=$v";
            else
                $result[] = $v;

        return implode(',', $result);
    }

    private function field($name)
    {
        if (!is_array($name))
            return "`" . str_replace('`', '``', $name) . "`";

        $result = array();
        foreach ($name as $n)
            $result[] = $this->field($n);

        return implode(',', $result);
    }

    private function affCount()
    {
        $this->isMySQLInit();
        return @mysqli_affected_rows($this->mysqli);
    }

    private function newID()
    {
        $this->isMySQLInit();
        return @mysqli_insert_id($this->mysqli);
    }

    private function filter($values, $fields = '')
    {
        if (!is_array($values) or !$fields)
            return $values;
        $fields = $this->toArray($fields);
        $result = array();
        foreach ($fields as $f)
            $result[$f] = $values[$f];

        return $result;
    }

    private function toArray($array, $dlm = ',')
    {
        if (is_array($array))
            return $array;

        $result = array();
        foreach (explode($dlm, $array) as $value) {
            $value = trim($value);
            if (strlen($value) > 0)
                $result[] = $value;
        }
        return $result;
    }
}
