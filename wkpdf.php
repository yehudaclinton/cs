<?php

require __DIR__ . '/vendor/autoload.php';
use mikehaertl\wkhtmlto\Pdf;

//$url2 = "https://www.sefaria.org/api/texts/Be'er_HaGolah_on_Shulchan_Arukh%2C_Yoreh_De'ah.1";
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

$pdf = new Pdf(array(
    'binary' => '/usr/bin/wkhtmltopdf',
    //'no-outline',         // Make Chrome not complain

    //'margin-top'    => 15, //doing it in css page
    //'margin-right'  => 15,
    //'margin-bottom' => 15,
    //'margin-left'   => 15,

    // Default page options
    //'disable-smart-shrinking',

    'ignoreWarnings' => true,
    'commandOptions' => array(
        'useExec' => true,      // Can help on Windows systems
        'procEnv' => array(
            // Check the output of 'locale -a' on your system to find supported languages
            'LANG' => 'en_US.utf-8',
        ),
    ),

    'user-style-sheet' => 'wk.css',
));

$sefer="<div style='text-align: center; padding-bottom: 15px'>שולחן ערוך יורה דעה</div>";
$pg=array(1); // pageNum, siman, sif

$length=1;//until we actually start getting the data and find out how long it is
for($pp=1; $pp<11; $pp++){//=$length
$plen=0;
  for($sn=1; $sn<=$length; $sn++){
    $url = "https://www.sefaria.org/api/texts/Shulchan_Arukh%2C_Yoreh_De'ah.$sn";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $result = curl_exec($ch);
    if (!$result) exit('cURL Error: ' . curl_error($ch));
    curl_close($ch);
    $data = json_decode($result, true);
    $sif = '';
    $length = $data['length'];
    for($x=0; $x < sizeof($data['he']); $x++){
      $sif = $sif."(".numToHebrew($x+1).")".$data['he'][$x];
      //plen here also and track which sif up to
      //$plen = $plen+mb_strlen($sif, 'UTF-8');
      //if($plen>3000) break;
    }
  //$pp=$sn+1;
  $pg[1]=$sn;
  $plen = $plen+mb_strlen($sif, 'UTF-8');
  if($plen>20000) break;

  //sections
    //$sif = str_replace("<b>","<div style='font-weight: bold;'>",$sif);
    //$sif = str_replace("</b>","</div>",$sif);
    //$sif = strip_tags($sif,"<b><small><div><span>");
    $sefer=$sefer."<div style='text-align: justify; padding-bottom: 15px;'><span style='font-size: 25px;'><b>".numToHebrew($sn)."</b></span>$sif</div>";//	
  }

  //second half of page
  $p="";
  for($sn=1; $sn<=$pg[1]; $sn++){
  $url2 = "https://www.sefaria.org/api/texts/Ba'er_Hetev_on_Shulchan_Arukh,_Yoreh_De'ah.".$sn;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url2);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $result = curl_exec($ch);
    if (!$result) exit('cURL Error: ' . curl_error($ch));
    curl_close($ch);
    $data = json_decode($result, true);
    $p=$p.strip_tags($data['he'][$sn],"<b><div>");
  }
    $secondp = "<div style='text-align: center; padding-bottom: 15px;'>פירוש</div>".$p;

  $pages = $pages."<div>".$sefer."</div><div>".$secondp."</div>page: ".$pg[0];
  
}

$pdf->addPage("<html lang='he' dir='rtl'><head><meta charset='UTF-8' />".$pages."</head></html>");


if (!$pdf->send('report.pdf')) {
    $error = $pdf->getError();
    print_r($error);
}
