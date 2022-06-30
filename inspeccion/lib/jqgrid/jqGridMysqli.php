<?php class jqGridDB
{
    public static function getInterface()
    {
        return 'mysqli';
    }
    public static function prepare($conn, $sqlElement, $params, $bind = true)
    {
        if ($conn && strlen($sqlElement) > 0)
        {
            $sql = mysqli_stmt_init($conn);
            mysqli_stmt_prepare($sql, (string)$sqlElement);
            if (!$bind) return $sql;
            if (is_array($params))
            {
                $t = "";
                $cnt = count($params);
                for ($i = 0;$i < $cnt;$i++)
                {
                    $v = $params[$i];
                    if (is_string($v)) $t .= "s";
                    else if (is_int($v)) $t .= "i";
                    else if (is_float($v)) $t .= "d";
                    else $t .= "b";
                    $ar[] = & $params[$i];
                }
                if ($t)
                {
                    call_user_func_array('mysqli_stmt_bind_param', array_merge(array(
                        $sql,
                        $t
                    ) , $ar));
                }
            }
            return $sql;
        }
        return false;
    }
    public static function limit($sqlId, $dbtype, $nrows = - 1, $offset = - 1, $order = '', $sort = '')
    {
        $psql = $sqlId;
        $offsetStr = ($offset >= 0) ? "$offset, " : '';
        if ($nrows < 0) $nrows = '18446744073709551615';
        $psql .= " LIMIT $offsetStr$nrows";
        return $psql;
    }
    public static function execute($psql, $prm = null)
    {
        $ret = false;
        if ($psql)
        {
            $ret = mysqli_stmt_execute($psql);
        }
        return $ret;
    }
    public static function query($conn, $sql)
    {
        if ($conn && strlen($sql) > 0)
        {
            return mysqli_query($conn, $sql);
        }
        return false;
    }
    public static function bindValues($stmt, $binds, $types)
    {
        $tp = "";
        foreach ($binds as $key => $field)
        {
            switch ($types[$key])
            {
                case 'numeric':
                    $tp .= "d";
                break;
                case 'string':
                case 'date':
                case 'time':
                case 'boolean':
                case 'datetime':
                    $tp .= "s";
                break;
                case 'int':
                    $tp .= "i";
                break;
                case 'blob':
                    $tp .= "d";
                break;
                case 'custom':
                    $v = $field;
                    if (is_int($v)) $tp .= "i";
                    else if (is_float($v)) $tp .= "d";
                    else if (is_string($v)) $tp .= "s";
                    else $tp .= "d";
                    break;
                }
                $ar[] = & $binds[$key];
            }
            call_user_func_array('mysqli_stmt_bind_param', array_merge(array(
                $stmt,
                $tp
            ) , $ar));
            return true;
        }
        public static function beginTransaction($conn)
        {
            return mysqli_autocommit($conn, false);
        }
        public static function commit($conn)
        {
            return mysqli_commit($conn);;
        }
        public static function rollBack($conn)
        {
            return mysqli_rollback($conn);
        }
        public static function lastInsertId($conn, $table, $IdCol, $dbtype)
        {
            return mysqli_insert_id($conn);
        }
        public static function fetch_object($psql, $fetchall, $conn)
        {
            if ($psql)
            {
                $ret = null;
                $meta = mysqli_stmt_result_metadata($psql);
                while ($column = mysqli_fetch_field($meta))
                {
                    $colname = str_replace(' ', '_', $column->name);
                    $result[$colname] = "";
                    $resultArray[$colname] = & $result[$colname];
                }
                call_user_func_array(array(
                    $psql,
                    'bind_result'
                ) , $resultArray);
                if (!$fetchall)
                {
                    mysqli_stmt_fetch($psql);
                    $ret = new stdClass();
                    foreach ($resultArray as $key => $value)
                    {
                        $ret->$key = $value;
                    }
                }
                else
                {
                    while (mysqli_stmt_fetch($psql))
                    {
                        $obj = new stdClass();
                        foreach ($resultArray as $key => $value)
                        {
                            $obj->$key = $value;
                        }
                        $ret[] = $obj;
                    }
                    return $ret;
                }
                return $ret;
            }
            return false;
        }
        public static function fetch_num($psql)
        {
            if ($psql)
            {
                if (get_class($psql) == "mysqli_result") return mysqli_fetch_array($psql, MYSQLI_NUM);
                else return mysqli_stmt_fetch($psql);
            }
            return false;
        }
        public static function fetch_assoc($psql, $conn)
        {
            if ($psql)
            {
                if (get_class($psql) == "mysqli_result") return mysqli_fetch_array($psql, MYSQLI_ASSOC);
                else return mysqli_stmt_fetch($psql);
            }
            return false;
        }
        public static function closeCursor($sql)
        {
            if ($sql)
            {
                if (get_class($sql) == "mysqli_result") mysqli_free_result($sql);
                else mysqli_stmt_free_result($sql);
            }
        }
        public static function columnCount($rs)
        {
            if ($rs)
            {
                if (get_class($rs) == "mysqli_result") return mysqli_num_fields($rs);
                else return mysqli_stmt_field_count($rs);
            }
            else return 0;
        }
        public static function getColumnMeta($index, $sql)
        {
            if ($sql && $index >= 0)
            {
                $newmeta = array();
                if (get_class($sql) == "mysqli_result")
                {
                    $mt = mysqli_fetch_field_direct($sql, $index);
                }
                else
                {
                    $fd = mysqli_stmt_result_metadata($sql);
                    $mt = mysqli_fetch_field_direct($fd, $index);
                }
                $newmeta["name"] = $mt->name;
                $newmeta["native_type"] = $mt->type;
                $newmeta["len"] = $mt->length;
                return $newmeta;
            }
            return false;
        }
        public static function MetaType($t, $dbtype)
        {
            if (is_array($t))
            {
                $type = $t["native_type"];
                $len = $t["len"];
                switch ($type)
                {
                    case 1:
                    case 2:
                    case 3:
                    case 8:
                    case 9:
                    case 16:
                    case 13:
                        return 'int';
                    case 253:
                    case 254:
                    case 252:
                        return 'string';
                    case 10:
                    case 11:
                        return 'date';
                    case 7:
                    case 12:
                        return 'datetime';
                    default:
                        return 'numeric';
                }
            }
        }
        public static function getPrimaryKey($table, $conn, $dbtype)
        {
            if (strlen($table) > 0 && $conn && strlen($dbtype) > 0)
            {
                $sql = "select column_name from information_schema.statistics where table_name='" . $table . "'";
                $rs = self::query($conn, $sql);
                if ($rs)
                {
                    $res = mysqli_fetch_array($rs, MYSQLI_NUM);
                    self::closeCursor($rs);
                    if ($res)
                    {
                        return $res[0];
                    }
                }
            }
            return false;
        }
        public static function errorMessage($conn)
        {
            return mysqli_error($conn);
        }
    } ?>