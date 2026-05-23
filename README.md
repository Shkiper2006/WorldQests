# WorldQests

WordPress-плагин **World Quest** с модульной архитектурой (Admin / Frontend / REST / Domain / Repository / Service).

## Установка

1. Склонируйте репозиторий в директорию плагинов WordPress:
   `wp-content/plugins/world-quest`.
2. Убедитесь, что файл входа плагина находится в корне: `world-quest.php`.

## Активация

1. Откройте **WordPress Admin → Plugins**.
2. Найдите **World Quest**.
3. Нажмите **Activate**.

## Зависимости

- PHP 8.1+
- WordPress 6.4+

## Базовое использование shortcode

После активации плагина доступен shortcode:

```text
[world_quest title="My Quest"]
```

- `title` (optional): заголовок блока на фронтенде.

Также доступен health endpoint:

```text
GET /wp-json/world-quest/v1/health
```

## Локализация

Единый text domain плагина: `worldquest`.

### Генерация POT/переводов

1. POT-файл уже добавлен в репозиторий: `languages/worldquest.pot`.
2. При необходимости пересоберите его (опционально, если используете WP-CLI):
   ```bash
   wp i18n make-pot . languages/worldquest.pot --domain=worldquest --exclude=node_modules,vendor,tests
   ```
3. Создайте `.po/.mo` на основе `languages/worldquest.pot` (например, через Poedit).
