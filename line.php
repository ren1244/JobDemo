<?php
class LineAPI
{
    private $channelSecret;
    private $channelAccessToken;
    private $err='';
    private $timestamp;
    private $source;
    private $replyToken=false;
    private $messageFuncs=[];
    private $eventFuncs=[];
    
    public function __construct($channelSecret, $channelAccessToken)
    {
        $this->channelSecret=$channelSecret;
        $this->channelAccessToken=$channelAccessToken;
    }
    
    /** 
     * 回傳錯誤訊息
     *
     * @return string 錯誤訊息
     */
    public function errorInfo()
    {
        return $this->err;
    }
    
    /** 
     * 執行。(在執行前先設定好 messageFuncs 以及 eventFuncs)
     *
     * @return int status code
     */
    public function execute() //執行
    {
        //驗證 X-Line-Signature
        $headers=apache_request_headers();
        if(!isset($headers['X-Line-Signature'])){
            return 400;
        }
        $signature=$headers['X-Line-Signature'];
        $jsonData=file_get_contents('php://input');
        $check=base64_encode(hash_hmac("sha256",$jsonData,$this->channelSecret,true));
        if($check!==$signature){
            return 403;
        }
        $jsonData=json_decode($jsonData, true);
        $events=$jsonData['events'];
        foreach($jsonData['events'] as $lineEvent){
            $this->timestamp=$lineEvent['timestamp'];
            $this->source=$lineEvent['source'];
            //message:有人發送訊息
            if($lineEvent['type']==='message'){
                $this->replyToken=$lineEvent['replyToken'];
                $this->onEventMessage($lineEvent['message']);
            }
            //Follow event:被加好友
            if($lineEvent['type']==='follow' && $this->eventFuncs['follow']){
                $this->replyToken=$lineEvent['replyToken'];
                $this->eventFuncs['follow']();
            }
            //Unfollow event:被封鎖
            if($lineEvent['type']==='unfollow' && $this->eventFuncs['unfollow']){
                $this->eventFuncs['unfollow']();
            }
            //Join event:機器人被加入群組或聊天室
            if($lineEvent['type']==='join' && $this->eventFuncs['join']){
                $this->replyToken=$lineEvent['replyToken'];
                $this->eventFuncs['join']();
            }
            //Leave event:機器人從群組或聊天室被移除
            if($lineEvent['type']==='leave' && $this->eventFuncs['leave']){
                $this->eventFuncs['leave']();
            }
            //Member join event:有人加入群組
            if($lineEvent['memberJoined']==='join' && $this->eventFuncs['memberJoined']){
                $this->replyToken=$lineEvent['replyToken'];
                $this->eventFuncs['memberJoined']($lineEvent['joined']['members']);
            }
            //Member leave event:有人離開群組
            if($lineEvent['type']==='memberLeft' && $this->eventFuncs['memberLeft']){
                $this->eventFuncs['memberLeft']($lineEvent['joined']['members']);
            }
            //----以下進階功能先不管----
            //Postback event:
            //Beacon event
            //Account link event
            //Device link event
            //Device unlink event
        }
        return 200;
    }
    
    /** 
     * 設定 messageFuncs
     *
     * @param string key message type，參考[...]
     * @param function 針對不同 message type 所要執行的 callback
     * @return void
     */
    public function setMessageFuncs($key, $callback)
    {
        $this->messageFuncs[$key]=Closure::bind($callback,$this);;
    }
    
    /** 
     * 設定 eventFuncs
     *
     * @param string key type，參考[...]
     * @param function 針對不同 message type 所要執行的 callback
     * @return void
     */
    public function setEventFuncs($key, $callback)
    {
        $this->eventFuncs[$key]=Closure::bind($callback,$this);
    }
    
    private function onEventMessage($msg)
    {
        $list=[
            'text'=>['id','text'],
            'image'=>['id','contentProvider'],
            'video'=>['id','duration','contentProvider'],
            'audio'=>['id','duration','contentProvider'],
            'file'=>['id','fileName','fileSize'],
            'location'=>['id','title','address','latitude','longitude'],
            'sticker'=>['id','sticker','stickerId']
        ];
        foreach($list as $type=>$params){
            if($type===$msg['type']){
                if($this->messageFuncs[$type]){
                    $paramsArr=[];
                    foreach($params as $key){
                        $paramsArr[]=$msg[$key];
                    }
                    call_user_func_array(
                        $this->messageFuncs[$type],
                        $paramsArr
                    );
                }
                break;
            }
        }
    }
    
    /** 
     * 發送訊息給 line 
     *
     * @param string method    'get','post'
     * @param string url       line API url
     * @param array  headerArr 要傳送的 headers
     * @param array  paraArr   要傳送的 parameters，為 key=>value 陣列
     * @return array {'data':回應內容,'code':http回應碼}
     */
    private function sendRequest($method,$url,$headerArr=NULL,$paraArr=NULL)
	{
		$mathod=strtolower($method);
		if(empty($paraArr))
			$paraArr=[];
		if(empty($headerArr))
			$headerArr=[];
		$ch=curl_init();
		
		//url設定
		curl_setopt($ch,CURLOPT_URL,$url);
		//header設定
		curl_setopt($ch,CURLOPT_HEADEROPT,CURLOPT_HTTPHEADER);
		curl_setopt($ch,CURLOPT_HTTPHEADER,$headerArr);
		//method與參數設定
		if($method==="post")
		{
			curl_setopt($ch,CURLOPT_POST,true);
			curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($paraArr));
		}
		//其他
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		
		$responseData=curl_exec($ch);
		$responseCode=curl_getinfo($ch,CURLINFO_HTTP_CODE);
		curl_close($ch);
		return ["data"=>$responseData,"code"=>$responseCode];
	}
    
    public function sendReplyMessage($massages)
    {
        if(!$this->replyToken){
            return false;
        }
        if(gettype($massages)==='string'){
            $massages=['type'=>'text','text'=>$massages];
        }
        $this->sendRequest(
            'post',
            'https://api.line.me/v2/bot/message/reply',
            [
                'Content-Type:application/json',
                'Authorization: Bearer {'.$this->channelAccessToken.'}'
            ],[
                'replyToken'=>$this->replyToken,
                'messages'=>$massages
            ]
        );
        $this->replyToken=false;
        return true;
    }
    
    public function sendPushMessage($targetId, $massages)
    {
        if(gettype($massages)==='string'){
            $massages=['type'=>'text','text'=>$massages];
        }
        $this->sendRequest(
            'post',
            'https://api.line.me/v2/bot/message/push',
            [
                'Content-Type:application/json',
                'Authorization: Bearer {'.$this->channelAccessToken.'}'
            ],[
                'to'=>$targetId,
                'messages'=>$massages
            ]
        );
        $this->replyToken=false;
        return true;
    }
}






