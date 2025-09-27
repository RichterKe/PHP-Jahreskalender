<?php
/*
    Kalender mit Kalenderwochen generieren
    
    Pro Monat werden maximal 6 x 8 Wocheneinträge benötigt
    (KW, Mo, Di, MI, Do, Fr, Sa, So)
    Minimum werden 4 x 8 Wocheneinträge benötigt
    
    Die Kalenderwoche 1 eines Jahres beginnt in der ersten
    Woche in dem der 1.1. nicht auf Fr, Sa oder So fällt.
    
    Der gregorianische Kalender gilt seit dem 15. Oktober 1582
    ISO_8601 definiert den Kalender bis 9999
    
    Übergabe in HTML            "Kalender.php?jahr=nnnn"
    Übergabe in der Lonsole     "Kalender.php jahr=nnnn"
    Bei fehlender Jahreszahl wird die aktuelle Jahreszahl verwendet
    Gültige Jahreszahlen 2000 - 2099
    
*/

/* ################################################################
   # Globale Variablen                                            #
   ################################################################ */    

$pgm_Call = 0;                          // 1=HTML, 2=Kommandozeile
$ar_monate = array("Kalender", "Januar", "Februar", "März", "April", "Mai", "Juni",
                   "Juli", "August", "September", "Oktober", "November", "Dezember");
$ar_wochen = array("KW", "Mo", "Di", "Mi", "Do", "Fr", "Sa", "So");
$ar_k_jahr = array();
$ar_k_monat = array();
$ar_k_woche = array("  ", "  ", "  ", "  ", "  ", "  ", "  ", "  ");
$zlr_jahr = 2000;
$zlr_monat = 1;  
$zlr_woche = 0;
$zlr_tag = 1;

/* ################################################################
   # Unterprogramme                                               #
   ################################################################ */ 

/* Kalender Array generieren */
function erstelle_monat()
{
    global $zlr_tag, $zlr_woche, $zlr_monat, $zlr_jahr, $ar_k_monat;
    global $ar_wochen;
    $zeile = 0;
    $ar_k_woche = array("  ", "  ", "  ", "  ", "  ", "  ", "  ", "  ");
    //$anzahl =  cal_days_in_month(CAL_GREGORIAN, $zlr_monat, $zlr_jahr);
    $anzahl = date("t",mktime(0,0,0,$zlr_monat,1,$zlr_jahr)); 
    for ($index = 1; $index <= $anzahl; $index++)
    {
        //echo($index." - ");
        $uts = "";
        $uts .= $zlr_jahr.":";
        $uts .= str_pad($zlr_monat, 2, "0", STR_PAD_LEFT).":";
        $uts .= str_pad($index, 2, "0", STR_PAD_LEFT)." ";
        $uts .= "07:00:00";        
        $ar_info = getdate(strtotime($uts));
        $utag = $ar_info['wday'];
        if ($utag == 0) $utag = 7;
        //echo($ar_wochen[$utag]."\n");
        if (($index == 1) AND ($zlr_monat == 1) AND ($utag <= 4)) $zlr_woche = 1;
        //$ar_k_woche[$utag] = $index;
        $ar_k_woche[$utag] = str_pad($index,2," ",STR_PAD_LEFT);
        if ($utag == 7)
        {
            //$ar_k_woche[0] = $zlr_woche;
            $ar_k_woche[0] = str_pad($zlr_woche, 2, "0", STR_PAD_LEFT);
            if ($ar_k_woche[0] == "00") $ar_k_woche[0] = "  ";
            $zlr_woche += 1;
            $ar_k_monat[$zeile] = $ar_k_woche;
            $zeile += 1;
            $ar_k_woche = array("  ", "  ", "  ", "  ", "  ", "  ", "  ", "  ");
        }
    } 
    if (trim($ar_k_woche[1]) != "")
    {
        //$ar_k_woche[0] = $zlr_woche;
        $ar_k_woche[0] = str_pad($zlr_woche, 2, "0", STR_PAD_LEFT);
        $ar_k_monat[$zeile] = $ar_k_woche;
        $zeile += 1;
    }
    while ($zeile < 6)
    {
        $ar_k_woche = array("  ", "  ", "  ", "  ", "  ", "  ", "  ", "  ");
        $ar_k_monat[$zeile] = $ar_k_woche;
        $zeile += 1;
    }                                  


}                 

/* ################################################################
   # Hauptprogramm                                                #
   ################################################################ */ 

/*  Parameterübergabe verwalten */
//Aufruf aus HTML
if (isset($_SERVER['QUERY_STRING']))
{
    $param = $_SERVER['QUERY_STRING'];
    $pgm_call = 1;
}
//Aufruf von der Kommandozeile
else
{
    if (isset($argv[1])) $param = $argv[1];
    $pgm_call = 2;
}
if (is_string($param)) parse_str($param, $out);
if (isset($out["jahr"])) $jahr = $out["jahr"]; 

/* Die richtige Zeitzone einstellen */
date_default_timezone_set('Europe/Berlin');

