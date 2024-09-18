<?php

namespace Package\DocTalk;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Package\DocTalk\Livewire\Chat\Chatlist;
use Package\DocTalk\Livewire\Chat\Message;
use Package\DocTalk\Livewire\Chat\Sidebar;
use Package\DocTalk\Livewire\Offline;
use Package\DocTalk\Livewire\Pages\Admin\AddDocs;
use Package\DocTalk\Livewire\Pages\Admin\Settings;
use Package\DocTalk\Livewire\Pages\Chat;

class DocTalkServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (!config('doctalk.enabled')) {
            abort(404);
        }

        // routes
        if (!$this->app->routesAreCached()) {
            require __DIR__ . '/Http/routes.php';
        }

        // setup config values for needed packages
        $this->setPackageConfigs();

        // views
        $this->loadViewsFrom(__DIR__ . '/Views', 'doctalk');

        // publish our files over to main laravel app
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/Assets' => public_path('vendor/doctalk/assets'),
                __DIR__ . '/Config/doctalk.php' => config_path('doctalk.php'),
                __DIR__ . '/Config/markdown.php' => config_path('markdown.php'),
            ], 'doctalk');
        }

        // livewire components
        if (!$this->app->runningInConsole()) {
            $this->registerLivewireComponents();
        }

        // custom validators
        $this->registerCustomValidators();
    }

    public function register()
    {
        //
    }

    private function registerLivewireComponents(): void
    {
        Livewire::component('doctalk.adddocs', AddDocs::class);
        Livewire::component('doctalk.settings', Settings::class);
        Livewire::component('doctalk.chat', Chat::class);
        Livewire::component('doctalk.offline', Offline::class);
        Livewire::component('doctalk.chatlist', Chatlist::class);
        Livewire::component('doctalk.sidebar', Sidebar::class);
    }

    private function registerCustomValidators(): void
    {
        Validator::extend('max_combined_size', function ($attribute, $value, $parameters) {
            $maxSize = (int)$parameters[0] * 1024; // Convert to bytes

            $totalSize = array_reduce($value, function ($carry, $file) {
                return $carry + $file->getSize();
            }, 0);

            return $totalSize <= $maxSize;
        });
    }

    private function setPackageConfigs(): void
    {
        // set php config values for file uploads
        ini_set('upload_max_filesize', config('doctalk.max_files_upload_size', 25600) . 'K');
        ini_set('post_max_size', config('doctalk.max_files_upload_size', 25600) . 'K');

        config(['livewire.temporary_file_upload.rules' => ['max:' . config('doctalk.max_files_upload_size', 25600)]]);
    }
}
