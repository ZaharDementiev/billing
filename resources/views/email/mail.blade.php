<html>
<head>
    <title>{!! $email->subject !!} </title>
</head>
<body>

<p>{{$email->subject}}</p>
{!! $email->attachments !!}

</body>
</html>
