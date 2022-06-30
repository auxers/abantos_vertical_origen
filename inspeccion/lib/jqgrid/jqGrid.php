<?php require_once 'jqUtils.php';
class jqGrid
{
    public $version = '4.4.4.0';
    protected $pdo;
    protected $odbc;
    protected $I = '';
    protected $dbtype;
    protected $select = "";
    protected $params = null;
    protected $dbdateformat = 'Y-m-d';
    protected $dbtimeformat = 'Y-m-d H:i:s';
    protected $userdateformat = 'd/m/Y';
    protected $usertimeformat = 'd/m/Y H:i:s';
    protected static $queryLog = array();
    protected $tmpvar = false;
    public function logQuery($sql, $data = null, $types = null, $input = null, $fld = null, $primary = '')
    {
        self::$queryLog[] = array(
            'time' => date('Y-m-d H:i:s') ,
            'query' => $sql,
            'data' => $data,
            'types' => $types,
            'fields' => $fld,
            'primary' => $primary,
            'input' => $input
        );
    }
    public $debug = false;
    public $logtofile = true;
    protected $logfile = "jqGrid.log";
    public function setLogFile($file)
    {
        if ($file && is_string($file))
        {
            $this->logfile = $file;
        }
    }
    public function debugout()
    {
        if ($this->logtofile)
        {
            $fh = @fopen($this->logfile, "a+");
            if ($fh)
            {
                $the_string = "Executed " . count(self::$queryLog) . " query(s) - " . date('Y-m-d H:i:s') . "\n";
                $the_string .= print_r(self::$queryLog, true);
                fputs($fh, $the_string, strlen($the_string));
                fclose($fh);
                return (true);
            }
            else
            {
                echo "Can not write to log!";
            }
        }
        else
        {
            echo "<pre>\n";
            print_r(self::$queryLog);
            echo "</pre>\n";
        }
    }
    public $showError = false;
    public $errorMessage = '';
    public function sendErrorHeader()
    {
        if ($this->errorMessage)
        {
            header($_SERVER["SERVER_PROTOCOL"] . " 500 Internal Server error.");
            if ($this->customClass)
            {
                try
                {
                    $this->errorMessage = call_user_func(array(
                        $this->customClass,
                        $this->customError
                    ) , $this->oper, $this->errorMessage);
                }
                catch(Exception $e)
                {
                    echo "Can not call the method class - " . $e->getMessage();
                }
            }
            else if (function_exists($this->customError))
            {
                $this->errorMessage = call_user_func($this->customError, $this->oper, $this->errorMessage);
            }
            die($this->errorMessage);
        }
    }
    protected $GridParams = array(
        "page" => "page",
        "rows" => "rows",
        "sort" => "sidx",
        "order" => "sord",
        "search" => "_search",
        "nd" => "nd",
        "id" => "id",
        "filter" => "filters",
        "searchField" => "searchField",
        "searchOper" => "searchOper",
        "searchString" => "searchString",
        "oper" => "oper",
        "query" => "grid",
        "addoper" => "add",
        "editoper" => "edit",
        "deloper" => "del",
        "excel" => "excel",
        "subgrid" => "subgrid",
        "totalrows" => "totalrows",
        "autocomplete" => "autocmpl"
    );
    public $dataType = "xml";
    public $encoding = "utf-8";
    public $jsonencode = true;
    public $datearray = array();
    public $mongointegers = array();
    public $mongofields = array();
    public $SelectCommand = "";
    public $ExportCommand = "";
    public $gSQLMaxRows = 1000;
    public $SubgridCommand = "";
    public $table = "";
    protected $primaryKey;
    public $readFromXML = false;
    protected $userdata = null;
    public $customFunc = null;
    public $customClass = false;
    public $customError = null;
    public $xmlCDATA = false;
    public $optimizeSearch = false;
    public $cacheCount = false;
    public $performcount = true;
    public $oper;
    function __construct($db = null, $odbctype = '')
    {
        if (class_exists('jqGridDB')) $interface = jqGridDB::getInterface();
        else $interface = 'local';
        $this->pdo = $db;
        if ($interface == 'pdo' && is_object($this->pdo))
        {
            $this
                ->pdo
                ->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->dbtype = $this
                ->pdo
                ->getAttribute(PDO::ATTR_DRIVER_NAME);
            if ($this->dbtype == 'pgsql') $this->I = 'I';
        }
        else
        {
            $this->dbtype = $interface . $odbctype;
            $this->odbc = $odbctype;
        }
        $oper = $this->GridParams["oper"];
        $this->oper = jqGridUtils::GetParam($oper, false);
    }
    protected function parseSql($sqlElement, $params, $bind = true)
    {
        $sql = jqGridDB::prepare($this->pdo, $sqlElement, $params, $bind);
        return $sql;
    }
    protected function execute($sqlId, $params, &$sql, $limit = false, $nrows = - 1, $offset = - 1, $order = '', $sort = '')
    {
        if ($this->dbtype == 'mongodb')
        {
            return jqGridDB::mongoexecute($sqlId, $params, $sql, $limit, $nrows = 0, $offset, $order, $sort, $this->mongofields);
        }
        if ($this->dbtype == 'array')
        {
            if ($params && is_array($params))
            {
                foreach ($params as $k => $v) $params[$k] = "'" . $v . "'";
            }
        }
        $this->select = $sqlId;
        if ($limit)
        {
            if ($this->dbtype == "adodb")
            {
                $sql = jqGridDB::limit($this->pdo, $this->select, $nrows, $offset, $params);
                if ($this->debug) $this->logQuery($sql->sql, $params);
                return $sql ? true : false;
            }
            else
            {
                $this->select = jqGridDB::limit($this->select, $this->dbtype, $nrows, $offset, $order, $sort);
            }
        }
        if ($this->debug) $this->logQuery($this->select, $params);
        $this->params = $params;
        try
        {
            $sql = $this->parseSql($this->select, $params);
            $ret = true;
            if ($sql)
            {
                if ($this->dbtype == "adodb")
                {
                    $sql = jqGridDB::execute($sql, $params, $this->pdo);
                    $ret = $sql ? true : false;
                }
                else
                {
                    $ret = jqGridDB::execute($sql, $params, $this->pdo);
                }
            }
            if (!$ret)
            {
                $this->errorMessage = jqGridDB::errorMessage($this->pdo);
                throw new Exception($this->errorMessage);
            }
        }
        catch(Exception $e)
        {
            if (!$this->errorMessage) $this->errorMessage = $e->getMessage();
            if ($this->showError)
            {
                $this->sendErrorHeader();
            }
            else
            {
                echo $this->errorMessage;
            }
            return false;
        }
        return true;
    }
    protected function getSqlElement($sqlId)
    {
        $tmp = explode('.', $sqlId);
        $sqlFile = trim($tmp[0]) . '.xml';
        if (file_exists($sqlFile))
        {
            $root = simplexml_load_file($sqlFile);
            foreach ($root->sql as $sql)
            {
                if ($sql['Id'] == $tmp[1])
                {
                    if (isset($sql['table']) && strlen($sql['table']) > 0)
                    {
                        $this->table = $sql['table'];
                    }
                    if (isset($sql['primary']) && strlen($sql['primary']) > 0)
                    {
                        $this->primaryKey = $sql['primary'];
                    }
                    return $sql;
                }
            }
        }
        return false;
    }
    protected function _getcount($sql, array $params = null, array $sumcols = null)
    {
        $qryRecs = new stdClass();
        $qryRecs->COUNT = 0;
        $s = '';
        if (is_array($sumcols) && !empty($sumcols))
        {
            foreach ($sumcols as $k => $v)
            {
                if (is_array($v))
                {
                    foreach ($v as $dbfield => $oper)
                    {
                        $s .= "," . trim($oper) . "(" . $dbfield . ") AS " . $k;
                    }
                }
                else
                {
                    $s .= ",SUM(" . $v . ") AS " . $k;
                }
            }
        }
        if (preg_match("/^\s*SELECT\s+DISTINCT/is", $sql) || preg_match('/\s+GROUP\s+BY\s+/is', $sql) || preg_match('/\s+UNION\s+/is', $sql) || substr_count(strtoupper($sql) , 'SELECT ') > 1 || substr_count(strtoupper($sql) , ' FROM ') > 1 || $this->dbtype == 'oci8')
        {
            $rewritesql = "SELECT COUNT(*) AS COUNT " . $s . " FROM($sql) gridalias";
        }
        else
        {
            $rewritesql = preg_replace('/^\s*SELECT\s.*\s+FROM\s/Uis', 'SELECT COUNT(*) AS COUNT ' . $s . ' FROM ', $sql);
        }
        if (isset($rewritesql) && $rewritesql != $sql)
        {
            if (preg_match('/\sLIMIT\s+[0-9]+/i', $sql, $limitarr)) $rewritesql .= $limitarr[0];
            $qryRecs = $this->queryForObject($rewritesql, $params, false);
            if ($qryRecs) return $qryRecs;
        }
        return $qryRecs;
    }
    protected function queryForObject($sqlId, $params, $fetchAll = false)
    {
        $sql = null;
        $ret = $this->execute($sqlId, $params, $sql, false);
        if ($ret)
        {
            $ret = jqGridDB::fetch_object($sql, $fetchAll, $this->pdo);
            jqGridDB::closeCursor($sql);
        }
        return $ret;
    }
    protected function getStringForGroup($group, $prm)
    {
        $i_ = $this->I;
        $sopt = array(
            'eq' => "=",
            'ne' => "<>",
            'lt' => "<",
            'le' => "<=",
            'gt' => ">",
            'ge' => ">=",
            'bw' => " {$i_}LIKE ",
            'bn' => " NOT {$i_}LIKE ",
            'in' => ' IN ',
            'ni' => ' NOT IN',
            'ew' => " {$i_}LIKE ",
            'en' => " NOT {$i_}LIKE ",
            'cn' => " {$i_}LIKE ",
            'nc' => " NOT {$i_}LIKE ",
            'nu' => 'IS NULL',
            'nn' => 'IS NOT NULL'
        );
        $s = "(";
        if (isset($group['groups']) && is_array($group['groups']) && count($group['groups']) > 0)
        {
            for ($j = 0;$j < count($group['groups']);$j++)
            {
                if (strlen($s) > 1)
                {
                    $s .= " " . $group['groupOp'] . " ";
                }
                try
                {
                    $dat = $this->getStringForGroup($group['groups'][$j], $prm);
                    $s .= $dat[0];
                    $prm = $prm + $dat[1];
                }
                catch(Exception $e)
                {
                    echo $e->getMessage();
                }
            }
        }
        if (isset($group['rules']) && count($group['rules']) > 0)
        {
            try
            {
                foreach ($group['rules'] as $key => $val)
                {
                    if (strlen($s) > 1)
                    {
                        $s .= " " . $group['groupOp'] . " ";
                    }
                    $field = $val['field'];
                    $op = $val['op'];
                    $v = $val['data'];
                    if (strtolower($this->encoding) != 'utf-8')
                    {
                        $v = iconv("utf-8", $this->encoding . "//TRANSLIT", $v);
                    }
                    if ($op)
                    {
                        if (in_array($field, $this->datearray))
                        {
                            $v = jqGridUtils::parseDate($this->userdateformat, $v, $this->dbdateformat);
                        }
                        switch ($op)
                        {
                            case 'bw':
                            case 'bn':
                                $s .= $field . ' ' . $sopt[$op] . " ?";
                                $prm[] = "$v%";
                            break;
                            case 'ew':
                            case 'en':
                                $s .= $field . ' ' . $sopt[$op] . " ?";
                                $prm[] = "%$v";
                            break;
                            case 'cn':
                            case 'nc':
                                $s .= $field . ' ' . $sopt[$op] . " ?";
                                $prm[] = "%$v%";
                            break;
                            case 'in':
                            case 'ni':
                                $s .= $field . ' ' . $sopt[$op] . "( ?)";
                                $prm[] = $v;
                            break;
                            case 'nu':
                            case 'nn':
                                $s .= $field . ' ' . $sopt[$op] . " ";
                            break;
                            default:
                                $s .= $field . ' ' . $sopt[$op] . " ?";
                                $prm[] = $v;
                            break;
                        }
                    }
                }
            }
            catch(Exception $e)
            {
                echo $e->getMessage();
            }
        }
        $s .= ")";
        if ($s == "()")
        {
            return array(
                "",
                $prm
            );
        }
        else
        {
            return array(
                $s,
                $prm
            );
        }
    }
    protected function _buildSearch(array $prm = null, $str_filter = '')
    {
        $filters = ($str_filter && strlen($str_filter) > 0) ? $str_filter : jqGridUtils::GetParam($this->GridParams["filter"], "");
        $rules = "";
        if ($filters)
        {
            $count = 0;
            if (function_exists('json_decode') && strtolower(trim($this->encoding)) == "utf-8" && $count == 0)
            {
                $jsona = json_decode($filters, true);
            }
            else
            {
                $jsona = jqGridUtils::decode($filters);
            }
            if (is_array($jsona))
            {
                $gopr = $jsona['groupOp'];
                $rules[0]['data'] = 'dummy';
            }
        }
        else if (jqGridUtils::GetParam($this->GridParams['searchField'], ''))
        {
            $gopr = '';
            $rules[0]['field'] = jqGridUtils::GetParam($this->GridParams['searchField'], '');
            $rules[0]['op'] = jqGridUtils::GetParam($this->GridParams['searchOper'], '');
            $rules[0]['data'] = jqGridUtils::GetParam($this->GridParams['searchString'], '');
            $jsona = array();
            $jsona['groupOp'] = "AND";
            $jsona['rules'] = $rules;
            $jsona['groups'] = array();
        }
        $ret = array(
            "",
            $prm
        );
        if ($jsona)
        {
            if ($rules && count($rules) > 0)
            {
                if (!is_array($prm))
                {
                    $prm = array();
                }
                $ret = $this->getStringForGroup($jsona, $prm);
                if (count($ret[1]) == 0) $ret[1] = null;
            }
        }
        return $ret;
    }
    public function buildSearch($filter, $otype = 'str')
    {
        $ret = $this->_buildSearch(null, $filter);
        if ($otype === 'str')
        {
            $s2a = explode("?", $ret[0]);
            $csa = count($s2a);
            $s = "";
            for ($i = 0;$i < $csa - 1;$i++)
            {
                $s .= $s2a[$i] . " '" . $ret[1][$i] . "' ";
            }
            $s .= $s2a[$csa - 1];
            return $s;
        }
        return $ret;
    }
    protected function _setSQL()
    {
        $sqlId = false;
        if ($this->readFromXML == true && strlen($this->SelectCommand) > 0)
        {
            $sqlId = $this->getSqlElement($this->SelectCommand);
        }
        else if ($this->SelectCommand && strlen($this->SelectCommand) > 0)
        {
            $sqlId = $this->SelectCommand;
        }
        else if ($this->table && strlen($this->table) > 0)
        {
            if ($this->dbtype == 'mongodb')
            {
                $sqlId = $this->table;
            }
            else
            {
                $sqlId = "SELECT * FROM " . (string)$this->table;
            }
        }
        if ($this->dbtype == 'mongodb')
        {
            $sqlId = $this
                ->pdo
                ->selectCollection($sqlId);
        }
        return $sqlId;
    }
    public function getUserDate()
    {
        return $this->userdateformat;
    }
    public function setUserDate($newformat)
    {
        $this->userdateformat = $newformat;
    }
    public function getUserTime()
    {
        return $this->usertimeformat;
    }
    public function setUserTime($newformat)
    {
        $this->usertimeformat = $newformat;
    }
    public function getDbDate()
    {
        return $this->dbdateformat;
    }
    public function setDbDate($newformat)
    {
        $this->dbdateformat = $newformat;
    }
    public function getDbTime()
    {
        return $this->dbtimeformat;
    }
    public function setDbTime($newformat)
    {
        $this->dbtimeformat = $newformat;
    }
    public function getGridParams()
    {
        return $this->GridParams;
    }
    public function setGridParams($_aparams)
    {
        if (is_array($_aparams) && !empty($_aparams))
        {
            $this->GridParams = array_merge($this->GridParams, $_aparams);
        }
    }
    public function selectLimit($limsql = '', $nrows = - 1, $offset = - 1, array $params = null, $order = '', $sort = '')
    {
        $sql = null;
        $sqlId = strlen($limsql) > 0 ? $limsql : $this->_setSQL();
        if (!$sqlId) return false;
        $ret = $this->execute($sqlId, $params, $sql, true, $nrows, $offset, $order, $sort);
        if ($ret)
        {
            $ret = jqGridDB::fetch_object($sql, true, $this->pdo);
            jqGridDB::closeCursor($sql);
            return $ret;
        }
        else return $ret;
    }
    public function queryGrid(array $summary = null, array $params = null, $echo = true)
    {
        $sql = null;
        $sqlId = $this->_setSQL();
        if (!$sqlId) return false;
        $page = $this->GridParams['page'];
        $page = (int)jqGridUtils::GetParam($page, '1');
        $limit = $this->GridParams['rows'];
        $limit = (int)jqGridUtils::GetParam($limit, '20');
        $sidx = $this->GridParams['sort'];
        $sidx = jqGridUtils::GetParam($sidx, '');
        $sord = $this->GridParams['order'];
        $sord = jqGridUtils::GetParam($sord, '');
        $search = $this->GridParams['search'];
        $search = jqGridUtils::GetParam($search, 'false');
        $totalrows = jqGridUtils::GetParam($this->GridParams['totalrows'], '');
        $sord = preg_replace("/[^a-zA-Z0-9]/", "", $sord);
        $sidx = preg_replace("/[^a-zA-Z0-9. _,]/", "", $sidx);
        $performcount = true;
        $gridcnt = false;
        $gridsrearch = '1';
        if ($this->cacheCount)
        {
            $gridcnt = jqGridUtils::GetParam('grid_recs', false);
            $gridsrearch = jqGridUtils::GetParam('grid_search', '1');
            if ($gridcnt && (int)$gridcnt >= 0) $performcount = false;
        }
        if ($search == 'true')
        {
            if ($this->dbtype == 'mongodb')
            {
                $params = jqGridDB::_mongoSearch($params, $this->GridParams, $this->encoding, $this->datearray, $this->mongointegers);
            }
            else
            {
                $sGrid = $this->_buildSearch($params);
                if ($this->optimizeSearch === true || $this->dbtype == 'array')
                {
                    $whr = "";
                    if ($sGrid[0])
                    {
                        if (preg_match("/\s+WHERE\s+/is", $sqlId)) $whr = " AND " . $sGrid[0];
                        else $whr = " WHERE " . $sGrid[0];
                    }
                    $sqlId .= $whr;
                }
                else
                {
                    $whr = $sGrid[0] ? " WHERE " . $sGrid[0] : "";
                    $sqlId = "SELECT * FROM(" . $sqlId . ") gridsearch" . $whr;
                }
                $params = $sGrid[1];
                if ($this->cacheCount && $gridsrearch != "-1")
                {
                    $tmps = crc32($whr . "data" . implode(" ", $params));
                    if ($gridsrearch != $tmps)
                    {
                        $performcount = true;
                    }
                    $gridsrearch = $tmps;
                }
            }
        }
        else
        {
            if ($this->cacheCount && $gridsrearch != "-1")
            {
                if ($gridsrearch != '1')
                {
                    $performcount = true;
                }
            }
        }
        $performcount = $performcount && $this->performcount;
        if ($performcount)
        {
            if ($this->dbtype == 'mongodb')
            {
                $qryData = jqGridDB::_mongocount($sqlId, $params, $summary);
            }
            else
            {
                $qryData = $this->_getcount($sqlId, $params, $summary);
            }
            if (is_object($qryData))
            {
                if (!isset($qryData->count)) $qryData->count = null;
                if (!isset($qryData->COUNT)) $qryData->COUNT = null;
                $count = $qryData->COUNT ? $qryData->COUNT : ($qryData->count ? $qryData->count : 0);
            }
            else
            {
                $count = isset($qryData['COUNT']) ? $qryData['COUNT'] : 0;
            }
        }
        else
        {
            $count = $gridcnt;
        }
        if ($count > 0)
        {
            $total_pages = ceil($count / $limit);
        }
        else
        {
            $count = 0;
            $total_pages = 0;
            $page = 0;
        }
        if ($page > $total_pages) $page = $total_pages;
        $start = $limit * $page - $limit;
        if ($start < 0) $start = 0;
        if ($this->dbtype == 'sqlsrv' || $this->dbtype == 'odbcsqlsrv')
        {
            $difrec = abs($start - $count);
            if ($difrec < $limit)
            {
                $limit = $difrec;
            }
        }
        $result = new stdClass();
        if (is_array($summary))
        {
            if (is_array($qryData)) unset($qryData['COUNT']);
            else unset($qryData->COUNT, $qryData->count);
            foreach ($qryData as $k => $v)
            {
                if ($v == null) $v = 0;
                $result->userdata[$k] = $v;
            }
        }
        if ($this->cacheCount)
        {
            $result->userdata['grid_recs'] = $count;
            $result->userdata['grid_search'] = $gridsrearch;
            $result->userdata['outres'] = $performcount;
        }
        if ($this->userdata)
        {
            if (!isset($result->userdata)) $result->userdata = array();
            $result->userdata = jqGridUtils::array_extend($result->userdata, $this->userdata);
        }
        $result->records = $count;
        $result->page = $page;
        $result->total = $total_pages;
        $uselimit = true;
        if ($totalrows)
        {
            $totalrows = (int)$totalrows;
            if (is_int($totalrows))
            {
                if ($totalrows == - 1)
                {
                    $uselimit = false;
                }
                else if ($totalrows > 0)
                {
                    $limit = $totalrows;
                }
            }
        }
        if ($this->dbtype !== 'mongodb')
        {
            if ($sidx) $sqlId .= " ORDER BY " . $sidx . " " . $sord;
        }
        $ret = $this->execute($sqlId, $params, $sql, $uselimit, $limit, $start, $sidx, $sord);
        if ($ret)
        {
            $result->rows = jqGridDB::fetch_object($sql, true, $this->pdo);
            jqGridDB::closeCursor($sql);
            if ($this->customClass)
            {
                try
                {
                    $result = call_user_func(array(
                        $this->customClass,
                        $this->customFunc
                    ) , $result, $this->pdo);
                }
                catch(Exception $e)
                {
                    echo "Can not call the method class - " . $e->getMessage();
                }
            }
            else if (function_exists($this->customFunc))
            {
                $result = call_user_func($this->customFunc, $result, $this->pdo);
            }
            if ($echo)
            {
                $this->_gridResponse($result);
            }
            else
            {
                if ($this->debug) $this->debugout();
                return $result;
            }
        }
        else
        {
            echo "Could not execute query!!!";
        }
        if ($this->debug) $this->debugout();
    }
    public function getSqlQuery()
    {
        return $this->select;
    }
    public function getQueryParams()
    {
        return $this->params;
    }
    protected function _rs($params = null, $summary = null, $excel = false)
    {
        if ($this->ExportCommand && strlen($this->ExportCommand) > 0) $sqlId = $this->ExportCommand;
        else $sqlId = $this->_setSQL();
        if (!$sqlId) return false;
        $sidx = $this->GridParams['sort'];
        $sidx = jqGridUtils::GetParam($sidx, '');
        $sord = $this->GridParams['order'];
        $sord = jqGridUtils::GetParam($sord, '');
        $search = $this->GridParams['search'];
        $search = jqGridUtils::GetParam($search, 'false');
        $sord = preg_replace("/[^a-zA-Z0-9]/", "", $sord);
        $sidx = preg_replace("/[^a-zA-Z0-9. _,]/", "", $sidx);
        if ($search == 'true')
        {
            if ($this->dbtype == 'mongodb')
            {
                $params = jqGridDB::_mongoSearch($params, $this->GridParams, $this->encoding, $this->datearray, $this->mongointegers);
            }
            else
            {
                $sGrid = $this->_buildSearch($params);
                if ($this->dbtype == 'array')
                {
                    $whr = "";
                    if ($sGrid[0])
                    {
                        if (preg_match("/\s+WHERE\s+/is", $sqlId)) $whr = " AND " . $sGrid[0];
                        else $whr = " WHERE " . $sGrid[0];
                    }
                    $sqlId .= $whr;
                }
                else
                {
                    $whr = $sGrid[0] ? " WHERE " . $sGrid[0] : "";
                    $sqlId = "SELECT * FROM(" . $sqlId . ") gridsearch" . $whr;
                }
                $params = $sGrid[1];
            }
        }
        if ($this->dbtype !== 'mongodb')
        {
            if ($sidx) $sqlId .= " ORDER BY " . $sidx . " " . $sord;
        }
        if (!$excel && is_array($summary))
        {
            if ($this->dbtype == 'mongodb')
            {
                $qryData = jqGridDB::_mongocount($sqlId, $params, $summary);
            }
            else
            {
                $qryData = $this->_getcount($sqlId, $params, $summary);
            }
            unset($qryData->COUNT, $qryData->count);
            foreach ($qryData as $k => $v)
            {
                if ($v == null) $v = 0;
                $this->tmpvar[$k] = $v;
            }
        }
        if ($this->userdata)
        {
            if (!$this->tmpvar)
            {
                $this->tmpvar = array();
            }
            $this->tmpvar = jqGridUtils::array_extend($this->tmpvar, $this->userdata);
        }
        if ($this->debug)
        {
            $this->logQuery($sqlId, $params);
            $this->debugout();
        }
        $ret = $this->execute($sqlId, $params, $sql, true, $this->gSQLMaxRows, 0, $sidx, $sord);
        return $sql;
    }
    protected $PDF = array(
        "page_orientation" => "P",
        "unit" => "mm",
        "page_format" => "A4",
        "creator" => "jqGrid",
        "author" => "jqGrid",
        "title" => "jqGrid PDF",
        "subject" => "Subject",
        "keywords" => "table, grid",
        "margin_left" => 15,
        "margin_top" => 7,
        "margin_right" => 15,
        "margin_bottom" => 25,
        "margin_header" => 5,
        "margin_footer" => 10,
        "font_name_main" => "helvetica",
        "font_size_main" => 10,
        "header_logo" => "",
        "header_logo_width" => 0,
        "header_title" => "",
        "header_string" => "",
        "header" => false,
        "footer" => true,
        "font_monospaced" => "courier",
        "font_name_data" => "helvetica",
        "font_size_data" => 8,
        "image_scale_ratio" => 1.25,
        "grid_head_color" => "#dfeffc",
        "grid_head_text_color" => "#2e6e9e",
        "grid_draw_color" => "#5c9ccc",
        "grid_header_height" => 6,
        "grid_row_color" => "#ffffff",
        "grid_row_text_color" => "#000000",
        "grid_row_height" => 5,
        "grid_alternate_rows" => false,
        "path_to_pdf_class" => "tcpdf/tcpdf.php",
        "shrink_cell" => true,
        "reprint_grid_header" => false,
        "shrink_header" => true,
        "unicode" => true,
        "encoding" => "UTF-8",
        "destination" => "D"
    );
    public function setPdfOptions($apdf)
    {
        if (is_array($apdf) and count($apdf) > 0)
        {
            $this->PDF = jqGridUtils::array_extend($this->PDF, $apdf);
        }
    }
    protected function rs2pdf($rs, &$pdf, $colmodel = false, $summary = null)
    {
        $s = '';
        $rows = 0;
        $gSQLMaxRows = $this->gSQLMaxRows;
        if (!$rs)
        {
            printf('Bad Record set rs2pdf');
            return false;
        }
        $typearr = array();
        $ncols = jqGridDB::columnCount($rs);
        $model = false;
        $nmodel = is_array($colmodel) ? count($colmodel) : -1;
        if ($nmodel > 0)
        {
            for ($i = 0;$i < $nmodel;$i++)
            {
                if ($colmodel[$i]['name'] == 'actions')
                {
                    array_splice($colmodel, $i, 1);
                    $nmodel--;
                    break;
                }
            }
        }
        if ($colmodel && $nmodel == $ncols)
        {
            $model = true;
        }
        $ahidden = array();
        $aselect = array();
        $totw = 0;
        $pw = $pdf->getPageWidth();
        $margins = $pdf->getMargins();
        $pw = $pw - $margins['left'] - $margins['right'];
        for ($i = 0;$i < $ncols;$i++)
        {
            $ahidden[$i] = ($model && isset($colmodel[$i]["hidden"])) ? $colmodel[$i]["hidden"] : false;
            $colwidth[$i] = ($model && isset($colmodel[$i]["width"])) ? (int)$colmodel[$i]["width"] : 150;
            if ($ahidden[$i]) continue;
            $totw = $totw + $colwidth[$i];
        }
        $pd = $this->PDF;
        $pdf->SetLineWidth(0.2);
        $field = array();
        $fnmkeys = array();
        function printTHeader($ncols, $maxheigh, $awidth, $aname, $ahidden, $pdf, $pd)
        {
            $pdf->SetFillColorArray($pdf->convertHTMLColorToDec($pd['grid_head_color']));
            $pdf->SetTextColorArray($pdf->convertHTMLColorToDec($pd['grid_head_text_color']));
            $pdf->SetDrawColorArray($pdf->convertHTMLColorToDec($pd['grid_draw_color']));
            $pdf->SetFont('', 'B');
            for ($i = 0;$i < $ncols;$i++)
            {
                if ($ahidden[$i])
                {
                    continue;
                }
                if (!$pd['shrink_header'])
                {
                    $pdf->MultiCell($awidth[$i], $maxheigh, $aname[$i], 1, 'C', true, 0, '', '', true, 0, true, true, 0, 'B', false);
                }
                else
                {
                    $pdf->Cell($awidth[$i], $pd['grid_header_height'], $aname[$i], 1, 0, 'C', 1, '', 1);
                }
            }
        }
        $maxheigh = $pd['grid_header_height'];
        for ($i = 0;$i < $ncols;$i++)
        {
            $aselect[$i] = false;
            if ($model && isset($colmodel[$i]["formatter"]))
            {
                if ($colmodel[$i]["formatter"] == "select")
                {
                    $asl = isset($colmodel[$i]["formatoptions"]) ? $colmodel[$i]["formatoptions"] : $colmodel[$i]["editoptions"];
                    if (isset($asl["value"]))
                    {
                        $sep = isset($asl["separator"]) ? $asl["separator"] : ":";
                        $delim = isset($asl["delimiter"]) ? $asl["delimiter"] : ";
";
                        $list = explode($delim, $asl["value"]);
                        foreach ($list as $key => $val)
                        {
                            $items = explode($sep, $val);
                            $aselect[$i][$items[0]] = $items[1];
                        }
                    }
                }
            }
            $fnmkeys[$i] = "";
            if ($ahidden[$i])
            {
                continue;
            }
            if ($model)
            {
                $fname[$i] = isset($colmodel[$i]["label"]) ? $colmodel[$i]["label"] : $colmodel[$i]["name"];
                $typearr[$i] = isset($colmodel[$i]["sorttype"]) ? $colmodel[$i]["sorttype"] : '';
                $align[$i] = isset($colmodel[$i]["align"]) ? strtoupper(substr($colmodel[$i]["align"], 0, 1)) : "L";
            }
            else
            {
                $field = jqGridDB::getColumnMeta($i, $rs);
                $fname[$i] = $field["name"];
                $typearr[$i] = jqGridDB::MetaType($field, $this->dbtype);
                $align[$i] = "L";
            }
            $fname[$i] = htmlspecialchars($fname[$i]);
            $fnmkeys[$i] = $model ? $colmodel[$i]["name"] : $fname[$i];
            $colwidth[$i] = ($colwidth[$i] / $totw) * 100;
            $colwidth[$i] = ($pw / 100) * $colwidth[$i];
            if (strlen($fname[$i]) == 0) $fname[$i] = '';
            if (!$pd['shrink_header'])
            {
                $maxheigh = max($maxheigh, $pdf->getStringHeight($colwidth[$i], $fname[$i], false, true, '', 1));
            }
        }
        printTHeader($ncols, $maxheigh, $colwidth, $fname, $ahidden, $pdf, $pd);
        $pdf->Ln();
        if ($this->dbtype == 'mysqli')
        {
            $fld = $rs->field_count;
            $count = 1;
            $fieldnames[0] = & $rs;
            for ($i = 0;$i < $fld;$i++)
            {
                $fieldnames[$i + 1] = & $res_arr[$i];
            }
            call_user_func_array('mysqli_stmt_bind_result', $fieldnames);
        }
        $datefmt = $this->userdateformat;
        $timefmt = $this->usertimeformat;
        $pdf->SetFillColorArray($pdf->convertHTMLColorToDec($pd['grid_row_color']));
        $pdf->SetTextColorArray($pdf->convertHTMLColorToDec($pd['grid_row_text_color']));
        $pdf->SetFont('');
        $fill = false;
        if (!$pd['shrink_cell'])
        {
            $dimensions = $pdf->getPageDimensions();
        }
        while ($r = jqGridDB::fetch_num($rs, $this->pdo))
        {
            if ($this->dbtype == 'mysqli') $r = $res_arr;
            $varr = array();
            $maxh = $pd['grid_row_height'];
            for ($i = 0;$i < $ncols;$i++)
            {
                if (isset($ahidden[$i]) && $ahidden[$i]) continue;
                $v = $r[$i];
                if (is_array($aselect[$i]))
                {
                    if (isset($aselect[$i][$v]))
                    {
                        $v1 = $aselect[$i][$v];
                        if ($v1) $v = $v1;
                    }
                    $typearr[$i] = 'string';
                }
                $type = $typearr[$i];
                switch ($type)
                {
                    case 'date':
                        $v = $datefmt != $this->dbdateformat ? jqGridUtils::parseDate($this->dbdateformat, $v, $datefmt) : $v;
                    break;
                    case 'datetime':
                        $v = $timefmt != $this->dbtimeformat ? jqGridUtils::parseDate($this->dbtimeformat, $v, $timefmt) : $v;
                    break;
                    case 'numeric':
                    case 'int':
                        $v = trim($v);
                    break;
                    default:
                        $v = trim($v);
                        if (strlen($v) == 0) $v = '';
                    }
                    if (!$pd['shrink_cell'])
                    {
                        $varr[$i] = $v;
                        $maxh = max($maxh, $pdf->getStringHeight($colwidth[$i], $v, false, true, '', 1));
                    }
                    else
                    {
                        $pdf->Cell($colwidth[$i], $pd['grid_row_height'], $v, 1, 0, $align[$i], $fill, '', 1);
                    }
            }
            if (!$pd['shrink_cell'])
            {
                $startY = $pdf->GetY();
                if (($startY + $maxh) + $dimensions['bm'] > ($dimensions['hk']))
                {
                    $pdf->AddPage();
                    if ($pd['reprint_grid_header'])
                    {
                        printTHeader($ncols, $maxheigh, $colwidth, $fname, $ahidden, $pdf, $pd);
                        $pdf->Ln();
                        $pdf->SetFillColorArray($pdf->convertHTMLColorToDec($pd['grid_row_color']));
                        $pdf->SetTextColorArray($pdf->convertHTMLColorToDec($pd['grid_row_text_color']));
                        $pdf->SetFont('');
                    }
                }
                for ($i = 0;$i < $ncols;$i++)
                {
                    if (isset($ahidden[$i]) && $ahidden[$i]) continue;
                    $pdf->MultiCell($colwidth[$i], $maxh, $varr[$i], 1, $align[$i], $fill, 0, '', '', true, 0, true, true, 0, 'T', false);
                }
            }
            if ($pd['grid_alternate_rows'])
            {
                $fill = !$fill;
            }
            $pdf->Ln();
            $rows += 1;
            if ($rows >= $gSQLMaxRows)
            {
                break;
            }
        }
        if ($this->tmpvar)
        {
            $pdf->SetFont('', 'B');
            for ($i = 0;$i < $ncols;$i++)
            {
                if (isset($ahidden[$i]) && $ahidden[$i]) continue;
                $vv = '';
                foreach ($this->tmpvar as $key => $v)
                {
                    if ($fnmkeys[$i] == $key)
                    {
                        $vv = $v;
                        break;
                    }
                }
                $pdf->Cell($colwidth[$i], $pd['grid_row_height'], $vv, 1, 0, $align[$i], $fill, '', 1);
            }
        }
    }
    private function rs2csv($rs, $colmodel, $sep = ';
', $sepreplace = ' ', $echo = true, $filename = 'exportdata.csv', $addtitles = true, $quote = '"', $escquote = '"', $replaceNewLine = ' ')
    {
        if (!$rs) return '';
        $NEWLINE = "\r\n";
        $escquotequote = $escquote . $quote;
        $gSQLMaxRows = $this->gSQLMaxRows;
        $s = '';
        $ncols = jqGridDB::columnCount($rs);
        $model = false;
        $nmodel = is_array($colmodel) ? count($colmodel) : -1;
        if ($nmodel > 0)
        {
            for ($i = 0;$i < $nmodel;$i++)
            {
                if ($colmodel[$i]['name'] == 'actions')
                {
                    array_splice($colmodel, $i, 1);
                    $nmodel--;
                    break;
                }
            }
        }
        if ($colmodel && $nmodel == $ncols)
        {
            $model = true;
        }
        $fnames = array();
        for ($i = 0;$i < $ncols;$i++)
        {
            if ($model)
            {
                $fname = isset($colmodel[$i]["label"]) ? $colmodel[$i]["label"] : $colmodel[$i]["name"];
                $field["name"] = $colmodel[$i]["name"];
                $typearr[$i] = isset($colmodel[$i]["sorttype"]) ? $colmodel[$i]["sorttype"] : '';
            }
            else
            {
                $field = jqGridDB::getColumnMeta($i, $rs);
                $fname = $field["name"];
                $typearr[$i] = jqGridDB::MetaType($field, $this->dbtype);
            }
            $fnames[$i] = $field["name"];
            $v = $fname;
            if ($escquote) $v = str_replace($quote, $escquotequote, $v);
            $v = strip_tags(str_replace("\n", $replaceNewLine, str_replace("\r\n", $replaceNewLine, str_replace($sep, $sepreplace, $v))));
            $ahidden[$i] = ($model && isset($colmodel[$i]["hidden"])) ? $colmodel[$i]["hidden"] : false;
            if (!$ahidden[$i]) $elements[] = $v;
            $aselect[$i] = false;
            if ($model && isset($colmodel[$i]["formatter"]))
            {
                if ($colmodel[$i]["formatter"] == "select")
                {
                    $asl = isset($colmodel[$i]["formatoptions"]) ? $colmodel[$i]["formatoptions"] : $colmodel[$i]["editoptions"];
                    if (isset($asl["value"]))
                    {
                        $sep2 = isset($asl["separator"]) ? $asl["separator"] : ":";
                        $delim = isset($asl["delimiter"]) ? $asl["delimiter"] : ";
";
                        $list = explode($delim, $asl["value"]);
                        foreach ($list as $key => $val)
                        {
                            $items = explode($sep2, $val);
                            $aselect[$i][$items[0]] = $items[1];
                        }
                    }
                }
            }
        }
        if ($addtitles)
        {
            $s .= implode($sep, $elements) . $NEWLINE;
        }
        $datefmt = $this->userdateformat;
        $timefmt = $this->usertimeformat;
        if ($this->dbtype == 'mysqli')
        {
            $fld = $rs->field_count;
            $count = 1;
            $fieldnames[0] = & $rs;
            for ($i = 0;$i < $fld;$i++)
            {
                $fieldnames[$i + 1] = & $res_arr[$i];
            }
            call_user_func_array('mysqli_stmt_bind_result', $fieldnames);
        }
        if ($echo)
        {
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: private");
            header("Content-type: application/octet-stream");
            header("Content-Disposition: attachment;
 filename=\"" . $filename . "\"");
            header("Accept-Ranges: bytes");
        }
        $line = 0;
        while ($r = jqGridDB::fetch_num($rs, $this->pdo))
        {
            if ($this->dbtype == 'mysqli') $r = $res_arr;
            $elements = array();
            $i = 0;
            for ($i = 0;$i < $ncols;$i++)
            {
                if (isset($ahidden[$i]) && $ahidden[$i]) continue;
                $v = $r[$i];
                if (is_array($aselect[$i]))
                {
                    if (isset($aselect[$i][$v]))
                    {
                        $v1 = $aselect[$i][$v];
                        if ($v1) $v = $v1;
                    }
                    $typearr[$i] = 'string';
                }
                $type = $typearr[$i];
                switch ($type)
                {
                    case 'date':
                        $v = $datefmt != $this->dbdateformat ? jqGridUtils::parseDate($this->dbdateformat, $v, $datefmt) : $v;
                    break;
                    case 'datetime':
                        $v = $timefmt != $this->dbtimeformat ? jqGridUtils::parseDate($this->dbtimeformat, $v, $timefmt) : $v;
                    break;
                    case 'numeric':
                    case 'int':
                        $v = trim($v);
                    break;
                    default:
                        $v = trim($v);
                        if (strlen($v) == 0) $v = '';
                    }
                    if ($escquote) $v = str_replace($quote, $escquotequote, trim($v));
                    $v = strip_tags(str_replace("\n", $replaceNewLine, str_replace("\r\n", $replaceNewLine, str_replace($sep, $sepreplace, $v))));
                    if (strpos($v, $sep) !== false || strpos($v, $quote) !== false) $elements[] = "$quote$v$quote";
                    else $elements[] = $v;
                }
                $s .= implode($sep, $elements) . $NEWLINE;
                $line += 1;
                if ($echo)
                {
                    if ($echo === true) echo $s;
                    $s = '';
                }
                if ($line >= $gSQLMaxRows)
                {
                    break;
                }
            }
            if ($echo)
            {
                if ($echo === true) echo $s;
                $s = '';
            }
            if ($this->tmpvar)
            {
                $elements = array();
                for ($i = 0;$i < $ncols;$i++)
                {
                    if (isset($ahidden[$i]) && $ahidden[$i]) continue;
                    foreach ($this->tmpvar as $key => $vv)
                    {
                        if ($fnames[$i] == $key)
                        {
                            $v = $vv;
                            break;
                        }
                        else
                        {
                            $v = '';
                        }
                    }
                    if ($escquote) $v = str_replace($quote, $escquotequote, trim($v));
                    $v = strip_tags(str_replace("\n", $replaceNewLine, str_replace("\r\n", $replaceNewLine, str_replace($sep, $sepreplace, $v))));
                    if (strpos($v, $sep) !== false || strpos($v, $quote) !== false) $elements[] = "$quote$v$quote";
                    else $elements[] = $v;
                }
                $s .= implode($sep, $elements) . $NEWLINE;
                if ($echo)
                {
                    if ($echo === true) echo $s;
                    $s = '';
                }
            }
            return $s;
        }
        public function exportToCsv(array $summary = null, array $params = null, array $colmodel = null, $echo = true, $filename = 'exportdata.csv', $sep = ';
', $sepreplace = ' ')
        {
            $sql = null;
            $sql = $this->_rs($params, $summary, false);
            if ($sql)
            {
                $ret = $this->rs2csv($sql, $colmodel, $sep, $sepreplace, $echo, $filename);
                jqGridDB::closeCursor($sql);
                return $ret;
            }
            else return "Error:Could not execute the query";
        }
        protected function rs2excel($rs, $colmodel = false, $echo = true, $filename = 'exportdata.xls', $summary = false)
        {
            $s = '';
            $rows = 0;
            $gSQLMaxRows = $this->gSQLMaxRows;
            if (!$rs)
            {
                printf('Bad Record set rs2excel');
                return false;
            }
            $typearr = array();
            $ncols = jqGridDB::columnCount($rs);
            $hdr = '<?xml version="1.0" encoding="' . $this->encoding . '"?>';
            $hdr .= '<?mso-application progid="Excel.Sheet"?>';
            $hdr .= '<ss:Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:html="http://www.w3.org/TR/REC-html40">';
            $hdr .= '<ss:Styles>' . '<ss:Style ss:ID="1"><ss:Font ss:Bold="1"/></ss:Style>' . '<ss:Style ss:ID="sd"><NumberFormat ss:Format="Short Date"/></ss:Style>' . '<ss:Style ss:ID="ld"><NumberFormat ss:Format="General Date"/></ss:Style>' . '<ss:Style ss:ID="nmb"><NumberFormat ss:Format="Standard"/></ss:Style>' . '<ss:Style ss:ID="intnm"><NumberFormat ss:Format="General Number"/></ss:Style>' . '</ss:Styles>';
            $hdr .= '<ss:Worksheet ss:Name="Sheet1">';
            $hdr .= '<ss:Table>';
            $nmodel = is_array($colmodel) ? count($colmodel) : -1;
            if ($nmodel > 0)
            {
                for ($i = 0;$i < $nmodel;$i++)
                {
                    if ($colmodel[$i]['name'] == 'actions')
                    {
                        array_splice($colmodel, $i, 1);
                        $nmodel--;
                        break;
                    }
                }
            }
            $model = false;
            if ($colmodel && $nmodel == $ncols)
            {
                $model = true;
            }
            $hdr1 = '<ss:Row ss:StyleID="1">';
            $aSum = array();
            $aFormula = array();
            $ahidden = array();
            $aselect = array();
            $hiddencount = 0;
            for ($i = 0;$i < $ncols;$i++)
            {
                $ahidden[$i] = ($model && isset($colmodel[$i]["hidden"])) ? $colmodel[$i]["hidden"] : false;
                $aselect[$i] = false;
                if ($model && isset($colmodel[$i]["formatter"]))
                {
                    if ($colmodel[$i]["formatter"] == "select")
                    {
                        $asl = isset($colmodel[$i]["formatoptions"]) ? $colmodel[$i]["formatoptions"] : $colmodel[$i]["editoptions"];
                        if (isset($asl["value"]))
                        {
                            $sep = isset($asl["separator"]) ? $asl["separator"] : ":";
                            $delim = isset($asl["delimiter"]) ? $asl["delimiter"] : ";
";
                            $list = explode($delim, $asl["value"]);
                            foreach ($list as $key => $val)
                            {
                                $items = explode($sep, $val);
                                $aselect[$i][$items[0]] = $items[1];
                            }
                        }
                    }
                }
                if ($ahidden[$i])
                {
                    $hiddencount++;
                    continue;
                }
                $column = ($model && isset($colmodel[$i]["width"])) ? (int)$colmodel[$i]["width"] : 0;
                if ($column > 0)
                {
                    $column = $column * 72 / 96;
                    $hdr .= '<ss:Column ss:Width="' . $column . '"/>';
                }
                else $hdr .= '<ss:Column ss:AutoFitWidth="1"/>';
                $field = array();
                if ($model)
                {
                    $fname = isset($colmodel[$i]["label"]) ? $colmodel[$i]["label"] : $colmodel[$i]["name"];
                    $field["name"] = $colmodel[$i]["name"];
                    $typearr[$i] = isset($colmodel[$i]["sorttype"]) ? $colmodel[$i]["sorttype"] : '';
                }
                else
                {
                    $field = jqGridDB::getColumnMeta($i, $rs);
                    $fname = $field["name"];
                    $typearr[$i] = jqGridDB::MetaType($field, $this->dbtype);
                }
                if ($summary && is_array($summary))
                {
                    foreach ($summary as $key => $val)
                    {
                        if (is_array($val))
                        {
                            foreach ($val as $fld => $formula)
                            {
                                if ($field["name"] == $key)
                                {
                                    $aSum[] = $i - $hiddencount;
                                    $aFormula[] = $formula;
                                }
                            }
                        }
                        else
                        {
                            if ($field["name"] == $key)
                            {
                                $aSum[] = $i - $hiddencount;
                                $aFormula[] = "SUM";
                            }
                        }
                    }
                }
                $fname = htmlspecialchars($fname);
                if (strlen($fname) == 0) $fname = '';
                $hdr1 .= '<ss:Cell><ss:Data ss:Type="String">' . $fname . '</ss:Data></ss:Cell>';
            }
            $hdr1 .= '</ss:Row>';
            if (!$echo) $html = $hdr . $hdr1;
            if ($this->dbtype == 'mysqli')
            {
                $fld = $rs->field_count;
                $count = 1;
                $fieldnames[0] = & $rs;
                for ($i = 0;$i < $fld;$i++)
                {
                    $fieldnames[$i + 1] = & $res_arr[$i];
                }
                call_user_func_array('mysqli_stmt_bind_result', $fieldnames);
            }
            while ($r = jqGridDB::fetch_num($rs, $this->pdo))
            {
                if ($this->dbtype == 'mysqli') $r = $res_arr;
                $s .= '<ss:Row>';
                for ($i = 0;$i < $ncols;$i++)
                {
                    if (isset($ahidden[$i]) && $ahidden[$i]) continue;
                    $v = $r[$i];
                    if (is_array($aselect[$i]))
                    {
                        if (isset($aselect[$i][$v]))
                        {
                            $v1 = $aselect[$i][$v];
                            if ($v1) $v = $v1;
                        }
                        $typearr[$i] = 'string';
                    }
                    switch ($typearr[$i])
                    {
                        case 'date':
                            if (substr($v, 0, 4) == '0000' || empty($v) || $v == 'NULL')
                            {
                                $v = '1899-12-31T00:00:00.000';
                                $s .= '<ss:Cell ss:StyleID="sd"><ss:Data ss:Type="DateTime">' . $v . '</ss:Data></ss:Cell>';
                            }
                            else if (!strpos($v, ':'))
                            {
                                $v .= "T00:00:00.000";
                                $s .= '<ss:Cell ss:StyleID="sd"><ss:Data ss:Type="DateTime">' . $v . '</ss:Data></ss:Cell>';
                            }
                            else
                            {
                                $thous = substr($v, -4);
                                if (strpos($thous, ".") === false && strpos($v, ".") === false) $v .= ".000";
                                $s .= '<ss:Cell ss:StyleID="sd"><ss:Data ss:Type="DateTime">' . str_replace(" ", "T", trim($v)) . '</ss:Data></ss:Cell>';
                            }
                            break;
                        case 'datetime':
                            if (substr($v, 0, 4) == '0000' || empty($v) || $v == 'NULL')
                            {
                                $v = '1899-12-31T00:00:00.000';
                                $s .= '<ss:Cell ss:StyleID="ld"><ss:Data ss:Type="DateTime">' . $v . '</ss:Data></ss:Cell>';
                            }
                            else
                            {
                                $thous = substr($v, -4);
                                if (strpos($thous, ".") === false && strpos($v, ".") === false) $v .= ".000";
                                $s .= '<ss:Cell ss:StyleID="ld"><ss:Data ss:Type="DateTime">' . str_replace(" ", "T", trim($v)) . '</ss:Data></ss:Cell>';
                            }
                            break;
                        case 'int':
                            $s .= '<ss:Cell ss:StyleID="intnm"><ss:Data ss:Type="Number">' . stripslashes((trim($v))) . '</ss:Data></ss:Cell>';
                            break;
                        case 'numeric':
                            $s .= '<ss:Cell ss:StyleID="nmb"><ss:Data ss:Type="Number">' . stripslashes((trim($v))) . '</ss:Data></ss:Cell>';
                            break;
                        default:
                            $v = htmlspecialchars(trim($v));
                            if (strlen($v) == 0) $v = '';
                            $s .= '<ss:Cell><ss:Data ss:Type="String">' . stripslashes($v) . '</ss:Data></ss:Cell>';
                        }
                    }
                    $s .= '</ss:Row>';
                    $rows += 1;
                    if ($rows >= $gSQLMaxRows)
                    {
                        break;
                    }
                }
                if (count($aSum) > 0 && $rows > 0)
                {
                    $s .= '<Row>';
                    foreach ($aSum as $ind => $ival)
                    {
                        $s .= '<Cell ss:StyleID="1" ss:Index="' . ($ival + 1) . '" ss:Formula="=' . $aFormula[$ind] . '(R[-' . ($rows) . ']C:R[-1]C)"><Data ss:Type="Number"></Data></Cell>';
                    }
                    $s .= '</Row>';
                }
                if ($echo)
                {
                    header('Content-Type: application/ms-excel;
');
                    header("Content-Disposition: attachment;
 filename=" . $filename);
                    echo $hdr . $hdr1;
                    echo $s . '</ss:Table></ss:Worksheet></ss:Workbook>';
                }
                else
                {
                    $html .= $s . '</ss:Table></ss:Worksheet></ss:Workbook>';
                    return $html;
                }
            }
            public function querySubGrid($params, $echo = true)
            {
                if ($this->SubgridCommand && strlen($this->SubgridCommand) > 0)
                {
                    $result = new stdClass();
                    $result->rows = $this->queryForObject($this->SubgridCommand, $params, true);
                    if ($echo) $this->_gridResponse($result);
                    else return $result;
                }
            }
            protected function _gridResponse($response)
            {
                if ($this->dataType == "xml")
                {
                    if (isset($response->records))
                    {
                        $response->rows["records"] = $response->records;
                        unset($response->records);
                    }
                    if (isset($response->total))
                    {
                        $response->rows["total"] = $response->total;
                        unset($response->total);
                    }
                    if (isset($response->page))
                    {
                        $response->rows["page"] = $response->page;
                        unset($response->page);
                    }
                    if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml"))
                    {
                        header("Content-type: application/xhtml+xml;
charset=", $this->encoding);
                    }
                    else
                    {
                        header("Content-type: text/xml;
charset=" . $this->encoding);
                    }
                    echo jqGridUtils::toXml($response, 'root', null, $this->encoding, $this->xmlCDATA);
                }
                else if ($this->dataType == "json")
                {
                    header("Content-type: text/x-json;
charset=" . $this->encoding);
                    if (function_exists('json_encode') && strtolower($this->encoding) == 'utf-8')
                    {
                        echo json_encode($response);
                    }
                    else
                    {
                        echo jqGridUtils::encode($response);
                    }
                }
            }
            public function addUserData($adata)
            {
                if (is_array($adata)) $this->userdata = $adata;
            }
        }
        class jqGridEdit extends jqGrid
        {
            protected $fields = array();
            protected $successmsg = "";
            public function setSuccessMsg($msg)
            {
                if ($msg)
                {
                    $this->successmsg = $msg;
                }
            }
            public $serialKey = true;
            public $getLastInsert = false;
            protected $lastId = null;
            public $trans = true;
            public $add = true;
            public $edit = true;
            public $del = true;
            public $mtype = "POST";
            public $decodeinput = false;
            public function getPrimaryKeyId()
            {
                return $this->primaryKey;
            }
            public function setPrimaryKeyId($keyid)
            {
                $this->primaryKey = $keyid;
            }
            public function setTable($_newtable)
            {
                $this->table = $_newtable;
            }
            protected function _buildFields()
            {
                $result = false;
                if (strlen(trim($this->table)) > 0)
                {
                    $wh = ($this->dbtype == 'sqlite') ? "" : " WHERE 1=2";
                    $sql = "SELECT * FROM " . $this->table . $wh;
                    if ($this->debug)
                    {
                        $this->logQuery($sql);
                        $this->debugout();
                    }
                    try
                    {
                        $select = jqGridDB::query($this->pdo, $sql);
                        if ($select)
                        {
                            $colcount = jqGridDB::columnCount($select);
                            $this->fields = array();
                            for ($i = 0;$i < $colcount;$i++)
                            {
                                $meta = jqGridDB::getColumnMeta($i, $select);
                                $type = jqGridDB::MetaType($meta, $this->dbtype);
                                $this->fields[$meta['name']] = array(
                                    'type' => $type
                                );
                            }
                            jqGridDB::closeCursor($select);
                            $result = true;
                        }
                        else
                        {
                            $this->errorMessage = jqGridDB::errorMessage($this->pdo);
                            throw new Exception($this->errorMessage);
                        }
                    }
                    catch(Exception $e)
                    {
                        $result = false;
                        if (!$this->errorMessage) $this->errorMessage = $e->getMessage();
                    }
                }
                else
                {
                    $this->errorMessage = "No database table is set to operate!";
                }
                if ($this->showError && !$result)
                {
                    $this->sendErrorHeader();
                }
                return $result;
            }
            protected $_addarray = array();
            protected $_addarrayb = array();
            protected $_editarray = array();
            protected $_editarrayb = array();
            protected $_delarray = array();
            protected $_delarrayb = array();
            protected function _actionsCRUDGrid($oper, $event)
            {
                $ret = true;
                switch ($oper)
                {
                    case 'add':
                        if ($event == 'before')
                        {
                            $ar = $this->_addarrayb;
                        }
                        else
                        {
                            $ar = $this->_addarray;
                        }
                        $acnt = count($ar);
                        if ($acnt > 0)
                        {
                            for ($i = 0;$i < $acnt;$i++)
                            {
                                if ($this->debug) $this->logQuery($ar[$i]['sql'], $ar[$i]['params']);
                                $stmt = jqGridDB::prepare($this->pdo, $ar[$i]['sql'], $ar[$i]['params']);
                                $result = jqGridDB::execute($stmt, $ar[$i]['params'], $this->pdo);
                                if (!$result)
                                {
                                    $ret = false;
                                    break;
                                }
                                jqGridDB::closeCursor($this->dbtype == "adodb" ? $result : $stmt);
                            }
                        }
                        break;
                    case 'edit':
                        if ($event == 'before')
                        {
                            $ar = $this->_editarrayb;
                        }
                        else
                        {
                            $ar = $this->_editarray;
                        }
                        $acnt = count($ar);
                        if ($acnt > 0)
                        {
                            for ($i = 0;$i < $acnt;$i++)
                            {
                                if ($this->debug) $this->logQuery($ar[$i]['sql'], $ar[$i]['params']);
                                $stmt = jqGridDB::prepare($this->pdo, $ar[$i]['sql'], $ar[$i]['params']);
                                $result = jqGridDB::execute($stmt, $ar[$i]['params'], $this->pdo);
                                if (!$result)
                                {
                                    $ret = false;
                                    break;
                                }
                                jqGridDB::closeCursor($this->dbtype == "adodb" ? $result : $stmt);
                            }
                        }
                        break;
                    case 'del':
                        if ($event == 'before')
                        {
                            $ar = $this->_delarrayb;
                        }
                        else
                        {
                            $ar = $this->_delarray;
                        }
                        $acnt = count($ar);
                        if ($acnt > 0)
                        {
                            for ($i = 0;$i < $acnt;$i++)
                            {
                                if ($this->debug) $this->logQuery($ar[$i]['sql'], $ar[$i]['params']);
                                $stmt = jqGridDB::prepare($this->pdo, $ar[$i]['sql'], $ar[$i]['params']);
                                $result = $stmt ? jqGridDB::execute($stmt, $ar[$i]['params'], $this->pdo) : false;
                                if (!$result)
                                {
                                    $ret = false;
                                    break;
                                }
                                jqGridDB::closeCursor($this->dbtype == "adodb" ? $result : $stmt);
                            }
                        }
                        break;
                    }
                    return $ret;
                }
                public function setBeforeCrudAction($oper, $sql, $params = null)
                {
                    switch ($oper)
                    {
                        case 'add':
                            $this->_addarrayb[] = array(
                                "sql" => $sql,
                                "params" => $params
                            );
                        break;
                        case 'edit':
                            $this->_editarrayb[] = array(
                                "sql" => $sql,
                                "params" => $params
                            );
                        break;
                        case 'del':
                            $this->_delarrayb[] = array(
                                "sql" => $sql,
                                "params" => $params
                            );
                        break;
                    }
                }
                public function setAfterCrudAction($oper, $sql, $params = null)
                {
                    switch ($oper)
                    {
                        case 'add':
                            $this->_addarray[] = array(
                                "sql" => $sql,
                                "params" => $params
                            );
                        break;
                        case 'edit':
                            $this->_editarray[] = array(
                                "sql" => $sql,
                                "params" => $params
                            );
                        break;
                        case 'del':
                            $this->_delarray[] = array(
                                "sql" => $sql,
                                "params" => $params
                            );
                        break;
                    }
                }
                public function getFields()
                {
                    return $this->fields;
                }
                public function insert($data)
                {
                    if (!$this->add) return false;
                    if (!$this->_buildFields())
                    {
                        return false;
                    }
                    if (!$this->checkPrimary())
                    {
                        return false;
                    }
                    $datefmt = $this->userdateformat;
                    $timefmt = $this->usertimeformat;
                    if ($this->serialKey) unset($data[$this->getPrimaryKeyId() ]);
                    $tableFields = array_keys($this->fields);
                    $rowFields = array_intersect($tableFields, array_keys($data));
                    $insertFields = array();
                    $binds = array();
                    $types = array();
                    $v = '';
                    foreach ($rowFields as $key => $val)
                    {
                        $insertFields[] = "?";
                        $t = $this->fields[$val]["type"];
                        $value = $data[$val];
                        if (strtolower($this->encoding) != 'utf-8')
                        {
                            $value = iconv("utf-8", $this->encoding . "//TRANSLIT", $value);
                        }
                        if (strtolower($value) == 'null')
                        {
                            $v = NULL;
                        }
                        else if (trim($value) == "")
                        {
                            $v = $value;
                        }
                        else
                        {
                            switch ($t)
                            {
                                case 'date':
                                    $v = $datefmt != $this->dbdateformat ? jqGridUtils::parseDate($datefmt, $value, $this->dbdateformat) : $value;
                                break;
                                case 'datetime':
                                    $v = $timefmt != $this->dbtimeformat ? jqGridUtils::parseDate($timefmt, $value, $this->dbtimeformat) : $value;
                                break;
                                case 'time':
                                    $v = jqGridUtils::parseDate($timefmt, $value, 'H:i:s');
                                break;
                                default:
                                    $v = $value;
                            }
                            if ($this->decodeinput) $v = htmlspecialchars_decode($v);
                        }
                        $types[] = $t;
                        $binds[] = $v;
                        unset($v);
                    }
                    $result = false;
                    if (count($insertFields) > 0)
                    {
                        $sql = "INSERT INTO " . $this->table . "(" . implode(', ', $rowFields) . ")" . " VALUES( " . implode(', ', $insertFields) . ")";
                        $stmt = $this->parseSql($sql, $binds, false);
                        if ($stmt)
                        {
                            jqGridDB::bindValues($stmt, $binds, $types);
                            if ($this->trans)
                            {
                                try
                                {
                                    jqGridDB::beginTransaction($this->pdo);
                                    $result = $this->_actionsCRUDGrid('add', 'before');
                                    if ($this->debug) $this->logQuery($sql, $binds, $types, $data, $this->fields, $this->primaryKey);
                                    $ret = false;
                                    if ($result)
                                    {
                                        $ret = jqGridDB::execute($stmt, $binds, $this->pdo);
                                    }
                                    $result = $ret ? true : false;
                                    if ($result)
                                    {
                                        if ($this->serialKey && $this->getLastInsert)
                                        {
                                            $this->lastId = jqGridDB::lastInsertId($this->pdo, $this->table, $this->primaryKey, $this->dbtype);
                                            if (!is_numeric($this->lastId))
                                            {
                                                $result = false;
                                            }
                                        }
                                    }
                                    if ($result)
                                    {
                                        $saver = $this->showError;
                                        $this->showError = false;
                                        $result = $this->_actionsCRUDGrid('add', 'after');
                                        $this->showError = $saver;
                                    }
                                    if ($result)
                                    {
                                        $result = jqGridDB::commit($this->pdo);
                                    }
                                    jqGridDB::closeCursor($this->dbtype == "adodb" ? $ret : $stmt);
                                    if (!$result)
                                    {
                                        $this->errorMessage = jqGridDB::errorMessage($this->pdo);
                                        throw new Exception($this->errorMessage);
                                    }
                                }
                                catch(Exception $e)
                                {
                                    jqGridDB::rollBack($this->pdo);
                                    $result = false;
                                    if (!$this->errorMessage) $this->errorMessage = $e->getMessage();
                                }
                            }
                            else
                            {
                                try
                                {
                                    $result = $this->_actionsCRUDGrid('add', 'before');
                                    if ($this->debug) $this->logQuery($sql, $binds, $types, $data, $this->fields, $this->primaryKey);
                                    $ret = false;
                                    if ($result)
                                    {
                                        $ret = jqGridDB::execute($stmt, $binds, $this->pdo);
                                    }
                                    $result = $ret ? true : false;
                                    jqGridDB::closeCursor($this->dbtype == "adodb" ? $ret : $stmt);
                                    if ($this->serialKey && $this->getLastInsert && $result)
                                    {
                                        $this->lastId = jqGridDB::lastInsertId($this->pdo, $this->table, $this->primaryKey, $this->dbtype);
                                        if (!is_numeric($this->lastId))
                                        {
                                            $result = false;
                                        }
                                    }
                                    if ($result) $result = $this->_actionsCRUDGrid('add', 'after');
                                    if (!$result)
                                    {
                                        $this->errorMessage = jqGridDB::errorMessage($this->pdo);
                                        throw new Exception($this->errorMessage);
                                    }
                                }
                                catch(Exception $e)
                                {
                                    $result = false;
                                    if (!$this->errorMessage) $this->errorMessage = $e->getMessage();
                                }
                            }
                        }
                        else
                        {
                            $this->errorMessage = "Error when preparing a INSERT statement!";
                            $result = false;
                        }
                    }
                    else
                    {
                        $this->errorMessage = "Data posted does not match insert fields!";
                        $result = false;
                    }
                    if ($this->debug) $this->debugout();
                    if ($this->showError && !$result)
                    {
                        $this->sendErrorHeader();
                    }
                    return $result;
                }
                public function update($data)
                {
                    if (!$this->edit) return false;
                    if (!$this->_buildFields())
                    {
                        return false;
                    }
                    if (!$this->checkPrimary())
                    {
                        return false;
                    }
                    $datefmt = $this->userdateformat;
                    $timefmt = $this->usertimeformat;
                    $custom = false;
                    $tableFields = array_keys($this->fields);
                    $rowFields = array_intersect($tableFields, array_keys($data));
                    $updateFields = array();
                    $binds = array();
                    $types = array();
                    $pk = $this->getPrimaryKeyId();
                    foreach ($rowFields as $key => $field)
                    {
                        $t = $this->fields[$field]["type"];
                        $value = $data[$field];
                        if (strtolower($this->encoding) != 'utf-8')
                        {
                            $value = iconv("utf-8", $this->encoding . "//TRANSLIT", $value);
                        }
                        if (strtolower($value) == 'null')
                        {
                            $v = NULL;
                        }
                        else if (trim($value) == "")
                        {
                            $v = $value;
                        }
                        else
                        {
                            switch ($t)
                            {
                                case 'date':
                                    $v = $datefmt != $this->dbdateformat ? jqGridUtils::parseDate($datefmt, $value, $this->dbdateformat) : $value;
                                break;
                                case 'datetime':
                                    $v = $timefmt != $this->dbtimeformat ? jqGridUtils::parseDate($timefmt, $value, $this->dbtimeformat) : $value;
                                break;
                                case 'time':
                                    $v = jqGridUtils::parseDate($timefmt, $value, 'H:i:s');
                                break;
                                default:
                                    $v = $value;
                            }
                            if ($this->decodeinput) $v = htmlspecialchars_decode($v);
                        }
                        if ($field != $pk)
                        {
                            $updateFields[] = $field . " = ?";
                            $binds[] = $v;
                            $types[] = $t;
                        }
                        else if ($field == $pk)
                        {
                            $v2 = $v;
                            $t2 = $t;
                        }
                        unset($v);
                    }
                    $result = false;
                    if (!isset($v2))
                    {
                        $this->errorMessage = "Primary key/value is missing or is not correctly set!";
                        if ($this->showError)
                        {
                            $this->sendErrorHeader();
                        }
                        return $result;
                    }
                    $binds[] = $v2;
                    $types[] = $t2;
                    if (count($updateFields) > 0)
                    {
                        $sql = "UPDATE " . $this->table . " SET " . implode(', ', $updateFields) . " WHERE " . $pk . " = ?";
                        $stmt = $this->parseSql($sql, $binds, false);
                        if ($stmt)
                        {
                            jqGridDB::bindValues($stmt, $binds, $types);
                            if ($this->trans)
                            {
                                try
                                {
                                    jqGridDB::beginTransaction($this->pdo);
                                    $result = $this->_actionsCRUDGrid('edit', 'before');
                                    if ($this->debug) $this->logQuery($sql, $binds, $types, $data, $this->fields, $this->primaryKey);
                                    $ret = false;
                                    if ($result)
                                    {
                                        $ret = jqGridDB::execute($stmt, $binds, $this->pdo);
                                    }
                                    $result = $ret ? true : false;
                                    jqGridDB::closeCursor($this->dbtype == "adodb" ? $ret : $stmt);
                                    if ($result)
                                    {
                                        $result = $this->_actionsCRUDGrid('edit', 'after');
                                    }
                                    if ($result)
                                    {
                                        $result = jqGridDB::commit($this->pdo);
                                    }
                                    else
                                    {
                                        $this->errorMessage = jqGridDB::errorMessage($this->pdo);
                                        throw new Exception($this->errorMessage);
                                    }
                                }
                                catch(Exception $e)
                                {
                                    jqGridDB::rollBack($this->pdo);
                                    $result = false;
                                    if (!$this->errorMessage) $this->errorMessage = $e->getMessage();
                                }
                            }
                            else
                            {
                                try
                                {
                                    $result = $this->_actionsCRUDGrid('edit', 'before');
                                    if ($this->debug) $this->logQuery($sql, $binds, $types, $data, $this->fields, $this->primaryKey);
                                    $ret = false;
                                    if ($result)
                                    {
                                        $ret = jqGridDB::execute($stmt, $binds, $this->pdo);
                                    }
                                    $result = $ret ? true : false;
                                    jqGridDB::closeCursor($this->dbtype == "adodb" ? $ret : $stmt);
                                    if ($result)
                                    {
                                        $result = $this->_actionsCRUDGrid('edit', 'after');
                                    }
                                    if (!$result)
                                    {
                                        $this->errorMessage = jqGridDB::errorMessage($this->pdo);
                                        throw new Exception($this->errorMessage);
                                    }
                                }
                                catch(Exception $e)
                                {
                                    $result = false;
                                    if (!$this->errorMessage) $this->errorMessage = $e->getMessage();
                                }
                            }
                        }
                        else
                        {
                            $this->errorMessage = "Error when preparing a UPDATE statement!";
                        }
                    }
                    else
                    {
                        $this->errorMessage = "Data posted does not match update fields!";
                    }
                    if ($this->debug) $this->debugout();
                    if ($this->showError && !$result)
                    {
                        $this->sendErrorHeader();
                    }
                    return $result;
                }
                public function getLastInsertId()
                {
                    return $this->lastId;
                }
                public function delete(array $data, $where = '', array $params = null)
                {
                    $result = false;
                    if (!$this->del) return $result;
                    if (!$this->checkPrimary())
                    {
                        return $result;
                    }
                    $ide = null;
                    $binds = array(&$ide
                    );
                    $types = array();
                    $odbc = strpos($this->dbtype, 'odbc');
                    if (count($data) > 0)
                    {
                        if ($where && strlen($where) > 0)
                        {
                            $id = "";
                            $sql = "DELETE FROM " . $this->table . " WHERE " . $where;
                            $stmt = $this->parseSql($sql, $params);
                            $delids = "";
                            $custom = true;
                        }
                        else
                        {
                            $id = $this->getPrimaryKeyId();
                            if (!isset($data[$id]))
                            {
                                $this->errorMessage = "Missed data id value to perform delete!";
                                if ($this->showError)
                                {
                                    $this->sendErrorHeader();
                                }
                                return $result;
                            }
                            $sql = "DELETE FROM " . $this->table . " WHERE " . $id . "=?";
                            $stmt = $odbc === false ? $this->parseSql($sql, $binds, false) : true;
                            $delids = explode(",", $data[$id]);
                            $custom = false;
                        }
                        $types[0] = 'custom';
                        if ($stmt)
                        {
                            if ($this->trans)
                            {
                                try
                                {
                                    jqGridDB::beginTransaction($this->pdo);
                                    $result = $this->_actionsCRUDGrid('del', 'before');
                                    if ($custom)
                                    {
                                        if ($this->debug) $this->logQuery($sql, $params, false, $data, null, $this->primaryKey);
                                        $result = jqGridDB::execute($stmt, $params, $this->pdo);
                                    }
                                    else
                                    {
                                        foreach ($delids as $i => $ide)
                                        {
                                            $delids[$i] = trim($delids[$i]);
                                            $binds[0] = & $delids[$i];
                                            if ($this->debug) $this->logQuery($sql, $binds, $types, $data, $this->fields, $this->primaryKey);
                                            if ($odbc === false)
                                            {
                                                jqGridDB::bindValues($stmt, $binds, $types);
                                                $result = jqGridDB::execute($stmt, $binds, $this->pdo);
                                            }
                                            else
                                            {
                                                $stmt = jqGridDB::prepare($this->pdo, $sql, $binds, false, false);
                                                $result = jqGridDB::execute($stmt, $binds, $this->pdo);
                                                jqGridDB::closeCursor($stmt);
                                            }
                                            if (!$result)
                                            {
                                                break;
                                            }
                                            unset($binds[0]);
                                        }
                                    }
                                    $ret = $result ? true : false;
                                    if ($odbc === false)
                                    {
                                        jqGridDB::closeCursor($this->dbtype == "adodb" ? $result : $stmt);
                                    }
                                    if ($ret) $result = $this->_actionsCRUDGrid('del', 'after');
                                    else $result = false;
                                    if ($result)
                                    {
                                        jqGridDB::commit($this->pdo);
                                    }
                                    else
                                    {
                                        $this->errorMessage = jqGridDB::errorMessage($this->pdo);
                                        throw new Exception($this->errorMessage);
                                    }
                                }
                                catch(Exception $e)
                                {
                                    jqGridDB::rollBack($this->pdo);
                                    $result = false;
                                    if (!$this->errorMessage) $this->errorMessage = $e->getMessage();
                                }
                            }
                            else
                            {
                                try
                                {
                                    $result = $this->_actionsCRUDGrid('del', 'before');
                                    if ($result)
                                    {
                                        if ($custom)
                                        {
                                            $result = jqGridDB::execute($stmt, $params, $this->pdo);
                                        }
                                        else
                                        {
                                            foreach ($delids as $i => $ide)
                                            {
                                                $delids[$i] = trim($delids[$i]);
                                                $binds[0] = & $delids[$i];
                                                if ($this->debug) $this->logQuery($sql, $binds, $types, $data, $this->fields, $this->primaryKey);
                                                if ($odbc === false)
                                                {
                                                    jqGridDB::bindValues($stmt, $binds, $types);
                                                    $result = jqGridDB::execute($stmt, $binds, $this->pdo);
                                                }
                                                else
                                                {
                                                    $stmt = jqGridDB::prepare($this->pdo, $sql, $binds, false, false);
                                                    $result = jqGridDB::execute($stmt, $binds, $this->pdo);
                                                    jqGridDB::closeCursor($stmt);
                                                }
                                                if (!$result)
                                                {
                                                    break;
                                                }
                                                unset($binds[0]);
                                            }
                                        }
                                    }
                                    $ret = $result ? true : false;
                                    if ($odbc == false)
                                    {
                                        jqGridDB::closeCursor($this->dbtype == "adodb" ? $result : $stmt);
                                    }
                                    if ($ret) $result = $this->_actionsCRUDGrid('del', 'after');
                                    else $result = false;
                                    if (!$result)
                                    {
                                        $this->errorMessage = jqGridDB::errorMessage($this->pdo);
                                        throw new Exception($this->errorMessage);
                                    }
                                }
                                catch(Exception $e)
                                {
                                    $result = false;
                                    if (!$this->errorMessage) $this->errorMessage = $e->getMessage();
                                }
                            }
                        }
                    }
                    if ($this->debug) $this->debugout();
                    if ($this->showError && !$result)
                    {
                        $this->sendErrorHeader();
                    }
                    return $result;
                }
                protected function checkPrimary()
                {
                    $result = true;
                    $errmsg = "Primary key can not be found!";
                    if (strlen(trim($this->table)) > 0 && !$this->primaryKey)
                    {
                        $this->primaryKey = jqGridDB::getPrimaryKey($this->table, $this->pdo, $this->dbtype);
                        if (!$this->primaryKey)
                        {
                            $this->errorMessage = $errmsg . " " . jqGridDB::errorMessage($this->pdo);
                            $result = false;
                        }
                    }
                    if ($this->showError && !$result)
                    {
                        $this->sendErrorHeader();
                    }
                    return $result;
                }
                public function editGrid(array $summary = null, array $params = null, $oper = false, $echo = true)
                {
                    if (!$oper)
                    {
                        $oper = $this->oper ? $this->oper : "grid";
                    }
                    switch ($oper)
                    {
                        case $this->GridParams["editoper"]:
                            $data = strtolower($this->mtype) == "post" ? jqGridUtils::Strip($_POST) : jqGridUtils::Strip($_GET);
                            if ($this->update($data))
                            {
                                if ($this->successmsg)
                                {
                                    echo $this->successmsg;
                                }
                            }
                        break;
                        case $this->GridParams["addoper"]:
                            $data = strtolower($this->mtype) == "post" ? jqGridUtils::Strip($_POST) : jqGridUtils::Strip($_GET);
                            if ($this->insert($data))
                            {
                                if ($this->getLastInsert)
                                {
                                    echo $this->getPrimaryKeyId() . "#" . $this->lastId;
                                }
                                else
                                {
                                    if ($this->successmsg) echo $this->successmsg;
                                }
                            }
                            break;
                        case $this->GridParams["deloper"]:
                            $data = strtolower($this->mtype) == "post" ? jqGridUtils::Strip($_POST) : jqGridUtils::Strip($_GET);
                            if ($this->delete($data))
                            {
                                if ($this->successmsg)
                                {
                                    echo $this->successmsg;
                                }
                            }
                            break;
                        default:
                            return $this->queryGrid($summary, $params, $echo);
                        }
                }
            }
            class jqGridRender extends jqGridEdit
            {
                protected $gridOptions = array(
                    "width" => "650",
                    "hoverrows" => false,
                    "viewrecords" => true,
                    "jsonReader" => array(
                        "repeatitems" => false,
                        "subgrid" => array(
                            "repeatitems" => false
                        )
                    ) ,
                    "xmlReader" => array(
                        "repeatitems" => false,
                        "subgrid" => array(
                            "repeatitems" => false
                        )
                    ) ,
                    "gridview" => true
                );
                public $navigator = false;
                public $toolbarfilter = false;
                public $inlineNav = false;
                public $export = true;
                public $exportfile = 'exportdata.xml';
                public $pdffile = 'exportdata.pdf';
                public $csvfile = 'exportdata.csv';
                public $csvsep = ';
';
                public $csvsepreplace = ";
";
                public $sharedEditOptions = false;
                public $sharedAddOptions = false;
                public $sharedDelOptions = false;
                protected $navOptions = array(
                    "edit" => true,
                    "add" => true,
                    "del" => true,
                    "search" => true,
                    "refresh" => true,
                    "view" => false,
                    "excel" => true,
                    "pdf" => false,
                    "csv" => false,
                    "columns" => false
                );
                protected $editOptions = array(
                    "drag" => true,
                    "resize" => true,
                    "closeOnEscape" => true,
                    "dataheight" => 150,
                    "errorTextFormat" => "js:function(r){ return r.responseText;
}"
                );
                protected $addOptions = array(
                    "drag" => true,
                    "resize" => true,
                    "closeOnEscape" => true,
                    "dataheight" => 150,
                    "errorTextFormat" => "js:function(r){ return r.responseText;
}"
                );
                protected $viewOptions = array(
                    "drag" => true,
                    "resize" => true,
                    "closeOnEscape" => true,
                    "dataheight" => 150
                );
                protected $delOptions = array(
                    "errorTextFormat" => "js:function(r){ return r.responseText;
}"
                );
                protected $searchOptions = array(
                    "drag" => true,
                    "closeAfterSearch" => true,
                    "multipleSearch" => true
                );
                protected $filterOptions = array(
                    "stringResult" => true
                );
                protected $colModel = array();
                protected $runSetCommands = true;
                protected $gridMethods = array();
                protected $customCode = "";
                protected $expoptions = array(
                    "excel" => array(
                        "caption" => "",
                        "title" => "Export To Excel",
                        "buttonicon" => "ui-icon-newwin"
                    ) ,
                    "pdf" => array(
                        "caption" => "",
                        "title" => "Export To Pdf",
                        "buttonicon" => "ui-icon-print"
                    ) ,
                    "csv" => array(
                        "caption" => "",
                        "title" => "Export To CSV",
                        "buttonicon" => "ui-icon-document"
                    ) ,
                    "columns" => array(
                        "caption" => "",
                        "title" => "Visible Columns",
                        "buttonicon" => "ui-icon-calculator",
                        "options" => array()
                    )
                );
                protected $inlineNavOpt = array(
                    "addParams" => array() ,
                    "editParams" => array()
                );
                protected $groupdata;
                public function getColModel()
                {
                    return $this->colModel;
                }
                public function getGridOption($key)
                {
                    if (array_key_exists($key, $this->gridOptions)) return $this->gridOptions[$key];
                    else return false;
                }
                public function setGridOptions($aoptions)
                {
                    if (is_array($aoptions)) $this->gridOptions = jqGridUtils::array_extend($this->gridOptions, $aoptions);
                }
                public function setUrl($newurl)
                {
                    if (!$this->runSetCommands) return false;
                    if (strlen($newurl) > 0)
                    {
                        $this->setGridOptions(array(
                            "url" => $newurl,
                            "editurl" => $newurl,
                            "cellurl" => $newurl
                        ));
                        return true;
                    }
                    return false;
                }
                public function setSubGrid($suburl = '', $subnames = false, $subwidth = false, $subalign = false, $subparams = false)
                {
                    if (!$this->runSetCommands) return false;
                    if ($subnames && is_array($subnames))
                    {
                        $scount = count($subnames);
                        for ($i = 0;$i < $scount;$i++)
                        {
                            if (!isset($subwidth[$i])) $subwidth[$i] = 100;
                            if (!isset($subalign[$i])) $subalign[$i] = 'center';
                        }
                        $this->setGridOptions(array(
                            "gridview" => false,
                            "subGrid" => true,
                            "subGridUrl" => $suburl,
                            "subGridModel" => array(
                                array(
                                    "name" => $subnames,
                                    "width" => $subwidth,
                                    "align" => $subalign,
                                    "params" => $subparams
                                )
                            )
                        ));
                        return true;
                    }
                    return false;
                }
                public function setSubGridGrid($subgridurl, $subgridnames = null)
                {
                    if (!$this->runSetCommands) return false;
                    $this->setGridOptions(array(
                        "subGrid" => true,
                        "gridview" => false
                    ));
                    $setval = (is_array($subgridnames) && count($subgridnames) > 0) ? 'true' : 'false';
                    if ($setval == 'true')
                    {
                        $anames = implode(",", $subgridnames);
                    }
                    else
                    {
                        $anames = '';
                    }
                    $subgr = <<<SUBGRID
function(subgridid,id)
{
    var data = {subgrid:subgridid, rowid:id};

    if('$setval' == 'true') {
        var anm= '$anames';

        anm = anm.split(",");

        var rd = jQuery(this).jqGrid('getRowData', id);

        if(rd) {
            for(var i=0;
 i<anm.length;
 i++) {
                if(rd[anm[i]]) {
                    data[anm[i]] = rd[anm[i]];

                }
            }
        }
    }
 $("#"+jQuery.jgrid.jqID(subgridid)).load('$subgridurl',data);

}
SUBGRID;
                    

                    $this->setGridEvent('subGridRowExpanded', $subgr);
                    return true;
                }
                public function setSelect($colname, $data, $formatter = true, $editing = true, $seraching = true, $defvals = array() , $sep = ":", $delim = ";
")
                {
                    $s1 = "";
                    $prop = array();
                    $goper = $this->oper ? $this->oper : 'nooper';
                    if (($goper == 'nooper' || $goper == $this->GridParams["excel"] || $goper == "pdf" || $goper == "csv")) $runme = true;
                    else $runme = !in_array($goper, array_values($this->GridParams));
                    if (!$this->runSetCommands && !$runme) return false;
                    if (count($this->colModel) > 0 && $runme)
                    {
                        if (is_string($data))
                        {
                            $aset = jqGridDB::query($this->pdo, $data);
                            if ($aset)
                            {
                                $i = 0;
                                $s = '';
                                while ($row = jqGridDB::fetch_num($aset, $this->pdo))
                                {
                                    if ($i == 0)
                                    {
                                        $s1 .= $row[0] . $sep . $row[1];
                                    }
                                    else
                                    {
                                        $s1 .= $delim . $row[0] . $sep . $row[1];
                                    }
                                    $i++;
                                }
                            }
                            jqGridDB::closeCursor($aset);
                        }
                        else if (is_array($data))
                        {
                            $i = 0;
                            foreach ($data as $k => $v)
                            {
                                if ($i == 0)
                                {
                                    $s1 .= $k . $sep . $v;
                                }
                                else
                                {
                                    $s1 .= $delim . $k . $sep . $v;
                                }
                                $i++;
                            }
                        }
                        if ($editing)
                        {
                            $prop = array_merge($prop, array(
                                'edittype' => 'select',
                                'editoptions' => array(
                                    'value' => $s1,
                                    'separator' => $sep,
                                    'delimiter' => $delim
                                )
                            ));
                        }
                        if ($formatter)
                        {
                            $prop = array_merge($prop, array(
                                'formatter' => 'select',
                                'editoptions' => array(
                                    'value' => $s1,
                                    'separator' => $sep,
                                    'delimiter' => $delim
                                )
                            ));
                        }
                        if ($seraching)
                        {
                            if (is_array($defvals) && count($defvals) > 0)
                            {
                                foreach ($defvals as $k => $v)
                                {
                                    $s1 = $k . $sep . $v . $delim . $s1;
                                }
                            }
                            $prop = array_merge($prop, array(
                                "stype" => "select",
                                "searchoptions" => array(
                                    "value" => $s1,
                                    'separator' => $sep,
                                    'delimiter' => $delim
                                )
                            ));
                        }
                        if (count($prop) > 0)
                        {
                            $this->setColProperty($colname, $prop);
                        }
                        return true;
                    }
                    return false;
                }
                public function setAutocomplete($colname, $target = false, $data = '', $options = null, $editing = true, $searching = false)
                {
                    try
                    {
                        $ac = new jqAutocomplete($this->pdo, $this->odbc);
                        $ac->encoding = $this->encoding;
                        if (is_string($data))
                        {
                            $ac->SelectCommand = $data;
                            $url = $this->getGridOption('url');
                            if (!$url)
                            {
                                $url = basename(__FILE__);
                            }
                            $ac->setSource($url);
                        }
                        else if (is_array($data))
                        {
                            $ac->setSource($data);
                        }
                        if ($colname)
                        {
                            if ($ac->isNotACQuery())
                            {
                                if (is_array($options) && count($options) > 0)
                                {
                                    if (isset($options['cache']))
                                    {
                                        $ac->cache = $options['cache'];
                                        unset($options['cache']);
                                    }
                                    if (isset($options['searchType']))
                                    {
                                        $ac->searchType = $options['searchType'];
                                        unset($options['searchType']);
                                    }
                                    if (isset($options['ajaxtype']))
                                    {
                                        $ac->ajaxtype = $options['ajaxtype'];
                                        unset($options['ajaxtype']);
                                    }
                                    if (isset($options['scroll']))
                                    {
                                        $ac->scroll = $options['scroll'];
                                        unset($options['scroll']);
                                    }
                                    if (isset($options['height']))
                                    {
                                        $ac->height = $options['height'];
                                        unset($options['height']);
                                    }
                                    if (isset($options['itemLength']))
                                    {
                                        $ac->setLength($options['itemLength']);
                                        unset($options['itemLength']);
                                    }
                                    if (isset($options['fontsize']))
                                    {
                                        $ac->fontsize = $options['fontsize'];
                                        unset($options['fontsize']);
                                    }
                                    if (isset($options['strictcheck']))
                                    {
                                        $ac->strictcheck = $options['strictcheck'];
                                        unset($options['strictcheck']);
                                    }
                                    $ac->setOption($options);
                                }
                                if ($editing)
                                {
                                    $script = $ac->renderAutocomplete($colname, $target, false, false);
                                    $script = str_replace("jQuery('" . $colname . "')", "jQuery(el)", $script);
                                    $script = "setTimeout(function(){" . $script . "},200);
";
                                    $this->setColProperty($colname, array(
                                        "editoptions" => array(
                                            "dataInit" => "js:function(el){" . $script . "}"
                                        )
                                    ));
                                }
                                if ($searching)
                                {
                                    $ac->setOption('select', "js:function(e,u){ $(e.target).trigger('change');
}");
                                    $script = $ac->renderAutocomplete($colname, false, false, false);
                                    $script = str_replace("jQuery('" . $colname . "')", "jQuery(el)", $script);
                                    $script = "setTimeout(function(){" . $script . "},100);
";
                                    $this->setColProperty($colname, array(
                                        "searchoptions" => array(
                                            "dataInit" => "js:function(el){" . $script . "}"
                                        )
                                    ));
                                }
                            }
                            else
                            {
                                if (isset($options['searchType']))
                                {
                                    $ac->searchType = $options['searchType'];
                                }
                                $ac->renderAutocomplete($colname, $target, true, true, false);
                            }
                        }
                    }
                    catch(Exception $e)
                    {
                        $e->getMessage();
                    }
                }
                public function setDatepicker($colname, $options = null, $editing = true, $searching = true)
                {
                    try
                    {
                        if ($colname)
                        {
                            if ($this->runSetCommands)
                            {
                                $dp = new jqCalendar();
                                if (isset($options['buttonIcon']))
                                {
                                    $dp->buttonIcon = $options['buttonIcon'];
                                    unset($options['buttonIcon']);
                                }
                                if (isset($options['buttonOnly']))
                                {
                                    $dp->buttonOnly = $options['buttonOnly'];
                                    unset($options['buttonOnly']);
                                }
                                if (isset($options['fontsize']))
                                {
                                    $dp->fontsize = $options['fontsize'];
                                    unset($options['fontsize']);
                                }
                                if (is_array($options) && count($options) > 0)
                                {
                                    $dp->setOption($options);
                                }
                                if (!isset($options['dateFormat']))
                                {
                                    $ud = $this->getUserDate();
                                    $ud = jqGridUtils::phpTojsDate($ud);
                                    $dp->setOption('dateFormat', $ud);
                                }
                                $script = $dp->renderCalendar($colname, false, false);
                                $script = str_replace("jQuery('" . $colname . "')", "jQuery(el)", $script);
                                $script = "setTimeout(function(){" . $script . "},100);
";
                                if ($editing)
                                {
                                    $this->setColProperty($colname, array(
                                        "editoptions" => array(
                                            "dataInit" => "js:function(el){" . $script . "}"
                                        )
                                    ));
                                }
                                if ($searching)
                                {
                                    $this->setColProperty($colname, array(
                                        "searchoptions" => array(
                                            "dataInit" => "js:function(el){" . $script . "}"
                                        )
                                    ));
                                }
                            }
                        }
                    }
                    catch(Exception $e)
                    {
                        $e->getMessage();
                    }
                }
                public function setGridEvent($event, $code)
                {
                    if (!$this->runSetCommands) return false;
                    $this->gridOptions[$event] = "js:" . $code;
                    return true;
                }
                public function setNavOptions($module, $aoptions)
                {
                    $ret = false;
                    if (!$this->runSetCommands) return $ret;
                    switch ($module)
                    {
                        case 'navigator':
                            $this->navOptions = array_merge($this->navOptions, $aoptions);
                            $ret = true;
                        break;
                        case 'add':
                            $this->addOptions = array_merge($this->addOptions, $aoptions);
                            $ret = true;
                        break;
                        case 'edit':
                            $this->editOptions = array_merge($this->editOptions, $aoptions);
                            $ret = true;
                        break;
                        case 'del':
                            $this->delOptions = array_merge($this->delOptions, $aoptions);
                            $ret = true;
                        break;
                        case 'search':
                            $this->searchOptions = array_merge($this->searchOptions, $aoptions);
                            $ret = true;
                        break;
                        case 'view':
                            $this->viewOptions = array_merge($this->viewOptions, $aoptions);
                            $ret = true;
                        break;
                    }
                    return $ret;
                }
                public function setNavEvent($module, $event, $code)
                {
                    $ret = false;
                    if (!$this->runSetCommands) return $ret;
                    switch ($module)
                    {
                        case 'navigator':
                            $this->navOptions[$event] = "js:" . $code;
                            $ret = true;
                        break;
                        case 'add':
                            $this->addOptions[$event] = "js:" . $code;
                            $ret = true;
                        break;
                        case 'edit':
                            $this->editOptions[$event] = "js:" . $code;
                            $ret = true;
                        break;
                        case 'del':
                            $this->delOptions[$event] = "js:" . $code;
                            $ret = true;
                        break;
                        case 'search':
                            $this->searchOptions[$event] = "js:" . $code;
                            $ret = true;
                        break;
                        case 'view':
                            $this->viewOptions[$event] = "js:" . $code;
                            $ret = true;
                        break;
                    }
                    return $ret;
                }
                public function inlineNavOptions($module, $aoptions)
                {
                    $ret = false;
                    if (!$this->runSetCommands) return $ret;
                    switch ($module)
                    {
                        case 'navigator':
                            $this->inlineNavOpt = array_merge($this->inlineNavOpt, $aoptions);
                            $ret = true;
                        break;
                        case 'add':
                            $this->inlineNavOpt['addParams'] = array_merge($this->inlineNavOpt['addParams'], $aoptions);
                            $ret = true;
                        break;
                        case 'edit':
                            $this->inlineNavOpt['editParams'] = array_merge($this->inlineNavOpt['editParams'], $aoptions);
                            $ret = true;
                        break;
                    }
                    return $ret;
                }
                public function inlineNavEvent($module, $event, $code)
                {
                    $ret = false;
                    if (!$this->runSetCommands) return $ret;
                    if ($module == "add")
                    {
                        $this->inlineNavOpt['addParams'][$event] = "js:" . $code;
                        $ret = true;
                    }
                    else if ($module == "edit")
                    {
                        $this->inlineNavOpt['editParams'][$event] = "js:" . $code;
                        $ret = true;
                    }
                    return $ret;
                }
                public function getInlineOptions()
                {
                    return $this->inlineNavOpt;
                }
                public function setFilterOptions($aoptions)
                {
                    if ($this->runSetCommands)
                    {
                        if (is_array($aoptions)) $this->filterOptions = jqGridUtils::array_extend($this->filterOptions, $aoptions);
                    }
                }
                public function callGridMethod($grid, $method, array $aoptions = null)
                {
                    if ($this->runSetCommands)
                    {
                        $prm = '';
                        if (is_array($aoptions) && count($aoptions) > 0)
                        {
                            $prm = jqGridUtils::encode($aoptions);
                            $prm = substr($prm, 1);
                            $prm = substr($prm, 0, -1);
                            $prm = "," . $prm;
                        }
                        if (strpos($grid, "#") === false || strpos($grid, "#") > 0)
                        {
                            $grid = "#" . $grid;
                        }
                        $this->gridMethods[] = "jQuery('" . $grid . "').jqGrid('" . $method . "'" . $prm . ");
";
                    }
                }
                public function setJSCode($code)
                {
                    if ($this->runSetCommands)
                    {
                        $this->customCode = "js:" . $code;
                    }
                }
                public function setColModel(array $model = null, array $params = null, array $labels = null)
                {
                    $goper = $this->oper ? $this->oper : 'nooper';
                    if (($goper == 'nooper' || $goper == $this->GridParams["excel"] || $goper == "pdf" || $goper == "csv")) $runme = true;
                    else $runme = !in_array($goper, array_values($this->GridParams));
                    if ($runme)
                    {
                        if (is_array($model) && count($model) > 0)
                        {
                            $this->colModel = $model;
                            if ($this->primaryKey)
                            {
                                $this->setColProperty($this->primaryKey, array(
                                    "key" => true
                                ));
                            }
                            return true;
                        }
                        $sql = null;
                        $sqlId = $this->_setSQL();
                        if (!$sqlId) return false;
                        $nof = ($this->dbtype == 'sqlite' || $this->dbtype == 'db2' || $this->dbtype == 'array' || $this->dbtype == 'mongodb' || $this->dbtype == 'adodb') ? 1 : 0;
                        $ret = $this->execute($sqlId, $params, $sql, true, $nof, 0);
                        if ($ret)
                        {
                            if (is_array($labels) && count($labels) > 0) $names = true;
                            else $names = false;
                            $colcount = jqGridDB::columnCount($sql);
                            for ($i = 0;$i < $colcount;$i++)
                            {
                                $meta = jqGridDB::getColumnMeta($i, $sql);
                                if (strtolower($meta['name']) == 'jqgrid_row') continue;
                                if ($names && array_key_exists($meta['name'], $labels)) $this->colModel[] = array(
                                    'label' => $labels[$meta['name']],
                                    'name' => $meta['name'],
                                    'index' => $meta['name'],
                                    'sorttype' => jqGridDB::MetaType($meta, $this->dbtype)
                                );
                                else $this->colModel[] = array(
                                    'name' => $meta['name'],
                                    'index' => $meta['name'],
                                    'sorttype' => jqGridDB::MetaType($meta, $this->dbtype)
                                );
                            }
                            jqGridDB::closeCursor($sql);
                            if ($this->primaryKey) $pk = $this->primaryKey;
                            else
                            {
                                $pk = jqGridDB::getPrimaryKey($this->table, $this->pdo, $this->dbtype);
                                if ($pk !== false)
                                {
                                    $this->primaryKey = $pk;
                                }
                            }
                            if ($pk === false)
                            {
                                $pk = 0;
                            }
                            $this->setColProperty($pk, array(
                                "key" => true
                            ));
                        }
                        else
                        {
                            $this->errorMessage = jqGridDB::errorMessage($sql);
                            if ($this->showError)
                            {
                                $this->sendErrorHeader();
                            }
                            return $ret;
                        }
                    }
                    if (!$runme)
                    {
                        $this->runSetCommands = false;
                    }
                    return true;
                }
                public function setColProperty($colname, array $aproperties)
                {
                    $ret = false;
                    if (!is_array($aproperties)) return $ret;
                    if (count($this->colModel) > 0)
                    {
                        if (is_int($colname))
                        {
                            $this->colModel[$colname] = jqGridUtils::array_extend($this->colModel[$colname], $aproperties);
                            $ret = true;
                        }
                        else
                        {
                            foreach ($this->colModel as $key => $val)
                            {
                                if ($val['name'] == trim($colname))
                                {
                                    $this->colModel[$key] = jqGridUtils::array_extend($this->colModel[$key], $aproperties);
                                    $ret = true;
                                    break;
                                }
                            }
                        }
                    }
                    return $ret;
                }
                public function addCol(array $aproperties, $position = 'last')
                {
                    if (!$this->runSetCommands) return false;
                    if (is_array($aproperties) && count($aproperties) > 0 && strlen($position))
                    {
                        $cmcnt = count($this->colModel);
                        if ($cmcnt > 0)
                        {
                            if (strtolower($position) === 'first')
                            {
                                array_unshift($this->colModel, $aproperties);
                            }
                            else if (strtolower($position) === 'last')
                            {
                                array_push($this->colModel, $aproperties);
                            }
                            else if ((int)$position >= 0 && (int)$position <= $cmcnt - 1)
                            {
                                $a = array_slice($this->colModel, 0, $position + 1);
                                $b = array_slice($this->colModel, $position + 1);
                                array_push($a, $aproperties);
                                $this->colModel = array();
                                foreach ($b as $cm)
                                {
                                    $a[] = $cm;
                                }
                                $this->colModel = $a;
                            }
                            $aproperties = null;
                            return true;
                        }
                    }
                    return false;
                }
                public function setButtonOptions($exptype, $aoptions)
                {
                    if (is_array($aoptions) && count($aoptions) > 0)
                    {
                        switch ($exptype)
                        {
                            case 'excel':
                                $this->expoptions['excel'] = jqGridUtils::array_extend($this->expoptions['excel'], $aoptions);
                            break;
                            case 'pdf':
                                $this->expoptions['pdf'] = jqGridUtils::array_extend($this->expoptions['pdf'], $aoptions);
                            break;
                            case 'csv':
                                $this->expoptions['csv'] = jqGridUtils::array_extend($this->expoptions['csv'], $aoptions);
                            break;
                            case 'columns':
                                $this->expoptions['columns'] = jqGridUtils::array_extend($this->expoptions['columns'], $aoptions);
                            break;
                        }
                    }
                }
                public function groupCalcs($func, $v, $name, $record)
                {
                    switch (strtolower($func))
                    {
                        case 'sum':
                        case 'avg':
                            if ($v == '') $v = 0;
                            $ret = (float)$v + (float)$record[$name];
                            break;
                        case 'min':
                            if ($v == '')
                            {
                                return $record[$name];
                            }
                            $ret = min(array(
                                $v,
                                $record[$name]
                            ));
                            break;
                        case 'max':
                            if ($v == '')
                            {
                                return $record[$name];
                            }
                            $ret = max(array(
                                $v,
                                $record[$name]
                            ));
                            break;
                        case 'count':
                            if ($v == '')
                            {
                                $v = 0;
                            }
                            if (isset($record[$name]))
                            {
                                $ret = $v + 1;
                            }
                            else
                            {
                                $ret = 0;
                            }
                            break;
                        default:
                            $ret = '';
                        }
                        return $ret;
                }
                private function groupingSetup($rs, $colmodel)
                {
                    if (isset($this->gridOptions['groupingView']))
                    {
                        $grpopt = $this->gridOptions['groupingView'];
                    }
                    else
                    {
                        printf('No grouping is set!');
                        return false;
                    }
                    if (!$rs)
                    {
                        printf('Bad Record set groupingSetup');
                        return false;
                    }
                    $this->groupdata = new ArrayIterator();
                    $maxrows = $this->gSQLMaxRows;
                    $ncols = jqGridDB::columnCount($rs);
                    $nmodel = is_array($colmodel) ? count($colmodel) : -1;
                    if ($nmodel > 0)
                    {
                        for ($i = 0;$i < $nmodel;$i++)
                        {
                            if ($colmodel[$i]['name'] == 'actions')
                            {
                                array_splice($colmodel, $i, 1);
                                $nmodel--;
                                break;
                            }
                        }
                    }
                    $model = false;
                    if ($colmodel && $nmodel == $ncols)
                    {
                        $model = true;
                    }
                    $grp = new stdClass();
                    $grp->summary = array();
                    $grp->columns = array();
                    $grp->totalwidth = 0;
                    $grpopt['groupIndex'] = array();
                    for ($i = 0;$i < $ncols;$i++)
                    {
                        $atmp = array();
                        $atmp['hidden'] = ($model && isset($colmodel[$i]["hidden"])) ? $colmodel[$i]["hidden"] : false;
                        $atmp['select'] = false;
                        if ($model && isset($colmodel[$i]["formatter"]))
                        {
                            if ($colmodel[$i]["formatter"] == "select")
                            {
                                $asl = isset($colmodel[$i]["formatoptions"]) ? $colmodel[$i]["formatoptions"] : $colmodel[$i]["editoptions"];
                                if (isset($asl["value"]))
                                {
                                    $sep = isset($asl["separator"]) ? $asl["separator"] : ":";
                                    $delim = isset($asl["delimiter"]) ? $asl["delimiter"] : ";
";
                                    $list = explode($delim, $asl["value"]);
                                    foreach ($list as $key => $val)
                                    {
                                        $items = explode($sep, $val);
                                        $atmp['select'][$items[0]] = $items[1];
                                    }
                                }
                            }
                        }
                        $atmp['width'] = ($model && isset($colmodel[$i]["width"])) ? (int)$colmodel[$i]["width"] : 150;
                        if ($model)
                        {
                            $atmp['label'] = isset($colmodel[$i]["label"]) ? $colmodel[$i]["label"] : $colmodel[$i]["name"];
                            $atmp["name"] = $colmodel[$i]["name"];
                            $atmp['type'] = isset($colmodel[$i]["sorttype"]) ? $colmodel[$i]["sorttype"] : '';
                        }
                        else
                        {
                            $field = jqGridDB::getColumnMeta($i, $rs);
                            $atmp['label'] = $field["name"];
                            $atmp["name"] = $field["name"];
                            $atmp['type'] = jqGridDB::MetaType($field, $this->dbtype);
                        }
                        $atmp['align'] = isset($colmodel[$i]["align"]) ? strtoupper(substr($colmodel[$i]["align"], 0, 1)) : "L";
                        for ($j = 0;$j < count($grpopt['groupField']);$j++)
                        {
                            if ($atmp['name'] == $grpopt['groupField'][$j])
                            {
                                $grpopt['groupIndex'][$j] = $i;
                            }
                        }
                        if (isset($colmodel[$i]["summaryType"]))
                        {
                            $grp->summary[] = array(
                                "nm" => $atmp['name'],
                                "st" => $colmodel[$i]["summaryType"],
                                "v" => ''
                            );
                        }
                        $grp->totalwidth += $atmp['hidden'] ? 0 : $atmp['width'];
                        $grp->columns[$i] = $atmp;
                    }
                    for ($j = 0;$j < count($grpopt['groupField']);$j++)
                    {
                        if (!isset($grpopt['groupSummary'][$j]))
                        {
                            $grpopt['groupSummary'][$j] = false;
                        }
                    }
                    $grp->lastvalues = array();
                    $grp->groups = array();
                    $grp->counters = array();
                    $irow = 0;
                    $fieldnames = array();
                    $grlen = count($grpopt['groupIndex']);
                    if ($this->dbtype == 'mysqli')
                    {
                        $fieldnames[0] = & $rs;
                        $res_arr = array();
                        for ($i = 0;$i < $ncols;$i++)
                        {
                            $fieldnames[$i + 1] = & $res_arr[$grp->columns[$i]['name']];
                        }
                        call_user_func_array('mysqli_stmt_bind_result', $fieldnames);
                    }
                    while ($r = jqGridDB::fetch_assoc($rs, $this->pdo))
                    {
                        if ($this->dbtype == 'mysqli')
                        {
                            $r = jqGridUtils::array_extend(array() , $res_arr);
                        }
                        $changed = 0;
                        for ($i = 0;$i < $grlen;$i++)
                        {
                            $fieldInd = $grpopt['groupIndex'][$i];
                            $fieldName = $grpopt['groupField'][$i];
                            if (array_key_exists($fieldName, $r))
                            {
                                $v = $r[$fieldName];
                                if ($grp->columns[$fieldInd]['select'])
                                {
                                    if (isset($grp->columns[$fieldInd]['select'][$v]))
                                    {
                                        $v = $grp->columns[$fieldInd]['select'][$v];
                                    }
                                }
                                if ($irow == 0)
                                {
                                    $grp->groups[] = array(
                                        "idx" => $i,
                                        "dataIndex" => $fieldName,
                                        "value" => $v,
                                        "startRow" => $irow,
                                        "cnt" => 1,
                                        "summary" => array()
                                    );
                                    $grp->lastvalues[$i] = $v;
                                    $grp->counters[$i] = array(
                                        "cnt" => 1,
                                        "pos" => count($grp->groups) - 1,
                                        "summary" => $grp->summary
                                    );
                                    foreach ($grp->counters[$i]['summary'] as $sumkey => $sumval)
                                    {
                                        $grp->counters[$i]['summary'][$sumkey]['v'] = $this->groupCalcs($sumval['st'], $sumval['v'], $sumval['nm'], $r);
                                    }
                                    $grp->groups[$grp->counters[$i]['pos']]['summary'] = $grp->counters[$i]['summary'];
                                }
                                else
                                {
                                    if ($v != $grp->lastvalues[$i])
                                    {
                                        $grp->groups[] = array(
                                            "idx" => $i,
                                            "dataIndex" => $fieldName,
                                            "value" => $v,
                                            "startRow" => $irow,
                                            "cnt" => 1,
                                            "summary" => array()
                                        );
                                        $grp->lastvalues[$i] = $v;
                                        $changed = 1;
                                        $grp->counters[$i] = array(
                                            "cnt" => 1,
                                            "pos" => count($grp->groups) - 1,
                                            "summary" => $grp->summary
                                        );
                                        foreach ($grp->counters[$i]['summary'] as $sumkey => $sumval)
                                        {
                                            $grp->counters[$i]['summary'][$sumkey]['v'] = $this->groupCalcs($sumval['st'], $sumval['v'], $sumval['nm'], $r);
                                        }
                                        $grp->groups[$grp->counters[$i]['pos']]['summary'] = $grp->counters[$i]['summary'];
                                    }
                                    else
                                    {
                                        if ($changed == 1)
                                        {
                                            $grp->groups[] = array(
                                                "idx" => $i,
                                                "dataIndex" => $fieldName,
                                                "value" => $v,
                                                "startRow" => $irow,
                                                "cnt" => 1,
                                                "summary" => array()
                                            );
                                            $grp->lastvalues[$i] = $v;
                                            $grp->counters[$i] = array(
                                                "cnt" => 1,
                                                "pos" => count($grp->groups) - 1,
                                                "summary" => $grp->summary
                                            );
                                            foreach ($grp->counters[$i]['summary'] as $sumkey => $sumval)
                                            {
                                                $grp->counters[$i]['summary'][$sumkey]['v'] = $this->groupCalcs($sumval['st'], $sumval['v'], $sumval['nm'], $r);
                                            }
                                            $grp->groups[$grp->counters[$i]['pos']]['summary'] = $grp->counters[$i]['summary'];
                                        }
                                        else
                                        {
                                            $grp->counters[$i]['cnt']++;
                                            $grp->groups[$grp->counters[$i]['pos']]['cnt'] = $grp->counters[$i]['cnt'];
                                            foreach ($grp->counters[$i]['summary'] as $sumkey => $sumval)
                                            {
                                                $grp->counters[$i]['summary'][$sumkey]['v'] = $this->groupCalcs($sumval['st'], $sumval['v'], $sumval['nm'], $r);
                                            }
                                            $grp->groups[$grp->counters[$i]['pos']]['summary'] = $grp->counters[$i]['summary'];
                                        }
                                    }
                                }
                            }
                        }
                        $this->groupdata[] = $r;
                        $irow++;
                        if ($irow >= $maxrows)
                        {
                            break;
                        }
                    }
                    return $grp;
                }
                protected function findGroupIdx($ind, $offset, $grp)
                {
                    $ret = array();
                    if ($offset === 0)
                    {
                        $ret = $grp[$ind];
                    }
                    else
                    {
                        $id = $grp[$ind]['idx'];
                        if ($id === 0)
                        {
                            $ret = $grp[$ind];
                        }
                        for ($i = $ind;$i >= 0;$i--)
                        {
                            if ($grp[$i]['idx'] === $id - $offset)
                            {
                                $ret = $grp[$i];
                                break;
                            }
                        }
                    }
                    return $ret;
                }
                protected function js_template($format = '')
                {
                    $numargs = func_num_args();
                    $args = func_get_args();
                    for ($i = 1;$i < $numargs;$i++)
                    {
                        if (is_array($args[$i]))
                        {
                            $nmarr = $args[$i];
                            foreach ($nmarr as $k => $arr)
                            {
                                $args[0] = str_replace('{' . $arr['nm'] . '}', $arr['v'], $args[0]);
                            }
                        }
                        else
                        {
                            $args[0] = str_replace('{' . ($i - 1) . '}', $args[$i], $args[0]);
                        }
                    }
                    return isset($args[0]) ? $args[0] : '';
                }
                protected function rs2excel2($echo = true, $filename = 'exportdata.xls', $grpinfo = null)
                {
                    $s = '';
                    $ncols = count($grpinfo->columns);
                    $hdr = '<?xml version="1.0" encoding="' . $this->encoding . '"?>';
                    $hdr .= '<?mso-application progid="Excel.Sheet"?>';
                    $hdr .= '<ss:Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:html="http://www.w3.org/TR/REC-html40">';
                    $hdr .= '<ss:Styles>' . '<ss:Style ss:ID="1"><ss:Font ss:Bold="1"/></ss:Style>' . '<ss:Style ss:ID="sd"><NumberFormat ss:Format="Short Date"/></ss:Style>' . '<ss:Style ss:ID="ld"><NumberFormat ss:Format="General Date"/></ss:Style>' . '<ss:Style ss:ID="nmb"><NumberFormat ss:Format="Standard"/></ss:Style>' . '<ss:Style ss:ID="intnm"><NumberFormat ss:Format="General Number"/></ss:Style>' . '<ss:Style ss:ID="headcol"><ss:Font ss:Bold="1"/></ss:Style>' . '<ss:Style ss:ID="Default" ss:Name="Normal"> <Alignment ss:Vertical="Bottom"/><Borders/><Interior/> <NumberFormat/><Protection/></Style>' . '</ss:Styles>';
                    $hdr .= '<ss:Worksheet ss:Name="Sheet1">';
                    $hdr .= '<ss:Table>';
                    $hdr1 = '<ss:Row ss:StyleID="1">';
                    $hiddencount = 0;
                    for ($i = 0;$i < $ncols;$i++)
                    {
                        if ($grpinfo->columns[$i]['hidden'] == true)
                        {
                            $hiddencount++;
                            continue;
                        }
                        if ($grpinfo->columns[$i]['width'] > 0)
                        {
                            $hdr .= '<ss:Column ss:Width="' . ((int)$grpinfo->columns[$i]['width'] * 72 / 96) . '"/>';
                        }
                        else
                        {
                            $hdr .= '<ss:Column ss:AutoFitWidth="1"/>';
                        }
                        $fname = htmlspecialchars($grpinfo->columns[$i]['label']);
                        if (strlen($fname) == 0) $fname = '';
                        $hdr1 .= '<ss:Cell><ss:Data ss:Type="String">' . $fname . '</ss:Data></ss:Cell>';
                    }
                    $hdr1 .= '</ss:Row>';
                    if (!$echo) $html = $hdr . $hdr1;
                    $toEnd = 0;
                    $grpopt = $this->gridOptions['groupingView'];
                    $len = count($grpopt['groupField']);
                    $sumreverse = array_reverse($grpopt['groupSummary']);
                    foreach ($grpinfo->groups as $i => $n)
                    {
                        $toEnd++;
                        $v = $n['value'];
                        if (!isset($grpopt['groupText'][$n['idx']]))
                        {
                            $gt = "{0}";
                        }
                        else
                        {
                            $gt = $grpopt['groupText'][$n['idx']];
                        }
                        $s .= '<ss:Row><ss:Cell ss:MergeAcross="' . ($ncols - $hiddencount - 1) . '" ss:StyleID="headcol"><ss:Data ss:Type="String">' . str_repeat('&nbsp;
&nbsp;
', $n['idx']) . $this->js_template($gt, $v, $n['cnt'], $n['summary']) . '</ss:Data></ss:Cell></ss:Row>';
                        $leaf = $len - 1 === $n['idx'];
                        if ($leaf)
                        {
                            if (isset($grpinfo->groups[$i + 1]))
                            {
                                $gg = $grpinfo->groups[$i + 1];
                                $end = $gg['startRow'];
                            }
                            else
                            {
                                $gg = false;
                                $end = $this
                                    ->groupdata
                                    ->count();
                            }
                            foreach (new LimitIterator($this->groupdata, $n['startRow'], ($end - $n['startRow'])) as $row)
                            {
                                $s .= '<ss:Row>';
                                $j = 0;
                                foreach ($row as $key => $r)
                                {
                                    if ($grpinfo->columns[$j]['hidden'] == true)
                                    {
                                        $j++;
                                        continue;
                                    }
                                    $v = $r;
                                    if (is_array($grpinfo->columns[$j]['select']))
                                    {
                                        if (isset($grpinfo->columns[$j]['select'][$v]))
                                        {
                                            $v1 = $grpinfo->columns[$j]['select'][$v];
                                            if ($v1) $v = $v1;
                                        }
                                        $grpinfo->columns[$j]['type'] = 'string';
                                    }
                                    switch ($grpinfo->columns[$j]['type'])
                                    {
                                        case 'date':
                                            if (substr($v, 0, 4) == '0000' || empty($v) || $v == 'NULL')
                                            {
                                                $v = '1899-12-31T00:00:00.000';
                                                $s .= '<ss:Cell ss:StyleID="sd"><ss:Data ss:Type="DateTime">' . $v . '</ss:Data></ss:Cell>';
                                            }
                                            else if (!strpos($v, ':'))
                                            {
                                                $v .= "T00:00:00.000";
                                                $s .= '<ss:Cell ss:StyleID="sd"><ss:Data ss:Type="DateTime">' . $v . '</ss:Data></ss:Cell>';
                                            }
                                            else
                                            {
                                                $thous = substr($v, -4);
                                                if (strpos($thous, ".") === false && strpos($v, ".") === false) $v .= ".000";
                                                $s .= '<ss:Cell ss:StyleID="sd"><ss:Data ss:Type="DateTime">' . str_replace(" ", "T", trim($v)) . '</ss:Data></ss:Cell>';
                                            }
                                            break;
                                        case 'datetime':
                                            if (substr($v, 0, 4) == '0000' || empty($v) || $v == 'NULL')
                                            {
                                                $v = '1899-12-31T00:00:00.000';
                                                $s .= '<ss:Cell ss:StyleID="ld"><ss:Data ss:Type="DateTime">' . $v . '</ss:Data></ss:Cell>';
                                            }
                                            else
                                            {
                                                $thous = substr($v, -4);
                                                if (strpos($thous, ".") === false && strpos($v, ".") === false) $v .= ".000";
                                                $s .= '<ss:Cell ss:StyleID="ld"><ss:Data ss:Type="DateTime">' . str_replace(" ", "T", trim($v)) . '</ss:Data></ss:Cell>';
                                            }
                                            break;
                                        case 'int':
                                            $s .= '<ss:Cell ss:StyleID="intnm"><ss:Data ss:Type="Number">' . stripslashes($v) . '</ss:Data></ss:Cell>';
                                            break;
                                        case 'numeric':
                                            $s .= '<ss:Cell ss:StyleID="nmb"><ss:Data ss:Type="Number">' . stripslashes($v) . '</ss:Data></ss:Cell>';
                                            break;
                                        default:
                                            $v = htmlspecialchars(trim($v));
                                            if (strlen($v) == 0) $v = '';
                                            $s .= '<ss:Cell><ss:Data ss:Type="String">' . stripslashes($v) . '</ss:Data></ss:Cell>';
                                        }
                                        $j++;
                                    }
                                    $s .= '</ss:Row>';
                                }
                                if ($gg)
                                {
                                    for ($jj = 0;$jj < $len;$jj++)
                                    {
                                        if ($gg['dataIndex'] === $this->gridOptions['groupingView']['groupField'][$jj])
                                        {
                                            break;
                                        }
                                    }
                                    $toEnd = $len - $jj;
                                }
                                for ($ik = 0;$ik < $toEnd;$ik++)
                                {
                                    if (!$sumreverse[$ik])
                                    {
                                        continue;
                                    }
                                    $s .= '<ss:Row>';
                                    $fdata = $this->findGroupIdx($i, $ik, $grpinfo->groups);
                                    $grlen = $fdata['cnt'];
                                    for ($ij = 0;$ij < $ncols;$ij++)
                                    {
                                        if ($grpinfo->columns[$ij]['hidden'] == true)
                                        {
                                            continue;
                                        }
                                        $tmpdata = '<ss:Cell><ss:Data ss:Type="String"></ss:Data></ss:Cell>';
                                        $tplfld = "{0}";
                                        foreach ($fdata['summary'] as $sk => $sv)
                                        {
                                            if ($sv['nm'] == $this->colModel[$ij]['name'])
                                            {
                                                if (isset($this->colModel[$ij]['summaryTpl']))
                                                {
                                                    $tplfld = $this->colModel[$ij]['summaryTpl'];
                                                }
                                                if (is_string($sv['st']) && strtolower($sv['st']) == 'avg')
                                                {
                                                    if ($sv['v'] && $grlen > 0)
                                                    {
                                                        $sv['v'] = $sv['v'] / $grlen;
                                                    }
                                                }
                                                $styleid = "Default";
                                                if (is_integer($sv['v']))
                                                {
                                                    $styleid = "intnum";
                                                }
                                                else if (is_float($sv['v']))
                                                {
                                                    $styleid = "nmb";
                                                }
                                                $tmpdata = '<ss:Cell ss:StyleID="' . $styleid . '"><ss:Data ss:Type="Number">' . $this->js_template($tplfld, $sv['v']) . '</ss:Data></ss:Cell>';
                                                break;
                                            }
                                        }
                                        $s .= $tmpdata;
                                    }
                                    $s .= '</ss:Row>';
                                }
                                $toEnd = $jj;
                            }
                        }
                        $this->groupdata = null;
                        if ($this->tmpvar)
                        {
                            $s .= '<ss:Row>';
                            for ($i = 0;$i < $ncols;$i++)
                            {
                                if ($grpinfo->columns[$i]['hidden'] == true)
                                {
                                    continue;
                                }
                                $vv = '';
                                foreach ($this->tmpvar as $key => $v)
                                {
                                    if ($grpinfo->columns[$i]['name'] == $key)
                                    {
                                        $vv = $v;
                                        break;
                                    }
                                }
                                if ($vv)
                                {
                                    $s .= '<ss:Cell ss:StyleID="nmb"><ss:Data ss:Type="Number">' . stripslashes($vv) . '</ss:Data></ss:Cell>';
                                }
                                else
                                {
                                    $s .= '<ss:Cell><ss:Data ss:Type="String"></ss:Data></ss:Cell>';
                                }
                            }
                            $s .= '</ss:Row>';
                        }
                        if ($echo)
                        {
                            header('Content-Type: application/ms-excel;
');
                            header("Content-Disposition: attachment;
 filename=" . $filename);
                            echo $hdr . $hdr1;
                            echo $s . '</ss:Table></ss:Worksheet></ss:Workbook>';
                        }
                        else
                        {
                            $html .= $s . '</ss:Table></ss:Worksheet></ss:Workbook>';
                            return $html;
                        }
                }
                public function exportToExcel(array $summary = null, array $params = null, array $colmodel = null, $echo = true, $filename = 'exportdata.xml')
                {
                    $sql = null;
                    $sql = $this->_rs($params, $summary);
                    if ($sql)
                    {
                        if (isset($this->gridOptions['grouping']) && $this->gridOptions['grouping'] == true)
                        {
                            $groupinfo = $this->groupingSetup($sql, $colmodel);
                            jqGridDB::closeCursor($sql);
                            $ret = $this->rs2excel2($echo, $filename, $groupinfo);
                        }
                        else
                        {
                            $ret = $this->rs2excel($sql, $colmodel, $echo, $filename, $summary);
                            jqGridDB::closeCursor($sql);
                        }
                        return $ret;
                    }
                    else return "Error:Could not execute the query";
                }
                protected function rs2pdf2(&$pdf, $grpinfo = null)
                {
                    $ncols = count($grpinfo->columns);
                    $totw = 0;
                    $pw = $pdf->getPageWidth();
                    $margins = $pdf->getMargins();
                    $pw = $pw - $margins['left'] - $margins['right'];
                    $pd = $this->PDF;
                    $pdf->SetLineWidth(0.2);
                    function printTHeader2($ncols, $maxheigh, $grinfo, $pdf, $pd)
                    {
                        $pdf->SetFillColorArray($pdf->convertHTMLColorToDec($pd['grid_head_color']));
                        $pdf->SetTextColorArray($pdf->convertHTMLColorToDec($pd['grid_head_text_color']));
                        $pdf->SetDrawColorArray($pdf->convertHTMLColorToDec($pd['grid_draw_color']));
                        $pdf->SetFont('', 'B');
                        for ($i = 0;$i < $ncols;$i++)
                        {
                            if ($grinfo->columns[$i]['hidden'])
                            {
                                continue;
                            }
                            if (!$pd['shrink_header'])
                            {
                                $pdf->MultiCell($grinfo->columns[$i]['width'], $maxheigh, $grinfo->columns[$i]['label'], 1, 'C', true, 0, '', '', true, 0, true, true, 0, 'B', false);
                            }
                            else
                            {
                                $pdf->Cell($grinfo->columns[$i]['width'], $pd['grid_header_height'], $grinfo->columns[$i]['label'], 1, 0, 'C', 1, '', 1);
                            }
                        }
                    }
                    $totw = 0;
                    $maxheigh = $pd['grid_header_height'];
                    for ($i = 0;$i < $ncols;$i++)
                    {
                        if ($grpinfo->columns[$i]['hidden'])
                        {
                            continue;
                        }
                        $grpinfo->columns[$i]['width'] = ($grpinfo->columns[$i]['width'] / $grpinfo->totalwidth) * $pw;
                        $totw += $grpinfo->columns[$i]['width'];
                        if (!$pd['shrink_header'])
                        {
                            $maxheigh = max($maxheigh, $pdf->getStringHeight($colwidth[$i], $grpinfo->columns[$i]['label'], false, true, '', 1));
                        }
                    }
                    printTHeader2($ncols, $maxheigh, $grpinfo, $pdf, $pd);
                    $pdf->Ln();
                    $datefmt = $this->userdateformat;
                    $timefmt = $this->usertimeformat;
                    $pdf->SetFillColorArray($pdf->convertHTMLColorToDec($pd['grid_row_color']));
                    $pdf->SetTextColorArray($pdf->convertHTMLColorToDec($pd['grid_row_text_color']));
                    $fill = false;
                    $dimensions = array();
                    if (!$pd['shrink_cell'])
                    {
                        $dimensions = $pdf->getPageDimensions();
                    }
                    $toEnd = 0;
                    $grpopt = $this->gridOptions['groupingView'];
                    $len = count($grpopt['groupField']);
                    $sumreverse = array_reverse($grpopt['groupSummary']);
                    foreach ($grpinfo->groups as $i => $n)
                    {
                        $toEnd++;
                        $v = $n['value'];
                        if (!isset($grpopt['groupText'][$n['idx']]))
                        {
                            $gt = "{0}";
                        }
                        else
                        {
                            $gt = $grpopt['groupText'][$n['idx']];
                        }
                        $pdf->SetFont('', 'B');
                        $pdf->Cell($totw, $pd['grid_row_height'], str_repeat(' ', $n['idx']) . $this->js_template($gt, $v, $n['cnt'], $n['summary']) , 1, 0, 'L', 0, '', 1);
                        $leaf = $len - 1 === $n['idx'];
                        $pdf->Ln();
                        if ($leaf)
                        {
                            if (isset($grpinfo->groups[$i + 1]))
                            {
                                $gg = $grpinfo->groups[$i + 1];
                                $end = $gg['startRow'];
                            }
                            else
                            {
                                $gg = false;
                                $end = $this
                                    ->groupdata
                                    ->count();
                            }
                            $pdf->SetFont('');
                            foreach (new LimitIterator($this->groupdata, $n['startRow'], ($end - $n['startRow'])) as $row)
                            {
                                $j = 0;
                                foreach ($row as $key => $r)
                                {
                                    if ($grpinfo->columns[$j]['hidden'] == true)
                                    {
                                        $j++;
                                        continue;
                                    }
                                    $v = $r;
                                    if (is_array($grpinfo->columns[$j]['select']))
                                    {
                                        if (isset($grpinfo->columns[$j]['select'][$v]))
                                        {
                                            $v1 = $grpinfo->columns[$j]['select'][$v];
                                            if ($v1) $v = $v1;
                                        }
                                        $grpinfo->columns[$j]['type'] = 'string';
                                    }
                                    $varr = array();
                                    $maxh = $pd['grid_row_height'];
                                    switch ($grpinfo->columns[$j]['type'])
                                    {
                                        case 'date':
                                            $v = $datefmt != $this->dbdateformat ? jqGridUtils::parseDate($this->dbdateformat, $v, $datefmt) : $v;
                                        break;
                                        case 'datetime':
                                            $v = $timefmt != $this->dbtimeformat ? jqGridUtils::parseDate($this->dbtimeformat, $v, $timefmt) : $v;
                                        break;
                                        case 'numeric':
                                        case 'int':
                                            $v = trim($v);
                                        break;
                                        default:
                                            $v = trim($v);
                                            if (strlen($v) == 0) $v = '';
                                        }
                                        if (!$pd['shrink_cell'])
                                        {
                                            $varr[$key] = $v;
                                            $maxh = max($maxh, $pdf->getStringHeight($grpinfo->columns[$j]['width'], $v, false, true, '', 1));
                                        }
                                        else
                                        {
                                            $pdf->Cell($grpinfo->columns[$j]['width'], $pd['grid_row_height'], $v, 1, 0, $grpinfo->columns[$j]['align'], $fill, '', 1);
                                        }
                                        $j++;
                                }
                                if (!$pd['shrink_cell'])
                                {
                                    $startY = $pdf->GetY();
                                    if (($startY + $maxh) + $dimensions['bm'] > ($dimensions['hk']))
                                    {
                                        $pdf->AddPage();
                                        if ($pd['reprint_grid_header'])
                                        {
                                            printTHeader2($ncols, $maxheigh, $grpinfo, $pdf, $pd);
                                            $pdf->Ln();
                                            $pdf->SetFillColorArray($pdf->convertHTMLColorToDec($pd['grid_row_color']));
                                            $pdf->SetTextColorArray($pdf->convertHTMLColorToDec($pd['grid_row_text_color']));
                                            $pdf->SetFont('');
                                        }
                                    }
                                    for ($ii = 0;$ii < $ncols;$ii++)
                                    {
                                        if ($grpinfo->columns[$ii]['hidden'] == true) continue;
                                        $pdf->MultiCell($grpinfo->columns[$ii]['width'], $maxh, $varr[$ii], 1, $grpinfo->columns[$ii]['align'], $fill, 0, '', '', true, 0, true, true, 0, 'T', false);
                                    }
                                }
                                if ($pd['grid_alternate_rows'])
                                {
                                    $fill = !$fill;
                                }
                                $pdf->Ln();
                            }
                            if ($gg)
                            {
                                for ($jj = 0;$jj < $len;$jj++)
                                {
                                    if ($gg['dataIndex'] === $this->gridOptions['groupingView']['groupField'][$jj])
                                    {
                                        break;
                                    }
                                }
                                $toEnd = $len - $jj;
                            }
                            for ($ik = 0;$ik < $toEnd;$ik++)
                            {
                                if (!$sumreverse[$ik])
                                {
                                    continue;
                                }
                                $fdata = $this->findGroupIdx($i, $ik, $grpinfo->groups);
                                $grlen = $fdata['cnt'];
                                for ($ij = 0;$ij < $ncols;$ij++)
                                {
                                    if ($grpinfo->columns[$ij]['hidden'] == true)
                                    {
                                        continue;
                                    }
                                    $v = '';
                                    $tplfld = "{0}";
                                    foreach ($fdata['summary'] as $sk => $sv)
                                    {
                                        if ($sv['nm'] == $this->colModel[$ij]['name'])
                                        {
                                            if (isset($this->colModel[$ij]['summaryTpl']))
                                            {
                                                $tplfld = $this->colModel[$ij]['summaryTpl'];
                                            }
                                            if (is_string($sv['st']) && strtolower($sv['st']) == 'avg')
                                            {
                                                if ($sv['v'] && $grlen > 0)
                                                {
                                                    $sv['v'] = $sv['v'] / $grlen;
                                                }
                                            }
                                            $v = $this->js_template($tplfld, $sv['v']);
                                            break;
                                        }
                                    }
                                    $pdf->Cell($grpinfo->columns[$ij]['width'], $pd['grid_row_height'], $v, 1, 0, $grpinfo->columns[$ij]['align'], 0, '', 1);
                                }
                                $pdf->Ln();
                            }
                            $toEnd = $jj;
                        }
                    }
                    if ($this->tmpvar)
                    {
                        $pdf->SetFont('', 'B');
                        for ($i = 0;$i < $ncols;$i++)
                        {
                            if ($grpinfo->columns[$i]['hidden'] == true) continue;
                            $vv = '';
                            foreach ($this->tmpvar as $key => $v)
                            {
                                if ($grpinfo->columns[$i]['name'] == $key)
                                {
                                    $vv = $v;
                                    break;
                                }
                            }
                            $pdf->Cell($grpinfo->columns[$i]['width'], $pd['grid_row_height'], $vv, 1, 0, $grpinfo->columns[$i]['align'], $fill, '', 1);
                        }
                    }
                }
                public function exportToPdf(array $summary = null, array $params = null, array $colmodel = null, $filename = 'exportdata.pdf')
                {
                    $sql = null;
                    global $l;
                    $sql = $this->_rs($params, $summary);
                    if ($sql)
                    {
                        $pd = $this->PDF;
                        try
                        {
                            include ($pd['path_to_pdf_class']);
                            $pdf = new TCPDF($pd['page_orientation'], $pd['unit'], $pd['page_format'], $pd['unicode'], $pd['encoding'], false);
                            $pdf->SetCreator($pd['creator']);
                            $pdf->SetAuthor($pd['author']);
                            $pdf->SetTitle($pd['title']);
                            $pdf->SetSubject($pd['subject']);
                            $pdf->SetKeywords($pd['keywords']);
                            $pdf->SetMargins($pd['margin_left'], $pd['margin_top'], $pd['margin_right']);
                            $pdf->SetHeaderMargin($pd['margin_header']);
                            $pdf->setHeaderFont(Array(
                                $pd['font_name_main'],
                                '',
                                $pd['font_size_main']
                            ));
                            if ($pd['header'] === true)
                            {
                                $pdf->SetHeaderData($pd['header_logo'], $pd['header_logo_width'], $pd['header_title'], $pd['header_string']);
                            }
                            else
                            {
                                $pdf->setPrintHeader(false);
                            }
                            $pdf->SetDefaultMonospacedFont($pd['font_monospaced']);
                            $pdf->setFooterFont(Array(
                                $pd['font_name_data'],
                                '',
                                $pd['font_size_data']
                            ));
                            $pdf->SetFooterMargin($pd['margin_footer']);
                            if ($pd['footer'] !== true)
                            {
                                $pdf->setPrintFooter(false);
                            }
                            $pdf->setImageScale($pd['image_scale_ratio']);
                            $pdf->SetAutoPageBreak(true, 17);
                            $pdf->setLanguageArray($l);
                            $pdf->AddPage();
                            $pdf->SetFont($pd['font_name_data'], '', $pd['font_size_data']);
                            if (isset($this->gridOptions['grouping']) && $this->gridOptions['grouping'] == true)
                            {
                                $groupinfo = $this->groupingSetup($sql, $colmodel);
                                jqGridDB::closeCursor($sql);
                                $ret = $this->rs2pdf2($pdf, $groupinfo);
                            }
                            else
                            {
                                $ret = $this->rs2pdf($sql, $pdf, $colmodel, $summary);
                                jqGridDB::closeCursor($sql);
                            }
                            $pdf->Output($filename, $pd['destination']);
                            exit();
                        }
                        catch(Exception $e)
                        {
                            return false;
                        }
                    }
                    else
                    {
                        return "Error:Could not execute the query";
                    }
                }
                public function renderGrid($tblelement = '', $pager = '', $script = true, array $summary = null, array $params = null, $createtbl = false, $createpg = false, $echo = true)
                {
                    $oper = $this->GridParams["oper"];
                    $goper = $this->oper ? $this->oper : 'nooper';
                    if ($goper == $this->GridParams["autocomplete"])
                    {
                        return false;
                    }
                    else if ($goper == $this->GridParams["excel"])
                    {
                        if (!$this->export) return false;
                        $this->exportToExcel($summary, $params, $this->colModel, true, $this->exportfile);
                    }
                    else if ($goper == "pdf")
                    {
                        if (!$this->export) return false;
                        $this->exportToPdf($summary, $params, $this->colModel, $this->pdffile);
                    }
                    else if ($goper == "csv")
                    {
                        if (!$this->export) return false;
                        $this->exportToCsv($summary, $params, $this->colModel, true, $this->csvfile, $this->csvsep, $this->csvsepreplace);
                    }
                    else if (in_array($goper, array_values($this->GridParams)))
                    {
                        if ($this->inlineNav)
                        {
                            $this->getLastInsert = true;
                        }
                        return $this->editGrid($summary, $params, $goper, $echo);
                    }
                    else
                    {
                        if (!isset($this->gridOptions["datatype"])) $this->gridOptions["datatype"] = $this->dataType;
                        $ed = true;
                        if (isset($this->gridOptions['cmTemplate']))
                        {
                            $edt = $this->gridOptions['cmTemplate'];
                            $ed = isset($edt['editable']) ? $edt['editable'] : true;
                        }
                        foreach ($this->colModel as $k => $cm)
                        {
                            if (!isset($this->colModel[$k]['editable']))
                            {
                                $this->colModel[$k]['editable'] = $ed;
                            }
                        }
                        $this->gridOptions['colModel'] = $this->colModel;
                        if (isset($this->gridOptions['postData'])) $this->gridOptions['postData'] = jqGridUtils::array_extend($this->gridOptions['postData'], array(
                            $oper => $this->GridParams["query"]
                        ));
                        else $this->setGridOptions(array(
                            "postData" => array(
                                $oper => $this->GridParams["query"]
                            )
                        ));
                        if (isset($this->primaryKey))
                        {
                            $this->GridParams["id"] = $this->primaryKey;
                        }
                        $this->setGridOptions(array(
                            "prmNames" => $this->GridParams
                        ));
                        $s = '';
                        if ($createtbl)
                        {
                            $tmptbl = $tblelement;
                            if (strpos($tblelement, "#") === false)
                            {
                                $tblelement = "#" . $tblelement;
                            }
                            else
                            {
                                $tmptbl = substr($tblelement, 1);
                            }
                            $s .= "<table id='" . $tmptbl . "'></table>";
                        }
                        if (strlen($pager) > 0)
                        {
                            $tmppg = $pager;
                            if (strpos($pager, "#") === false)
                            {
                                $pager = "#" . $pager;
                            }
                            else
                            {
                                $tmppg = substr($pager, 1);
                            }
                            if ($createpg)
                            {
                                $s .= "<div id='" . $tmppg . "'></div>";
                            }
                        }
                        if (!isset($this->gridOptions['loadError']))
                        {
                            $err = "function(xhr,status, err){ try {jQuery.jgrid.info_dialog(jQuery.jgrid.errors.errcap,'<div class=\"ui-state-error\">'+ xhr.responseText +'</div>', jQuery.jgrid.edit.bClose,{buttonalign:'right'});
} catch(e) { alert(xhr.responseText);
} }";
                            $this->setGridEvent('loadError', $err);
                        }
                        if (strlen($pager) > 0) $this->setGridOptions(array(
                            "pager" => $pager
                        ));
                        if ($this->sharedEditOptions == true)
                        {
                            $this->gridOptions['editOptions'] = $this->editOptions;
                        }
                        if ($this->sharedAddOptions == true)
                        {
                            $this->gridOptions['addOptions'] = $this->addOptions;
                        }
                        if ($this->sharedDelOptions == true)
                        {
                            $this->gridOptions['delOptions'] = $this->delOptions;
                        }
                        if ($script)
                        {
                            $s .= "<script type='text/javascript'>";
                            $s .= "jQuery(document).ready(function($) {";
                        }
                        $s .= "jQuery('" . $tblelement . "').jqGrid(" . jqGridUtils::encode($this->gridOptions) . ");
";
                        if ($this->navigator && strlen($pager) > 0)
                        {
                            $s .= "jQuery('" . $tblelement . "').jqGrid('navGrid','" . $pager . "'," . jqGridUtils::encode($this->navOptions);
                            $s .= "," . jqGridUtils::encode($this->editOptions);
                            $s .= "," . jqGridUtils::encode($this->addOptions);
                            $s .= "," . jqGridUtils::encode($this->delOptions);
                            $s .= "," . jqGridUtils::encode($this->searchOptions);
                            $s .= "," . jqGridUtils::encode($this->viewOptions) . ");
";
                            if ($this->navOptions["excel"] == true)
                            {
                                $eurl = $this->getGridOption('url');
                                $exexcel = <<<EXCELE
onClickButton : function(e)
{
    try {
        jQuery("$tblelement").jqGrid('excelExport',{tag:'excel', url:'$eurl'});

    } catch(e) {
        window.location= '$eurl?oper=excel';

    }
}
EXCELE;
                                

                                $s .= "jQuery('" . $tblelement . "').jqGrid('navButtonAdd','" . $pager . "',{id:'" . $tmppg . "_excel', caption:'" . $this->expoptions['excel']['caption'] . "',title:'" . $this->expoptions['excel']['title'] . "'," . $exexcel . ",buttonicon:'" . $this->expoptions['excel']['buttonicon'] . "'});
";
                            }
                            if ($this->navOptions["pdf"] == true)
                            {
                                $eurl = $this->getGridOption('url');
                                $expdf = <<<PDFE
onClickButton : function(e)
{
    try {
        jQuery("$tblelement").jqGrid('excelExport',{tag:'pdf', url:'$eurl'});

    } catch(e) {
        window.location= '$eurl?oper=pdf';

    }
}
PDFE;
                                

                                $s .= "jQuery('" . $tblelement . "').jqGrid('navButtonAdd','" . $pager . "',{id:'" . $tmppg . "_pdf',caption:'" . $this->expoptions['pdf']['caption'] . "',title:'" . $this->expoptions['pdf']['title'] . "'," . $expdf . ", buttonicon:'" . $this->expoptions['pdf']['buttonicon'] . "'});
";
                            }
                            if ($this->navOptions["csv"] == true)
                            {
                                $eurl = $this->getGridOption('url');
                                $excsv = <<<CSVE
onClickButton : function(e)
{
    try {
        jQuery("$tblelement").jqGrid('excelExport',{tag:'csv', url:'$eurl'});

    } catch(e) {
        window.location= '$eurl?oper=csv';

    }
}
CSVE;
                                

                                $s .= "jQuery('" . $tblelement . "').jqGrid('navButtonAdd','" . $pager . "',{id:'" . $tmppg . "_csv',caption:'" . $this->expoptions['csv']['caption'] . "',title:'" . $this->expoptions['csv']['title'] . "'," . $excsv . ",buttonicon:'" . $this->expoptions['csv']['buttonicon'] . "'});
";
                            }
                            if ($this->navOptions["columns"] == true)
                            {
                                $clopt = jqGridUtils::encode($this->expoptions['columns']['options']);
                                $excolumns = <<<COLUMNS
onClickButton : function(e)
{
    jQuery("$tblelement").jqGrid('columnChooser',$clopt);

}
COLUMNS;
                                

                                $s .= "jQuery('" . $tblelement . "').jqGrid('navButtonAdd','" . $pager . "',{id:'" . $tmppg . "_col',caption:'" . $this->expoptions['columns']['caption'] . "',title:'" . $this->expoptions['columns']['title'] . "'," . $excolumns . ",buttonicon:'" . $this->expoptions['columns']['buttonicon'] . "'});
";
                            }
                        }
                        if ($this->inlineNav && strlen($pager) > 0)
                        {
                            $aftersave = <<<AFTERS
function(id, res)
{
    res = res.responseText.split("#");

    try {
        $(this).jqGrid('setCell', id, res[0], res[1]);

        $("#"+id, "#"+this.p.id).removeClass("jqgrid-new-row").attr("id",res[1] );

        $(this)[0].p.selrow = res[1];

    } catch(asr) {}
}
AFTERS;
                            

                            $this->inlineNavOpt['addParams'] = jqGridUtils::array_extend($this->inlineNavOpt['addParams'], array(
                                "aftersavefunc" => "js:" . $aftersave
                            ));
                            $this->inlineNavOpt['editParams'] = jqGridUtils::array_extend($this->inlineNavOpt['editParams'], array(
                                "aftersavefunc" => "js:" . $aftersave
                            ));
                            $s .= "jQuery('" . $tblelement . "').jqGrid('inlineNav','" . $pager . "'," . jqGridUtils::encode($this->inlineNavOpt) . ");
\n";
                        }
                        if ($this->toolbarfilter)
                        {
                            $s .= "jQuery('" . $tblelement . "').jqGrid('filterToolbar'," . jqGridUtils::encode($this->filterOptions) . ");
\n";
                        }
                        $gM = count($this->gridMethods);
                        if ($gM > 0)
                        {
                            for ($i = 0;$i < $gM;$i++)
                            {
                                $s .= $this->gridMethods[$i] . "\n";
                            }
                        }
                        if (strlen($this->customCode) > 0) $s .= jqGridUtils::encode($this->customCode);
                        if ($script) $s .= " });
</script>";
                        if ($echo)
                        {
                            echo $s;
                        }
                        return $echo ? "" : $s;
                    }
                }
            } ?>