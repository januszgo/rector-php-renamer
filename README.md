# PrefixInfRector – masowa refaktoryzacja PHP z użyciem AST

Ten projekt zawiera **niestandardową regułę Rectora**, która umożliwia:

- dodanie prefixu `inf_` do **klas**, **funkcji** i ich użyć
- bezpieczną refaktoryzację opartą o **AST (Abstract Syntax Tree)**
- modyfikację `include / include_once / require` do postaci:

```php
include_once($_SERVER['DOCUMENT_ROOT'].'/ścieżka/do/pliku.php');
```

Projekt powstał jako narzędzie jednorazowe / archiwizacyjne do migracji starszych codebase’ów PHP.

---

## Wymagania

- PHP **8.3+** (testowane na PHP 8.5)
- Composer
- Rector `^2.x`
- System: Windows / Linux / macOS

---

## Struktura

```
.
├── PrefixInfRector.php
├── src/
│   └── Rector/
│       └── rector.php
├── vendor/
├── composer.json
└── README.md
```

---

## Instalacja Rectora

```bash
composer require rector/rector --dev
```

Sprawdzenie:

```bash
vendor/bin/rector --version
```

---

## Użycie

Dry-run (zalecane):

```bash
vendor/bin/rector process src --config src/Rector/rector.php --dry-run
```

Zapis zmian:

```bash
vendor/bin/rector process src --config src/Rector/rector.php
```

---

## Co robi PrefixInfRector

### Automatycznie:
- dodaje prefix `inf_` do klas i funkcji
- aktualizuje wszystkie wywołania (`new`, `::`, `->`, `()`)
- działa na AST (bez regexów)

### Niestety błędnie dodaje prefixy do tego typu tokenów - niestety konieczne są ręczne poprawki, idealnie do tego nadaje się Visual Studio Code mające rozbudowane narzędzia z tego zakresu:

- adnotacji (`@My`, `@My_SQL`)
- stringów SQL
- nazw ORM


---

## Include / Require

```php
include 'admin/lib/config_cms.php';
```

zamieniane jest na:

```php
include_once($_SERVER['DOCUMENT_ROOT'].'/admin/lib/config_cms.php');
```

---

## Tryb DRY-RUN

Zawsze uruchamiaj najpierw:

```bash
vendor/bin/rector process src --dry-run
```
