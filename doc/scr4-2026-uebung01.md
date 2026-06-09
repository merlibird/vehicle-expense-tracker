SCR 4 / SS 20206 Übung 01
Übungsleitung: Mag. Elmar Putz / Mag. Mario Rader

# Projektaufgabe SCR4 “Fahrzeugkosten-Tracker”

Erstellen Sie eine Webanwendung zur Verwaltung von Fahrzeugkosten, mit der registrierte
Benutzer*innen Ausgaben rund um ein oder mehrere Fahrzeuge erfassen, kategorisieren und
analysieren können. Ziel ist eine übersichtliche Darstellung der laufenden Kosten pro Fahrzeug,
gruppiert nach Monat und Kategorie.

# Funktionale Anforderungen

## Grundfunktionalitäten

**Fahrzeuge**
● Jedes Fahrzeug enthält zumindest: Bezeichnung (z. B. Marke, Modell), Kennzeichen,
Erstzulassung
● Fahrzeuge können erstellt, bearbeitet und (weich) gelöscht werden
● Ein*e Benutzer*in kann mehrere Fahrzeuge verwalten
**Kostenbuchungen**
Jede Buchung enthält: Datum, Betrag, Kategorie (z. B. Tanken, Werkstatt, Versicherung, Steuer,
Sonstiges), Beschreibung / Notiz, Kilometerstand zum Zeitpunkt der Buchung, Status (aktiv /
gelöscht). Buchungen können erstellt, bearbeitet und weich gelöscht werden (Löschung über Flag,
nicht physikalisch).
**_Tankbuchungen (Spezialkategorie)_**
● Zusätzlich zu den allgemeinen Buchungsfeldern: getankte Liter und Kraftstoffpreis pro
Liter
● Basis für die Berechnung des Durchschnittsverbrauchs (optional, siehe 1.3)
**Kategorien**
● Kategorien können zentral verwaltet werden
● Buchungseintrag kann Kategorien zugeordnet werden (m:n)
**Übersicht / Auswertung**
● Einträge werden pro Fahrzeug historisch gespeichert
● Basis für Verbrauchsauswertungen (optional)
**Kilometerstand-Verlauf**
● Einträge werden pro Fahrzeug historisch gespeichert
● Basis für Verbrauchsauswertungen (optional)


SCR 4 / SS 20206 Übung 01
Übungsleitung: Mag. Elmar Putz / Mag. Mario Rader
**Übersicht / Auswertung**
● Monatliche Gesamtkosten pro Fahrzeug
● Übersichtliche Auflistung aller Buchungen
● Filter: nach Fahrzeug, Monat, Jahr, Kategorie
**Benutzer**
● Informationen zu User (Vorname/Nachname, Bild, ....)
● Authentifizierung und Login
● Benutzeraktionen werden in einem Log erfasst und gespeichert (IP Adresse, Aktion,
Benutzername, Timestamp)

## Benutzer*innen / Rollen

Es existieren zumindest folgende Benutzerrollen:

**1. Anonyme Besucher*innen**
    a. Können sich registrieren, keine Daten einsehbar
**2. Registrierte Benutzer*innen**
    a. Können Buchungen verwalten und Auswertungen einsehen.
    b. Nur eigene Daten sichtbar.
**3. Administrator*in**
    a. Kann Benutzer*innen deaktivieren.
_HINWEIS:_ ein UI für die Benutzer- und Rollenverwaltung muss nicht implementiert werden, es
reicht, diese in der Datenbank entsprechend zu verwalten, die Logik ist jedoch erforderlich.
**Optionale Erweiterung: Durchschnittsverbrauch**
Berechnung des Durchschnittsverbrauchs (l/100 km) auf Basis von Tankbuchungen und
Kilometerstandeinträgen, inkl. Darstellung des Verlaufs über mehrere Tankfüllungen.

# Non-funktionale Anforderungen

## Programmierung Server

Umsetzung mittels PHP >= 8.5 (OOP) – achten Sie auf einen sauberen Aufbau und eine Trennung
der einzelnen Anwendungsschichten. Achten Sie auch darauf, die Features von PHP 8
(Typisierung, Operatoren, Strict) zu nutzen. Für die Anbindung der Datenquelle benutzen Sie die
Bibliothek _PDO_. Sichern Sie Ihre Anwendung gegen SQL-Injection und XSS-Angriffe durch
serverseitige Prüfung der Eingaben und Parameter (Validierung,, UI).


