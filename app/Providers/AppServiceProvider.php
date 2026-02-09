<?php

namespace App\Providers;

use App\Models\Attachment;
use App\Observers\AttachmentObserver;
use App\View\Html\Sanitizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register the HTML Sanitizer for XSS protection
        $this->app->scoped(Sanitizer::class, function () {
            return new Sanitizer(new HtmlSanitizer(
                (new HtmlSanitizerConfig)
                    ->allowSafeElements()
                    // Explicitly allow elements used by Trix editor
                    ->allowElement('div')
                    ->allowElement('p')
                    ->allowElement('br')
                    ->allowElement('ul')
                    ->allowElement('ol')
                    ->allowElement('li')
                    ->allowElement('strong')
                    ->allowElement('em')
                    ->allowElement('del')
                    ->allowElement('a', ['href', 'target', 'rel'])
                    ->allowElement('blockquote')
                    ->allowElement('pre')
                    ->allowElement('h1')
                    ->allowElement('h2')
                    ->allowElement('h3')
                    ->allowElement('code')
                    ->allowAttribute('class', '*')
                    ->allowAttribute('style', '*')
                    ->allowAttribute('id', '*')
            ));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register observers
        Attachment::observe(AttachmentObserver::class);

        // Automatically eager load relationships (Laravel 12.0.8+)
        Model::automaticallyEagerLoadRelationships();

        // Prevent lazy loading in non-production (catch N+1 issues early)
        // Model::preventLazyLoading(! app()->isProduction());

        // Enable HTTPS in production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
            $this->app['request']->server->set('HTTPS', 'on');
        }

        // Optimize queries
        DB::enableQueryLog();
    }
}