/* Jahreszahl automatisch ermitteln */
if (isset($jahr))
{
    if (is_numeric($jahr))
    {
        if (($jahr >= 2000) AND ($jahr <= 2099)) $zlr_jahr = $jahr;
    } 
}
else
{
    $zlr_jahr = getdate()['year'];
}



//erstelle_monat();
//var_dump($ar_k_monat);

/* HTML Anzeige */
if ($pgm_call == 1)
{
    ?>

    <!DOCTYPE HTML>
    <html lang="de">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Jahreskalender<?php echo($zlr_jahr); ?></title>
        </head>    

        <style>
            body              { min-width:300px; max-width:1350px; }
            div               { display:flex; flex-flow:row wrap; }
            table             { border:3px ridge #1B838A; margin:1px; padding:0px; font-weight:bold; }
            table th          { text-align:center; height:50px; margin:0px; padding:0px; }
            table tr          { text-align:center; margin:0px; padding:0px; border:0px; }
            table td          { text-align:center; background-color:#F2F2F2; height:20px; width:30px;
                                margin:0px; padding:1px; border:2px solid #FFFFFF; }
            table tr:nth-child(1) td {background-color:#00FF00; font-size:18px; font-weight:bold;}
            table tr:nth-child(2) td {background-color:#C0FFC0; }
            table tr:nth-child(1n+3) td:nth-child(1) {background-color:#C0FFC0; color:#888888; }
            table tr:nth-child(2) td:nth-child(1) {color:#888888; }
            table tr:nth-child(1n+3) td:nth-child(1n+7) {background-color:#CACACA; }
            #rahmen           { border-top:1px solid #1683CE; }
            #marker           { border:2px solid #FF0000; }
            #titel            { font-size:24px; font-weight:bold; }
            a:link            { text-decoration:none; color:#000000; }
            a:visited         { text-decoration:none; color:#000000; }
            a:focus           { text-decoration:none; color:#000000; }
            a:hover           { text-decoration:none; color:#000000; }
            a:active          { text-decoration:none; color:#000000; }
        </style>    

        <body>

            <span id="titel">
                Kalender <?php echo($zlr_jahr); ?> 
            </span>
            <br> <br>     
            <div>
            
            <?php
            for ($ind = 1; $ind <= 12; $ind++)
            {
                $zlr_monat = $ind;
                erstelle_monat();
                $ar_k_jahr[$zlr_monat] = $ar_k_monat;
            }            
                        
            for ($inda = 1; $inda <= 12; $inda++)
            {
                $ar_k_monat = $ar_k_jahr[$inda];
                if ($inda == 1) echo ('<a href="kalender.php?jahr='.($zlr_jahr-1).'">');
                if ($inda == 12) echo ('<a href="kalender.php?jahr='.($zlr_jahr+1).'">');                
                echo ("<table>"."\r\n");
                echo ('<tr><td colspan="8">');
                echo (str_replace("ä","&auml;",$ar_monate[$inda])." ".$zlr_jahr);
                echo ("</td></tr>"."\r\n");
                echo ("<tr>");
                foreach ($ar_wochen as $value)
                {
                    echo ("<td>".$value."</td>");
                }
                echo ("</tr>"."\r\n");
                foreach ($ar_k_monat as $value)
                {
                    $ar_k_woche = $value;
                    echo ("<tr>");
                    foreach ($ar_k_woche as $vkey => $vtag)
                    {
                        if ( (getdate()['year']==$zlr_jahr) AND (getdate()['mon']==$inda) AND (getdate()['mday']==$vtag) AND ($vkey != 0) ) 
                        {
                            echo ('<td id="marker">'.$vtag."</td>"); 
                        }
                        else
                        {
                            echo ('<td>'.$vtag."</td>");
                        }
                        
                    }
                    echo ("</tr>"."\r\n");
                }               
                
                echo ("</table>"."\r\n");       
                if (($inda == 1) OR ($inda == 12)) echo ("</a>");                
            }
            ?>
            
            </div>
        </body>
    </html> 
 
    <?php

}


/* Bildschirmanzeige */
if ($pgm_call == 2)
{
    echo ("\r\n");
    echo ($ar_monate[0]." ".$zlr_jahr."\r\n");
    echo ("\r\n");

    for ($ind = 1; $ind <= 12; $ind++)
    {
        $zlr_monat = $ind;
        erstelle_monat();
        $ar_k_jahr[$zlr_monat] = $ar_k_monat;
    }
    //var_dump($ar_k_jahr);

    for ($inda = 1; $inda <= 12; $inda++)
    {
        $ar_k_monat = $ar_k_jahr[$inda];
        echo ("-- ".$ar_monate[$inda]." ".$zlr_jahr." --\r\n");
        foreach ($ar_wochen as $value)
        {
            echo ($value."  ");
        }
        echo ("\r\n");
        foreach ($ar_k_monat as $value)
        {
            $ar_k_woche = $value;
            foreach ($ar_k_woche as $vtag)
            {
                echo ($vtag."  ");
            }
            echo ("\r\n");
        }
        if (trim($ar_k_woche[0]) != "") echo ("\r\n");
    
    }
}


?>
