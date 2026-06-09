# MiniTrust

Kis Symfony 8 alapú értékelő rendszer, amely lehetővé teszi a felhasználók számára, hogy értékeléseket hozzanak létre és
megtekintsék a cégstatisztikákat. Az alkalmazás MySQL adatbázist használ, és a teljes környezet Dockerben futtatható.

- új értékelés létrehozása
- értékelések listázása
- értékelések cég szerinti listázása
- cégstatisztikák megjelenítése
- az értékelések szöveg LLM segítségével validálja, hogy nem tartalmaz-e sértő vagy spam jellegű tartalmat

## App indítása Docker-rel

### 0) Előfeltételek

- Docker Desktop (vagy kompatibilis Docker + Compose)
- Git

### 1) Kód letöltése

```bash
git clone git@github.com:catchke2ro/minitrust.git minitrust
cd minitrust
```

### 2) Környezeti fájl beállítása

.env fájl létrehozása a `.env.example` alapján.
A `MYSQL_*` értékek legyenek kitöltve a `.env` fájlban indítás előtt, hogy a MySQL konténer megfelelően tudjon létrejönni.

```bash
cp .env.example .env
```

### 3) Konténerek felépítése

```bash
docker compose build
```

### 4) PHP függőségek telepítése

```bash
docker compose run --rm php composer install
```

### 5) Docker konténerek indítása

```bash
docker compose up -d
```

### 6) Migrációk futtatása

```bash
docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
```

### 7) Alkalmazás kipróbálása

- App: `http://localhost`
- HTTPS (ha kell): `https://localhost`
- MySQL host gépről: `127.0.0.1:3366`

## Tesztek futtatása

```bash
docker compose exec php php bin/phpunit
```

A funkcionális tesztek futtatásához létre kell hozni a test adatbázist:

```bash
docker compose exec php bin/console --env=test doctrine:database:create
docker compose exec php bin/console --env=test doctrine:schema:create
```

## AI alapú tartalomellenőrzés beállítása

Az értékelés szövegére fut egy AI validáció (sértő/explicit tartalom szűrése).
Ehhez add meg legalább a Mistral API kulcsot a `.env` fájlban:

```bash
MISTRAL_API_KEY=your_mistral_api_key
```

## Munkaidő napló feladatonként

| Feladat                                                                                      | Becsült idő    |
|----------------------------------------------------------------------------------------------|----------------|
| Projekt bootstrap (Symfony alap, Függőségek, Doctrine, Carbon, PHP-CS-Fixer, IDE Beállítás)  | ~1 óra 30 perc |
| Domain alapok (Review entity + migration, repository)                                        | ~45 perc       |
| Fő funkciók és UI (review CRUD flow, layout, ikonok, értékelés CSS, fordítások, cég oldalak) | ~2 óra         |
| Tesztek (PHPUnit telepítés, unit + functional tesztek)                                       | ~45 perc       |
| LLM moderáció + dokumentáció (`README`, `.env.example`)                                      | ~30 perc       |
| Befejezés, javítások                                                                         | ~30 perc       |

**Összesen: ~6 óra**
