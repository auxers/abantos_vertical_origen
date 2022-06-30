<?php class jqAutocomplete
{
    public $version = '4.4.4.0';
    protected $aoptions = array(
        "appendTo" => "body",
        "disabled" => false,
        "delay" => 300,
        "minLength" => 1,
        "source" => null
    );
    protected $conn = null;
    protected $dbtype = '';
    protected $source;
    protected $element;
    protected $itemLength = 10;
    protected $runAll = true;
    protected $term = '';
    protected $cachearray = "cache";
    public $cache = false;
    public $SelectCommand = '';
    public $searchType = "startWith";
    public $loadAll = false;
    public $ajaxtype = "GET";
    public $scroll = false;
    public $height = "110px";
    public $encoding = "utf-8";
    public $fontsize = '11px';
    public $strictcheck = true;
    public function isNotACQuery()
    {
        return $this->runAll;
    }
    function __construct($db = null, $odbctype = '')
    {
        if (class_exists('jqGridDB')) $interface = jqGridDB::getInterface();
        else $interface = 'local';
        $this->conn = $db;
        if ($interface == 'pdo')
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
            $this->dbtype = $interface . $odbctype;;
        }
        $this->term = jqGridUtils::GetParam('term', -1);
        if ($this->term !== - 1) $this->runAll = false;
        $this->element = jqGridUtils::GetParam('acelem', '');
    }
    public function getOption($option)
    {
        if (array_key_exists($option, $this->aoptions)) return $this->aoptions[$option];
        else return false;
    }
    public function setOption($option, $value = null)
    {
        if (!$this->runAll) return false;
        if (isset($option))
        {
            if (is_array($option))
            {
                foreach ($option as $key => $value)
                {
                    $this->aoptions[$key] = $value;
                }
                return true;
            }
            else if ($value != null)
            {
                $this->aoptions[$option] = $value;
            }
            return true;
        }
        return false;
    }
    public function setEvent($event, $code)
    {
        if ($this->runAll)
        {
            $this->aoptions[$event] = "js:" . $code;
        }
    }
    public function setSource($source)
    {
        if (!$this->runAll) return false;
        $this->source = $source;
    }
    private function _setSrc($element)
    {
        if (is_string($this->source))
        {
            if ($this->cache)
            {
                $this->cachearray .= rand(0, 10000);
            }
            $accache = <<< ACCACHE
function(request, response)
{
	request.acelem = '$element';

	request.oper = 'autocmpl';

	if( request.term in $this->cachearray )
	{
		response( $this->cachearray[ request.term ] );

		return;

	}
	$.ajax({
		url: "$this->source",
		dataType: "json",
		data: request,
		type: "$this->ajaxtype",
		error: function(res, status) {
			alert(res.status+" : "+res.statusText+". Status: "+status);

		},
		success: function( data ) {
			if(data) {
				$this->cachearray[ request.term ] = data;

				response( data );

			}
		}
	});

}
ACCACHE;
            

            $acnocache = <<< ACNOCACHE
function(request, response)
{
	request.acelem = '$element';

	request.oper = 'autocmpl';

	$.ajax({
		url: "$this->source",
		dataType: "json",
		data: request,
		type: "$this->ajaxtype",
		error: function(res, status) {
			alert(res.status+" : "+res.statusText+". Status: "+status);

		},
		success: function( data ) {
			response( data );

		}
	});

}
ACNOCACHE;
            

            if ($this->cache)
            {
                $res = "js:" . $accache;
            }
            else if ($this->loadAll)
            {
                $res = $this->getACData();
            }
            else
            {
                $res = "js:" . $acnocache;
            }
            $this->setOption('source', $res);
        }
        else if (is_array($this->source))
        {
            $this->setOption('source', $this->source);
        }
    }
    public function setLength($num)
    {
        if (is_int($num) && $num > 0)
        {
            $this->itemLength = $num;
        }
        else if (is_bool($num))
        {
            $this->itemLength = - 1;
            $this->loadAll = true;
        }
    }
    public function queryAutocomplete()
    {
        return $this->getACData();
    }
    private function getACData()
    {
        $result = array();
        if (strlen($this->SelectCommand) > 0)
        {
            $prmlen = substr_count($this->SelectCommand, "?");
            if ($prmlen > 0)
            {
                $params = array();
                if (strtolower($this->encoding) != 'utf-8')
                {
                    $this->term = iconv("utf-8", $this->encoding . "//TRANSLIT", $this->term);
                }
                for ($i = 1;$i <= $prmlen;$i++)
                {
                    switch ($this->searchType)
                    {
                        case 'startWith':
                            array_push($params, $this->term . "%");
                        break;
                        case 'contain':
                            array_push($params, "%" . $this->term . "%");
                        break;
                        case 'endWith':
                            array_push($params, "%" . $this->term);
                        break;
                        default:
                            array_push($params, $this->term);
                        break;
                    }
                }
            }
            else
            {
                $params = null;
            }
            $adodblimit = false;
            if ($this->itemLength > 0 && !$this->loadAll)
            {
                if ($this->dbtype == "adodb")
                {
                    $sql1 = jqGridDB::limit($this->SelectCommand, $this->dbtype, $this->itemLength, 0);
                    $adodblimit = true;
                }
                else
                {
                    $sqlCmd = jqGridDB::limit($this->SelectCommand, $this->dbtype, $this->itemLength, 0);
                }
            }
            else
            {
                $sqlCmd = $this->SelectCommand;
            }
            if (!$adodblimit)
            {
                $sql1 = jqGridDB::prepare($this->conn, $sqlCmd, $params, true);
                if ($this->dbtype == "adodb")
                {
                    $sql1 = jqGridDB::execute($sql1, $params, $this->conn);
                }
                else
                {
                    $ret = jqGridDB::execute($sql1, $params, $this->conn);
                }
            }
            $ncols = jqGridDB::columnCount($sql1);
            $res_arr = array();
            if ($this->dbtype == 'mysqli')
            {
                $fieldnames[0] = & $sql1;
                for ($i = 0;$i < $ncols;$i++)
                {
                    $fieldnames[$i + 1] = & $res_arr[$i];
                }
                call_user_func_array('mysqli_stmt_bind_result', $fieldnames);
            }
            if ($this->dbtype == 'oci8' || $this->dbtype == 'db2')
            {
                $ncols--;
            }
            while ($row = jqGridDB::fetch_num($sql1, $this->conn))
            {
                if ($this->dbtype == 'mysqli') $row = $res_arr;
                if ($ncols == 1)
                {
                    array_push($result, array(
                        "value" => $row[0],
                        "label" => $row[0]
                    ));
                }
                else if ($ncols == 2)
                {
                    array_push($result, array(
                        "value" => $row[0],
                        "label" => $row[1]
                    ));
                }
                else if ($ncols >= 3)
                {
                    array_push($result, array(
                        "value" => $row[0],
                        "label" => $row[1],
                        "id" => $row[2]
                    ));
                }
            }
            jqGridDB::closeCursor($sql1);
        }
        return $result;
    }
    public function renderAutocomplete($element, $target = false, $script = true, $echo = true, $runme = true)
    {
        if ($this->runAll && $runme)
        {
            $this->_setSrc($element);
            $s = "";
            if ($script)
            {
                $s .= "<script type='text/javascript'>";
                $s .= "jQuery(document).ready(function() {";
            }
            if ($this->cache)
            {
                $s .= "var $this->cachearray = {};
";
            }
            if ($target)
            {
                $trg = <<<TARGET
function(event, ui)
{
	// change function to set target value
	var ival;

	if(ui.item) {
		ival = ui.item.id || ui.item.value;

	}
	if(ival) {
		jQuery("$target").val(ival);

	} else {
		jQuery("$target").val("");

		if("$this->strictcheck" == "true"){
		this.value = "";

	}
}
}
TARGET;
                

                $this->setOption('change', "js:" . $trg);
            }
            $s .= "if(jQuery.ui) { if(jQuery.ui.autocomplete){";
            $s .= "jQuery('" . $element . "').autocomplete(" . jqGridUtils::encode($this->aoptions) . ");
";
            $s .= "jQuery('" . $element . "').autocomplete('widget').css('font-size','" . $this->fontsize . "');
";
            if ($this->scroll)
            {
                $s .= "jQuery('" . $element . "').autocomplete('widget').css({'height':'$this->height','overflow-y':'auto'});
";
            }
            $s .= "} }";
            if ($script) $s .= " });
</script>";
            if ($echo)
            {
                echo $s;
            }
            else
            {
                return $s;
            }
        }
        else
        {
            if (trim($this->element) === trim($element))
            {
                header("Content-type: text/x-json;
charset=" . $this->encoding);
                if (function_exists('json_encode') && strtolower($this->encoding) == 'utf-8')
                {
                    echo json_encode($this->getACData());
                }
                else
                {
                    echo jqGridUtils::encode($this->getACData());
                }
            }
        }
    }
} ?>