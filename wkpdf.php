<?php

require __DIR__ . '/vendor/autoload.php';
use mikehaertl\wkhtmlto\Pdf;

//$url2 = "https://www.sefaria.org/api/texts/Be'er_HaGolah_on_Shulchan_Arukh%2C_Yoreh_De'ah.1";
//https://www.sefaria.org/api/texts/Ba'er_Hetev_on_Shulchan_Arukh,_Yoreh_De'ah.1


function numToHebrew($num) {
    $hebrew_numerals = explode(" ", "א ב ג ד ה ו ז ח ט ");
    $tens = array("", "י", "כ", "ל", "מ", "נ", "ס", "ע", "פ", "צ");
    $hundreds = array("", "ק", "ר", "ש", "ת");

    $hebrew = "";

    // Handle special cases for 15 and 16
    if ($num == 15) {
        return "ט״ו";
    }
    if ($num == 16) {
        return "ט״ז";
    }

    // Calculate hundreds
    while ($num >= 100) {
        $hebrew .= $hundreds[intval($num / 100)];
        $num %= 100;
    }

    // Calculate tens
    if ($num >= 10) {
        $hebrew .= $tens[intval($num / 10)];
        $num %= 10;
    }

    // Calculate ones
    if ($num > 0) {
        $hebrew .= $hebrew_numerals[$num - 1];
    }

    return $hebrew;
}

/*
$pdf = new Pdf(array(
    'binary' => '/usr/bin/wkhtmltopdf',
    //'no-outline',         // Make Chrome not complain

    //'disable-smart-shrinking',

    'ignoreWarnings' => true,
    'commandOptions' => array(
        'procEnv' => array(
            'LANG' => 'en_US.utf-8',
        ),
    ),
    'user-style-sheet' => 'wk.css',
));
*/

$sefer="<div style='text-align: center; padding-bottom: 15px'>שולחן ערוך יורה דעה</div>";
$pg=array(1,1,1); // pageNum, sn, sif

$length=1;//until we actually get the data and find out how long it is
for($pp=1; $pp<5; $pp++){//=$length
  $first = true;
  $plen=0;
  for($sn=$pg[1]; $sn<=$length; $sn++){ //$pg[1]

    $url1 = "https://www.sefaria.org/api/texts/Shulchan_Arukh%2C_Yoreh_De'ah.$sn";
    // if($first){
    //   $sn--;
    //   $first = false;
    // } 

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $result = curl_exec($ch);
    if (!$result) exit('cURL Error: ' . curl_error($ch));
    curl_close($ch);
    $data = json_decode($result, true);
    $sif = '';
    $length = $data['length'];
    for($x=$pg[0]-1; $x < sizeof($data['he']); $x++){
      $sif = $sif."(".numToHebrew($x+1).")".$data['he'][$x];
      //plen here also and track which sif up to
      //$plen = $plen+mb_strlen($sif, 'UTF-8');
      //if($plen>3000) break;
    }
    //$pp=$sn+1;
    $plen = mb_strlen($sif, 'UTF-8');
    $pg[1] = $sn+1;


    //sections
    //$sif = str_replace("<b>","<div style='font-weight: bold;'>",$sif);
    //$sif = str_replace("</b>","</div>",$sif);
    //$sif = strip_tags($sif,"<b><small><div><span>");
    $sefer=$sefer."<div style='text-align: justify; padding-bottom: 15px;'><span style='font-size: 25px;'><b>".numToHebrew($sn)."</b></span>$sif</div>";	
    if($plen>20000) break;
  }

  //second half of page
  $p="<div style='text-align: justify;'>";
  for($sn=1; $sn<=$pg[1]; $sn++){
    $url2 = "https://www.sefaria.org/api/texts/Ba'er_Hetev_on_Shulchan_Arukh,_Yoreh_De'ah.$pg[1]";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url2);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $result = curl_exec($ch);
    if (!$result) exit('cURL Error: ' . curl_error($ch));
    curl_close($ch);
    $data = json_decode($result, true);
    //$p=$p.strip_tags($data['he'][$pg[1]],"<b><div>");


    for($x=0; $x < sizeof($data['he']); $x++){
      $p = $p."(".numToHebrew($x+1).")".strip_tags($data['he'][$x],"<b><div>");
      $plen = mb_strlen($p, 'UTF-8');
      if($plen>10000) break;
    }

  }
  $secondp = "<div style='text-align: center; padding-bottom: 15px;'>פירוש</div>".$p."</div>";
  

  $pages = $pages."<div>".$sefer."</div><div>".$secondp."</div>page: ".$pp;
  $pg[0]++;
}
/*for testing*/
echo strip_tags(str_replace("<div>","\n",$pages));
/*
$pdf->addPage("<html lang='he' dir='rtl'><head><meta charset='UTF-8' />".$pages."</head></html>");


if (!$pdf->send('report.pdf')) {
    $error = $pdf->getError();
    print_r($error);
}
*/
