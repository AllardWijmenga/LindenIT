# GridWatch 2030 – installatie en testscenario's

## Starten

```
docker compose up -d --build
```

Open daarna http://localhost:8080 en log in. Logs staan in `logs/app.log`.
Opnieuw beginnen met een schone database: `docker compose down -v && docker compose up -d --build`
(`Database/init.sql` draait alleen bij de allereerste start van de database).

## Mappen

| Map / bestand | Doel |
|---|---|
| `web/` | De website: alle PHP-pagina's, `style.css`, JavaScript. Wordt in de container gemount, dus wijzigingen zijn direct zichtbaar. |
| `web/includes/` | `auth.php` (sessie), `db.php` (database), `logger.php` (logging), `header.html`. Niet bereikbaar via de browser. |
| `web/api.php` | REST API, bereikbaar als `/api/...` (via `web/.htaccess`) |
| `web/scenarios.php` | Acties achter de knoppen op het tabblad "Scenario's" |
| `Database/init.sql` | Tabellen en voorbeelddata |
| `docker/` | Apache-instelling en entrypoint (logt starten en stoppen) |
| `logs/` | `app.log`, gekoppeld aan de container |

## Containers

| Container | Image | Taak | Poort |
|---|---|---|---|
| `webapp` | php:8.3-apache (eigen build) | Website, API en logging | 8080 → 80 |
| `db` | mariadb:11 | Database `gridwatch` | intern 3306 |

## Accounts (inloggen met e-mailadres)

| E-mail | Wachtwoord | Rol |
|---|---|---|
| `admin@gridwatch.local` | `Gr1d!Watch2030` | admin |
| `operator@gridwatch.local` | `Operator!2030` | operator |

Nieuwe gebruikers maak je aan via de registratiepagina.

## Database

| Tabel | Kolommen |
|---|---|
| `users` | id, name, pass (bcrypt), email, role, date_created |
| `stations` | id, name, location, status |
| `events` | id, timestamp, type, location, description |
| `sensor_data` | id, station_id, timestamp, power_mw, temperature |

## API (alleen lezen)

| Endpoint | Doel |
|---|---|
| `GET /api/stations` | Alle stations |
| `GET /api/station/<id>` | Eén station met laatste meting |
| `GET /api/alerts` | Laatste 50 meldingen (events) |
| `GET /api/sensor-data` | Laatste sensorwaarden per station |
| `GET /api/grid-health` | Bonus: samenvatting van het net |

## Logregels

| Gebeurtenis | Logregel |
|---|---|
| Container gestart | `INFO Application started` |
| Container gestopt | `INFO Application stopped` |
| Succesvolle login | `INFO User admin logged in` |
| Mislukte login | `WARN Invalid password for user admin` / `WARN Unknown user x@y.nl` |
| API-verzoek | `INFO GET /api/stations from 172.18.0.1` |
| Applicatiefout | `ERROR Test exception generated` |
| Database niet bereikbaar | `ERROR Database connection failed: ...` |
| Hoge belasting | `INFO Generated 1000 sensor records` |

## Testscenario's

| # | Scenario | Uitvoeren | Verwachte logregel |
|---|---|---|---|
| 1 | Succesvolle login | Inloggen als `admin@gridwatch.local` | `INFO User admin logged in` |
| 2 | Foutieve logins | 3x verkeerd wachtwoord (`welkom123`, `test123`, `admin123`) | `WARN Invalid password for user admin` (3x) |
| 3 | Veel API-verkeer | Scenario's → "Start API-flood", of de `curl`-lus hieronder | `INFO GET /api/stations from ...` (40x) |
| 4 | Applicatiefout | Scenario's → "Genereer foutmelding" | `ERROR Test exception generated` |
| 5 | Containeruitval | `docker stop webapp` | `INFO Application stopped` |
| 6 | Hoge belasting | Scenario's → "Genereer sensorrecords" (1000) | `INFO Generated 1000 sensor records` |

API-flood vanaf de terminal:

```
for i in $(seq 1 40); do curl -s http://localhost:8080/api/stations > /dev/null; done
```

Container weer starten: `docker start webapp` (logt `INFO Application started`).
Logs live volgen: `tail -f logs/app.log`.
