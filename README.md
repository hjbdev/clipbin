# Clipbin

An alternative to Streamable you can self host

## Deplyoment
Deployment is like any other Laravel 11 app, I'd encourage you to [read the documentation](https://laravel.com/docs/11.x/deployment) on that first.

- Install PHP8.2, Composer, FFMpeg and Bun.
- Install dependencies with `composer install`
- Set DB variables in .env (easiest solution is `DB_CONNECTION=sqlite`)
- Set `FFMPEG_BINARY` and `FFPROBE_BINARY` in .env if they're different to the defaults (see `config/services.php`)
- Set `ALLOWED_EMAILS` in the env. A list separated with semicolons `;`.
- Make a queue worker with a larger than default timeout value to allow video jobs to complete.