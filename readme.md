## DocChat

A laravel plugin that allows to chat with PDF documents using OpenAI and Gemini.

### Requirements

- Postgesql >= 16
- pgvector extension installed ([pgvector GitHub](https://github.com/pgvector/pgvector))
- PHP >= 8.1
- Laravel >= 10
- Livewire 3

### Installation

Install these packages:

```bash
composer require pgvector/pgvector
composer require livewire/livewire
composer require smalot/pdfparser
composer require yooper/php-text-analysis
composer require spatie/laravel-markdown
composer require tempest/highlight
```

Add DocChat to Composer:

```json
"psr-4": {
    "App\\": "app/",
    ...
    "Package\\DocTalk\\": "packages/doctalk/src/"
}
```

and run `composer dump-autoload` command.

And DocChat to Providers in `config/app.php`:

```php
'providers' => [
    ...
    Package\DocTalk\DocTalkServiceProvider::class,
],
```

Publish DocChat Assets:

```bash
php artisan vendor:publish --tag="doctalk" --force
```

Run Migrations:

```bash
php artisan migrate --database=pgsql --path=packages/doctalk/src/Migrations
```

Visit `/chat-with-docs/chat` to see the DocChat in action.

Visit `/chat-with-docs/admin/docs` to add the PDF documents in database.
