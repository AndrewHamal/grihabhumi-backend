<?php

use Botble\Theme\Events\ThemeRoutingAfterEvent;
use Botble\Theme\Events\ThemeRoutingBeforeEvent;

Route::group(['namespace' => 'Botble\Theme\Http\Controllers', 'middleware' => ['web', 'core']], function () {
    Route::group(apply_filters(BASE_FILTER_GROUP_PUBLIC_ROUTE, []), function () {
        event(new ThemeRoutingBeforeEvent);

        Route::get('/', [
            'as'   => 'public.index',
            'uses' => 'PublicController@getIndex',
        ]);

        Route::get('sitemap.xml', [
            'as'   => 'public.sitemap',
            'uses' => 'PublicController@getSiteMap',
        ]);

        event(new ThemeRoutingAfterEvent);
    });
});
