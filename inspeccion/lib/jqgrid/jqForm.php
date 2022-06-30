<?php class jqForm
{
    protected $aelements = array();
    protected $conn;
    protected $dbtype = 'none';
    protected $idprefix;
    private $maxcol = 2;
    private $frmheader = false;
    private $frmfooter = false;
    private $tblstyle = "";
    private $labelstyle = "";
    private $datastyle = "";
    public $inputclass = "ui-widget-content ui-corner-all";
    public $SelectCommand = "";
    public $table = "";
    private $primarykeys = array();
    private $formevents = array();
    private $fields = array();
    public $oper;
    private $url;
    private $customCode;
    private $formoptions = array();
    private $errorMesage;
    public $DbDate = 'Y-m-d';
    public $DbTime = 'H:i:s';
    public $DbDateTime = 'Y-m-d H:i:s';
    public $UserDate = 'Y-m-d';
    public $UserTime = 'H:i:s';
    public $UserDateTime = 'Y-m-d H:i:s';
    public $serialKey = true;
    public $encoding = 'utf-8';
    public $trans = true;
    public $add = true;
    public $edit = true;
    public $demo = false;
    protected $_checkboxon = false;
    protected $_files = false;
    protected $fileupload = array();
    protected $filedef = array(
        'dir' => './',
        'filetypes' => '',
        'filesize' => 1048576,
        'fileprefix' => ''
    );
    function __construct($name, $aproperties = array())
    {
        $this->aelements[] = array();
        $this->oper = jqGridUtils::GetParam('jqform', 'no');
        $this->setFormProperties($name, $aproperties);
    }
    protected function propToString($prop, $exclude = null)
    {
        if ($exclude && is_array($exclude))
        {
            foreach ($exclude as $k => $v)
            {
                unset($prop[$v]);
            }
        }
        $ret = "";
        if (isset($prop['novalidate']) && $prop['novalidate'] == "1")
        {
            $prop['novalidate'] = "novalidate";
        }
        if (isset($prop['hidden']) && $prop['hidden'] == "1")
        {
            $prop['hidden'] = "hidden";
        }
        if (isset($prop['disabled']) && $prop['disabled'] == "1")
        {
            $prop['disabled'] = "disabled";
        }
        if (isset($prop['readonly']) && $prop['readonly'] == "1")
        {
            $prop['readonly'] = "readonly";
        }
        if (isset($prop['required']) && $prop['required'] == "1")
        {
            $prop['required'] = "required";
        }
        if (isset($prop['autofocus']) && $prop['autofocus'] == "1")
        {
            $prop['autofocus'] = "autofocus";
        }
        if (isset($prop['checked']) && $prop['checked'] == "1")
        {
            $prop['autofocus'] = "checked";
        }
        if (isset($prop['formnovalidate']) && $prop['formnovalidate'] == "1")
        {
            $prop['formnovalidate'] = "formnovalidate";
        }
        if (is_array($prop) && count($prop) > 0)
        {
            $new_array = array_map(create_function('$key, $value', 'return $key." = \"".$value."\" ";
') , array_keys($prop) , array_values($prop));
            $ret = implode($new_array);
        }
        return $ret;
    }
    public function setConnection($db)
    {
        if ($db)
        {
            $this->conn = $db;
            if (class_exists('jqGridDB')) $interface = jqGridDB::getInterface();
            else $interface = 'none';
            if ($interface == 'pdo' && is_object($this->conn))
            {
                $this
                    ->conn
                    ->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->dbtype = $this
                    ->conn
                    ->getAttribute(PDO::ATTR_DRIVER_NAME);
            }
            else
            {
                $this->dbtype = $interface;
            }
        }
    }
    public function setColumnLayout($layout = 'twocolumn')
    {
        if ($layout && $layout == 'onecolumn')
        {
            $this->maxcol = 1;
        }
        else
        {
            $this->maxcol = 2;
        }
    }
    public function setUrl($nurl)
    {
        if ($nurl && strlen($nurl) > 0)
        {
            $this->url = $nurl;
        }
    }
    protected function setLabel($aprop)
    {
        $lbl = "<td style=\"" . $this->labelstyle . "\"";
        if (isset($aprop['labelalign']))
        {
            $lbl .= " align=\"" . $aprop['labelalign'] . "\">";
        }
        else
        {
            $lbl .= ">";
        }
        $lbl .= " <label for=\"" . $aprop['id'] . "\"";
        if (isset($aprop['labelclass']))
        {
            $lbl .= " class=\"" . $aprop['labelclass'] . "\">";
        }
        else
        {
            $lbl .= ">";
        }
        if (isset($aprop['label']))
        {
            $lbl .= $aprop['label'];
        }
        $lbl .= "</label>";
        $lbl .= "</td>";
        return $lbl;
    }
    public function setTableStyles($table = '', $label = '', $data = '')
    {
        $this->tblstyle = $table;
        $this->labelstyle = $label;
        $this->datastyle = $data;
    }
    protected function setFormProperties($name, $aproperties = array())
    {
        $this->aelements[0] = array(
            "name" => $name,
            "type" => "form",
            "prop" => $aproperties
        );
    }
    public function setFormHeader($content, $icon = "", $style = null)
    {
        if (!$style)
        {
            $style = array(
                "style" => "padding:6px;
"
            );
        }
        $hdr = "<div class=\"ui-state-default ui-corner-all\" " . $this->propToString($style) . ">";
        if ($icon && strlen($icon) > 0)
        {
            $hdr .= "<span class=\"ui-icon " . $icon . "\" style=\"float:left;
 margin:-2px 5px 0 0;
\"></span>";
        }
        $hdr .= $content;
        $hdr .= "</div>";
        $this->frmheader = $hdr;
    }
    public function setFormFooter($content, $style = null)
    {
        if (!$style)
        {
            $style = array(
                "style" => "padding:6px;
"
            );
        }
        $hdr = "<div " . $this->propToString($style) . ">";
        $hdr .= $content;
        $hdr .= "</div>";
        $this->frmfooter = $hdr;
    }
    public function setPrimaryKeys($keys)
    {
        $this->primarykeys = explode(",", $keys);
    }
    public function addElement($name, $type, $aproperties = array())
    {
        $this->aelements[] = array(
            "name" => $name,
            "type" => $type,
            "prop" => $aproperties
        );
        if (strtolower($type) == 'file')
        {
            $this->_files = true;
            $this->fileupload[$name] = $this->filedef;
        }
    }
    public function addEvent($field, $event, $code)
    {
        $this->formevents[] = array(
            "field" => $field,
            "event" => $event,
            "code" => "js:" . $code
        );
    }
    public function addGroup($name, $elemetnts, $properties)
    {
        $this->aelements[] = array(
            "name" => $name,
            "type" => "group",
            "prop" => $properties,
            "elem" => $elemetnts
        );
    }
    protected function _buildOptions($datastr = null, $datasql = '')
    {
        $str = "";
        if ($datastr)
        {
            if (is_array($datastr))
            {
                foreach ($datastr as $k => $v)
                {
                    $str .= '<option value="' . $k . '">' . $v . '</option>';
                }
            }
            else if (is_string($datastr))
            {
                $elements = explode(";
", $datastr);
                foreach ($elements as $k => $v)
                {
                    $elm = explode(":", $v);
                    $str .= '<option value="' . $elm[0] . '">' . $elm[1] . '</option>';
                }
            }
        }
        if ($datasql && strlen($datasql) > 0 && $this->conn)
        {
            try
            {
                $query = jqGridDB::query($this->conn, $datasql);
                if ($query)
                {
                    while ($row = jqGridDB::fetch_num($query, $this->conn))
                    {
                        if (count($row) == 2)
                        {
                            $str .= '<option value="' . $row[0] . '">' . $row[1] . '</option>';
                        }
                        else
                        {
                            $str .= '<option value="' . $row[0] . '">' . $row[0] . '</option>';
                        }
                    }
                }
            }
            catch(Exception $e)
            {
            }
        }
        return $str;
    }
    public function createElement($name, $type, $prop)
    {
        $retstr = "";
        if (!isset($prop['class']))
        {
            $prop['class'] = "";
        }
        switch (strtolower($type))
        {
            case 'select':
                if (!isset($prop['datalist'])) $prop['datalist'] = "";
                if (!isset($prop['datasql'])) $prop['datasql'] = "";
                $prop['class'] .= " ui-select " . $this->inputclass;
                $retstr = "<select name='" . $name . "' " . $this->propToString($prop, array(
                    'label',
                    'labelalign',
                    'labelclass',
                    'datalist',
                    'datasql'
                )) . ">";
                $retstr .= $this->_buildOptions($prop['datalist'], $prop['datasql']);
                $retstr .= "</select>";
                break;
            case 'textarea':
                $prop['class'] .= " ui-textarea " . $this->inputclass;
                $retstr = "<textarea name='" . $name . "' " . $this->propToString($prop, array(
                    'label',
                    'labelalign',
                    'labelclass',
                    'type'
                )) . "/></textarea>";
                break;
            case 'button':
            case 'submit':
            case 'reset':
                $prop['type'] = $type;
                $prop['class'] .= ($prop['class'] != "" ? " " : "") . "ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary";
                $retstr = "<input " . $this->propToString($prop, array(
                    'label',
                    'labelalign',
                    'labelclass'
                )) . "/>";
                break;
            case 'radio':
                $prop['type'] = $type;
                $prop['class'] .= " ui-input " . $this->inputclass;
                $text = !isset($prop['text']) ? "" : $prop['text'];
                $retstr = "<input " . $this->propToString($prop, array(
                    'label',
                    'labelalign',
                    'labelclass',
                    'text'
                )) . "/>" . $text;
                break;
            case 'checkbox':
                $this->_checkboxon = true;
                if (!isset($prop['value']))
                {
                    $prop['value'] = 'on';
                }
                if (!isset($prop['offval']))
                {
                    $prop['offval'] = 'off';
                }
            default:
                $prop['type'] = $type;
                $prop['class'] .= " ui-input " . $this->inputclass;
                $retstr = "<input " . $this->propToString($prop, array(
                    'label',
                    'labelalign',
                    'labelclass'
                )) . "/>";
            }
            return $retstr;
        }
        public function createGroup($name, $elements, $prop = null)
        {
            if (!isset($prop['style']))
            {
                $prop['style'] = "width:100%;
";
            }
            else
            {
                if (strpos($prop['style'], "width") == false)
                {
                    $prop['style'] .= "width:100%;
";
                }
            }
            if (!isset($prop['separator'])) $prop['separator'] = " ";
            if (!isset($prop['label'])) $prop['label'] = "";
            $gstr = "<div name='" . $name . "' " . $this->propToString($prop, array(
                'label',
                'labelalign',
                'labelclass',
                'separator'
            )) . ">";
            $gstr .= $prop['label'] . implode($prop['separator'], $elements);
            $gstr .= "</div>";
            return $gstr;
        }
        public function setUploadOptions($name, $options)
        {
            if (is_array($options))
            {
                $this->fileupload[$name] = jqGridUtils::array_extend($this->filedef, $options);
            }
        }
        protected function saveUploads()
        {
            $ret = true;
            if ($this->_files)
            {
                if (!empty($_FILES))
                {
                    function trv($value)
                    {
                        return trim($value);
                    }
                    foreach ($this->fileupload as $k => $v)
                    {
                        if ((!empty($_FILES[$k])) && ($_FILES[$k]['error'] == 0))
                        {
                            $filename = basename($_FILES[$k]['name']);
                            $ext = substr($filename, strrpos($filename, '.') + 1);
                            if (trim($v['filetypes']) == '') $ftype = false;
                            else $ftype = explode(",", $v['filetypes']);
                            if ($ftype)
                            {
                                $ftype = array_map("trv", $ftype);
                                if (!in_array($ext, $ftype))
                                {
                                    $ret = false;
                                    $this->errorMesage = "The file: " . $filename . ". you attempted to upload is not allowed.";
                                    break;
                                }
                            }
                            if (filesize($_FILES[$k]['tmp_name']) > $v['filesize'])
                            {
                                $ret = false;
                                $this->errorMesage = "The file: " . $filename . " you attempted to upload is too large.";
                                break;
                            }
                            if (!is_writable($v['dir']))
                            {
                                $ret = false;
                                $this->errorMesage = "You cannot upload to the specified directory, please CHMOD it to 777.";
                                break;
                            }
                            $newname = $v['dir'] . $v['prefix'] . $filename;
                            if (!file_exists($newname))
                            {
                                if ($this->demo)
                                {
                                    unlink($_FILES[$k]['tmp_name']);
                                    echo "File: " . $filename . " succefully uploaded.";
                                }
                                else if ((move_uploaded_file($_FILES[$k]['tmp_name'], $newname)))
                                {
                                }
                                else
                                {
                                    $ret = false;
                                    $this->errorMesage = "Error: A problem occurred during file upload!";
                                    break;
                                }
                            }
                            else
                            {
                                $ret = false;
                                $this->errorMesage = "Error: File " . $_FILES[$k]["name"] . " already exists";
                                break;
                            }
                        }
                        else
                        {
                            if ($_FILES[$k]['error'] != 4)
                            {
                                $ret = false;
                                $this->errorMesage = "A problem occurred during file upload! Error Number: " . $_FILES[$k]['error'];
                                break;
                            }
                        }
                    }
                }
            }
            return $ret;
        }
        public function setJSCode($code)
        {
            $this->customCode = "js:" . $code;
        }
        public function setAjaxOptions($aoptions)
        {
            if (is_array($aoptions) && count($aoptions) > 0)
            {
                $this->formoptions = jqGridUtils::array_extend($this->formoptions, $aoptions);
            }
        }
        protected function _buildFields()
        {
            $result = false;
            if ($this->table)
            {
                $wh = ($this->dbtype == 'sqlite') ? "" : " WHERE 1=2";
                $sql = "SELECT * FROM " . $this->table . $wh;
                try
                {
                    $select = jqGridDB::query($this->conn, $sql);
                    if ($select)
                    {
                        $colcount = jqGridDB::columnCount($select);
                        $rev = array();
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
                        $this->errorMesage = jqGridDB::errorMessage($this->conn);
                        throw new Exception($this->errorMesage);
                    }
                }
                catch(Exception $e)
                {
                    $result = false;
                    if (!$this->errorMesage) $this->errorMesage = $e->getMessage();
                    echo $this->errorMesage;
                }
            }
            return $result;
        }
        public function getPrimaryKeyId()
        {
            return $this->primarykeys;
        }
        private function outputDemoData($sql, $data)
        {
            $str = "";
            $str .= "<div class='ui-widget ui-state-highlight' style='margin:5px 5px;
 padding:5px 5px;
width:600px'>";
            $str .= "<div><b>Response:</b></div>";
            $str .= "<div>SQL Command:</div><div><b>" . $sql . "</b></div>";
            $str .= "<div>Data:</div><div><b>" . implode(" ,<br/> ", $data) . "</b></div>";
            $str .= "</div>";
            return $str;
        }
        public function insert($data)
        {
            if (!$this->add) return false;
            if (!$this->_buildFields())
            {
                return false;
            }
            $datefmt = $this->UserDate;
            $timefmt = $this->UserDateTime;
            if ($this->serialKey)
            {
                foreach ($this->primarykeys as $k => $v)
                {
                    unset($data[$v]);
                }
            }
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
                else
                {
                    switch ($t)
                    {
                        case 'date':
                            $v = $datefmt != $this->DbDate ? jqGridUtils::parseDate($datefmt, $value, $this->DbDate) : $value;
                        break;
                        case 'datetime':
                            $v = $timefmt != $this->DbDateTime ? jqGridUtils::parseDate($timefmt, $value, $this->DbDateTime) : $value;
                        break;
                        case 'time':
                            $v = $this->UserTime != $this->DbTime ? jqGridUtils::parseDate($this->UserTime, $value, $this->DbTime) : $value;
                        break;
                        case 'int':
                            $v = (int)$value;
                        default:
                            $v = $value;
                    }
                }
                $types[] = $t;
                $binds[] = $v;
                unset($v);
            }
            $result = false;
            if (count($insertFields) > 0)
            {
                $sql = "INSERT INTO " . $this->table . "(" . implode(', ', $rowFields) . ")" . " VALUES( " . implode(', ', $insertFields) . ")";
                if ($this->demo)
                {
                    echo $this->outputDemoData($sql, $binds);
                    return true;
                }
                $stmt = jqGridDB::prepare($this->conn, $sql, $binds, false);
                if ($stmt)
                {
                    jqGridDB::bindValues($stmt, $binds, $types);
                    if ($this->trans)
                    {
                        try
                        {
                            jqGridDB::beginTransaction($this->conn);
                            $result = jqGridDB::execute($stmt, $binds, $this->conn);
                            if ($result)
                            {
                                $result = jqGridDB::commit($this->conn);
                            }
                            $ret = $result ? true : false;
                            jqGridDB::closeCursor($this->dbtype == "adodb" ? $result : $stmt);
                            if (!$ret)
                            {
                                $this->errorMessage = jqGridDB::errorMessage($this->conn);
                                throw new Exception($this->errorMesage);
                            }
                        }
                        catch(Exception $e)
                        {
                            jqGridDB::rollBack($this->conn);
                            $result = false;
                            if (!$this->errorMesage) $this->errorMesage = $e->getMessage();
                            echo $this->errorMesage;
                        }
                    }
                    else
                    {
                        try
                        {
                            $result = jqGridDB::execute($stmt, $binds, $this->conn);
                            $ret = $result ? true : false;
                            jqGridDB::closeCursor($this->dbtype == "adodb" ? $result : $stmt);
                            if (!$ret)
                            {
                                $this->errorMessage = jqGridDB::errorMessage($this->conn);
                                throw new Exception($this->errorMesage);
                            }
                        }
                        catch(Exception $e)
                        {
                            if (!$this->errorMesage) $this->errorMesage = $e->getMessage();
                            echo $this->errorMesage;
                        }
                    }
                }
                else
                {
                    $result = false;
                }
            }
            else
            {
                $result = false;
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
            $datefmt = $this->UserDate;
            $timefmt = $this->UserDateTime;
            $tableFields = array_keys($this->fields);
            $rowFields = array_intersect($tableFields, array_keys($data));
            $updateFields = array();
            $binds = array();
            $types = array();
            $v2 = array();
            $t2 = array();
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
                else
                {
                    switch ($t)
                    {
                        case 'date':
                            $v = $datefmt != $this->DbDate ? jqGridUtils::parseDate($datefmt, $value, $this->DbDate) : $value;
                        break;
                        case 'datetime':
                            $v = $timefmt != $this->DbDateTime ? jqGridUtils::parseDate($timefmt, $value, $this->DbDateTime) : $value;
                        break;
                        case 'time':
                            $v = $this->UserTime != $this->DbTime ? jqGridUtils::parseDate($this->UserTime, $value, $this->DbTime) : $value;
                        break;
                        default:
                            $v = $value;
                    }
                }
                if (in_array($field, $this->primarykeys))
                {
                    $v2[] = $v;
                    $t2[] = $t;
                }
                else
                {
                    $updateFields[] = $field . " = ?";
                    $binds[] = $v;
                    $types[] = $t;
                }
                unset($v);
            }
            if (count($v2) == 0) die("Primary value is missing");
            foreach ($v2 as $kk => $vv)
            {
                $binds[] = $vv;
                $types[] = $t2[$kk];
            }
            $result = false;
            if (count($updateFields) > 0)
            {
                $sql = "UPDATE " . $this->table . " SET " . implode(', ', $updateFields) . " WHERE " . implode("= ? AND ", $this->primarykeys) . " = ?";
                if ($this->demo)
                {
                    echo $this->outputDemoData($sql, $binds);
                    return true;
                }
                $stmt = jqGridDB::prepare($this->conn, $sql, $binds, false);
                if ($stmt)
                {
                    jqGridDB::bindValues($stmt, $binds, $types);
                    if ($this->trans)
                    {
                        try
                        {
                            jqGridDB::beginTransaction($this->conn);
                            $result = jqGridDB::execute($stmt, $binds, $this->conn);
                            $ret = $result ? true : false;
                            jqGridDB::closeCursor($this->dbtype == "adodb" ? $result : $stmt);
                            if ($ret)
                            {
                                $result = jqGridDB::commit($this->conn);
                            }
                            else
                            {
                                $this->errorMessage = jqGridDB::errorMessage($this->conn);
                                throw new Exception($this->errorMesage);
                            }
                        }
                        catch(Exception $e)
                        {
                            jqGridDB::rollBack($this->conn);
                            $result = false;
                            if (!$this->errorMesage) $this->errorMesage = $e->getMessage();
                            echo $this->errorMesage;
                        }
                    }
                    else
                    {
                        try
                        {
                            $result = jqGridDB::execute($stmt, $binds, $this->conn);
                            $ret = $result ? true : false;
                            jqGridDB::closeCursor($this->dbtype == "adodb" ? $result : $stmt);
                            if (!$ret)
                            {
                                $this->errorMessage = jqGridDB::errorMessage($this->conn);
                                throw new Exception($this->errorMesage);
                            }
                        }
                        catch(Exception $e)
                        {
                            $result = false;
                            if (!$this->errorMesage) $this->errorMesage = $e->getMessage();
                            echo $this->errorMesage;
                        }
                    }
                }
            }
            return $result;
        }
        public function save($data)
        {
            $keys = array();
            $types = array();
            foreach ($this->primarykeys as $k => $v)
            {
                $keys[] = $data[$v];
                $types[] = 'custom';
            }
            $sql = "SELECT COUNT(*) FROM " . $this->table . " WHERE " . implode(" = ? AND ", $this->primarykeys) . " =? ";
            try
            {
                $stmt = jqGridDB::prepare($this->conn, $sql, $keys, false);
                jqGridDB::bindValues($stmt, $keys, $types);
                $result = jqGridDB::execute($stmt, $keys, $this->conn);
                if ($this->dbtype == "adodb")
                {
                    $stmt = $result;
                }
                if ($result)
                {
                    $res = jqGridDB::fetch_num($stmt, $this->conn);
                    if ((int)$res[0] == 1)
                    {
                        $result = $this->update($data);
                    }
                    else if ((int)$res[0] == 0)
                    {
                        $result = $this->insert($data);
                    }
                }
                if (!$result)
                {
                    $this->errorMessage = jqGridDB::errorMessage($this->conn);
                    throw new Exception($this->errorMesage);
                }
                jqGridDB::closeCursor($stmt);
                if ($result)
                {
                    echo "success";
                }
            }
            catch(Exception $e)
            {
                $result = false;
                if (!$this->errorMesage) $this->errorMesage = $e->getMessage();
                echo $this->errorMesage;
            }
            return $result;
        }
        public function renderForm(array $params = null)
        {
            if ($this->oper == "no")
            {
                $row = $this->aelements[0];
                $str = "<form name=\"" . $row['name'] . "\" " . $this->propToString($row['prop']) . ">";
                $id = $row['prop']['id'];
                $acnt = count($this->aelements);
                $this->idprefix = $row['name'] . "_";
                $str .= "<table style=\"" . $this->tblstyle . "\">";
                if ($this->frmheader)
                {
                    $str .= "<thead><tr><th align='left' colspan=\"" . $this->maxcol . "\">";
                    $str .= $this->frmheader;
                    $str .= "</th></tr></thead>";
                }
                if ($this->frmfooter)
                {
                    $str .= "<tfoot><tr><td colspan=\"" . $this->maxcol . "\">";
                    $str .= $this->frmfooter;
                    $str .= "</td></tr></tfoot>";
                }
                $str .= "<tbody>";
                for ($i = 1;$i < $acnt;$i++)
                {
                    $row = $this->aelements[$i];
                    if (!isset($row['prop']['id']))
                    {
                        $row['prop']['id'] = $this->idprefix . $row['name'];
                    }
                    $row['prop']['name'] = $row['name'];
                    $trs = $row['type'] == 'hidden' ? " style='display:none;
'" : "";
                    $str .= "<tr" . $trs . ">";
                    if ($row['type'] == 'group')
                    {
                        $str .= "<td colspan=\"" . $this->maxcol . "\">";
                        $str .= $this->createGroup($row['name'], $row['elem'], $row['prop']);
                        $str .= "</td></tr>";
                        continue;
                    }
                    $str .= $this->setLabel($row['prop']);
                    if ($this->maxcol == 1)
                    {
                        $str .= "</tr><tr>";
                    }
                    $str .= "<td style=\"" . $this->datastyle . "\">";
                    $str .= $this->createElement($row['name'], $row['type'], $row['prop']);
                    $str .= "</td>";
                    $str .= "</tr>";
                }
                $str .= "</tbody></table>";
                $str .= "</form>";
                if ($this->demo)
                {
                    $str .= '<div id="demo"></div>';
                    if (isset($this->formoptions['success']) && strlen($this->formoptions['success']) > 0)
                    {
                    }
                    else
                    {
                        $this->formoptions['success'] = "js:function(response){ jQuery('#demo').empty().html(response).show('fast', 'swing', setTimeout(function(){ jQuery('#demo').fadeOut();
 },2500) ) ;
}";
                    }
                }
                $cnt = count($this->formevents);
                $str .= "<script type='text/javascript'>";
                $str .= "jQuery(document).ready(function($) {";
                if ($cnt > 0)
                {
                    foreach ($this->formevents as $k => $v)
                    {
                        $str .= "jQuery('#" . $v["field"] . "').bind('" . $v["event"] . "'," . jqGridUtils::encode($v["code"]) . ");
";
                    }
                }
                $nm = $row['name'];
                $url = $this->url;
                if (!$url)
                {
                    $url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
                }
                $aget = jqGridUtils::Strip($_GET);
                $apost = jqGridUtils::Strip($_POST);
                $aget = jqGridUtils::array_extend($aget, $apost);
                $aget['jqform'] = 'get';
                $ajson = json_encode($aget);
                $func = << < AJAXFUNCjQuery . ajax(
                {
                    url:
                        "$url", data:
                            $ajson, type:
                                'POST', dataType:
                                    'json', success:
                                        function (req, err)
                                        {
                                            $ . each(req, function (i, n)
                                            {
                                                var inel = $('[name="' + i + '"]', "#$id");
                                                try
                                                {
                                                    if (inel . is(":checkbox"))
                                                    {
                                                        if (n == inel . val())
                                                        {
                                                            inel[!!jQuery . fn . prop ? 'prop' : 'attr']("checked", true);
                                                        }
                                                    }
                                                    else
                                                    {
                                                        inel . val(n);
                                                    }
                                                }
                                                catch(error)
                                                {
                                                }
                                            });
                                        }
                                    });
                                    AJAXFUNC;
                                    if ($this->SelectCommand && strlen($this->SelectCommand) > 0)
                                    {
                                        $str .= $func;
                                    }
                                    $this->formoptions['url'] = $url;
                                    if (!isset($this->formoptions['data']))
                                    {
                                        $this->formoptions['data'] = array();
                                    }
                                    if ($this->_checkboxon)
                                    {
                                        if (!isset($this->formoptions['beforeSerialize']))
                                        {
                                            $bfs = << < BFSfunction(form, data)
                                            {
                                                var ts = this;
                                                jQuery("input:checkbox", form) . each(function ()
                                                {
                                                    if (!jQuery(this) . is(":checked"))
                                                    {
                                                        ts . data[this . name] = jQuery(this) . attr("offval");
                                                    }
                                                });
                                            }
                                            BFS;
                                            $this->formoptions['beforeSerialize'] = "js:" . $bfs;
                                        }
                                        else
                                        {
                                            $bfs = << < BFSvarts = this;
                                            jQuery("input:checkbox", form) . each(function ()
                                            {
                                                if (!jQuery(this) . is(":checked"))
                                                {
                                                    ts . data[this . name] = jQuery(this) . attr("offval");
                                                }
                                            });
                                            BFS;
                                            $rpos = strrpos($this->formoptions['beforeSerialize'], '}');
                                            $this->formoptions['beforeSerialize'] = substr_replace($this->formoptions['beforeSerialize'], $bfs . "}", $rpos);
                                        }
                                    }
                                    $this->formoptions['data']["jqform"] = "save";
                                    $str .= "jQuery('#" . $id . "').submit(function(){ jQuery(this).ajaxSubmit(" . jqGridUtils::encode($this->formoptions) . ");
 return false;
});
";
                                    if ($this->customCode && strlen($this->customCode) > 0)
                                    {
                                        $str .= jqGridUtils::encode($this->customCode);
                                    }
                                    $str .= "jQuery('button, input:submit, input:reset','#" . $id . "').hover(function(){jQuery(this).addClass('ui-state-hover');
},function(){ jQuery(this).removeClass('ui-state-hover');
});
";
                                    $str .= "});
";
                                    $str .= "</script>";
                                    return $str;
                                }
                                else if ($this->oper == 'get' && $this->conn)
                                {
                                    $sqlstr = jqGridDB::limit($this->SelectCommand, $this->dbtype, 1, 0);
                                    if ($this->dbtype != "adodb")
                                    {
                                        $stmt = jqGridDB::prepare($this->conn, $sqlstr, $params);
                                        $ret = jqGridDB::execute($stmt, $params, $this->conn);
                                    }
                                    else
                                    {
                                        $stmt = $sqlstr;
                                    }
                                    $result = jqGridDB::fetch_object($stmt, false, $this->conn);
                                    jqGridDB::closeCursor($stmt);
                                    $sqlstr = null;
                                    return json_encode($result);
                                }
                                else if ($this->oper == 'save' && $this->conn)
                                {
                                    $ret = true;
                                    if ($this->_files)
                                    {
                                        $ret = $this->saveUploads();
                                    }
                                    if ($ret)
                                    {
                                        $sdata = jqGridUtils::Strip($_POST);
                                        $this->save($sdata);
                                    }
                                    else
                                    {
                                        echo $this->errorMesage;
                                    }
                                }
                            }
                        } ?>