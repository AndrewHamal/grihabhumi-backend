<?php

namespace Botble\SeoHelper\Providers;

use Assets;
use BaseHelper;
use Botble\Base\Models\BaseModel;
use Botble\Page\Models\Page;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;
use MetaBox;
use SeoHelper;

class HookServiceProvider extends ServiceProvider
{
    public function boot()
    {
        add_action(BASE_ACTION_META_BOXES, [$this, 'addMetaBox'], 12, 2);
        add_action(BASE_ACTION_PUBLIC_RENDER_SINGLE, [$this, 'setSeoMeta'], 56, 2);
    }

    /**
     * @param string $screen
     * @param BaseModel $data
     */
    public function addMetaBox($priority, $data)
    {

    }

    /**
     * @return Factory|View
     */
    public function seoMetaBox()
    {
        $meta = [
            'seo_title'       => null,
            'seo_description' => null,
        ];

        $args = func_get_args();
        if (!empty($args[0]) && $args[0]->id) {
            $metadata = MetaBox::getMetaData($args[0], 'seo_meta', true);
        }

        if (!empty($metadata) && is_array($metadata)) {
            $meta = array_merge($meta, $metadata);
        }

        $object = $args[0];

        return view('packages/seo-helper::meta-box', compact('meta', 'object'));
    }

    /**
     * @param string $screen
     * @param BaseModel $object
     */
    public function setSeoMeta($screen, $object)
    {
        if (get_class($object) == Page::class && BaseHelper::isHomepage($object->id)) {
            return false;
        }

        $object->loadMissing('metadata');
        $meta = $object->getMetaData('seo_meta', true);

        if (!empty($meta)) {
            if (!empty($meta['seo_title'])) {
                SeoHelper::setTitle($meta['seo_title']);
            }

            if (!empty($meta['seo_description'])) {
                SeoHelper::setDescription($meta['seo_description']);
            }
        }
    }
}
