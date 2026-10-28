<?php

namespace Tests\Unit;

use Tests\TestCase;

/**
 * font-awesome-7.min.css shipped only the core and solid/regular icons, so every fa-brands icon
 * (the client footer's social links, the social login buttons) rendered as an empty circle.
 */
class FontAwesomeBrandIconsTest extends TestCase
{
    public function test_every_brand_icon_used_in_the_client_layouts_is_defined()
    {
        $css = file_get_contents(public_path(config('link.css.font-awesome')));
        $views = glob(resource_path('views/themes/default1/client/layout/*.blade.php'));

        $icons = [];
        foreach ($views as $view) {
            preg_match_all('/fa-brands (fa-[a-z0-9-]+)/', file_get_contents($view), $m);
            $icons = array_merge($icons, $m[1]);
        }
        $icons = array_diff(array_unique($icons), ['fa-fw']);

        $this->assertContains('fa-facebook', $icons);
        foreach ($icons as $icon) {
            $this->assertMatchesRegularExpression('/\.'.preg_quote($icon, '/').'[,{]/', $css, "$icon has no definition");
        }
        $this->assertStringContainsString('font-family:"Font Awesome 7 Brands"', $css);
    }
}
