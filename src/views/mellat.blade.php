<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>در حال اتصال به درگاه</title>
    <style>
        body{
            background: beige;
        }
        .container p{
            position: fixed;
            text-align: center;
            width: 100%;
            top: 40%;
        }
    </style>
</head>
<body>
    <div class="container">
        <p>در حال اتصال به درگاه</p>
    </div>
    <form id="form" action="https://bpm.shaparak.ir/pgwchannel/startpay.mellat" method="post" target="_self">
        <input type="hidden" name="RefId" value="{{$refId}}">
    </form>
    <script>
        var form=document.getElementById('form');
        setTimeout(function () {
            form.submit();
        },1000);
    </script>
</body>
</html>
