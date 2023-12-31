<?php

require __DIR__ . '/vendor/autoload.php';
use mikehaertl\wkhtmlto\Pdf;

$url2 = "https://www.sefaria.org/api/texts/Be'er_HaGolah_on_Shulchan_Arukh%2C_Yoreh_De'ah.1";
//https://www.sefaria.org/api/texts/Ba'er_Hetev_on_Shulchan_Arukh,_Yoreh_De'ah.1


function numToHebrew($num) {
    $hebrew_numerals = explode(" ", "א ב ג ד ה ו ז ח ט י כ ל מ נ ס ע פ צ ק ר ש ת");
    $special_numerals = array(15 => 'ט״ו', 16 => 'ט״ז');

    if(array_key_exists($num, $special_numerals)) {
        return $special_numerals[$num];
    }

    $result = '';
    foreach(array_reverse($hebrew_numerals, true) as $value => $hebrew) {
        while($num >= $value + 1) {
            $result .= $hebrew;
            $num -= $value + 1;
        }
    }
    return $result;
}


$sefer="<div style='text-align: center; padding-bottom: 15px'>שולחן ערוך יורה דעה</div>";
$length=1;//$length
$plen=0;
for($sn=1; $sn<=20; $sn++){
  $url = "https://www.sefaria.org/api/texts/Shulchan_Arukh%2C_Yoreh_De'ah.$sn";

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
  $result = curl_exec($ch);
  if (!$result) exit('cURL Error: ' . curl_error($ch));
  $data = json_decode($result, true);
  //$length=$data['length'];
  $txt = '';

  for($x=0; $x < sizeof($data['he']); $x++){
    $txt = $txt."(".numToHebrew($x=1).")".$data['he'][$x];//
    
    //plen here also and track which sif up to
  }
$plen = $plen+mb_strlen($txt, 'UTF-8');
//sections
  $txt = str_replace("<b>","<div style='font-weight: bold;'>",$txt);
  $txt = str_replace("</b>","</div>",$txt);
  $txt = strip_tags($txt,"<b><small><div><span>");
  $sefer=$sefer."<div style='text-align: justify;'><span style='font-size: 25px;'><b>".numToHebrew($sn)."</b></span>$txt</div>";//	
  if($plen>20000) break;
