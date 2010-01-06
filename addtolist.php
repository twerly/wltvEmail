<?php

function make_api_call($service,$body) {
  $service_url = 'http://api7.publicaster.com/Rest/'.$service.'/';
  $headers = Array('Content-type: text/xml', 'Authorization: 2RpFB4w63Dk=:fantasy123');

  $curl = curl_init($service_url);
  curl_setopt($curl, CURLOPT_HEADER, false);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($curl, CURLOPT_POST, 1);
  curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
  $curl_response = curl_exec($curl);
  curl_close($curl);

  return $curl_response;
}

$email = mysql_real_escape_string($_POST['email']);

$postString = "
<ListImport xmlns=\"http://schemas.datacontract.org/2004/07/BlueSkyFactory.Publicaster7.API.REST.Classes\" xmlns:i=\"http://www.w3.org/2001/XMLSchema-instance\">
 <Action>AppendAndUpdate</Action>
 <Data>
   Email
   $email
 </Data>
 <Delimiter>Comma</Delimiter>
 <ListID>21</ListID>
 <PrimaryKey>email</PrimaryKey>
 <UserID>2419</UserID>
</ListImport>
";


make_api_call("ListImports.svc",$postString);
header('Location: /?success=1');
?>