SCR 4 / SS 20206 Übung 01
Übungsleitung: Mag. Elmar Putz / Mag. Mario Rader
Bei Benutzeraktionen werden zusätzlich Daten zur Identifizierung mitgespeichert (bspw.
IP-Adresse der BenutzerInnen)

## Datenbank

Die Datenverwaltung erfolgt mittels MySQL / MariaDB (InnoDB). Achten Sie auf die Grundregeln
des Datenbank-Designs (Datentypen, Normalisierung, sinnvolle Constraints, ...).

## Client / Frontend

Die Verwendung von Javascript (auch Frameworks, aber lediglich UI Frameworks wie bspw.
Bootstrap - keine SPA-Frameworks wie Angular, React oder VueJS) ist erlaubt. Die Logik muss
serverseitig implementiert werden. Der HTML-Code sollte HTML5 valide sein, sämtliche
Formatierungen via CSS umgesetzt werden. Die Benutzeroberfläche sollte intuitiv bedienbar sein
und bei neuen Benutzer*innen der Anwendung keinerlei Erklärungsbedarf erfordern. Achten Sie
insbesondere auf etwaige notwendige Erklärungstexte, Validierung von Eingaben sowie die
Fehlerausgabe. Auch eine gezielte Verwendung von AJAX ist möglich, sofern diese für Sie sinnvoll
erscheint (ist jedoch nicht zwingend Voraussetzung).
_HINWEIS:_ das „Design“ sollte einfach, aber funktional sein – legen Sie Augenmerk auf intuitive
Bedienbarkeit und logische Führung der Benutzer*innen durch die Website (Texte,
Navigationselemente, Feedback bei Benutzeraktionen, Formular-Validierungen & -Layout, ...).
Achten Sie insbesondere auf eine übersichtliche Darstellung, wenn die Listen länger werden.
Nutzen Sie im Falle eines Frameworks die zur Verfügung stehenden Module und Elemente.

# Form der Abgabe

## Dokumentation

Erstellen Sie für Ihre Datenbank-Umsetzung ein ER- oder UML-Diagramm, welches die Entitäten
und Beziehungen visualisiert. Für die Anwendung erstellen Sie bitte eine textuelle Beschreibung
und / oder eine dokumentierte Skizze der Architektur.

## Testfälle

Führen Sie ausführliche Tests Ihrer Anwendung durch und dokumentieren Sie diese. Testen Sie
auch fehlerhafte Eingaben! Ihre Testfälle sollten auf jeden Fall folgende Zustände testen:
● Benutzerlogin (inkl. Fehlerüberprüfung und -behandlung) der unterschiedlichen Rollen
● Darstellung der Applikation aus Sicht der unterschiedlichen Rollen inkl. Status
● Alle elementaren CRUD Operationen
Erstellen Sie eine entsprechende Dokumentation und dokumentieren Sie darüber hinaus die
Testfälle in Form eines PDF-Dokuments.

## Anwendung

1. **Datenbank**
    SQL – Dump inkl. Testdaten und CREATE DATABASE statement


SCR 4 / SS 20206 Übung 01
Übungsleitung: Mag. Elmar Putz / Mag. Mario Rader

2. **Code**
    Zip-Archiv, Startdokument = index.php, muss auf Standard – DDEV Installation lauffähig
    sein. Die Dokumentation speichern Sie bitte im Unterverzeichnis /doc.

## Deadline

Laden Sie Ihre Anwendung in Form **einer einzigen Datei im ZIP-Format** im entsprechenden
Abgabe-Modul via Moodle hoch. Den konkreten Termin entnehmen Sie bitte diesem
Abgabe-Modul in Moodle. Der Abgabetermin wird noch kommuniziert.

## Beurteilung

Die Beurteilung der Übung erfolgt im Rahmen einer Präsentation vor der eigenen Gruppe bzw.
dem LVA-Leiter. Bei dieser Präsentation stellen Sie bitte Ihre Lösung vor, demonstrieren die
Testfälle und stehen für technische und inhaltliche Fragen bzw. Code-Reviews zur Verfügung.
