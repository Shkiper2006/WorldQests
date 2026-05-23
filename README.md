# WorldQests

WordPress-плагин **World Quest** с модульной архитектурой (Admin / Frontend / REST / Domain / Repository / Service).

## Установка

1. Склонируйте репозиторий в директорию плагинов WordPress:
   `wp-content/plugins/world-quest`.
2. Установите зависимости:
   ```bash
   composer install
   ```
3. Убедитесь, что файл входа плагина находится в корне: `world-quest.php`.

## Активация

1. Откройте **WordPress Admin → Plugins**.
2. Найдите **World Quest**.
3. Нажмите **Activate**.

## Зависимости

- PHP 8.1+
- WordPress 6.4+
- Composer (для генерации автозагрузки PSR-4)

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
