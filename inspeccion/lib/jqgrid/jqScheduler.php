<?php ini_set('diplay_errors', 1);
class jqScheduler
{
    public $version = '4.4.4.0';
    protected $options = array(
        "editable" => true,
        "defaultView" => 'agendaWeek',
        "slotMinutes" => 30,
        "selectable" => true,
        "theme" => true,
        "header" => array(
            "left" => 'prev,next today',
            "center" => 'title',
            "right" => 'agendaDay,agendaWeek,month,year'
        )
    );
    public $localenpath = "localization";
    public $templatepath = "templates";
    private $locale = "en_GB";
    private $template = "calendar.html";
    private $csstemplate = "calendar.css";
    private $usrdateformat = "d/m/Y";
    private $print_main = "print.html";
    private $print_rows = "print_rows.html";
    private $I = '';
    public $table = "events";
    private $user_id;
    public $calenderid = "calendar";
    public $datepickerid = "datepicker";
    public $eventid = "event";
    public $available_users = array();
    public $encoding = "utf-8";
    public $editable = true;
    public $multiple_cal = false;
    public $multiple_size = 3;
    public $backend_type = 'database';
    public $printformat = 'pdf';
    private $db;
    private $dbtype;
    private $backend = null;
    private $wherecond = null;
    private $whereparam = array();
    private $url;
    private $oper;
    protected $PDF = array(
        "page_orientation" => "P",
        "unit" => "mm",
        "page_format" => "A4",
        "creator" => "jqScheduler",
        "author" => "jqScheduler",
        "title" => "jqScheduler PDF",
        "subject" => "Subject",
        "keywords" => "Calendar",
        "margin_left" => 15,
        "margin_top" => 7,
        "margin_right" => 15,
        "margin_bottom" => 25,
        "margin_header" => 5,
        "margin_footer" => 10,
        "font_name_main" => "freeserif",
        "font_size_main" => 10,
        "header_logo" => "",
        "header_logo_width" => 0,
        "header_title" => "",
        "header_string" => "",
        "header" => false,
        "footer" => true,
        "font_monospaced" => "courier",
        "font_name_data" => "freeserif",
        "font_size_data" => 8,
        "margin_footer" => 10,
        "image_scale_ratio" => 1.25,
        "path_to_pdf_class" => "tcpdf/tcpdf.php",
        "filename" => "calendar.pdf"
    );
    protected $quote = '';
    function __construct($conn = null, $odbctype = '')
    {
        if (class_exists('jqGridDB')) $interface = jqGridDB::getInterface();
        else $interface = 'local';
        $this->db = $conn;
        if ($interface == 'pdo' && is_object($this->db))
        {
            $this
                ->db
                ->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->dbtype = $this
                ->db
                ->getAttribute(PDO::ATTR_DRIVER_NAME);
            if ($this->dbtype == 'pgsql') $this->I = 'I';
        }
        else
        {
            $this->dbtype = $interface . $odbctype;
        }
        $this->oper = jqGridUtils::GetParam('oper', false);
        if ($interface == 'valentina')
        {
            $this->quote = "`";
        }
    }
    public function setUrl($curl)
    {
        if ($curl && strlen($curl) > 0)
        {
            $this->url = $curl;
        }
    }
    public function setLocale($lng)
    {
        if ($lng && strlen($lng) > 0)
        {
            $this->locale = $lng;
        }
    }
    public function setTemplate($html, $css = '')
    {
        if (strlen($html) > 0)
        {
            $this->template = $html;
        }
        if (strlen($css) > 0)
        {
            $this->csstemplate = $css;
        }
    }
    public function setQuote($q = '')
    {
        $this->quote = $q;
    }
    public function arr2htmloption($arr, $selected = '')
    {
        $out = "";
        foreach ($arr as $k => $v)
        {
            $sel = "";
            if ($selected)
            {
                if (is_array($selected))
                {
                    if (in_array($k, $selected))
                    {
                        $sel = " selected='selected' ";
                    }
                }
                else if ($k == $selected)
                {
                    $sel = " selected='selected' ";
                }
            }
            $out .= "<option " . $sel . " value=\"" . $k . "\">" . htmlentities($v, ENT_QUOTES || ENT_IGNORE, "UTF-8") . "</option>";
        }
        return $out;
    }
    public function arr2css($arr)
    {
        $out = "";
        foreach ($arr as $k => $v)
        {
            $out .= "\n." . $k . ", .fc-agenda ." . $k . " .fc-event-skin, ." . $k . " a \n";
            $out .= "{\n";
            $out .= "background-color:" . $v . ";
\n";
            $out .= "border-color: " . $v . ";
\n";
            $out .= "}\n";
        }
        return $out;
    }
    public function getOption($option)
    {
        if (array_key_exists($option, $this->options)) return $this->options[$option];
        else return false;
    }
    public function setOption($option, $value = null)
    {
        if ($this->oper) return;
        if (isset($option))
        {
            if (is_array($option))
            {
                foreach ($option as $key => $value)
                {
                    $this->options[$key] = $value;
                }
                return true;
            }
            else if ($value != null)
            {
                $this->options[$option] = $value;
                return true;
            }
        }
        return false;
    }
    public function setEvent($event, $code)
    {
        if ($this->oper) return;
        if (isset($event) && isset($code))
        {
            $this->options[$event] = "js:" . $code;
        }
    }
    public function setUser($user_id, $usesession = true)
    {
        if ($usesession)
        {
            $user = jqSession::getInstance();
            if ($this->oper == 'switchUser')
            {
                $newuser = jqGridUtils::GetParam("_user_id", false);
                if ($newuser)
                {
                    $user->jq_user_id = $newuser;
                    $this->user_id = $user->jq_user_id;
                }
                exit();
            }
            if (isset($user->jq_user_id))
            {
                $this->user_id = $user->jq_user_id;
            }
            else
            {
                $user->jq_user_id = $user_id;
                $this->user_id = $user_id;
            }
        }
        else
        {
            $this->user_id = $user_id;
        }
    }
    public function setUserNames($anames)
    {
        if (is_array($anames))
        {
            $this->available_users = jqGridUtils::array_extend($this->available_users, $anames);
        }
    }
    public function setUserDateFormat($newformat)
    {
        if ($newformat && strlen($newformat) > 0)
        {
            $this->usrdateformat = $newformat;
        }
    }
    public function convertDateFormat($phpformat)
    {
        $fulcal = "";
        $datepick = "";
        $datearray = preg_split("//", $phpformat);
        foreach ($datearray as $k => $v)
        {
            switch ($v)
            {
                case 'j':
                    $fulcal .= 'd';
                    $datepick .= 'd';
                break;
                case 'd':
                    $fulcal .= 'dd';
                    $datepick .= 'dd';
                break;
                case 'D':
                    $fulcal .= 'ddd';
                    $datepick .= 'D';
                break;
                case 'l':
                    $fulcal .= 'dddd';
                    $datepick .= 'DD';
                break;
                case 'n':
                    $fulcal .= 'M';
                    $datepick .= 'm';
                break;
                case 'm':
                    $fulcal .= 'MM';
                    $datepick .= 'mm';
                break;
                case 'M':
                    $fulcal .= 'MMM';
                    $datepick .= 'M';
                break;
                case 'F':
                    $fulcal .= 'MMMM';
                    $datepick .= 'MM';
                break;
                case 'y':
                    $fulcal .= 'yy';
                    $datepick .= 'y';
                break;
                case 'Y':
                    $fulcal .= 'yyyy';
                    $datepick .= 'yy';
                break;
                default:
                    $fulcal .= $v;
                    $datepick .= $v;
            }
        }
        return array(
            $fulcal,
            $datepick
        );
    }
    private function exportiCal($start, $end)
    {
        if (!empty($this->user_id))
        {
            $search = jqGridUtils::GetParam('search', '');
            $stype = jqGridUtils::GetParam('stype', '');
            if ($search == 'true')
            {
                $searchconds = $this->composeSearch($stype);
                $this
                    ->backend
                    ->setSearchs($searchconds[0], $searchconds[1]);
            }
            $events = $this
                ->backend
                ->getEvents($start, $end);
            try
            {
                include $this->localenpath . '/' . $this->locale . '.inc';
                $locale = new jqEventLocalization();
            }
            catch(Exception $e)
            {
                $locale = array();
            }
            $ical = "BEGIN:VCALENDAR\n";
            $ical .= "VERSION:2.0\n";
            $ical .= "PRODID:-//Trirand jqSuite//NONSGML jqScheduler//EN\n";
            foreach ($events as $event)
            {
                $ical .= "BEGIN:VEVENT\n";
                $ical .= "DTSTART:" . date('Ymd\THis\Z', $event['start'] - date('Z')) . "\n";
                if ($start != $end)
                {
                    $ical .= "DTEND:" . date('Ymd\THis\Z', $event['end'] - date('Z')) . "\n";
                }
                $ical .= "SUMMARY:" . $event['title'] . "\n";
                $ical .= "DESCRIPTION:" . $event['description'] . "\n";
                if (!empty($event['location']))
                {
                    $ical .= "LOCATION:" . $event['location'] . "\n";
                }
                if (!empty($event['access']))
                {
                    $v = isset($locale->option_access[$event['access']]) ? $locale->option_access[$event['access']] : "PUBLIC";
                    $ical .= "CLASS:" . strtoupper($v) . "\n";
                }
                if (!empty($event['className']))
                {
                    $v = isset($locale->option_categories[$event['className']]) ? $locale->option_categories[$event['className']] : "WORK";
                    $ical .= "CATEGORIES:" . strtoupper($v) . "\n";
                }
                $ical .= "END:VEVENT\n";
            }
            $ical .= "END:VCALENDAR";
            return $ical;
        }
    }
    public function exportEvents()
    {
        $start = jqGridUtils::GetParam('start', '');
        $end = jqGridUtils::GetParam('end', '');
        header("Content-Type: text/calendar");
        header("Content-Disposition: inline;
 filename=calendar.ics");
        echo $this->exportiCal($start, $end);
        exit;
    }
    public function setPdfOptions($apdf)
    {
        if (is_array($apdf) and count($apdf) > 0)
        {
            $this->PDF = jqGridUtils::array_extend($this->PDF, $apdf);
        }
    }
    private function htmlTable()
    {
        $dir = dirname(__FILE__);
        include $this->localenpath . '/' . $this->locale . '.inc';
        $locale = new jqEventLocalization();
        try
        {
            $str_rows = file_get_contents($this->templatepath . '/' . $this->print_rows, FILE_USE_INCLUDE_PATH);
            $search = jqGridUtils::GetParam('search', '');
            $stype = jqGridUtils::GetParam('stype', '');
            if ($search == 'true')
            {
                $searchconds = $this->composeSearch($stype);
                $this
                    ->backend
                    ->setSearchs($searchconds[0], $searchconds[1]);
            }
            $rowdata = $this
                ->backend
                ->getEvents(jqGridUtils::GetParam('start', '') , jqGridUtils::GetParam('end', ''));
            $ldays = array();
            $ldays['Sun'] = $locale->fullcalendar['dayNamesShort'][0];
            $ldays['Mon'] = $locale->fullcalendar['dayNamesShort'][1];
            $ldays['Tue'] = $locale->fullcalendar['dayNamesShort'][2];
            $ldays['Wed'] = $locale->fullcalendar['dayNamesShort'][3];
            $ldays['Thu'] = $locale->fullcalendar['dayNamesShort'][4];
            $ldays['Fri'] = $locale->fullcalendar['dayNamesShort'][5];
            $ldays['Sat'] = $locale->fullcalendar['dayNamesShort'][6];
            $rowsTemplates = array();
            foreach ($rowdata as $data)
            {
                $row = new jqTemplate("dummy");
                if (date('d M Y', (int)$data['start']) == date('d M Y', (int)$data['end']))
                {
                    $data['mydate'] = strftime('%d %b %Y', (int)$data['start']);
                }
                else
                {
                    $data['mydate'] = strftime('%d %b', (int)$data['start']) . " - " . strftime('%d %b %Y', (int)$data['end']);
                }
                if (strpos($this->locale, 'en') === false)
                {
                    $data['mydate'] = str_replace(array(
                        'Jan',
                        'Feb',
                        'Mar',
                        'Apr',
                        'May',
                        'Jun',
                        'Jul',
                        'Aug',
                        'Sep',
                        'Oct',
                        'Nov',
                        'Dec'
                    ) , $locale->fullcalendar['monthNamesShort'], $data['mydate']);
                }
                $data['myday'] = $ldays[date('D', $data['start']) ];
                $data['start'] = date('H:i', $data['start']);
                $data['categories'] = $locale->option_categories[$data['className']];
                foreach ($data as $key => $value)
                {
                    $row->set($key, $value);
                }
                if ($data['allDay'] == 1)
                {
                    $ald = "<td colspan=\"2\" align=\"center\" style=\"border: 1px soid #5c9ccc;
\">" . $locale->form_all_day . "</td>";
                }
                else
                {
                    $ald = "<td style=\"border: 1px soid #5c9ccc;
\">&nbsp;
" . $data['start'] . "</td>";
                    $ald .= "<td style=\"border: 1px soid #5c9ccc;
\">&nbsp;
" . date('H:i', $data['end']) . "</td>";
                }
                $row->set('allday', $ald);
                $rowsTemplates[] = $row;
            }
            $rowContents = jqTemplate::merge($rowsTemplates, $str_rows);
            $layout = new jqTemplate($dir . '/' . $this->templatepath . '/' . $this->print_main);
            if (count($this->available_users) > 0)
            {
                $us_id = "";
                if (is_array($this->user_id))
                {
                    $us = array();
                    foreach ($this->user_id as $k => $v)
                    {
                        $us[] = $this->available_users[$v];
                    }
                    $us_id = implode(", ", $us);
                }
                else
                {
                    $us_id = $this->available_users[$this->user_id];
                }
                $layout->set('calendar', $us_id);
            }
            $layout->set('h_day', $locale->fullcalendar['buttonText']['day']);
            $layout->set('h_start', $locale->form_start);
            $layout->set('h_end', $locale->form_end);
            $layout->set('h_location', $locale->form_location);
            $layout->set('h_categories', $locale->form_categories);
            $layout->set('h_title', $locale->form_title);
            $layout->set('h_description', $locale->form_description);
            $layout->set('rows', $rowContents);
            return $layout->output();
        }
        catch(Exception $e)
        {
            echo $e->getMessage();
            return false;
        }
    }
    public function printEvents()
    {
        if ($this->printformat == 'html')
        {
            header("Content-type: text/html;
charset=" . $this->encoding);
            echo $this->htmlTable();
        }
        else
        {
            try
            {
                $pd = $this->PDF;
                include ($pd['path_to_pdf_class']);
                $pdf = new TCPDF($pd['page_orientation'], $pd['unit'], $pd['page_format'], true, 'UTF-8', false);
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
                $pdf->AddPage();
                $pdf->SetFont($pd['font_name_data'], '', $pd['font_size_data']);
                $tbl = $this->htmlTable();
                $pdf->writeHTML($tbl, true, false, false, false, '');
                $pdf->Output($pd['filename'], 'D');
                exit();
            }
            catch(Exception $e)
            {
                echo $e->getMessage();
            }
        }
    }
    protected function getStringForGroup($group, $prm)
    {
        $datearray = array(
            'start',
            'end'
        );
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
                        if (in_array($field, $datearray))
                        {
                            $v = jqGridUtils::parseDate('d/m/Y H:i', $v, 'U');
                        }
                        if (in_array($field, array(
                            'user_id',
                            'start',
                            'end',
                            'all_day'
                        )))
                        {
                            $v = (int)$v;
                        }
                        $field = $this->quote . $field . $this->quote;
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
        $filters = ($str_filter && strlen($str_filter) > 0) ? $str_filter : jqGridUtils::GetParam('svalue', "");
        $rules = "";
        $jsona = false;
        if ($filters)
        {
            if (function_exists('json_decode') && strtolower(trim($this->encoding)) == "utf-8") $jsona = json_decode($filters, true);
            else $jsona = jqGridUtils::decode($filters);
            if (is_array($jsona))
            {
                $gopr = $jsona['groupOp'];
                $rules = $jsona['rules'];
            }
        }
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
            else
            {
                $ret = array(
                    "",
                    $prm
                );
            }
        }
        return $ret;
    }
    protected function composeSearch($stype = 'simple')
    {
        $s = "";
        $prm = array();
        $q = $this->quote;
        if ($stype == 'simple')
        {
            $i_ = $this->I;
            $sv = jqGridUtils::GetParam('svalue', '');
            if (strtolower($this->encoding) != 'utf-8')
            {
                $sv = iconv("utf-8", $this->encoding . "//TRANSLIT", $sv);
            }
            if ($sv)
            {
                $s = "( " . $q . "title" . $q . " {$i_}LIKE ? OR " . $q . "location" . $q . " {$i_}LIKE ? OR " . $q . "description" . $q . " {$i_}LIKE ? ) ";
                $prm = array(
                    "%" . $sv . "%",
                    "%" . $sv . "%",
                    "%" . $sv . "%"
                );
            }
        }
        else if ($stype == 'complex')
        {
            $res = $this->_buildSearch();
            $s = $res[0];
            $prm = $res[1];
        }
        return array(
            $s,
            $prm
        );
    }
    protected function _Response($response)
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
    public function setWhere($where, $prm = array())
    {
        $this->wherecond = $where;
        $this->whereparam = $prm;
    }
    public function render($script = true, $echo = true)
    {
        if ($this->oper && in_array($this->oper, array(
            'getEvent',
            'editEvent',
            'removeEvent',
            'newEvent',
            'resizeEvent',
            'moveEvent',
            'switchUser',
            'exportEvents',
            'printEvents'
        )))
        {
            if ($this->backend_type == 'database')
            {
                require_once ('backend/database.php');
                $this->backend = new Database($this->db);
                $this
                    ->backend
                    ->setUser($this->user_id);
                $this
                    ->backend
                    ->setTable($this->table);
                $this
                    ->backend
                    ->setQuote($this->quote);
            }
            else if ($this->backend_type == 'caldav')
            {
            }
            else if ($this->backend_type == 'google')
            {
            }
            if ($this->backend)
            {
                switch ($this->oper)
                {
                    case 'getEvent':
                        $search = jqGridUtils::GetParam('search', '');
                        $stype = jqGridUtils::GetParam('stype', '');
                        if ($this->wherecond && strlen($this->wherecond) > 0)
                        {
                            $this
                                ->backend
                                ->setWhere($this->wherecond, $this->whereparam);
                        }
                        if ($search == 'true')
                        {
                            $searchconds = $this->composeSearch($stype);
                            $this
                                ->backend
                                ->setSearchs($searchconds[0], $searchconds[1]);
                        }
                        $data = $this
                            ->backend
                            ->getEvents(jqGridUtils::GetParam('start', '') , jqGridUtils::GetParam('end', ''));
                        if ($echo) $this->_Response($data);
                        else return $data;
                        break;
                    case 'editEvent':
                        $data = jqGridUtils::Strip($_POST);
                        if (strtolower($this->encoding) != 'utf-8')
                        {
                            foreach ($data as $k => $v)
                            {
                                $v = iconv("utf-8", $this->encoding . "//TRANSLIT", $v);
                            }
                        }
                        $this
                            ->backend
                            ->editEvent($data);
                        break;
                    case 'removeEvent':
                        $this
                            ->backend
                            ->removeEvent(jqGridUtils::GetParam('event_id', '0'));
                        break;
                    case 'newEvent':
                        $data = jqGridUtils::Strip($_POST);
                        if (strtolower($this->encoding) != 'utf-8')
                        {
                            foreach ($data as $k => $v)
                            {
                                $v = iconv("utf-8", $this->encoding . "//TRANSLIT", $v);
                            }
                        }
                        $id = $this
                            ->backend
                            ->newEvent($data);
                        if ($id)
                        {
                            echo json_encode(array(
                                "event_id" => $id
                            ));
                        }
                        else
                        {
                            echo json_encode(array(
                                "event_id" => 0
                            ));
                        }
                        break;
                    case 'resizeEvent':
                        $this
                            ->backend
                            ->resizeEvent(jqGridUtils::GetParam('event_id', '') , jqGridUtils::GetParam('start', '') , jqGridUtils::GetParam('end', ''));
                        break;
                    case 'moveEvent':
                        $this
                            ->backend
                            ->moveEvent(jqGridUtils::GetParam('event_id', '') , jqGridUtils::GetParam('start', '') , jqGridUtils::GetParam('end', '') , jqGridUtils::GetParam('all_day', '0'));
                        break;
                    case 'exportEvents':
                        $this->exportEvents();
                        break;
                    case 'printEvents':
                        $this->printEvents();
                        break;
                    default:
                        return;
                        break;
                    }
                }
        }
        else
        {
            try
            {
                $st = "";
                $s = "";
                $dir = dirname(__FILE__);
                include $this->localenpath . '/' . $this->locale . '.inc';
                $locale = new jqEventLocalization();
                $dateformats = $this->convertDateFormat($this->usrdateformat);
                $locale->dateFormat = $dateformats[0];
                $jstrnarr = array(
                    'dateFormat' => $locale->dateFormat,
                    'bSave' => $locale->save,
                    'bAdd' => $locale->add,
                    'bDelete' => $locale->remove,
                    'bCancel' => $locale->cancel,
                    'bChange' => $locale->change,
                    'bClose' => $locale->close,
                    'bSearch' => $locale->find,
                    'editCaption' => $locale->captionedit,
                    'addCaption' => $locale->captionadd,
                    'userCaption' => $locale->captionchangeusr,
                    'searchCaption' => $locale->captionsearch
                );
                $tdata = new jqTemplate($dir . '/' . $this->templatepath . '/' . $this->template);
                $tdata->set('b_search', $locale->button_search);
                $tdata->set('b_adv_search', $locale->captionsearch);
                $tdata->set('found_events', $locale->found_events);
                $tdata->set('header_serach', $locale->header_search);
                $tdata->set('currentcal', $locale->currentcalendar);
                $tdata->set('b_user', $locale->button_user);
                $tdata->set('b_export', $locale->button_export);
                $tdata->set('b_print', $locale->button_print);
                if (isset($this->available_users) && isset($this->user_id))
                {
                    $us = "";
                    if (is_array($this->user_id))
                    {
                        foreach ($this->user_id as $k => $v)
                        {
                            $us .= "<li>" . $this->available_users[$v] . "</li>";
                        }
                    }
                    else
                    {
                        $us = "<li>" . $this->available_users[$this->user_id] . "</li>";
                    }
                    $tdata->set('caluser', $us);
                }
                $tdata->set('dpid', $this->datepickerid);
                $tdata->set('calid', $this->calenderid);
                $tdata->set('eventid', $this->eventid);
                $tdata->set('title', $locale->form_title);
                $tdata->set('location', $locale->form_location);
                $tdata->set('categories', $locale->form_categories);
                $tdata->set('option_categories', $this->arr2htmloption($locale->option_categories));
                $tdata->set('access', $locale->form_access);
                $tdata->set('option_access', $this->arr2htmloption($locale->option_access));
                $tdata->set('all_day', $locale->form_all_day);
                $tdata->set('start', $locale->form_start);
                $tdata->set('end', $locale->form_end);
                $tdata->set('description', $locale->form_description);
                $tdata->set('label_user', $locale->label_user);
                $tdata->set('captionlist', $locale->captionlist);
                $tdata->set('available_users', $this->arr2htmloption($this->available_users, $this->user_id));
                if (!$this->multiple_cal)
                {
                    $tdata->set('multitiple', "style='display:none'");
                    $tdata->set('multisize', "");
                }
                else
                {
                    $tdata->set('multisize', "multiple='multiple' size='" . $this->multiple_size . "'");
                }
                $tdata->set('username', $locale->form_user_id);
                $tdata->set('calusers', $this->arr2htmloption($this->available_users, ''));
                $st = $tdata->output();
                $tdata = null;
            }
            catch(Exception $e)
            {
                die($e->getMessage());
            }
            $s .= "<style type=text/css>";
            $s .= file_get_contents($this->templatepath . '/' . $this->csstemplate, FILE_USE_INCLUDE_PATH);
            $s .= $this->arr2css($locale->categories_css);
            $s .= "</style>";
            $s .= $st;
            $this->options = jqGridUtils::array_extend($locale->fullcalendar, $this->options);
            if (!$this->editable)
            {
                $this->options['editable'] = false;
                $this->setEvent('dayClick', 'function() { return false;
}');
                $this->setEvent('eventClick', 'jQuery.jqschedule.eventViewDialog');
            }
            else
            {
                $this->setEvent('eventClick', 'jQuery.jqschedule.eventEditDialog');
                $this->setEvent('dayClick', 'jQuery.jqschedule.eventAddDialog');
            }
            $this->setEvent('reportDayClick', 'jQuery.jqschedule.openDaySearchDlg');
            $this->setEvent('eventDrop', 'jQuery.jqschedule.eventDrop');
            $this->setEvent('eventResize', 'jQuery.jqschedule.eventResize');
            $this->setEvent('eventRender', 'jQuery.jqschedule.eventRender');
            $this->setEvent('events', 'jQuery.jqschedule.eventsFunc');
            $this->setEvent('loading', 'jQuery.jqschedule.eventLoading');
            if ($script)
            {
                $s .= "<script type='text/javascript'>";
                $s .= "jQuery(document).ready(function() {";
            }
            if (!$locale->use_datepicker_lang)
            {
                $locale->datepicker_lang['dateFormat'] = $dateformats[1];
                $s .= "jQuery.datepicker.setDefaults(" . jqGridUtils::encode($locale->datepicker_lang) . ");
";
                $locale->timepicker_lang['stepMinute'] = $this->options['slotMinutes'];
                $s .= "jQuery.timepicker.setDefaults(" . jqGridUtils::encode($locale->timepicker_lang) . ");
";
            }
            $locale->timepicker['isoTime'] = true;
            $locale->timepicker['timeInterval'] = $this->options['slotMinutes'];
            $s .= "jQuery.jqschedule.locales=" . jqGridUtils::encode($jstrnarr) . ";
";
            $s .= "jQuery.jqschedule.calendarid='#" . $this->calenderid . "';
";
            $s .= "jQuery.jqschedule.searchOpers=" . jqGridUtils::encode($locale->searchopers) . ";
";
            $s .= "jQuery.jqschedule.eventid='#" . $this->eventid . "';
";
            $s .= "jQuery.jqschedule.calendarurl='" . $this->url . "';
";
            $s .= "jQuery('#starttime,#endtime').calendricalTimeRange(" . jqGridUtils::encode($locale->timepicker) . ");
";
            $s .= "jQuery('#$this->calenderid').fullCalendar(" . jqGridUtils::encode($this->options) . ");
";
            $s .= "jQuery.jqschedule.processScript();
";
            $s .= "jQuery('#progresspar').position({ of: '.calwrapper' });
";
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
    }
} ?>