<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>456</title>
</head>
<body>
	<?php if(count($data)>0):?>
    <table>
        <tr>
            <th>使用者</th>
            <th>訊息</th>
        </tr>
        <?php foreach($data as $row):?>
        <tr onclick="setUser('<?php echo $row['userId'];?>')">
            <td><?php echo $row['userName'];?></td>
            <td><?php echo htmlspecialchars($row['msg']);?></td>
        </tr>
        <?php endforeach;?>
    </table>
    
    <div>
        <p>使用者ID</p>
        <input type='text' name="uid" id='uid'>
        <p>訊息</p>
        <textarea name="msg" id='msg'></textarea>
        <input type='submit' value='傳送' onclick="sendMsg()">
    </div>
    
    <script>
    function setUser(uid)
    {
        document.getElementById('uid').value=uid;
    }
    function sendMsg()
    {
        var baseUrl=location.href;
        if(baseUrl.substr(-1)!=='/'){
            baseUrl+='/';
        }
        var uid=document.getElementById('uid').value;
        var msg=document.getElementById('msg').value;
        var xhr=new XMLHttpRequest();
        xhr.open('POST',baseUrl+'pushMsg/'+uid);
        xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        xhr.onreadystatechange=function (){
            if(xhr.readyState==4){
                location.href=location.href;
            }
        }
        xhr.send('msg='+encodeURIComponent(msg));
    }
    </script>
    
    <?php else:?>
    <p>已經沒有訊息了</p>
    <?php endif;?>
    
</body>
</html>
