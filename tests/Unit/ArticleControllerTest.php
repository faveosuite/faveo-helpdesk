<?php

namespace Tests\Unit;

use App\Http\Requests\kb\ArticleRequest;
use App\Http\Requests\kb\ArticleUpdate;
use App\Http\Requests\kb\CategoryRequest;
use App\Model\kb\Article;
use App\Model\kb\Category;
use App\Model\kb\Relationship;
use App\User;
use Faker\Factory as FakerFactory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tests\TestCase;

class ArticleControllerTest extends TestCase
{
    protected $user; // Declare a user property

    // Set up the authenticated user before each test
    public function setUp(): void
    {
        parent::setUp();

        $faker = FakerFactory::create();

        //Create User -> Agent

        $str = Str::random(10);
        $password = Hash::make($str);
        $email = $faker->unique()->email();
        $user = new User([
            'first_name'  => $faker->firstName(),
            'last_name'   => $faker->lastName(),
            'email'       => $email,
            'user_name'   => $faker->unique()->userName(),
            'password'    => $password,
            'active'      => 1,
            'role'        => 'agent',
            'agent_tzone' => 81,
        ]);
        $user->save();

        // Check if data is inserted
        $this->assertDatabaseHas('users', ['email' => $email]);

        // Authenticate as the created user
        $this->actingAs($user);

        $this->assertAuthenticated();
    }

    /** @test */
    public function it_can_display_the_article_index_page()
    {
        $response = $this->get(route('article.index'));

        $response->assertStatus(200);
    }

    public function testStoreArticleWithCategories()
    {
        // Create a Category model for testing
        $data = [
            'name'        => 'Test Category '.Str::random(6),  // unique per run: the row persists after the test
            'description' => 'Test Category Description',
        ];

        $validator = Validator::make($data, (new CategoryRequest())->rules());

        $this->assertTrue($validator->passes());

        $response = $this->post(route('category.store'), $data);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('kb_category', $data);
        $category = Category::latest()->first();

        // Article data
        $articleData = [
            'name'        => 'Test Article '.Str::random(6),  // unique:kb_article — must differ per run
            'description' => 'Test Article Description',
            'category_id' => $category->id,
            'year'        => '2023',
            'month'       => '10',
            'day'         => '03',
            'hour'        => '12',
            'minute'      => '30',
        ];

        $articleRequest = new ArticleRequest($articleData);

        // Act
        try {
            $validator = Validator::make($articleData, (new ArticleRequest())->rules());

            $this->assertTrue($validator->passes());
            $response = $this->post(route('article.store'), $articleData);
            // ArticleController::store() redirects to 'article' with a success
            // flash. This previously asserted 200 only because unhandled errors
            // were served with a 200 status.
            $response->assertStatus(302);
            $response->assertSessionHas('success');
        } catch (Exception $e) {
            $response = null;
        }

        // Assert
        if ($response) {
            $response->assertStatus(302);

            $article = Article::latest()->first();

            $article_relationship = new Relationship();

            $article_relationship->category_id = $category->id;
            $article_relationship->article_id = $article->id;
            $article_relationship->save();

            // Verify that the article was created and the category relationship exists
            $this->assertDatabaseHas('kb_article', [
                'name'         => $articleData['name'],
                'slug'         => Str::slug($articleData['name'], '-'),
                'publish_time' => $articleData['year'].'-'.$articleData['month'].'-'.$articleData['day'].' '.$articleData['hour'].':'.$articleData['minute'].':00',
            ]);

            // Check if the category relationship exists
            $this->assertDatabaseHas('kb_article_relationship', [
                'category_id' => $category->id,
                'article_id'  => Article::latest()->first()->id, // Get the ID of the latest created article
            ]);
        } else {
            $this->fail('Exception thrown: '.$e->getMessage());
        }
    }

    public function testEditArticle()
    {
            // Arrange
        $article = Article::latest()->first(); // Create a sample Article for testing
        $relationship = Relationship::latest()->first(); // Create a sample Relationship for testing
        $category = Category::latest()->first(); // Create a sample Category for testing

        $assign = $relationship->where('article_id', 'id')->pluck('category_id');
        $category = $category->pluck('id', 'name');

        $response = $this->get(
            "/article/{$article->id}/edit",
            ['category'   => $category,
                'article' => $article,
                'assign'  => $assign,
            ]
        );
        $response->assertStatus(200);
    }

    public function testUpdateArticle()
    {
        $article = Article::latest()->first();
        $category = Category::latest()->first();

        $data = [
            'id'          => $article->id,
            'name'        => 'Updated Article Name',
            'description' => 'Updated Description',
            'slug'        => Str::slug('Updated Article Name', '-'),
            'category_id' => [1, 2],
            'year'        => '2023',
            'month'       => '10',
            'day'         => '03',
            'hour'        => '2',
            'minute'      => '20',
        ];

        $validator = Validator::make($data, (new ArticleUpdate())->rules());

        $this->assertTrue($validator->passes());

        $response = $this->put(route('article.update', $article->id, $data));

        $response->assertStatus(302);

        $article_relationship = Relationship::latest()->first();
        $article_relationship = $article_relationship->where('article_id', $article->id);
        $article_relationship->delete();

        $article = Article::latest()->first();
        $relation = new Relationship();
        $relation->category_id = $category->id;
        $relation->article_id = $article->id;
        $relation->save();
    }

    /** @test */
    public function it_can_delete_a_category()
    {
        // Create a sample article, relationship
        $article = Article::latest()->first();
        $relationship = Relationship::find($article->id);

        // Ensure the destroy route works as expected

        $response = $this->get("/article/delete/{$article->slug}");

        // Assert that success message is flashed
        $response->assertSessionHas('success', Lang::get('lang.article_deleted_successfully'));

        // Create a category
        $category = Category::latest()->first();

        // Create a related relationship (you may need to adjust this based on your actual relationships)
        $relation = Relationship::find($category->id);

        // Call the destroy method with the category ID
        $response = $this->get("/category/delete/{$category->id}");

        // Assert that the category is deleted from the database
        $this->assertDatabaseMissing('kb_category', ['id' => $category->id]);
    }

    public function it_cannot_delete_a_article_if_related()
    {
        // Create a category
        $article = Article::find(1);

        // Call the destroy method with the category ID (without creating related records)
        $response = $this->get("/article/delete/{$article->slug}");

        // Assert that the category is not deleted from the database
        $this->assertDatabaseHas('kb_article', ['id' => $article->id]);

        // Assert that the response is a redirect
        $response->assertRedirect();

        // Assert that the response has a failure message
        $response->assertSessionHas('fails', Lang::get('lang.article_not_deleted'));
    }
}
