<?php

namespace Tests\Unit;

use App\Model\kb\Article;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The public article list and search pages rendered Laravel's default Tailwind pagination, which
 * the Bootstrap 5 theme has no styles for (a huge arrow and duplicated controls), and search listed
 * private and draft articles to guests.
 */
class KbArticlePagesTest extends TestCase
{
    use DatabaseTransactions;

    private $tag;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tag = 'kbtest'.Str::lower(Str::random(8));
    }

    public function test_article_list_pagination_uses_bootstrap_markup()
    {
        $this->articles(12);

        $html = $this->page('article-list');

        $this->assertStringContainsString('class="page-item', $html);
        $this->assertStringNotContainsString('relative inline-flex', $html);
    }

    public function test_search_pagination_keeps_the_search_term()
    {
        $this->articles(12);

        $html = $this->page('search?s='.$this->tag);

        $this->assertStringContainsString('class="page-item', $html);
        $this->assertStringContainsString('search?s='.$this->tag.'&amp;page=2', $html);
    }

    public function test_search_hides_private_and_draft_articles_from_guests()
    {
        $this->articles(1);
        $this->articles(1, ['status' => 0, 'name' => $this->tag.' private']);
        $this->articles(1, ['type' => 0, 'name' => $this->tag.' draft']);

        $html = $this->page('search?s='.$this->tag);

        $this->assertStringContainsString($this->tag.' public', $html);
        $this->assertStringNotContainsString($this->tag.' private', $html);
        $this->assertStringNotContainsString($this->tag.' draft', $html);
    }

    public function test_search_without_results_shows_translated_message()
    {
        $html = $this->page('search?s='.$this->tag);

        $this->assertStringContainsString(trans('lang.no_results_found'), $html);
        $this->assertStringContainsString('id="section-categories"', $html);
    }

    private function page($url)
    {
        $_SERVER['REQUEST_URI'] = '/'.$url;

        return $this->get($url)->assertOk()->getContent();
    }

    private function articles($count, $attributes = [])
    {
        for ($i = 0; $i < $count; $i++) {
            Article::forceCreate(array_merge([
                'name'         => $this->tag.' public',
                'slug'         => $this->tag.'-'.Str::lower(Str::random(10)),
                'description'  => '<p>Body of '.$this->tag.'</p>',
                'status'       => 1,
                'type'         => 1,
                'publish_time' => now()->subDay(),
            ], $attributes));
        }
    }
}
