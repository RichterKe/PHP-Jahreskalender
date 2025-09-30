<h1>PHP Jahreskalender als Docker Container</h1>

Gibt eine HTML Seite aus mit Anzeige der Kalenderwochen.  
Blendet Termine ein.

<b>Vorbereiten:</b>    
Aus dem Verzeichnis "docker" die Datei "docker-compose.yaml" herunterladen.  

<b>Docker Container starten:</b>  
docker compose up -d  

<b>Docker Container beenden:</b>  
docker compose down
  
<b>Image wieder entfernen:</b>  
docker container rm -f jahreskalender  
docker image rm richterke/kalender:v3.0  
  
<b>Bedienung:</b>  
Starten im Webbrowser unter der Adresse "localhost" oder "127.0.0.1"  
Ein Jahr zurückblättern mit Mausklick auf das Monatsfeld Januar.  
Ein Jahr vorblättern mit Mausklick auf das Monatsfeld Dezember.  
Termine eingeben mit Mausklick auf den Buchstaben d im Wort Kalender  
Achtung: Auf dem Host wird ein Verzeichnis mit dem Namen "kalenderdaten" angelegt
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;in diesem Verzeichnis werden die Termine gespeichert und bei erneutem Aufruf
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;von dort geladen

Bei Fragen erreicht Ihr mich im Heimnetz Forum: https://forum.heimnetz.de/threads/jahreskalender.7330/
