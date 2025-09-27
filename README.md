PHP Jahreskalender als Docker Container

Gibt eine HTML Seite aus mit Anzeige der Kalenderwochen.  
Blendet Termine ein.

Vorbereiten:    
Aus dem Verzeichnis die Datei "docker-compose.yaml" herunterladen.  

Docker Container starten:  
docker compose up -d  

Docker Container beenden:  
docker compose down
  
Image wieder entfernen:  
docker container rm -f jahreskalender  
docker image rm richterke/kalender:v2.0  
  
Bedienung:  
Ein Jahr zurückblättern mit Mausklick auf das Monatsfeld Januar.  
Ein Jahr vorblättern mit Mausklick auf das Monatsfeld Dezember. 
Termine eingeben mit Mausklick auf den Buchstaben d im Wort Kalender  
