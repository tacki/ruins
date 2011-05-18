**************************************************************************
** STANDARDMETHODEN  *****************************************************
**************************************************************************

Folgende Standardmethoden können bei Klassen verwendet werden. Dabei ist
darauf zu achten, dass diese Methoden nicht mehr machen als sie sollen.

create():
Diese Methode füllt den mit 'new' erstellten Objekt mit Leerwerten.
Dabei sollte das Objekt danach alle erforderlichen Eigenschaften
besitzen (nur eben mit leeren Werten)! Das Objekt gilt danach als
geladen.

load():
Diese Methode füllt das Objekt mit Werten aus einer Datenquelle.
Dabei kann die Funktion sich auch um die korrekte Aufbereitung der
Daten kümmern. Das Objekt gilt danach als geladen.

save():
Diese Methode schreibt alle geänderten Daten in die Datenquelle
aus der die Daten stammen. Diese Funktion sollte sich auch um die
Aufbereitung der Daten kümmern, damit sie korrekt in die Datenquelle
geschrieben werden können. Die Daten im Objekt werden behalten,
deshalb gilt das Objekt auch immernoch als geladen.

delete():
Diese Methode löscht den geladenen Datensatz aus der Datenquelle
und entlädt sich mit der unload()-Methode anschliessend selbst.

unload():
Diese Methode entlädt das Objekt und setzt es in einen komplett
frischen Zustand (wie bei einem mit 'new' erstellten Objekt).
Das Objekt gilt danach als nicht geladen.

get():
Diese Methode gibt einen Eintrag, einen Wert oder ein Objekt aus
einem zuvor geladenen Objekt zurück. Diese Funktion kann bei sehr
simplen Klassen auch einen automatischen load()-Aufruf enthalten.

set():
Diese Methode ändert einen Eintrag, einen Wert, oder ein Objekt aus
einem zuvor geladenen Objekt. Diese Funktion kann bei sehr simplen
Klassen auch einen automatischen save()-Aufruf enthalten.

cleanup():
Diese Methode räumt das Objekt auf und entfernt z.b. temporäre
Daten, setzt Zähler, Timer, etc. zurück. Das Objekt gilt danach noch
als geladen.

import():
Diese Methode ermöglicht es, einen Datenbestand, der nicht aus der von
der Klasse vorgesehenen Datenquelle stammt, zu importieren. Das Objekt
gilt bei einem erfolgreichen Import als geladen.

export():
Diese Methode exportiert den Datenbestand in eine Form, die nicht zu der
primären Datenquelle der Klasse gehört. Das Objekt gilt danach immernoch
als geladen.


**************************************************************************
** ANMERKUNG  ************************************************************
**************************************************************************

Weitere Methoden sind natürlich möglich. Falls zu diesen Standardmethoden
noch eine weitere gezählt werden sollte, dann bitte mich (tacki)
kontaktieren.



