# FireArcade - Spelkasten Beheersysteem

## Projectbeschrijving
FireArcade is een beheersysteem voor een spelkastenbedrijf, waarmee verschillende gebruikersrollen (admin, verkoper, monteur, klant) specifieke taken kunnen uitvoeren binnen het platform.

## Systeemvereisten
- PHP 7.4 of hoger
- MySQL
- XAMPP/WAMP
- Webbrowser (Chrome, Firefox, Safari, Edge)

## Installatie
1. Installeer XAMPP (of een vergelijkbare webserver met PHP en MySQL)
2. Clone of download de FireArcade repository naar de htdocs map van XAMPP
3. Start de Apache en MySQL services in XAMPP
4. Importeer het databasebestand `firearcade.sql` in phpMyAdmin
5. Open een browser en navigeer naar `http://localhost/firearcade/`

## Database Configuratie
De database-instellingen kunnen worden aangepast in de configuratiebestanden van elke module:
```php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "firearcade";
```

## Projectstructuur
```
firearcade/
├── admin/                  # Beheerdersdeel
│   ├── account-beheer.php  # Gebruikersbeheer
│   ├── login.php           # Inlogpagina
│   └── ...
├── verkoper/               # Verkopersdeel
│   ├── verkoop-dashboard.php     # Dashboard voor verkopers
│   ├── bestelling-toevoegen.php  # Nieuwe bestellingen toevoegen
│   ├── klanten-beheer.php        # Klantenbeheer
│   ├── klant-toevoegen.php       # Nieuwe klanten toevoegen
│   └── ...
├── monteur/                # Monteurdeel
│   ├── monteur-dashboard.php     # Dashboard voor monteurs
│   └── ...
├── klant/                  # Klantendeel
│   ├── klant-dashboard.php       # Dashboard voor klanten
│   └── ...
├── css/                    # Stylesheets
│   ├── style.css           # Algemene stijlen
│   ├── login.css           # Stijlen voor inlogpagina
│   └── ...
├── index.html              # Startpagina
├── logout.php              # Uitlogscript
└── README.md               # Dit bestand
```

## Functionaliteiten

### Admin
- Gebruikersbeheer (accounts aanmaken, wijzigen, verwijderen)
- Systeeminstellingen beheren

### Verkoper
- Dashboard met overzicht van bestellingen
- Bestellingen toevoegen
- Klantenbeheer (toevoegen, wijzigen, verwijderen)

### Monteur
- Dashboard met overzicht van reparaties en onderhoud
- Reparatiestatus bijwerken

### Klant
- Persoonlijk dashboard
- Bestelgeschiedenis bekijken
- Reparatie/montage verzoeken indienen

## Inloggen
Standaard inloggegevens voor test:
- Admin: admin@firearcade.nl / password: password
- Verkoper: verkoper@gmail.nl / password: 12345678
- Monteur: monteur@gmail.nl / password: 12345678
- Klant: aiden@test.nl / password: 12345678