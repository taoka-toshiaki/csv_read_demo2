<?php
 // ログインした状態と同等にするためセッションを開始します
 session_start();
 // 暗号学的的に安全なランダムなバイナリを生成し、それを16進数に変換することでASCII文字列に変換します
  $toke_byte = openssl_random_pseudo_bytes(16);
  $csrf_token = bin2hex($toke_byte);
  // 生成したトークンをセッションに保存します
  $_SESSION['csrf_token'] = $csrf_token;
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<meta name="Description" content="Enter your description here"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<title>CSV</title>
</head>
<body>
    <input type="hidden" name="csrf_token" value="<?=$csrf_token?>">
    <span class="h3" id="cnt"></span><br><br>
    <span class="h4" id="read_csv"></span><br><br>
    <span class="h4" id="debug"></span><br><br>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/js/bootstrap.min.js"></script>
<script>
    window.onload = function(){
        read_csv(0,1);
    };
    function read_csv(cnt,reset_flag){
        try{
            $.ajax({
            type: "post",
            url: "./assets/php/class_csv.php",
            async: false,
            data: {csrf_token:document.getElementsByName("csrf_token")[0].value,reset_flag:reset_flag,filename:"dummy.csv",cnt:cnt},
            dataType: "json",
            success: function (response) {
                    if(response){
                        cnt = response.cnt;
                        document.getElementById("cnt").innerText = cnt;
                        if(response.data){
                            document.getElementById("read_csv").innerText = response.data[0] + response.sql;
                            document.getElementById("debug").innerText = cnt ===21?response.data:document.getElementById("debug").innerText;
                        }
                        setTimeout(function(){read_csv(cnt)},0);
                    }
                }
            });
        }catch(e){
            console.warn(e);
            read_csv(cnt);
        }
    }
</script>
</body>
</html>
