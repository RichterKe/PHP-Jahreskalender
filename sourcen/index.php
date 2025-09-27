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
    
    Übergabe in HTML            "jkalender.php?jahr=nnnn"
    Übergabe in der Konsole     "jkalender.php jahr=nnnn"
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
$index = 0;
$info_txt = "";
$cmdl;
$pgm_dat = "jkalender.dat"; 
$t_anfang = array();
$t_termin = array();
$t_ende = array();
$t_text = array();
$hit_01 = FALSE;
$hit_02 = FALSE;


/* ################################################################
   # Unterprogramme                                               #
   ################################################################ */ 

/* Funktion zum zerlegen einer Textzeile in die einzelnen Bestandteile
   und Rückgabe der Teile in einem Array, Element 0 enthält die Anzahl der Einträge */
function teile_text($text, $delim)  
{
    $tok = "";
    $index = 0;
    $respond = array("0" => "0");
    $tok = strtok($text,$delim);
    while ($tok !== false)
    {
        $index += 1;
        $respond[$index] = trim($tok);
        $respond[0] = $index;
        if ($index >= 9) break;
        $tok = strtok($delim);
    }
    return $respond;
}


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


/* Termin Arrays generieren */
function erstelle_termine()
{
    global $pgm_dat, $t_anfang, $t_termin, $t_ende, $t_text;
    
    $han_dat = "";
    $buffer = "";
    $teil = "";
    $dats = "";
    $erg = 9;
    $mode = "*";
    $temp = 9;
    $s_tag = 86400;
    $w_tag = array("SO", "MO", "DI", "MI", "DO", "FR", "SA");
    $akt_tag = getdate();
    $akt_stamp = mktime(12,0,0);
    $anzahl = 0;
    $temp_01 = 0;
    $temp_02 = 0;
    $temp_03 = 0;
    $d_test;
    $d_tag;
    $d_monat;
    $d_jahr;

    if (file_exists($pgm_dat))
    {
        $han_dat = fopen($pgm_dat, 'r');
        if ($han_dat)
        {
            while (($buffer = fgets($han_dat, 1024)) !== false)
            {
                $teil = teile_text($buffer," ");

                // Kommentarzeile
                if (($teil[0]>=1)AND(stripos($teil[1],"//")!=false))
                { 
                    $erg=0;
                } 
                
                // Wochentag
                elseif (($teil[0]>=4)AND(strlen($teil[1])==2)AND(in_array((strtoupper($teil[1])), $w_tag)))
                {
                    $temp_01 = array_search((strtoupper($teil[1])),$w_tag);
                    $temp_02 = $akt_tag["wday"];

                    if (($temp_01!==FALSE)AND($temp_01<$temp_02))
                    {
                        $erg=($akt_stamp+((7-($temp_02-$temp_01))*$s_tag));
                    }
                    elseif (($temp_01!==FALSE)AND($temp_01>$temp_02))
                    {
                        $erg=($akt_stamp+(($temp_01-$temp_02)*$s_tag));
                    }
                    elseif (($temp_01!==FALSE)AND$temp_01===$temp_02)
                    {
                        $erg=$akt_stamp;
                    }
                    
                    $temp=$erg;
                    $t_termin[]=$erg;                    
                    $t_anfang[]=$erg-($teil[2]*$s_tag);
                    $t_ende[]=$erg+($teil[3]*$s_tag);
                    $t_text[]=str_replace("_"," ",$teil[4]);  
                    $erg=($temp-(7*$s_tag));                      // 1 Wo zurück
                    $t_termin[]=$erg;                    
                    $t_anfang[]=$erg-($teil[2]*$s_tag);
                    $t_ende[]=$erg+($teil[3]*$s_tag);
                    $t_text[]=str_replace("_"," ",$teil[4]);                    
                    $erg=($temp+(7*$s_tag));                      // 1 Wo vor
                    $t_termin[]=$erg;                    
                    $t_anfang[]=$erg-($teil[2]*$s_tag);
                    $t_ende[]=$erg+($teil[3]*$s_tag);
                    $t_text[]=str_replace("_"," ",$teil[4]);
                    $mode = "W";                                                                    
                }
                
                // Datum
                elseif (($teil[0]>=4)AND(strlen($teil[1])==10))
                {
                    $dats = teile_text($teil[1],".");
                    if (($dats[0]==3)AND($dats[1]==99)AND($dats[2]==99)AND($dats[3]==9999)) //  Jeden Tag
                    {
                        $erg=mktime(12,0,0);
                        $mode = "*";                                           
                    }                    
                    elseif (($dats[0]==3)AND($dats[1]<=31)AND($dats[2]==99)AND($dats[3]==9999)) // Jeden Monat
                    {
                        $temp_01 = $akt_tag["mon"];
                        $erg=mktime(12,0,0,$temp_01,$dats[1]);
                        //Schauen ob der Tag von Erg der gleiche ist, ansonsten höchsten Tag nehmen
                        $d_test=getdate($erg);
                        if ($d_test["mday"]!=$dats[1])
                        {
                            //$anzahl =  cal_days_in_month(CAL_GREGORIAN, $akt_tag["mon"], $akt_tag["year"]);
                            $anzahl = date("t",mktime(0,0,0,$akt_tag["mon"],1, $akt_tag["year"]));
                            $erg=mktime(12,0,0,$temp_01,$anzahl);
                        }
                        $mode = "M";
                    } 
                    elseif (($dats[0]==3)AND($dats[1]<=31)AND($dats[2]<=12)AND($dats[3]==9999)) // Jedes Jahr
                    {
                        $erg=mktime(12,0,0,$dats[2],$dats[1]);
                        //Schauen ob der Tag von Erg der gleiche ist, ansonsten höchsten Tag nehmen
                        $d_test=getdate($erg);                   
                        if (($d_test["mday"])!=($dats[1]))
                        {
                            //$anzahl =  cal_days_in_month(CAL_GREGORIAN, $dats[2], $akt_tag["year"]);
                            $anzahl = date("t",mktime(0,0,0,$dats[2],1,$akt_tag["year"]));
                            $erg=mktime(12,0,0,$dats[2],$anzahl);
                        }
                        
                        $mode = "J";                       
                    }                    
                    elseif (($dats[0]==3)AND($dats[1]<=31)AND($dats[2]<=12)AND($dats[3]<=2199)) // An einem Datum
                    {
                        $erg=mktime(12,0,0,$dats[2],$dats[1],$dats[3]);
                        $mode = "D";                      
                    }
                    if ($erg!==FALSE)
                    {
                        $t_termin[]=$erg;
                        if ($mode=="*")                                 // Jeden Tag
                        {
                            $temp=$erg;                        
                            $t_anfang[]=$erg-($teil[2]*$s_tag);
                            $t_ende[]=$erg+($teil[3]*$s_tag);                        
                            $t_text[]=str_replace("_"," ",$teil[4]);
                            $erg=($temp-$s_tag);                      // 1 Tag zurück
                            $t_termin[]=$erg;
                            $t_anfang[]=$erg-($teil[2]*$s_tag);
                            $t_ende[]=$erg+($teil[3]*$s_tag);                        
                            $t_text[]=str_replace("_"," ",$teil[4]);
                            $erg=($temp+$s_tag);                      // 1 Tag vor
                            $t_termin[]=$erg;
                            $t_anfang[]=$erg-($teil[2]*$s_tag);
                            $t_ende[]=$erg+($teil[3]*$s_tag);                        
                            $t_text[]=str_replace("_"," ",$teil[4]);
                        }
                        elseif ($mode=="M")                             // Jeden Monat
                        {     
                            $temp=$erg;               
                            $t_anfang[]=$erg-($teil[2]*$s_tag);
                            $t_ende[]=$erg+($teil[3]*$s_tag);
                            $t_text[]=str_replace("_"," ",$teil[4]);
                            // 1 Monat zurück
                            $d_tag=$dats[1];
                            $d_monat=$akt_tag["mon"];
                            $d_jahr=$akt_tag["year"];
                            if ($d_monat == 1)
                            {
                                $d_monat=12;
                                $d_jahr=$d_jahr-1;
                            }
                            else
                            {
                                $d_monat=$d_monat-1;
                            }
                            //$anzahl =  cal_days_in_month(CAL_GREGORIAN, $d_monat, $d_jahr);
                            $anzahl = date("t",mktime(0,0,0,$d_monat,1,$d_jahr));
                            if ($anzahl < $d_tag)
                            {
                                $d_tag=$anzahl;
                            }
                            $erg=mktime(12,0,0,$d_monat,$d_tag,$d_jahr);
                            $t_termin[]=$erg;
                            $t_anfang[]=$erg-($teil[2]*$s_tag);
                            $t_ende[]=$erg+($teil[3]*$s_tag);                        
                            $t_text[]=str_replace("_"," ",$teil[4]);
                            // 1 Monat vor
                            $d_tag=$dats[1];
                            $d_monat=$akt_tag["mon"];
                            $d_jahr=$akt_tag["year"];                            
                            if ($d_monat == 12)
                            {
                                $d_monat=1;
                                $d_jahr=$d_jahr+1;
                            }
                            else
                            {
                                $d_monat=$d_monat+1;
                            }                            
                            //$anzahl =  cal_days_in_month(CAL_GREGORIAN, $d_monat, $d_jahr);
                            $anzahl = date("t",mktime(0,0,0,$d_monat,1,$d_jahr));
                            if ($anzahl < $d_tag)
                            {
                                $d_tag=$anzahl;
                            }                            
                            $erg=mktime(12,0,0,$d_monat,$d_tag,$d_jahr);
                            $t_termin[]=$erg;
                            $t_anfang[]=$erg-($teil[2]*$s_tag);
                            $t_ende[]=$erg+($teil[3]*$s_tag);                        
                            $t_text[]=str_replace("_"," ",$teil[4]);                            
             
                        }
                        elseif ($mode=="J")                             // Jedes Jahr
                        {
                            $temp=$erg;               
                            $t_anfang[]=$erg-($teil[2]*$s_tag);
                            $t_ende[]=$erg+($teil[3]*$s_tag);
                            $t_text[]=str_replace("_"," ",$teil[4]);
                            // 1 Jahr zurück
                            $d_tag=$dats[1];
                            $d_monat=$dats[2];
                            $d_jahr=$akt_tag["year"];  
                            $d_jahr=$d_jahr-1;
                            //$anzahl =  cal_days_in_month(CAL_GREGORIAN, $d_monat, $d_jahr);
                            $anzahl = date("t",mktime(0,0,0,$d_monat,1,$d_jahr));
                            if ($anzahl < $d_tag)
                            {
                                $d_tag=$anzahl;
                            }
                            $erg=mktime(12,0,0,$d_monat,$d_tag,$d_jahr);
                            $t_termin[]=$erg;
                            $t_anfang[]=$erg-($teil[2]*$s_tag);
                            $t_ende[]=$erg+($teil[3]*$s_tag);                        
                            $t_text[]=str_replace("_"," ",$teil[4]);                                                                                 
                            // 1 Jahr vor 
                            $d_tag=$dats[1];
                            $d_monat=$dats[2];
                            $d_jahr=$akt_tag["year"];
                            $d_jahr=$d_jahr+1;
                            //$anzahl =  cal_days_in_month(CAL_GREGORIAN, $d_monat, $d_jahr);
                            $anzahl = date("t",mktime(0,0,0,$d_monat,1,$d_jahr));
                            if ($anzahl < $d_tag)
                            {
                                $d_tag=$anzahl;
                            }
                            $erg=mktime(12,0,0,$d_monat,$d_tag,$d_jahr);
                            $t_termin[]=$erg;
                            $t_anfang[]=$erg-($teil[2]*$s_tag);
                            $t_ende[]=$erg+($teil[3]*$s_tag);                        
                            $t_text[]=str_replace("_"," ",$teil[4]);                                                          
                                                   
                        }
                        elseif ($mode=="D")                             // Fester Termin
                        {
                            $temp=$erg;               
                            $t_anfang[]=$erg-($teil[2]*$s_tag);
                            $t_ende[]=$erg+($teil[3]*$s_tag);
                            $t_text[]=str_replace("_"," ",$teil[4]);                        
                        }
                        
                    }                  
                }
                
            }
        }
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
if (is_string($param)) parse_str($param, $cmdl);

/* Die richtige Zeitzone einstellen */
date_default_timezone_set('Europe/Berlin');

/* Jahreszahl automatisch ermitteln */
if (isset($cmdl['jahr']))
{
    if (is_numeric($cmdl['jahr']))
    {
        if (($cmdl['jahr'] >= 2000) AND ($cmdl['jahr'] <= 2099)) $zlr_jahr = $cmdl['jahr'];
    } 
}
else
{
    $zlr_jahr = getdate()['year'];
}



/* Terminkalender generieren */
erstelle_termine();
/*  Test Anfang
echo("\r\n<br>\r\n<br>Anfang:\r\n<br>"); 
foreach ($t_anfang as $value)
{
    echo (date("D d.m.y",$value)."  /  ");
}
echo("\r\n<br>");

echo("\r\n<br>\r\n<br>Termin:\r\n<br>"); 
foreach ($t_termin as $value)
{
    echo (date("D d.m.y",$value)."  /  ");
}
echo("\r\n<br>");

echo("\r\n<br>\r\n<br>Ende:\r\n<br>"); 
foreach ($t_ende as $value)
{
    echo (date("D d.m.y",$value)."  /  ");
}
echo("\r\n<br>");

echo("\r\n<br>\r\n<br>Text:\r\n<br>"); 
foreach ($t_text as $value)
{
    echo ($value."  /  ");
}
echo("\r\n<br>");

return;
Test Ende   */

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
            #heute            { background-color:#00C0FF; }
            #heumar           { border:2px solid #FF0000; background-color:#00C0FF; }
            #titel            { font-size:24px; font-weight:bold; }
            a:link            { text-decoration:none; color:#000000; }
            a:visited         { text-decoration:none; color:#000000; }
            a:focus           { text-decoration:none; color:#000000; }
            a:hover           { text-decoration:none; color:#000000; }
            a:active          { text-decoration:none; color:#000000; }
        </style>    

        <body>

            <span id="titel">
                Kalen<a href="jkal-edit.php">d</a>er <?php echo($zlr_jahr); ?> 
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
                if ($inda == 1) echo ('<a href="index.php?jahr='.($zlr_jahr-1).'">');
                if ($inda == 12) echo ('<a href="index.php?jahr='.($zlr_jahr+1).'">');                
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
                        $hit_01=((getdate()['year']==$zlr_jahr)AND(getdate()['mon']==$inda)AND(getdate()['mday']==$vtag)AND($vkey!=0));
                        //$hit_02=((in_array(mktime(12,0,0,$inda,intval(trim($vtag)),$zlr_jahr),$t_termin))AND($vkey!=0));
                        
                        $index = 0;
                        $info_txt = "";
                        $hit_02 = FALSE;
                        for ($index=0;$index<(count($t_termin));$index++)
                        {
                            if ((abs(($t_termin[$index])-(mktime(12,0,0,$inda,intval(trim($vtag)),$zlr_jahr)))<3700)AND($vkey!==0)AND(is_numeric(trim($vtag))))                        
                            //if ((($t_termin[$index])===(mktime(12,0,0,$inda,intval(trim($vtag)),$zlr_jahr)))AND($vkey!==0)AND(is_numeric(trim($vtag))))
                            {
                                $hit_02=TRUE;
                                $info_txt=$info_txt.$t_text[$index]."\r\n";
                            }
                        }
                        
                        if (($hit_01===TRUE)AND($hit_02===FALSE))
                        {
                            echo ('<td id="heute">'.$vtag."</td>");
                        }
                        elseif (($hit_01===FALSE)AND($hit_02===TRUE))
                        {
                            echo ('<td id="marker" title="'.$info_txt.'">'.$vtag."</td>");
                        }                                                
                        elseif (($hit_01===TRUE)AND($hit_02===TRUE))
                        {
                            echo ('<td id="heumar" title="'.$info_txt.'">'.$vtag."</td>");
                        }
                        
                        //if ( (getdate()['year']==$zlr_jahr) AND (getdate()['mon']==$inda) AND (getdate()['mday']==$vtag) AND ($vkey != 0) ) 
                        //{
                            //echo ('<td id="marker">'.$vtag."</td>");
                        //    echo ('<td id="heute" title="Mc killroy was here">'.$vtag."</td>");
                            //echo ('<td id="heumar">'.$vtag."</td>"); 
                        //}
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
