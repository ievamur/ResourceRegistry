<script type="text/javascript">
    $(function() {		
        $("#details").tablesorter({sortList:[[0,0],[2,1]], widgets: ['zebra']});
        $("#options").tablesorter({sortList: [[0,0]], headers: { 3:{sorter: false}, 4:{sorter: false}}});
    });	
</script>


<?php
if (empty($list))
{
    $error_message = lang('rr_noawaitingforapproval');
}
if (!empty($message))
{
    echo '<p>' . $message . '</p>';
}
if (!empty($error_message))
{
    echo '<span>' . $error_message . '</span>';
}
if (!empty($list))
{
    $tmpl = array('table_open' => '<table  id="detailsi" class="itablesorter">');
    $this->table->set_template($tmpl);
    $this->table->set_heading(lang('rr_tbltitle_date'), lang('rr_tbltitle_requester'), lang('rr_tbltitle_requesttype') ,'');
    foreach ($list as $q)
    {
        if ($q['confirmed'])
        {
            $confirm = lang('rr_yes');
        } else
        {
            $confirm = lang('rr_no');
        }
        $cdate = $q['idate'];
        $detail = anchor(base_url()."/reports/awaiting/detail/" . $q['token'], '>>');
        $this->table->add_row($q['idate'], $q['requester'], $q['recipientname'].'<br />'.$q['type'] . " - " . $q['action'], $detail);
    }
    echo $this->table->generate();
}
