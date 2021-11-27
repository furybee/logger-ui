# Logger UI

![logger-ui](https://user-images.githubusercontent.com/45472257/142772523-e79ff302-664e-456b-914d-fa40bd39da5b.png)

## Installing The Logger UI Dashboard

This package provides a beautiful dashboard through your application that allows you to send logs to a database and show them. The Logger UI dashboard package can be installed in your project using Composer:

```sh
composer require furybee/logger-ui
```

After installing Logger UI, you may publish its assets using the `logger-ui:install` Artisan command. You should also run the `migrate` command in order to create the table needed to store Logger UI's data:

```sh
php artisan logger-ui:install
```

Note : if you are using SingleStore, add `--singlestore=on` option.

```sh
php artisan logger-ui:migrate
```

## Setup Logger UI as default channel

In your `config/logging.php` file, add the following channel

```php
'logger-ui' => [
    'driver' => 'custom',
    'path' => DBHandler::class,
    'via' => DBLogger::class,
    'level' => 'debug',
],
```

Then edit your `LOG_CHANNEL` env key for `logger-ui`

```php
LOG_CHANNEL=logger-ui
```

## Dashboard Authorization

Logger UI exposes a dashboard at the `/logger-ui` URI. Within your `app/Providers/LoggerUiServiceProvider.php` file, there is a gate method that controls access to the Logger UI dashboard. By default, all visitors are restricted. You should modify this gate as needed to grant access to your Logger UI dashboard:

```php
/**
 * Register the Logger UI gate.
 *
 * This gate determines who can access Logger UI in non-local environments.
 *
 * @return void
 */
protected function gate()
{
    Gate::define('viewLoggerUI', function ($user = null) {
        return in_array(optional($user)->email, [
            'hello@sebastienfontaine.me',
        ]);
    });
}
```

## Upgrading Logger UI

When upgrading to a new version of Logger UI, you should re-publish Logger UI's assets:

```sh
php artisan logger-ui:publish
```

To keep the assets up-to-date and avoid issues in future updates, you may add the `logger-ui:publish` command to the post-update-cmd scripts in your application's `composer.json` file:

```json
{
  "scripts": {
    "post-update-cmd": ["@php artisan logger-ui:publish --ansi"]
  }
}
```

## Customization

If you have not published Logger UI's configuration file, you may do so using the `vendor:publish` Artisan command:

```sh
php artisan vendor:publish --tag=logger-ui-config
```

Once the configuration file has been published, you may edit Logger UI's middleware or database by tweaking the middleware configuration option within this file.

### Database

If needed, you may update DB Connection and the Table where logger-ui will store the data.

```php
'db' => [
    'connection' => env('DB_CONNECTION', null),
    'table' => env('DB_LOGGER_UI_TABLE', 'logger_ui_entries')
],
```

### Middleware

If needed, you can customize the middleware stack used by Logger UI routes by updating your `config/logger-ui.php` file.

```php
/*
|--------------------------------------------------------------------------
| Logger UI Route Middleware
|--------------------------------------------------------------------------
|
| These middleware will be assigned to every Logger UI route - giving you
| the chance to add your own middleware to this list or change any of
| the existing middleware. Or, you can simply stick with this list.
|
*/

'middleware' => [
    'web',
    EnsureUserIsAuthorized::class,
],
```

## Inspiration

- https://github.com/laravel/vapor-ui
- https://github.com/laravel/telescope
