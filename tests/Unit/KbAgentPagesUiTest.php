<?php

namespace Tests\Unit;

use App\Model\kb\Article;
use App\Model\kb\Category;
use App\Model\kb\Page;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The agent knowledge base pages (category, article, pages, comments, settings) were left on
 * AdminLTE 3 / Bootstrap 4 markup: sub-menu arrows sat against the label, Comments and Settings
 * had no nav icon spacing, titles were an <h1> inside the layout's <h3>, KB settings had no title,
 * and the list Delete buttons used data-toggle, so their confirmation never opened.
 */
class KbAgentPagesUiTest extends TestCase
{
    use DatabaseTransactions;

    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $s = Str::random(8);
        $this->admin = User::create(['first_name' => 'T', 'last_name' => 'Admin', 'email' => $s.'@example.test', 'user_name' => 'a_'.$s,
            'password' => Hash::make(Str::random(10)), 'active' => 1, 'role' => 'admin', 'agent_tzone' => 81, 'primary_dpt' => 1, 'assign_group' => 1]);
    }

    public static function pages()
    {
        return [
            'add category'  => ['category/create', 'lang.category'],
            'all category'  => ['category', 'lang.category'],
            'add article'   => ['article/create', 'lang.article'],
            'all articles'  => ['article', 'lang.article'],
            'add page'      => ['page/create', 'lang.pages'],
            'all pages'     => ['page', 'lang.pages'],
            'comments'      => ['comment', 'lang.comments'],
            'settings'      => ['kb/settings', 'lang.settings'],
        ];
    }

    #[DataProvider('pages')]
    public function test_page_uses_the_agent_panel_layout_conventions($url, $title)
    {
        $html = $this->page($url);

        $this->assertMatchesRegularExpression($this->title($title), $html);
        $this->assertStringNotContainsString('class="right fa-solid fa-angle-left"', $html);
        $this->assertSame(3, substr_count($html, '<i class="nav-arrow fa-solid fa-angle-left"></i></p>') - $this->departmentArrows($html));
        $this->assertStringContainsString('<i class="nav-icon fa-solid fa-comment"></i>', $html);
        $this->assertStringContainsString('<i class="nav-icon fa-solid fa-gear"></i>', $html);
    }

    public function test_edit_pages_use_the_layout_title()
    {
        $category = Category::forceCreate(['name' => 'Cat '.Str::random(6), 'slug' => 'cat-'.Str::lower(Str::random(8)), 'description' => 'd', 'status' => 1, 'parent' => 0]);
        $article = $this->article('Edit me');
        $page = Page::forceCreate(['name' => 'Page '.Str::random(6), 'slug' => 'page-'.Str::lower(Str::random(8)), 'description' => 'd', 'status' => 1, 'visibility' => 1]);

        foreach (['category/'.$category->id.'/edit' => 'lang.category', 'article/'.$article->id.'/edit' => 'lang.article', 'page/'.$page->id.'/edit' => 'lang.pages'] as $url => $title) {
            $html = $this->page($url);
            $this->assertMatchesRegularExpression($this->title($title), $html, $url);
        }
    }

    public function test_list_delete_buttons_open_a_bootstrap_5_modal_and_escape_the_name()
    {
        $article = $this->article('<b>bold</b> name');
        $category = Category::forceCreate(['name' => '<b>bold</b> cat', 'slug' => 'cat-'.Str::lower(Str::random(8)), 'description' => 'd', 'status' => 1, 'parent' => 0]);
        $page = Page::forceCreate(['name' => '<b>bold</b> page', 'slug' => 'page-'.Str::lower(Str::random(8)), 'description' => 'd', 'status' => 1, 'visibility' => 1]);

        foreach (['get-articles' => 'deletearticle'.$article->id, 'get-categorys' => 'deletecategory'.$category->id, 'get-pages' => 'deletepage'.$page->id] as $url => $modal) {
            $actions = collect($this->actingAs($this->admin)->getJson($url.'?length=-1')->assertOk()->json('data'))->pluck('Actions')->implode('');

            $this->assertStringContainsString('data-bs-toggle="modal" data-bs-target="#'.$modal.'"', $actions, $url);
            $this->assertStringContainsString('<div class="modal fade" id="'.$modal.'"', $actions, $url);
            $this->assertStringNotContainsString('data-toggle=', $actions, $url);
            $this->assertStringContainsString('&lt;b&gt;bold&lt;/b&gt;', $actions, $url);
        }
    }

    public function test_article_edit_renders_its_delete_modal_inside_the_page()
    {
        $article = $this->article('Delete me');

        $html = $this->page('article/'.$article->id.'/edit');

        $this->assertLessThan(strpos($html, '</body>'), strpos($html, 'id="deletearticle'.$article->id.'"'));
        $this->assertGreaterThan(strpos($html, '<body'), strpos($html, 'id="deletearticle'.$article->id.'"'));
    }

    private function article($name)
    {
        return Article::forceCreate(['name' => $name, 'slug' => 'art-'.Str::lower(Str::random(10)), 'description' => '<p>d</p>',
            'status' => 1, 'type' => 1, 'publish_time' => now()->subDay()]);
    }

    private function page($url)
    {
        $_SERVER['REQUEST_URI'] = '/'.$url;

        return $this->actingAs($this->admin)->get($url)->assertOk()->getContent();
    }

    // the layout wraps PageHeader in <h3 class="m-0 text-dark">; pages give their title as an <h3> like the rest of the panel
    private function title($key)
    {
        return '#<h3 class="m-0 text-dark">\s*<h3>'.preg_quote(trans($key), '#').'</h3>\s*</h3>#';
    }

    // department menus in the shared ticket sidebar use the same arrow
    private function departmentArrows($html)
    {
        return substr_count($html, '<i class="nav-icon fa-solid fa-folder-open"></i>');
    }
}
