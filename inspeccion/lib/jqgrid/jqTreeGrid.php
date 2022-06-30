<?php include 'jqGrid.php';
class jqTreeGrid extends jqGridRender
{
    private $treemodel = 'nested';
    private $tableconfig = array(
        "id" => "id",
        "parent" => "parent",
        "left" => "lft",
        "right" => "rgt",
        "level" => "level",
        "leaf" => "isLeaf",
        "expanded" => "expanded",
        "loaded" => "loaded",
        "icon" => "icon"
    );
    private $res = array();
    private $data;
    private $leaf_nodes = array();
    public $expandAll = false;
    public $autoLoadNodes = true;
    public function setData($d)
    {
        $this->data = $d;
    }
    public function setLeafData($d)
    {
        $this->leaf_nodes = $d;
    }
    public function setTreeModel($model = 'nested')
    {
        if (strlen($model) > 0)
        {
            $this->treemodel = $model;
        }
    }
    public function getTreeModel()
    {
        return $this->treemodel;
    }
    public function setTableConfig($aconfig)
    {
        if (is_array($aconfig) && count($aconfig) > 0)
        {
            $this->tableconfig = jqGridUtils::array_extend($this->tableconfig, $aconfig);
            if (!isset($aconfig['table']))
            {
                $this->tableconfig['table'] = $this->table;
            }
        }
    }
    public function getTableConfig()
    {
        return $this->tableconfig;
    }
    public function getLeafNodes($node = 0)
    {
        $leaf = array();
        if (strlen($this->tableconfig['table']) > 0)
        {
            if ($this->treemodel == 'adjacency')
            {
                if ($node == 0)
                {
                    $where = "";
                }
                else
                {
                    $where = ' AND t1.%parent$s = ' . $node;
                }
                $s = jqGridUtils::sprintfn('SELECT t1.%id$s FROM %table$s AS t1 LEFT JOIN %table$s AS t2 ON t1.%id$s = t2.%parent$s WHERE t2.%id$s IS NULL' . $where, $this->tableconfig);
                if ($this->debug)
                {
                    $this->logQuery($s);
                }
                $q = jqGridDB::query($this->pdo, $s);
                if ($q)
                {
                    while ($row = jqGridDB::fetch_num($q, $this->pdo))
                    {
                        $leaf[$row[0]] = $row[0];
                    }
                }
                jqGridDB::closeCursor($q);
            }
            elseif ($this->treemodel == 'nested')
            {
            }
        }
        else
        {
            echo "no table set";
        }
        return $leaf;
    }
    protected function getChildNodes($node, $order_field = '', $id = false)
    {
        if ($this->treemodel == 'adjacency')
        {
            $order = "";
            if ($order_field) $order = "ORDER BY " . $order_field;
            $s = jqGridUtils::sprintfn('SELECT * FROM %table$s WHERE %parent$s = ' . (int)$node . ' ' . $order, $this->tableconfig);
            if ($this->debug)
            {
                $this->logQuery($s);
            }
            $q = jqGridDB::query($this->pdo, $s);
            if ($q)
            {
                while ($row = jqGridDB::fetch_assoc($q, $this->pdo))
                {
                    $nid = $row[$this->tableconfig['id']];
                    $this->res[] = $id ? $nid : $row;
                    if (!(isset($this->leaf_nodes[$nid]) && ($nid == $this->leaf_nodes[$nid])))
                    {
                        $this->getChildNodes($row[$this->tableconfig['id']], $order_field, $id);
                    }
                }
            }
            jqGridDB::closeCursor($q);
        }
        elseif ($this->treemodel == 'nested')
        {
            $s = jqGridUtils::sprintfn('SELECT %left$s, %right$s FROM %table$s WHERE %id$s = ' . (int)$node, $this->tableconfig);
            if ($this->debug)
            {
                $this->logQuery($s);
            }
            $q = jqGridDB::query($this->pdo, $s);
            $row = jqGridDB::fetch_num($q, $this->pdo);
            jqGridDB::closeCursor($q);
            if ($row)
            {
                $s = jqGridUtils::sprintfn('SELECT * FROM %table$s WHERE %left$s > ' . $row[0] . ' AND %left$s <' . $row[1], $this->tableconfig);
                if ($this->debug)
                {
                    $this->logQuery($s);
                }
                $q1 = jqGridDB::query($this->pdo, $s);
                while ($r = jqGridDB::fetch_assoc($q1, $this->pdo))
                {
                    $nid = $r[$this->tableconfig['id']];
                    $this->res[] = $id ? $nid : $r;
                }
                jqGridDB::closeCursor($q1);
            }
        }
    }
    protected function getChildren($node_id = null)
    {
        $children = null;
        if ((int)$node_id > 0)
        {
            $children = array();
            $parent = $this->tableconfig['parent'];
            foreach ($this->data as $id => $node)
            {
                if ((int)$node->{$parent} == (int)$node_id)
                {
                    $children[] = $node;
                }
            }
        }
        else
        {
            $children = $this->getRoots();
        }
        return $children;
    }
    protected function getRoots()
    {
        $roots = array();
        $parent = $this->tableconfig['parent'];
        if ($this->data)
        {
            foreach ($this->data as $id => $node)
            {
                if ($node->{$parent} === null)
                {
                    $roots[] = $node;
                }
            }
        }
        return $roots;
    }
    private function buildTreeArray($parent, $level, $res = null)
    {
        if ($this->treemodel == 'adjacency')
        {
            $result = $this->getChildren($parent);
            $slevel = $this->tableconfig['level'];
            $id = $this->tableconfig['id'];
            $leaf = $this->tableconfig['leaf'];
            $loaded = $this->tableconfig['loaded'];
            $expand = $this->tableconfig['expanded'];
            $expAll = $this->expandAll ? 'true' : 'false';
            $load = $this->autoLoadNodes ? 'false' : 'true';
            foreach ($result as $key => $node)
            {
                $node->{$slevel} = $level;
                $nid = (int)$node->{$id};
                $node->{$leaf} = (isset($this->leaf_nodes[$nid]) && ($nid == $this->leaf_nodes[$nid])) ? 'true' : 'false';
                $node->{$loaded} = $load;
                $node->{$expand} = $expAll;
                $this->res[] = $node;
                $this->buildTreeArray($node->{$id}, $level + 1);
            }
            return $this->res;
        }
        else if ($this->treemodel == 'nested')
        {
            $loaded = $this->tableconfig['loaded'];
            $expand = $this->tableconfig['expanded'];
            $expAll = $this->expandAll ? 'true' : 'false';
            $load = $this->autoLoadNodes ? 'false' : 'true';
            foreach ($res as $key => $node)
            {
                $node->{$loaded} = $load;
                $node->{$expand} = $expAll;
                $this->res[] = $node;
            }
            return $this->res;
        }
    }
    private function _setTreeGridOptions($model)
    {
        if (!$this->autoLoadNodes) $loadonce = true;
        else $loadonce = false;
        $treereader = array(
            "parent_id_field" => $this->tableconfig['parent'],
            "left_field" => $this->tableconfig['left'],
            "right_field" => $this->tableconfig['right'],
            "level_field" => $this->tableconfig['level'],
            "leaf_field" => $this->tableconfig['leaf'],
            "expanded_field" => $this->tableconfig['expanded'],
            "loaded" => $this->tableconfig['loaded'],
            "icon_field" => $this->tableconfig['icon']
        );
        if ($model == 'adjacency')
        {
            unset($treereader["left_field"], $treereader["right_field"]);
        }
        else
        {
            unset($treereader["parent_id_field"]);
        }
        $this->setGridOptions(array(
            "rowTotal" => - 1,
            "treeGrid" => true,
            "treedatatype" => $this->dataType,
            "treeGridModel" => $this->treemodel,
            "loadonce" => $loadonce,
            "rowNum" => 1000000,
            "scrollrows" => true,
            "viewrecords" => false,
            "treeReader" => $treereader
        ));
        if ($model == 'nested')
        {
            $this->setGridOptions(array(
                "sortname" => $this->tableconfig['left'],
                "sortorder" => 'ASC'
            ));
        }
    }
    private function renderAdjacency($summary, $params, $echo)
    {
        $response = null;
        $node = (int)jqGridUtils::GetParam("nodeid", "0");
        $n_lvl = (int)jqGridUtils::GetParam("n_level", "0");
        $data1 = $this->getLeafNodes($node);
        $this->setLeafData($data1);
        if ($this->autoLoadNodes)
        {
            $sql = $this->_setSQL();
            if ($node > 0)
            {
                $s = " " . $this->tableconfig["parent"] . " = " . (int)$node;
            }
            else
            {
                $s = " " . $this->tableconfig["parent"] . " IS NULL ";
            }
            if (preg_match("/WHERE/i", $sql)) $sql .= " AND " . $s;
            else $sql .= " WHERE " . $s;
            $this->readFromXML = false;
            $this->SelectCommand = $sql;
        }
        $this->performcount = false;
        $res = $this->queryGrid($summary, $params, false);
        $this->setData($res->rows);
        $n_lvl = $node == 0 ? 0 : $n_lvl + 1;
        $data = $this->buildTreeArray($node, $n_lvl);
        if (!isset($res->userdata)) $res->userdata = array();
        $response = array(
            "userdata" => $res->userdata,
            "rows" => $data,
            "total" => count($data) ,
            "page" => 1
        );
        if ($echo)
        {
            $this->_gridResponse($response);
        }
        else
        {
            return $response;
        }
    }
    private function renderNested($summary, $params, $echo)
    {
        $response = null;
        $node = (int)jqGridUtils::GetParam("nodeid", "0");
        $n_lvl = (int)jqGridUtils::GetParam("n_level", "0");
        if ($this->autoLoadNodes)
        {
            $sql = $this->_setSQL();
            $s = "";
            if ($node > 0)
            {
                $n_lft = (int)jqGridUtils::GetParam("n_left");
                $n_rgt = (int)jqGridUtils::GetParam("n_right");
                $s = " " . $this->tableconfig["left"] . " > " . $n_lft . " AND " . $this->tableconfig["left"] . " < " . $n_rgt . " AND " . $this->tableconfig["level"] . " = " . ($n_lvl + 1);
            }
            elseif ($n_lvl == 0)
            {
                $s = " " . $this->tableconfig["level"] . " = 0";
            }
            if (preg_match("/WHERE/i", $sql)) $sql .= " AND " . $s;
            else $sql .= " WHERE " . $s;
            $this->readFromXML = false;
            $this->SelectCommand = $sql;
        }
        $this->performcount = false;
        if (!$this->autoLoadNodes && $this->expandAll)
        {
            $qwg = $this->queryGrid($summary, $params, false);
            if (!isset($qwg->userdata)) $qwg->userdata = array();
            $data = $this->buildTreeArray(0, 0, $qwg->rows);
            $response = array(
                "userdata" => $qwg->userdata,
                "rows" => $data,
                "total" => count($data) ,
                "page" => 1
            );
            if ($echo)
            {
                $this->_gridResponse($response);
            }
            else
            {
                return $response;
            }
        }
        else
        {
            return $this->queryGrid($summary, $params, $echo);
        }
    }
    public function queryTree(array $summary = null, array $params = null, $echo = true)
    {
        $response = null;
        if ($this->treemodel == 'adjacency')
        {
            $response = $this->renderAdjacency($summary, $params, $echo);
        }
        elseif ($this->treemodel == 'nested')
        {
            $response = $this->renderNested($summary, $params, $echo);
        }
        else
        {
            $this->queryGrid($summary, $params, $echo);
        }
    }
    public function updateTreeNode($data)
    {
        return $this->update($data);
    }
    public function insertTreeNode($data)
    {
        $this->getLastInsert = true;
        if ($this->treemodel == 'nested')
        {
            $node = (isset($data['parent_id']) && (int)$data['parent_id'] > 0) ? $data['parent_id'] : 0;
            if ((int)$node > 0 && $node != 'null')
            {
                $s = jqGridUtils::sprintfn('SELECT %right$s, %level$s FROM %table$s WHERE %id$s = ' . (int)$node, $this->tableconfig);
            }
            else
            {
                $s = jqGridUtils::sprintfn('SELECT MAX( %right$s), "-1" as level FROM %table$s', $this->tableconfig);
            }
            if ($this->debug)
            {
                $this->logQuery($s);
            }
            $q = jqGridDB::query($this->pdo, $s);
            if (!$q)
            {
                $this->errorMesage = jqGridDB::errorMessage($this->pdo);
                if ($this->showError)
                {
                    $this->sendErrorHeader();
                }
                else
                {
                    die($this->errorMesage);
                }
            }
            $row = jqGridDB::fetch_num($q, $this->pdo);
            jqGridDB::closeCursor($q);
            if (!$row)
            {
                $row[0] = 1;
                $row[1] = - 1;
            }
            unset($data['parent_id']);
            $data[$this->tableconfig['level']] = (int)$row[1] + 1;
            if ((int)$row[1] == - 1)
            {
                $data[$this->tableconfig['left']] = (int)$row[0] + 1;
                $data[$this->tableconfig['right']] = (int)$row[0] + 2;
                $s1 = jqGridUtils::sprintfn('UPDATE %table$s SET %right$s = %right$s + 2 WHERE %right$s > ?', $this->tableconfig);
                $s2 = jqGridUtils::sprintfn('UPDATE %table$s SET %left$s = %left$s + 2 WHERE %left$s > ?', $this->tableconfig);
                $this->setBeforeCrudAction('add', $s1, array(
                    (int)$row[0]
                ));
                $this->setBeforeCrudAction('add', $s2, array(
                    (int)$row[0]
                ));
            }
            else
            {
                $data[$this->tableconfig['left']] = (int)$row[0];
                $data[$this->tableconfig['right']] = (int)$row[0] + 1;
                $s1 = jqGridUtils::sprintfn('UPDATE %table$s SET %left$s = CASE WHEN %left$s > ? THEN %left$s + 2 ELSE %left$s END, %right$s = CASE WHEN %right$s >= ? THEN %right$s + 2 ELSE %right$s END WHERE %right$s >= ?', $this->tableconfig);
                $this->setBeforeCrudAction('add', $s1, array(
                    (int)$row[0],
                    (int)$row[0],
                    (int)$row[0]
                ));
            }
        }
        return $this->insert($data);
    }
    public function deleteTreeNode($data)
    {
        if (!$this->add) return false;
        $where = '';
        $param = null;
        if ($this->treemodel == 'adjacency')
        {
            $this->setLeafData($this->getLeafNodes($data[$this->primaryKey]));
            $this->getChildNodes($data[$this->primaryKey], '', true);
            if (is_array($this->res) && count($this->res) > 0)
            {
                $data[$this->primaryKey] .= "," . implode(",", $this->res);
            }
        }
        elseif ($this->treemodel == 'nested')
        {
            $node = $data[$this->primaryKey];
            $s = jqGridUtils::sprintfn('SELECT %left$s, %right$s FROM %table$s WHERE %id$s = ' . (int)$node, $this->tableconfig);
            if ($this->debug)
            {
                $this->logQuery($s);
            }
            $q = jqGridDB::query($this->pdo, $s);
            if (!$q)
            {
                $this->errorMesage = jqGridDB::errorMessage($this->pdo);
                if ($this->showError)
                {
                    $this->sendErrorHeader();
                }
                else
                {
                    die($this->errorMesage);
                }
            }
            $row = jqGridDB::fetch_num($q, $this->pdo);
            jqGridDB::closeCursor($q);
            if (!$row)
            {
                return true;
            }
            $lft = (int)$row[0];
            $rgt = (int)$row[1];
            $width = $rgt - $lft + 1;
            $where = " " . $this->tableconfig['left'] . " BETWEEN ? AND ?";
            $param = array(
                (int)$lft,
                (int)$rgt
            );
            $s1 = jqGridUtils::sprintfn('UPDATE %table$s SET %right$s = %right$s - ? WHERE %right$s > ?', $this->tableconfig);
            $s2 = jqGridUtils::sprintfn('UPDATE %table$s SET %left$s = %left$s - ? WHERE %left$s > ?', $this->tableconfig);
            $this->setAfterCrudAction('del', $s1, array(
                (int)$width,
                (int)$rgt
            ));
            $this->setAfterCrudAction('del', $s2, array(
                (int)$width,
                (int)$rgt
            ));
        }
        return $this->delete($data, $where, $param);
    }
    public function editTree(array $summary = null, array $params = null, $oper = false, $echo = true)
    {
        if (!$oper)
        {
            $oper = $this->GridParams["oper"];
            $oper = jqGridUtils::GetParam($oper, "grid");
        }
        $okmsg = "success##Operation performed succefully";
        switch ($oper)
        {
            case $this->GridParams["editoper"]:
                $this->checkPrimary();
                $data = strtolower($this->mtype) == "post" ? jqGridUtils::Strip($_POST) : jqGridUtils::Strip($_GET);
                if ($this->updateTreeNode($data))
                {
                    $this->setSuccessMsg($okmsg . "##none");
                    if ($this->successmsg)
                    {
                        echo $this->successmsg;
                    }
                }
            break;
            case $this->GridParams["addoper"]:
                $this->checkPrimary();
                $data = strtolower($this->mtype) == "post" ? jqGridUtils::Strip($_POST) : jqGridUtils::Strip($_GET);
                $this->getLastInsert = true;
                if ($this->insertTreeNode($data))
                {
                    $this->setSuccessMsg($okmsg . "##" . $this->lastId);
                    if ($this->successmsg)
                    {
                        echo $this->successmsg;
                    }
                }
            break;
            case $this->GridParams["deloper"]:
                $this->checkPrimary();
                $data = strtolower($this->mtype) == "post" ? jqGridUtils::Strip($_POST) : jqGridUtils::Strip($_GET);
                if ($this->deleteTreeNode($data))
                {
                    $this->setSuccessMsg($okmsg);
                    if ($this->successmsg)
                    {
                        echo $this->successmsg;
                    }
                }
            break;
            default:
                $this->queryTree($summary, $params, $echo);
        }
    }
    public function renderTree($tblelement = '', $pager = '', $script = true, array $summary = null, array $params = null, $createtbl = false, $createpg = false, $echo = true)
    {
        $oper = jqGridUtils::GetParam('oper', 'nooper');
        $oper = $this->GridParams["oper"];
        $goper = jqGridUtils::GetParam($oper, 'nooper');
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
            $this->editTree($summary, $params, $goper);
        }
        else
        {
            $this->_setTreeGridOptions($this->treemodel);
            return $this->renderGrid($tblelement, $pager, $script, $summary, $params, $createtbl, $createpg, $echo);
        }
    }
} ?>