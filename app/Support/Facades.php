<?php

namespace App\Support;

/**
 * Laravel Facade Type Hints
 *
 * This file provides proper type hints for Laravel facades to resolve
 * IDE/LSP "Undefined type" errors. These are not meant to be used at runtime
 * but to provide better autocomplete and type checking.
 *
 * @method static \Illuminate\Routing\Router app(string $abstract, mixed $parameters = [])
 * @method static \Illuminate\Contracts\Bus\Dispatcher bus()
 * @method static \Illuminate\Cache\Repository cache()
 * @method static \Illuminate\Config\Repository config()
 * @method static \Illuminate\Cookie\CookieJar cookie()
 * @method static \Illuminate\Encryption\Encrypter crypt()
 * @method static \Illuminate\Database\Capsule\Manager db()
 * @method static \Illuminate\Events\Dispatcher event()
 * @method static \Illuminate\Filesystem\Filesystem file()
 * @method static \Illuminate\Hashing\BcryptHasher hash()
 * @method static \Illuminate\Http\Client\Factory http()
 * @method static \Illuminate\Contracts\Auth\Guard auth()
 * @method static \Illuminate\Contracts\Auth\StatefulGuard auth()
 * @method static \Illuminate\Contracts\Auth\PasswordBroker password()
 * @method static \Illuminate\Foundation\Application app()
 * @method static \Illuminate\Contracts\Container\Container app()
 * @method static \Illuminate\Support\Collection collect(mixed $items = [])
 * @method static \Illuminate\Database\Eloquent\Model|null firstOrCreate(array $attributes, array $values = [])
 * @method static \Illuminate\Mail\Mailer mail()
 * @method static \Illuminate\Notifications\ChannelManager notification()
 * @method static \Illuminate\Pagination\Paginator paginate($items, $perPage = null, $page = null, array $options = [])
 * @method static \Illuminate\Queue\QueueManager queue()
 * @method static \Illuminate\Redis\Connections\Connection redis()
 * @method static \Illuminate\Support\Facades\Request request()
 * @method static \Illuminate\Support\Str str($value = '')
 * @method static \Illuminate\Support\Facades\Storage storage()
 * @method static \Illuminate\Support\Facades\URL url()
 * @method static \Illuminate\Support\Facades\Validator validator()
 * @method static \Illuminate\Support\Facades\View view()
 * @method static \Illuminate\Support\Str str()
 * @method static \Illuminate\Support\Arr array()
 * @method static \Illuminate\Support\Str __()
 * @method static \Illuminate\Translation\Translator trans()
 * @method static \Illuminate\Translation\Translator choice()
 * @method static \Illuminate\Log\Logger log()
 * @method static \Illuminate\Mail\Mailable mailTo(mixed $users)
 * @method static \Illuminate\Notifications\AnonymousNotifiable notify(mixed $users)
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginate($items, $perPage = null, $page = null, array $options = [])
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginate($items, $perPage = null, $page = null, array $options = [])
 * @method static \Illuminate\Pagination\CursorPaginator cursorPaginate($items, $perPage = null, $page = null, array $options = [])
 */
class Facades
{
    // This class is intentionally empty - it exists only for type hinting
}
