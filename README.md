# custom-fields-rss
WordPress.org Plugin Mirror
In den Einstellungen für diese benutzerdefinierten Felder fügen Sie bitte nachfolgende 
fünf Felder in das dafür vorgesehene Textfeld hinzu.
Diese Einstellungen finden Sie im Menü Plugins -> installierte Plugins -> Custom Field Template -> 
Einstellungen -> Textfeld "Template Content":
<pre>
[nummer]
type = text
size = 255
label = Bitte eine eindeutige Nummer für diese Stellenanzeige hinterlegen.

[kontakt]
type = text
size = 255
label = Herr Max Mustermann max.mustermann@firma.de
default = "Max Mustermann max.mustermann@firma.de"

[link]
type = text
size = 255
label = Link zur Stelle

[tags]
type = text
size = 255
label = Kommagetrennte Schlagworte, mit MINT/IT/OFFICE am Ende: Softwareetnwickler, Administrator, Datenbank, IT
default = IT

[ort]
type = text
size = 255
default = Dresden
</pre>
Nachdem Sie auf „Einstellungen aktualisieren“ geklickt haben werden diese fünf Felder automatisch 
durch die Empfehlungsbund-Feederweiterung jeder Stelle hinzugefügt und können pro Stelle individuell angepasst werden.
Sie können auch Standardwerte hinterlegen. Dazu dient der „default“ Parameter in den benutzerdefinierten Feldern. 
Wenn Sie nur einen Standort haben können Sie diesen als Standardwert hinterlegen. 
