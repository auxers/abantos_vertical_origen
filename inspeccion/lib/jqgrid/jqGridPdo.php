<?php class jqGridDB
{
    public static function getInterface()
    {
        return 'pdo';
    }
    public static function prepare($conn, $sqlElement, $params, $bind = true)
    {
        if ($conn && strlen($sqlElement) > 0)
        {
            $sql = $conn->prepare((string)$sqlElement);
            if (!$bind) return $sql;
            if (is_array($params))
            {
                if (0 !== count(array_diff_key($params, array_keys(array_keys($params)))))
                {
                    foreach ($params as $k => $v)
                    {
                        if ($v === NULL) $sql->bindValue($k, NULL, PDO::PARAM_NULL);
                        else $sql->bindValue($k, $v);
                    }
                }
                else
                {
                    for ($i = 1;$i <= count($params);$i++)
                    {
                        if ($params[$i - 1] === NULL) $sql->bindValue($i, NULL, PDO::PARAM_NULL);
                        else $sql->bindValue($i, $params[$i - 1]);
                    }
                }
            }
            return $sql;
        }
        return false;
    }
    public static function limit($sqlId, $dbtype, $nrows = - 1, $offset = - 1, $order = '', $sort = '')
    {
        $psql = $sqlId;
        switch ($dbtype)
        {
            case 'mysql':
                $offsetStr = ($offset >= 0) ? "$offset, " : '';
                if ($nrows < 0) $nrows = '18446744073709551615';
                $psql .= " LIMIT $offsetStr$nrows";
                break;
            case 'pgsql':
                $offsetStr = ($offset >= 0) ? " OFFSET " . $offset : '';
                $limitStr = ($nrows >= 0) ? " LIMIT " . $nrows : '';
                $psql .= "$limitStr$offsetStr";
                break;
            case 'sqlite':
                $offsetStr = ($offset >= 0) ? " OFFSET $offset" : '';
                $limitStr = ($nrows >= 0) ? " LIMIT $nrows" : ($offset >= 0 ? ' LIMIT 999999999' : '');
                $psql .= "$limitStr$offsetStr";
                break;
            case 'sqlsrv':
                $psql = $sqlId;
                $nrows = intval($nrows);
                if ($nrows < 0) return false;
                $offset = intval($offset);
                if ($offset < 0) return false;
                $orderby = $order && strlen($order) > 0;
                if ($orderby !== false)
                {
                    $sort = (stripos($sort, 'desc') !== false) ? 'desc' : 'asc';
                }
                $psql = preg_replace('/^SELECT\s+(DISTINCT\s)?/i', 'SELECT $1TOP ' . ($nrows + $offset) . ' ', $psql);
                $psql = 'SELECT * FROM(SELECT TOP ' . $nrows . ' * FROM(' . $psql . ') AS inner_tbl';
                if ($orderby !== false)
                {
                    $orderrev = $order;
                    $orderrev = trim(preg_replace('/\bASC\b/i', 'DES', $orderrev));
                    $orderrev = trim(preg_replace('/\bDESC\b/i', 'ASC', $orderrev));
                    $orderrev = trim(preg_replace('/\bDES\b/i', 'DESC', $orderrev));
                    $psql .= ' ORDER BY ' . $orderrev . ' ';
                    $psql .= (stripos($sort, 'asc') !== false) ? 'DESC' : 'ASC';
                }
                $psql .= ') AS outer_tbl';
                if ($orderby !== false)
                {
                    $psql .= ' ORDER BY ' . $order . ' ' . $sort;
                }
                break;
            }
            return $psql;
        }
        public static function execute($psql, $prm = null)
        {
            $ret = false;
            if ($psql)
            {
                $ret = $psql->execute();
            }
            return $ret;
        }
        public static function query($conn, $sql)
        {
            if ($conn && strlen($sql) > 0)
            {
                return $conn->query($sql);
            }
            return false;
        }
        public static function bindValues($stmt, $binds, $types)
        {
            foreach ($binds as $key => $field)
            {
                if ($field === NULL)
                {
                    $stmt->bindValue($key + 1, NULL, PDO::PARAM_NULL);
                    continue;
                }
                switch ($types[$key])
                {
                    case 'numeric':
                    case 'string':
                    case 'date':
                    case 'time':
                    case 'datetime':
                        $stmt->bindValue($key + 1, $field, PDO::PARAM_STR);
                    break;
                    case 'int':
                        $stmt->bindValue($key + 1, (int)$field, PDO::PARAM_INT);
                    break;
                    case 'boolean':
                        $stmt->bindValue($key + 1, $field, PDO::PARAM_BOOL);
                    break;
                    case 'blob':
                        $stmt->bindValue($key + 1, $field, PDO::PARAM_LOB);
                    break;
                    case 'custom':
                        $stmt->bindValue($key + 1, $field);
                    break;
                }
            }
            return true;
        }
        public static function beginTransaction($conn)
        {
            return $conn->beginTransaction();
        }
        public static function commit($conn)
        {
            return $conn->commit();
        }
        public static function rollBack($conn)
        {
            return $conn->rollBack();
        }
        public static function lastInsertId($conn, $table, $IdCol, $dbtype)
        {
            if ($dbtype == 'pgsql')
            {
                return $conn->lastInsertId($table . '_' . $IdCol . '_seq');
            }
            else
            {
                return $conn->lastInsertId();
            }
        }
        public static function fetch_object($psql, $fetchall, $conn)
        {
            if ($psql)
            {
                $old = $conn->getAttribute(PDO::ATTR_CASE);
                $conn->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
                if (!$fetchall)
                {
                    $ret = $psql->fetch(PDO::FETCH_OBJ);
                }
                else
                {
                    $ret = $psql->fetchAll(PDO::FETCH_OBJ);
                }
                $conn->setAttribute(PDO::ATTR_CASE, $old);
                return $ret;
            }
            return false;
        }
        public static function fetch_num($psql)
        {
            if ($psql)
            {
                return $psql->fetch(PDO::FETCH_NUM);
            }
            return false;
        }
        public static function fetch_assoc($psql, $conn)
        {
            if ($psql)
            {
                $old = $conn->getAttribute(PDO::ATTR_CASE);
                $conn->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
                $ret = $psql->fetch(PDO::FETCH_ASSOC);
                $conn->setAttribute(PDO::ATTR_CASE, $old);
                return $ret;
            }
            return false;
        }
        public static function closeCursor($sql)
        {
            if ($sql) $sql->closeCursor();
        }
        public static function columnCount($rs)
        {
            if ($rs) return $rs->columnCount();
            else return 0;
        }
        public static function getColumnMeta($index, $sql)
        {
            if ($sql && $index >= 0)
            {
                return $sql->getColumnMeta($index);
            }
        }
        public static function MetaType($t, $dbtype)
        {
            $meta = "numeric";
            if (is_array($t))
            {
                $len = $t["len"];
                switch ($dbtype)
                {
                    case "pgsql":
                        $type = $t["native_type"];
                        $meta = self::MetaPgsql($type, $len);
                    break;
                    case "mysql":
                        $type = isset($t["native_type"]) ? $t["native_type"] : 'int';
                        $meta = self::MetaMysql($type, $len);
                    break;
                    case "sqlite":
                        $type = $t["sqlite:decl_type"];
                        $meta = self::MetaSqlite($type, $len);
                    break;
                    case "sqlsrv":
                        $type = $t["sqlsrv:decl_type"];
                        $meta = self::MetaSqlsrv($type, $len);
                    break;
                }
            }
            return $meta;
        }
        protected static function MetaPgsql($native_type, $max_len = - 1)
        {
            switch (strtoupper($native_type))
            {
                case 'MONEY':
                case 'INTERVAL':
                case 'CHAR':
                case 'CHARACTER':
                case 'VARCHAR':
                case 'NAME':
                case 'BPCHAR':
                case '_VARCHAR':
                case 'INET':
                case 'MACADDR':
                    return 'string';
                case 'TEXT':
                    return 'string';
                case 'IMAGE':
                case 'BLOB':
                case 'BIT':
                case 'VARBIT':
                case 'BYTEA':
                    return 'blob';
                case 'BOOL':
                case 'BOOLEAN':
                    return 'boolean';
                case 'DATE':
                    return 'date';
                case 'TIMESTAMP WITHOUT TIME ZONE':
                case 'TIME':
                case 'DATETIME':
                case 'TIMESTAMP':
                case 'TIMESTAMPTZ':
                    return 'datetime';
                case 'SMALLINT':
                case 'BIGINT':
                case 'INTEGER':
                case 'INT8':
                case 'INT4':
                case 'INT2':
                    return 'int';
                case 'OID':
                case 'SERIAL':
                    return 'int';
                default:
                    return 'numeric';
            }
        }
        protected static function MetaMysql($native_type, $max_length = - 1)
        {
            switch (strtoupper($native_type))
            {
                case 'STRING':
                case 'CHAR':
                case 'VARCHAR':
                case 'TINYBLOB':
                case 'TINYTEXT':
                case 'ENUM':
                case 'VAR_STRING':
                case 'SET':
                    return 'string';
                case 'TEXT':
                case 'LONGTEXT':
                case 'MEDIUMTEXT':
                    return 'string';
                case 'IMAGE':
                case 'LONGBLOB':
                case 'BLOB':
                case 'MEDIUMBLOB':
                case 'BINARY':
                    return 'blob';
                case 'YEAR':
                case 'DATE':
                    return 'date';
                case 'TIME':
                case 'DATETIME':
                case 'TIMESTAMP':
                    return 'datetime';
                case 'INT':
                case 'INTEGER':
                case 'BIGINT':
                case 'TINYINT':
                case 'MEDIUMINT':
                case 'SMALLINT':
                case 'LONG':
                    return 'int';
                default:
                    return 'numeric';
            }
        }
        protected static function MetaSqlite($native_type, $max_length = - 1)
        {
            switch (strtoupper($native_type))
            {
                case 'STRING':
                case 'CHAR':
                case 'CHARACTER':
                case 'VARCHAR':
                case 'NCHAR':
                case 'NVARCHAR':
                case 'TEXT':
                case 'CLOB':
                case 'VARYING CHARACTER':
                case 'NATIVE CHARACTER':
                    return 'string';
                case 'BLOB':
                    return 'blob';
                case 'DATE':
                    return 'date';
                case 'DATETIME':
                    return 'datetime';
                case 'INT':
                case 'INTEGER':
                case 'BIGINT':
                case 'TINYINT':
                case 'MEDIUMINT':
                case 'SMALLINT':
                case 'UNSIGNED BIG INT':
                case 'UNSIGNED':
                case 'INT2':
                case 'INT8':
                    return 'int';
                default:
                    return 'numeric';
            }
        }
        protected static function MetaSqlsrv($native_type, $max_length = - 1)
        {
            switch (strtoupper($native_type))
            {
                case 'BITINT':
                case 'CHAR':
                case 'DECIMAL':
                case 'MONEY':
                case 'NCHAR':
                case 'NUMERIC':
                case 'NVARCHAR':
                case 'NTEXT':
                case 'SMALLMONEY':
                case 'SQL_VARIANT':
                case 'TEXT':
                case 'TIMESTAMP':
                case 'UNIQUEIDENTIFIER':
                case 'VARCHAR':
                case 'XML':
                    return 'string';
                case 'BINARY':
                case 'GEOGRAPHY':
                case 'GEOMETRY':
                case 'IMAGE':
                case 'UDT':
                case 'VARBINARY':
                    return 'blob';
                case 'DATETIME':
                case 'DATE':
                case 'DATETIME2':
                case 'DATETIMEOFFSET':
                case 'SMALLDATETIME':
                case 'TIME':
                    return 'datetime';
                case 'INT':
                case 'BIT':
                case 'SMALLINT':
                case 'TINYINT':
                    return 'int';
                default:
                    return 'numeric';
            }
        }
        public static function getPrimaryKey($table, $conn, $dbtype)
        {
            if (strlen($table) > 0 && $conn && strlen($dbtype) > 0)
            {
                switch ($dbtype)
                {
                    case 'pgsql':
                        $sql = "select column_name from information_schema.constraint_column_usage where table_name = '" . $table . "'";
                        $rs = $conn->query($sql, PDO::FETCH_NUM);
                        if ($rs)
                        {
                            $res = $rs->fetch();
                            self::closeCursor($rs);
                            if ($res)
                            {
                                return $res[0];
                            }
                        }
                        return false;
                    break;
                    case 'mysql':
                        $sql = "select column_name from information_schema.statistics where table_name='" . $table . "'";
                        $rs = $conn->query($sql, PDO::FETCH_NUM);
                        if ($rs)
                        {
                            $res = $rs->fetch();
                            self::closeCursor($rs);
                            if ($res)
                            {
                                return $res[0];
                            }
                        }
                        return false;
                    break;
                    case 'sqlite':
                        $pos = strpos($table, ".");
                        if ($pos === false) $sql = "PRAGMA table_info($table)";
                        else
                        {
                            $schemaName = substr($table, 0, $pos);
                            $table = substr($table, $pos + 1);
                            $sql = "PRAGMA $schemaName.table_info($table)";
                        }
                        $res = false;
                        $stmt = $conn->query($sql, PDO::FETCH_ASSOC);
                        if ($stmt)
                        {
                            while ($row = $stmt->fetch())
                            {
                                if ($row['pk'] == 1)
                                {
                                    $res = $row['name'];
                                    break;
                                }
                            }
                            self::closeCursor($stmt);
                        }
                        return $res;
                        break;
                    case 'sqlsrv':
                        $sql = "exec sp_columns @table_name = '" . $table . "'";
                        $stmt = self::query($conn, $sql);
                        if (!$stmt)
                        {
                            return false;
                        }
                        $result = array();
                        while ($row = self::fetch_num($stmt))
                        {
                            $result[] = $row;
                        }
                        $owner = 1;
                        $table_name = 2;
                        $column_name = 3;
                        $type_name = 5;
                        $precision = 6;
                        $length = 7;
                        $scale = 8;
                        $nullable = 10;
                        $column_def = 12;
                        $column_position = 16;
                        if (count($result) == 0)
                        {
                            return false;
                        }
                        self::closeCursor($stmt);
                        $tableOwner = $result[0][$owner];
                        $sql = "exec sp_pkeys @table_owner = " . $tableOwner . ", @table_name = '" . $table . "'";
                        $stmt = self::query($conn, $sql);
                        if ($stmt)
                        {
                            $primaryKeysResult = self::fetch_num($stmt);
                            self::closeCursor($stmt);
                        }
                        $pkey_column_name = 3;
                        $pkey_key_seq = 4;
                        if ($primaryKeysResult && $primaryKeysResult[$pkey_column_name]) return $primaryKeysResult[$pkey_column_name];
                        return false;
                        break;
                    }
                }
                return false;
        }
        public static function errorMessage($conn)
        {
            $ret = $conn->errorInfo();
            return "Code: " . $ret[1] . ". " . $ret[2];
        }
    } ?>