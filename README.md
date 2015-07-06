# custom-fields-rss
WordPress.org Plugin Mirror
Die Ausschreibung von Stellenanzeigen und deren Weiterverteilung in bis zu 40 Meta-Jobbörsen, erfolgt für unsere Mitglieder über eine automatisierte Schnittstelle, welche über einen XML-RSS-Feed angebunden wird. Viele Partner verwenden ein Content-Management-System (CMS) zur Verwaltung Ihrer Website-Inhalte. Die meisten CMS bieten einen XML-RSS-Feed von Haus aus an. Einige unserer Partner verwenden Wordpress zur Verwaltung Ihrer Website und haben uns gefragt, ob wir ein Plugin zur Verfügung stellen, welches die Anpassung des Feeds an unsere Anforderungen erleichtert bzw. komplett übernimmt. 

Dazu haben wir nun ein Plugin erstellt.
Installieren Sie dazu einfach das heruntergeladene ZIP-File im Admin Bereich Ihres Wordpress und benutzerdefinierte Felder werden automatisch jeder Stelle im Feed hinzugefügt.
Um die benutzerdefinierten Felder wie "Ort", "Kontakt", "Link zur Stelle" und "Schlagworte" pro Stelle im Feed hinzuzufügen benötigen Sie ein weiteres Plugin "Custom Field Template". Dieses finden Sie 
<a target="_blank" href="https://wordpress.org/plugins/custom-field-template/">hier</a>. 

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
