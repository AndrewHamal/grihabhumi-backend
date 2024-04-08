<?php

namespace Botble\Setting\Providers;

use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Setting\Facades\SettingFacade;
use Botble\Setting\Models\Setting as SettingModel;
use Botble\Setting\Repositories\Caches\SettingCacheDecorator;
use Botble\Setting\Repositories\Eloquent\SettingRepository;
use Botble\Setting\Repositories\Interfaces\SettingInterface;
use Botble\Setting\Supports\SettingsManager;
use Botble\Setting\Supports\SettingStore;
use EmailHandler;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Foundation\Application;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\ServiceProvider;

class SettingServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    /**
     * This provider is deferred and should be lazy loaded.
     *
     * @var boolean
     */
    protected $defer = true;

    public function register()
    {
        $this->setNamespace('core/setting')
            ->loadAndPublishConfigurations(['general']);

        $this->app->singleton(SettingsManager::class, function (Application $app) {
            return new SettingsManager($app);
        });

        $this->app->singleton(SettingStore::class, function (Application $app) {
            return $app->make(SettingsManager::class)->driver();
        });

        AliasLoader::getInstance()->alias('Setting', SettingFacade::class);

        $this->app->bind(SettingInterface::class, function () {
            return new SettingCacheDecorator(
                new SettingRepository(new SettingModel)
            );
        });

        $this->loadHelpers();
    }

    public function boot()
    {
        $this
            ->loadRoutes(['web'])
            ->loadAndPublishViews()
            ->loadAndPublishTranslations()
            ->loadAndPublishConfigurations(['permissions', 'email'])
            ->loadMigrations()
            ->publishAssets();

        Event::listen(RouteMatched::class, function () {
            dashboard_menu();

            EmailHandler::addTemplateSettings('base', config('core.setting.email', []), 'core');
        });
    }

    /**
     * Which IoC bindings the provider provides.
     *
     * @return array
     */
    public function provides()
    {
        return [
            SettingsManager::class,
            SettingStore::class,
        ];
    }
}
