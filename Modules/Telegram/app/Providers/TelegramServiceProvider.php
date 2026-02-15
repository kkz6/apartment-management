<?php

namespace Modules\Telegram\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Modules\Telegram\Console\SetWebhookCommand;
use Modules\Telegram\Handlers\BalanceCommand;
use Modules\Telegram\Handlers\BalancesCommand;
use Modules\Telegram\Handlers\ChargesCommand;
use Modules\Telegram\Handlers\ExpensesCommand;
use Modules\Telegram\Handlers\GenerateCommand;
use Modules\Telegram\Handlers\HelpCommand;
use Modules\Telegram\Handlers\LinkCommand;
use Modules\Telegram\Handlers\PayCommand;
use Modules\Telegram\Handlers\PaymentsCommand;
use Modules\Telegram\Handlers\PendingCommand;
use Modules\Telegram\Handlers\StartCommand;
use Modules\Telegram\Handlers\SummaryCommand;
use Modules\Telegram\Middleware\AuthenticateAdmin;
use Modules\Telegram\Services\TelegramNotifier;
use Nwidart\Modules\Traits\PathNamespace;
use SergiX44\Nutgram\Configuration;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\RunningMode\Webhook;

class TelegramServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'Telegram';

    protected string $nameLower = 'telegram';

    public function boot(): void
    {
        $this->registerCommands();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));
        $this->registerBotHandlers();
    }

    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);

        $this->app->singleton(Nutgram::class, function ($app) {
            $token = config('services.telegram.bot_token');

            if (! $token) {
                $token = '0:placeholder';
            }

            $bot = new Nutgram($token, new Configuration(
                clientTimeout: 10,
            ));

            $bot->setRunningMode(Webhook::class);

            return $bot;
        });

        $this->app->singleton(TelegramNotifier::class, function ($app) {
            return new TelegramNotifier($app->make(Nutgram::class));
        });
    }

    protected function registerBotHandlers(): void
    {
        $bot = $this->app->make(Nutgram::class);

        if (! config('services.telegram.bot_token')) {
            return;
        }

        // Public commands (no auth required)
        $bot->onCommand('start', StartCommand::class);
        $bot->onText('/link(.*)', LinkCommand::class);

        // Authenticated commands
        $bot->group(function () use ($bot) {
            $bot->onCommand('help', HelpCommand::class);
            $bot->onCommand('summary', SummaryCommand::class);
            $bot->onText('/balance\b(.*)', BalanceCommand::class);
            $bot->onCommand('balances', BalancesCommand::class);
            $bot->onText('/charges\b(.*)', ChargesCommand::class);
            $bot->onText('/payments\b(.*)', PaymentsCommand::class);
            $bot->onCommand('expenses', ExpensesCommand::class);
            $bot->onCommand('pending', PendingCommand::class);
            $bot->onText('/pay\b(.*)', PayCommand::class);
            $bot->onText('/generate\b(.*)', GenerateCommand::class);
        })->middleware(AuthenticateAdmin::class);

        $bot->onFallback(function (Nutgram $bot) {
            $bot->sendMessage(text: "Unknown command. Use /help to see available commands.");
        });
    }

    protected function registerCommands(): void
    {
        $this->commands([
            SetWebhookCommand::class,
        ]);
    }

    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/' . $this->nameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->nameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);
            $this->loadJsonTranslationsFrom(module_path($this->name, 'lang'));
        }
    }

    protected function registerConfig(): void
    {
        $configPath = module_path($this->name, config('modules.paths.generator.config.path'));

        if (is_dir($configPath)) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($configPath));

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $config = str_replace($configPath . DIRECTORY_SEPARATOR, '', $file->getPathname());
                    $config_key = str_replace([DIRECTORY_SEPARATOR, '.php'], ['.', ''], $config);
                    $segments = explode('.', $this->nameLower . '.' . $config_key);

                    $normalized = [];
                    foreach ($segments as $segment) {
                        if (end($normalized) !== $segment) {
                            $normalized[] = $segment;
                        }
                    }

                    $key = ($config === 'config.php') ? $this->nameLower : implode('.', $normalized);

                    $this->publishes([$file->getPathname() => config_path($config)], 'config');
                    $this->merge_config_from($file->getPathname(), $key);
                }
            }
        }
    }

    protected function merge_config_from(string $path, string $key): void
    {
        $existing = config($key, []);
        $module_config = require $path;

        config([$key => array_replace_recursive($existing, $module_config)]);
    }

    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/' . $this->nameLower);
        $sourcePath = module_path($this->name, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->nameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->nameLower);

        Blade::componentNamespace(config('modules.namespace') . '\\' . $this->name . '\\View\\Components', $this->nameLower);
    }

    public function provides(): array
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->nameLower)) {
                $paths[] = $path . '/modules/' . $this->nameLower;
            }
        }

        return $paths;
    }
}
