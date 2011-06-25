=====
Ruins
=====

:Authors:
   Markus Schlegel

:Version:
   0.1

-------------------
Directory Standards
-------------------

Hauptverzeichnis:
~~~~~~~~~~~~~~~~~

**Common**
   In diesem Verzeichnis sind alle unabhängigen Elemente, die keinerlei Beziehung zu anderen Verzeichnissen haben.

**External**
   Dieses Verzeichnis beherbergt Bibliotheken und Klassen von externen Projekten. So liegt z.B. Doctrine, unser
   ORM System und damit die Basis von Ruins in diesem Ordner. Auch Smarty, das Template System ist hier zu finden.
   Externe Bibliotheken müssen sich natürlich nicht an unsere Verzeichnisstruktur halten.

**Main**
   Hier kommen alle Elemente hinein die sich direkt auf Ruins beziehen und auch Abhängigkeiten im Common-Tree
   haben können. Referenzen auf Module sind nicht erlaubt.

**Modules**
   Module können Abhängigkeiten im Common- und im Main-Tree haben, arbeiten jedoch in ihrem eigenen Namespace und
   sind damit relativ unabhängig vom Rest. Common und Main-Elemente können damit überladen werden.

Unterverzeichnis:
~~~~~~~~~~~~~~~~~

**Area**
   Das sind unsere 'Orte', also die eigentlichen Seiten mit einfacher Logik zur Darstellung der Views. Hier
   werden hauptsächlich die Controller und Manager eingesetzt.

**Controller**
   Controller sind die Logik des Programms, hier werden Entities geladen, verändert, abgespeichert oder
   anderweitig bearbeitet. Auch die Ausgabe der Views wird hierüber gesteuert.

**DoctrineExtensions**
   Wie der Name schon sagt liegen hier Erweiterungen für Doctrine, dem Engine von Ruins.

**Entities**
   Entities entsprechen dem Model, also dem Datenspeicher aus dem wir unsere unsere Daten beziehen. Entities
   enthalten Annotations (http://www.doctrine-project.org/docs/orm/2.0/en/reference/annotations-reference.html)
   und sind definieren damit das Aussehen des Models. Logik die sich auf das Entity oder damit verknüpfte
   andere Entities bezieht, ist in den Entities erlaubt.

**Functions**
   Hier sollten nur Funktionen hinein, die praktisch in keinen Manager passen wollen. Funktionen sollten allgemein
   vermieden werden und man sollte lieber auf Manager als Bibliothek zurückgreifen. Diese erfüllen einen sehr
   ähnlichen Zweck.

**Helpers**
   Helpers sind Codeschnipsel die im Controller includiert oder durch Ajax-Calls aufgerufen werden. Sie können
   ganz unterschiedliche Aufgaben erfüllen, allgemein sind sie jedoch auf nur einen Anwendungsbereich begrenzt.
   Wenn es möglich ist, sollten sie auch vermieden werden.

**Interfaces**
   Interfaces sind PHP-Interfaces, die der Logik nach nur im Common-Tree erlaubt sind. Sie definieren das Aussehen
   eines Controllers wie z.B. einer Schnittstelle. Sie enthalten keine Logik und sind damit allgemein gültig.

**Layers**
   Hier findet sich der Ersatz von Doctrines noch nicht fertiggestelltem Support für ValueObjects. Layers sind
   Objekte bei denen eine Änderung von Doctrine nicht erkannt werden würde. Ausserdem können Layer erweiterte
   Informationen zu einzelnen Werten bieten (siehe z.b. Layers/Money). Layer beziehen sich also immer nur auf den
   ihnen überlassenen Wert.

**Manager**
   Sozusagen der Ersatz für Funktions-Bibliotheken. Manager sind Klassen die *nur* statische enthalten und damit
   sowas wie die Helfer der Controller sind.

**Setup**
   Das Setup-Verzeichnis ist nur bei der Installation relevant. Es kann eine Initial.php enthalten, die am Ende
   der regulären Installation aufgerufen wird. Darin können z.b. Standardeinträge in die Datenbank enthalten sein
   oder andere einmalige Operationen. Wichtig ist dass das Script selbst danach schaut dass es zu keinen
   Duplikationen kommt.

**View**
   Hier kommt alles Ausgabe-relevante hinein. Dazu gehören insbesonders Templates, CSS-Dateien, JavaScripte und
   auch Bilder die später im Browser angezeigt werden sollen. PHP-Logik hat hier nichts verloren.

----------------
Coding Standards
----------------

Wir halten uns so gut wie möglich an den Coding-Standard des Zend Frameworkes.
(http://framework.zend.com/manual/de/coding-standard.html).
