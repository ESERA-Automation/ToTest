# ESERA Binäreingang Dual
Das Modul bindet den Binäreingang Dual von ESERA-Automation ein. Es werden automatisch Variablen angelegt und eingelesen.

### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Stellt via ESERA-Automation 1-Wire Controller / 1-Wire Gateway Verbindung zum Binäreingang Dual her.
* OWDID einstellbar
* Automatische Aktualisierung der Werte

### 2. Voraussetzungen

- IP-Symcon ab Version 4.2
- ESERA-Automation 1-Wire Controller / 1-Wire Gateway

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen:
`git://github.com/ESERA-Automation/IPS-Module.git`  

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'Binaer Eingang Dual'-Modul unter dem Hersteller 'ESERA-Automation' aufgeführt.  

__Konfigurationsseite__:

Name | Beschreibung
---- | ---------------------------------
OWD  | Auswahl der eingerichteten OWDID.

### 5. Statusvariablen und Profile

Die Statusvariablen werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Statusvariablen

Es werden automatisch alle übermittelten Werte angelegt.
- Eingang A
- Eingang B

__Unterstützte Datenpakete__

Typ       | Variablentyp
--------- | -------------
OWD       | Integer

##### Profile:

Es werden keine zusätzlichen Profile hinzugefügt

### 6. WebFront

Über das WebFront und die mobilen Apps werden die Variablen angezeigt. Es ist keine weitere Steuerung oder gesonderte Darstellung integriert.

### 7. PHP-Befehlsreferenz
Es gibt keine Befehle.
