<?php
class WebView extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
    
    private function showLog($obj)
    {
        echo '<pre>'.print_r($obj,true).'</pre>';
    }
    
    public function index()
    {
        $this->page();
    }
    
    public function page($page=1)
    {
        $this->load->helper('url');
        $this->db->order_by('id', 'DESC');
        $qry=$this->db->get('line_bot',5,($page-1)*5);
        if(!$qry){
            return;
        }
        $r=$qry->result_array();
        $this->load->view('webView',['data'=>$r,'targetUrl'=>base_url('WebView/pushMsg')]);
    }
    
    public function pushMsg($user) //這邊用POST
    {
        $msg=$this->input->post('msg');
        if(!$msg){
            return;
        }
        $this->load->model('LineMsg');
        $this->load->model('LineAPIModel');
        $this->LineAPIModel->setConsts(
            '417c30d759c7785c984bce69413794fe',
            'lS5AdcOP5JptCii3ZH2mGmpWdPcJotPL7OG1RVY2JFkL8u/CP6fWVTMZ3Rrstio3vUN6eRJ8jbhdQyFNlPnJLfqPTDA0IFOTS6K2ptUTbrIHQIzcTJnJQeLJIElPfu/9nCatG/pUdzoiCgpJLBfuDAdB04t89/1O/w1cDnyilFU='
        );
        
        $name=$this->LineMsg->getName($user);
        if($name===false){
            return;
        }
        
        $r=$this->LineAPIModel->sendPushMessage($user,[
            ['type'=>'text','text'=>$msg]
        ]);
        if($r){
            $this->LineMsg->insert($user,'To:'.$name,$msg,true);
        } else {
            $this->LineMsg->insert($user,'To:'.$name,'[傳送失敗]'.$msg,true);
        }
    }
    
    //建立資料庫
    public function init($reset=false)
    {
        $this->load->model('LineMsg');
        $this->LineMsg->initDatbase($reset);
    }
}