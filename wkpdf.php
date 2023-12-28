<?php

require __DIR__ . '/vendor/autoload.php';
use mikehaertl\wkhtmlto\Pdf;

$url2 = "https://www.sefaria.org/api/texts/Be'er_HaGolah_on_Shulchan_Arukh%2C_Yoreh_De'ah.1";

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


$sefer='';
$length=1;//$length
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
    $txt = $txt.$data['he'][$x];
  }

//sections
  $txt = str_replace("<b>","<div style='font-weight: bold;'>",$txt);
  $txt = str_replace("</b>","</div>",$txt);
  $txt = strip_tags($txt,"<b><small><p><2>");
  $sefer=$sefer."<p><h2>".numToHebrew($sn)."</h2>$txt</p>";
}

$pdf = new Pdf(array(
    'no-outline',         // Make Chrome not complain
    'margin-top'    => 15,
    'margin-right'  => 15,
    'margin-bottom' => 15,
    'margin-left'   => 15,

    // Default page options
    'disable-smart-shrinking',
    'user-style-sheet' => 'wk.css',
));

$pdf->addPage("<html lang='he' dir='rtl'><head><meta charset='UTF-8' /></head><div id='top' style='text-align: justify;>".$sefer."</div></html>");

if (!$pdf->send('report.pdf')) {
    $error = $pdf->getError();
    // ... handle error here
}
/**/
