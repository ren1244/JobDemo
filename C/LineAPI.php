<?php
class LineAPI extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('LineAPIModel');
        $this->load->model('LineMsg');
        $this->LineAPIModel->setConsts(
            '417c30d759c7785c984bce69413794fe',
            'lS5AdcOP5JptCii3ZH2mGmpWdPcJotPL7OG1RVY2JFkL8u/CP6fWVTMZ3Rrstio3vUN6eRJ8jbhdQyFNlPnJLfqPTDA0IFOTS6K2ptUTbrIHQIzcTJnJQeLJIElPfu/9nCatG/pUdzoiCgpJLBfuDAdB04t89/1O/w1cDnyilFU=',
            ['LineMsg'=>$this->LineMsg]
        );
    }
    
    public function index()
    {
        //text message
        $this->LineAPIModel->setMessageFuncs('text',function($id,$text){
            $uid=$this->getUserId();
            if($uid){
                //嘗試從資料庫找，如果沒有才向Line詢問使用者的 displayName
                $displayName=$this->resources['LineMsg']->getName($uid);
                if($displayName==='' || $displayName===false){
                    $displayName=$this->getProfit();
                    $displayName=$displayName===false?'':$displayName['displayName'];
                }
                //將訊息寫入資料庫
                $this->resources['LineMsg']->insert($uid,$displayName,$text);
            }
        });
        
        $r=$this->LineAPIModel->execute();
    }
}