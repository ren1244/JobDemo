<?php
//儲存 line 的資料
class LineMsg extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
    
    /** 
     * 初始化資料表或是重置
     *
     * @param bool reset 如果為true則覆蓋原有資料表，如果為false，當資料表存在則不處理
     * @return void
     */
    public function initDatbase($reset=false)
    {
        $this->load->dbforge();
        if($reset){
            $this->dbforge->drop_table('line_bot', true);
        }
        $this->dbforge->add_field([
            'id'=>[
                'type'=>'int',
                'unsigned'=>true,
                'auto_increment'=>true
            ],
            'userId'=>[
                'type'=>'varchar',
                'constraint'=>64
            ],
            'userName'=>[
                'type'=>'varchar',
                'constraint'=>128
            ],
            'msg'=>[
                'type'=>'varchar',
                'constraint'=>1024
            ],
            'send'=>[
                'type'=>'bool'
            ]
        ]);
        $this->dbforge->add_key('id', true);
        $this->dbforge->create_table('line_bot', true);
    }
    
    public function insert($userId, $userName, $msg, $sendFlag=false)
    {
        $this->db->insert('line_bot',[
            'userId'=>$userId,
            'userName'=>$userName,
            'msg'=>$msg,
            'send'=>$sendFlag
        ]);
    }
    
    /** 
     * 用 userId 查詢 userName
     *
     * @param string id userId
     * @return string|false 使用者名稱或是失敗
     */
    public function getName($id)
    {
        $this->db->where('userId', $id);
        $this->db->where('send', false);
        $this->db->order_by('id', 'DESC');
        $qry=$this->db->get('line_bot',1);
        $r=$qry->result_array();
        if(count($r)===0){
            return false;
        }
        return $r[0]['userName'];
    }
}