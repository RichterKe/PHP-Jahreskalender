PHP Jahreskalender als Docker Container

Gibt eine HTML Seite aus mit Anzeige der Kalenderwochen.  
Blendet Termine ein.

<b>Vorbereiten:</b>    
Aus dem Verzeichnis die Datei "docker-compose.yaml" herunterladen.  

<b>Docker Container starten:</b>  
docker compose up -d  

<b>Docker Container beenden:</b>  
docker compose down
  
<b>Image wieder entfernen:</b>  
docker container rm -f jahreskalender  
docker image rm richterke/kalender:v2.0  
  
<b>Bedienung:</b>  
Ein Jahr zurückblättern mit Mausklick auf das Monatsfeld Januar.  
Ein Jahr vorblättern mit Mausklick auf das Monatsfeld Dezember.  
Termine eingeben mit Mausklick auf den Buchstaben d im Wort Kalender  
