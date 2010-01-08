<?php
$img_path = "http://wltv.vaynermedia.com/images";

function is_new_episode($title){
    $db_host = "localhost";
    $db_user = "root";
    $db_pass = "thrasher66";
    $db_name = "wltv";
    $connection = mysql_connect($db_host, $db_user, $db_pass) or die("Could not connect to database");
    mysql_select_db($db_name, $connection) or die ("Could not select database");

    $title = mysql_real_escape_string($title);
    $sql = "SELECT COUNT(*) AS count FROM episodes WHERE episode='$title'";
    $result = mysql_query($sql) or die("Could not query the database");
    $row = mysql_fetch_array($result);
    if($row['count'] != 1){
        $sql = sprintf("INSERT INTO episodes (episode) VALUES ('%s')", $title);
        $result = mysql_query($sql) or die("Could not query the database");
        return TRUE;
    } else { return FALSE; }
}

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

//Grab the WLTV RSS Feed
$rss = simplexml_load_file('http://feeds.feedburner.com/WinelibraryTv');

$rss_id = 1;

// Set up the feed and grab the very first (i.e. most recent) episode info
$episodes = array();
if(is_new_episode($rss->channel->item[0]->title) || 1){
    foreach ($rss->channel->item as $item) {
       if ($rss_id == 1) {
       //Grab the episode number from the rss'd title, y'all
       $tweet_boom = explode('#', $item->title);
       if (is_numeric($tweet_boom[1])) {
               $ep_img = 'http://tv.winelibrary.com/episodes/episode'.$tweet_boom[1].'.jpg';
       } else {
               $ep_img = 'http://tv.winelibrary.com/episodes/default.jpg';
       }
       $curr_title   =   $item->title;
       $curr_url     =   $item->guid;
       $curr_date = date( "F j, Y", strtotime($item->pubDate) );
       $curr_description = substr($item->description, 0, strpos($item->description,"Having trouble"));
       }
       elseif($rss_id <= 4){
       $tweet_boom = explode('#', $item->title);
       if (is_numeric($tweet_boom[1])) {
               $temp_img = 'http://tv.winelibrary.com/episodes/episode'.$tweet_boom[1].'.jpg';
       } else {
               $temp_img = 'http://tv.winelibrary.com/episodes/default.jpg';
       }
       $temp_title = $item->title;
       $temp_url = $item->guid;
       array_push($episodes, array("title"=>$temp_title, "img"=>$temp_img, "url"=>$temp_url));
       }
    $rss_id++;
    }
    echo "hi";
    $rss = file_get_contents("http://feeds.feedburner.com/WinelibraryTv");
    $xml = new DOMDocument();
    $xml->loadxml($rss);
    $wines = $xml->getElementsByTagName('item')->item(0)->getElementsByTagName('description')->item(0)->nextSibling->nextSibling->nodeValue;
    $wines = substr($wines, strpos($wines, "<h3 class=\"wine-list\">Wines tasted in this episode"));
    $wines = substr($wines, 0, strpos($wines, "</table>"));
    //$wines = strip_tags($wines, "<th><a><em>");
    $wines = substr($wines, strpos($wines, "<th"));
    $winelist = array();
    if($wines != ""){
        $xml->loadHTML($wines);
        foreach($xml->getElementsByTagName('th') as $wine){
            $name = $wine->getElementsByTagName('a')->item(0)->nodeValue;
            $link = $wine->getElementsByTagName('a')->item(0)->getAttributeNode('href')->nodeValue;
            $region = $wine->getElementsByTagName('em')->item(0)->nodeValue;
            array_push($winelist, array("name"=>$name, "link"=>$link, "region"=>$region));
       }
    }

$creative = <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>$curr_title</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body style="margin:0; padding:0;">
<div style="width:100%; background-color:#F6F1DE;">
    <table style="width:740px; margin:auto; background-color:#F6F1DE; table-layout:auto; font-size:medium;" border="0" cellpadding="0" cellspacing="0" align="center">
        <tr><td style="text-align:center;" colspan="4">
            <img alt="Wine Library TV" src="$img_path/header.jpg"/>
        </td></tr>
        <tr>
            <td style="width:10px;"></td>
            <td style="width:520px;" valign="top">
                <table style="background-color:#F6F1DE; width:100%;" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td>
                            <h2 style="font-family:Georgia; font-size:140%; font-weight:normal; color:#990022; margin:10px 0px 0px 0px;">
                                <a style="text-decoration:none; color:#990022; font-weight:normal;" onmouseover="this.style.color='#4A0015';" onmouseout="this.style.color='#990022';" href="$curr_url">$curr_title</a>
                            </h2>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0px 0px 7px 0px;">
                            <strong style="font-family:'Lucida Grande',Verdana,sans-serif; line-height:1.5em; font-size:70%; color:#996633;">$curr_date</strong>
                        </td>
                    </tr>
                    <tr>
                        <td><a href="$curr_url"><img width="520" height="293" style="border:0px; margin-bottom:12px; width:520px" src="$ep_img" alt="$curr_title" /></a></td>
                    </tr>
                    <tr>
                        <td>
                            <p style="font-family:'Lucida Grande',Verdana,sans-serif; color:#38230E; font-size:75%; line-height:1.5em; margin:0 0 1.5em; padding:0;">$curr_description</p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table style="font-size:80%; color:#663300; font-family:'Lucida Grande',Verdana,sans-serif; background-color:#F6F1DE; width:100%;" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td style="background-color:#F4E6C9; padding:7px 10px; border-color:#E0D0B1; border-style:solid; border-width:1px;">
                                        <strong>Wines tasted in this episode:</strong>
                                    </td>
                                </tr>
EOF;
                                foreach($winelist as $wine){
$creative .= <<<EOF
                                <tr>
                                    <td style="background-color:#FBF8EB; padding:12px 5px; border-color:#E0D0B1; border-style:solid; border-width:0px 1px 1px 1px;">
                                        <a style="text-decoration:none; color:#268CCD; font-weight:bold; font-size:95%;" onmouseover="this.style.textDecoration='underline';" onmouseout="this.style.textDecoration='none';" href="$wine[link]">$wine[name]</a><br />
                                        <em style="color:#996633; font-size:90%; font-style:normal; font-weight:normal;">$wine[region]</em>
                                    </td>
                                </tr>
EOF;
                                }
$creative .= <<<EOF
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width:200px;" valign="top">
                <table style="width:100%; padding: 10px 0px 10px 10px;" border="0" cellpadding="0">
                    <tr>
                        <td style="padding-bottom:20px;">
                            <table style="width:100%; font-size:80%; color:#663300; font-family:'Lucida Grande',Verdana,sans-serif;" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="background-color:#F4E6C9; padding:7px 10px; border-color:#E0D0B1; border-style:solid; border-width:1px;">
                                        <strong>Social</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="background-color:#FBF8EB; padding:12px 5px; border-color:#E0D0B1; border-style:solid; border-width:0px 1px 1px 1px;">
                                        <a style="text-decoration:none; color:#268CCD; font-weight:bold; font-size:95%; padding:15px 80px 10px 0px;" href="http://corkd.com/"><img src="$img_path/corkdIcon.jpg" alt="" style="border:0px; padding:0px 3px 0px 5px; margin-right:0px;" />&nbsp;&nbsp;&nbsp;Cork'd</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="background-color:#FBF8EB; padding:12px 5px; border-color:#E0D0B1; border-style:solid; border-width:0px 1px 1px 1px;">
                                        <a style="text-decoration:none; color:#268CCD; font-weight:bold; font-size:95%; padding:15px 50px 10px 0px;" href="http://garyvaynerchuk.com/"><img src="$img_path/gvIcon.jpg" alt="" style="border:0px; padding:4px 0px 4px 5px; margin-right:0px;" />&nbsp;&nbsp;&nbsp;Gary's Blog</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table style="font-size:80%; color:#663300; font-family:'Lucida Grande',Verdana,sans-serif; background-color:#F6F1DE; width:100%;" border="0" cellpadding="10" cellspacing="0">
                                <tr>
                                    <td style="background-color:#F4E6C9; padding:7px 10px; border-color:#E0D0B1; border-style:solid; border-width:1px;">
                                        <strong>Recent Episodes</strong>
                                    </td>
                                </tr>
EOF;
                                foreach($episodes as $episode) {
$creative .= <<<EOF
                                <tr>
                                    <td style="background-color:#FBF8EB; border-color:#E0D0B1; border-style:solid; border-width:0px 1px 1px 1px;">
                                        <a href="$episode[url]"><img width="160" height="90" style="padding-bottom:5px; border:0px;" src="$episode[img]" alt="$episode[title]" /></a><br />
                                        <a style="display:block; text-decoration:none; color:#268CCD; font-size:80%;" onmouseover="this.style.textDecoration='underline';" onmouseout="this.style.textDecoration='none';" href="$episode[url]">$episode[title]</a>
                                    </td>
                                </tr>
EOF;
                                }
$creative .= <<<EOF
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width:10px;"></td>
        </tr>
        <tr>
            <td colspan="4" style="text-align:center; font-size: 10px; color: gray; padding:50px 0px 10px 0px;">You received this email because you are subscribed to <a href="http://tv.winelibrary.com">Wine Library TV</a>. To unsubscribe, [~Optout~]Click Here[~EndOptout~]</td>
        </tr>
    </table>
</div>
</body>
</html>
EOF;

echo $creative;

$email_content = htmlspecialchars($creative);

$postString = "
<Creative xmlns=\"http://schemas.datacontract.org/2004/07/BlueSkyFactory.Publicaster7.API.REST.Classes\" xmlns:i=\"http://www.w3.org/2001/XMLSchema-instance\">
<CreativeID>0</CreativeID>
<DateCreated>".date('Y-m-d\TG:i:s')."</DateCreated>
<Description></Description>
<EncodingID>16</EncodingID>
<FolderID>7</FolderID>
<HTML>$email_content</HTML>
<Name>WLTV " . date('m/j') . "</Name>
<Subject>$curr_title</Subject>
<Text>
You have received a HTML email from Wine Library TV, but it appears that your e-mail client is set to read messages in plain text.
To view the original graphical version of the email in your Internet browser, visit:
[~Viewinbrowser~]

=======================================================
To opt out of all future mailings from Wine Library TV, visit:
[~Optout~]

To forward this e-mail to a friend/colleague, visit:
[~Forward~]
=======================================================
</Text>
<Type>HTML</Type>
<UserID>2419</UserID>
</Creative>
";
$response = make_api_call('Creatives.svc', $postString);

// Grab the Creative ID for future use
if(preg_match('/<a:CreativeID.*a:CreativeID>/', $response, $id))
    { $CreativeID = strip_tags($id[0]); }

$postString = "
<CampaignDistribution xmlns=\"http://schemas.datacontract.org/2004/07/BlueSkyFactory.Publicaster7.API.REST.Classes\" xmlns:i=\"http://www.w3.org/2001/XMLSchema-instance\">
<CampaignDistributionID>0</CampaignDistributionID>
<DistributionSubject>$curr_title</DistributionSubject>
<GoogleTitle>$curr_title</GoogleTitle>
<IsSegmentation>false</IsSegmentation>
<IsSuppression>false</IsSuppression>
<IsTest>false</IsTest>
<ProcessGoogle>false</ProcessGoogle>
<SelectedCampaign>6</SelectedCampaign>
<SelectedEmail>$CreativeID</SelectedEmail>
<SelectedFromAddress>13</SelectedFromAddress>
<SelectedMailingList>20</SelectedMailingList>
<SelectedReplyToAddress>13</SelectedReplyToAddress>
<SendDate>".date('Y-m-d\TG:i:s')."</SendDate>
<TrackLinks>true</TrackLinks>
<UserID>2419</UserID>
</CampaignDistribution>
";
$response = make_api_call('CampaignDistributions.svc',$postString);

echo "<br />Sent";
} else { echo "Up to date";}

// Items to change before going live
// Selected Campaign = 9
// Selected Mailing List = 21

?>