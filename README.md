<a href="https://996.icu"><img src="https://img.shields.io/badge/link-996.icu-red.svg"></a>

# Install

```bash
composer require maxsky/swoft-app-key-generator
```

# Description

Support generate **APP_KEY** in Swoft Framework 2.x same as Laravel.

# Usage

Run command at project root path:

```bash
php ./swoft/bin key:generate
php ./swoft/bin key:generate --show
```

Add param `--show` will display a generate key without replace **APP_KEY** in `.env` file.

