<?php
$query = '[out:json];
relation["name"="Kangkung"]["admin_level"="7"];
out geom;';
$url = 'https://overpass-api.de/api/interpreter';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, "data=" . urlencode($query));
curl_setopt($ch, CURLOPT_USERAGENT, 'KangkungPeta/1.0');
$res = curl_exec($ch);
curl_close($ch);
file_put_contents('overpass_kangkung2.json', $res);
echo "Done";
