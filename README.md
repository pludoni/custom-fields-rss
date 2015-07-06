# custom-fields-rss - Empfehlungsbund Wordpress Plugin

Die Ausschreibung von Stellenanzeigen und deren Weiterverteilung in bis zu 40 Meta-Jobbörsen erfolgt für unsere Mitglieder über eine automatisierte Schnittstelle, welche über einen XML-RSS-Feed angebunden wird. Viele Partner verwenden ein Content-Management-System (CMS) zur Verwaltung Ihrer Website-Inhalte. Die meisten CMS bieten einen XML-RSS-Feed von Haus aus an. 


## Installation

Für den Empfehlungsbund-Feedimport werden benutzerdefinierte Felder ("Ort", "Kontakt", "Link zur Stelle" und "Tags") benötigt.

1. Installation des Plugins "Custom Field Template" <a target="_blank" href="https://wordpress.org/plugins/custom-field-template/">hier</a>. 
2. Installation dieses Plugins https://github.com/pludoni/custom-fields-rss/archive/1.0.zip.

In den Einstellungen für diese benutzerdefinierten Felder fügen Sie bitte nachfolgende 
fünf Felder in das dafür vorgesehene Textfeld hinzu.
Diese Einstellungen finden Sie im Menü ``Plugins -> installierte Plugins -> Custom Field Template -> 
Einstellungen -> Textfeld "Template Content"``:

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
label = Kommagetrennte Schlagworte, mit MINT/IT/OFFICE am Ende: Softwarentwickler, Administrator, Datenbank, IT
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

Die RSS-Feeds können dann im Empfehlungsbund importiert werden (http://www.empfehlungsbund.de/validator).
