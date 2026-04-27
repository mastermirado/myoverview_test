# Tests E2E avec Panther

## Principes clés du test via Selenium

### Architecture

```
Container PHP (phpunit)
    │
    │ WebDriver API (HTTP)
    ▼
Container Selenium (selenium:4444)
    │
    ├── Chrome (piloté via WebDriver)
    │       │
    │       │ HTTP
    │       ▼
    │   Container Nginx (web:80)
    │       │
    │       ▼
    │   Container PHP-FPM (app)
    │
    └── Écran virtuel Xvfb → noVNC (localhost:7900)
```

### Comment Panther se connecte à Selenium

`createPantherClient()` ne suffit pas — il faut explicitement passer le browser `SELENIUM` et l'URL du serveur :

```php
static::createPantherClient(
    ['browser' => PantherTestCase::SELENIUM],
    [],
    ['host' => 'http://selenium:4444/wd/hub']
);
```

Sans `browser => SELENIUM`, Panther utilise ChromeDriver local même si `PANTHER_SELENIUM_HOST` est défini dans `phpunit.dist.xml`.

### PANTHER_EXTERNAL_BASE_URI

Chrome tourne dans le container Selenium — il ne peut pas atteindre `127.0.0.1:9080` (serveur built-in du container PHP). Il faut pointer vers une URL accessible depuis le réseau Docker :

```xml
<server name="PANTHER_EXTERNAL_BASE_URI" value="http://web" />
```

Cela désactive le serveur built-in de Panther et utilise directement Nginx.

### Visualiser le navigateur en live

Le container Selenium expose un écran virtuel via noVNC :

- URL : http://localhost:7900
- Mot de passe : `secret`
- Le navigateur apparaît pendant l'exécution des tests

---

## Comparaison Selenium vs ChromeDriver local

| | ChromeDriver local | Selenium |
|---|---|---|
| **Où tourne Chrome** | Dans le container PHP | Dans le container Selenium |
| **Visible** | Non (headless) | Oui via noVNC (localhost:7900) |
| **URL de l'app** | `127.0.0.1:9080` (built-in server) | URL Docker réseau (`http://web`) |
| **Config dans le test** | `createPantherClient()` | `createPantherClient(['browser' => SELENIUM], [], ['host' => ...])` |
| **Débogage visuel** | Impossible | Possible via noVNC |
| **Dépendance externe** | Chromium dans l'image PHP | Container `selenium/standalone-chrome` |
| **Isolation** | Faible (même container que le code) | Forte (container dédié) |
| **CI/CD** | Simple, pas de container sup. | Nécessite le service `selenium` |
| **Cas d'usage** | Tests rapides, pipeline CI | Débogage, tests complexes |

### Quand utiliser lequel

**ChromeDriver local** — pipeline CI, tests de non-régression rapides, pas besoin de voir ce qui se passe.

**Selenium** — débogage d'un test qui échoue, vérification visuelle d'un rendu, tests d'interactions complexes (drag & drop, iframes, popups).
