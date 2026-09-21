<?php

namespace Tests\Unit;

use App\Http\Requests\kb\CategoryRequest;
use App\Model\kb\Category;
use App\Model\kb\Relationship;
use App\User;
use Faker\Factory as FakerFactory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    // Unique per test run. The name is created by testValidationPasses and
    // then re-used by the uniqueness tests, so it must be the same value
    // throughout a run — but NOT the same value across runs, or the row
    // left behind by the previous run makes the `unique` rule fail.
    private static $categoryName;

    private static function categoryName(): string
    {
        return self::$categoryName ??= 'New Category '.Str::random(6);
    }

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
            'first_name'   => $faker->firstName(),
            'last_name'    => $faker->lastName(),
            'email'        => $email,
            'user_name'    => $faker->unique()->userName(),
            'password'     => $password,
            'active'       => 1,
            'role'         => 'agent',
            'agent_tzone'  => 81,
        ]);
        $user->save();

        // Check if data is inserted
        $this->assertDatabaseHas('users', ['email'=>$email]);

        // Authenticate as the created user
        $this->actingAs($user);

        $this->assertAuthenticated();
    }

    /** @test */
    public function it_can_display_the_category_index_page()
    {
        $response = $this->get(route('category.index'));

        $response->assertStatus(200);
    }

    public function testValidationPasses()
    {
        $data = [
            'name'        => self::categoryName(),
            'description' => 'Category Description',
        ];

        $validator = Validator::make($data, (new CategoryRequest())->rules());

        $this->assertTrue($validator->passes());

        $response = $this->post(route('category.store'), $data);
        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('kb_category', $data);
    }

    public function testValidationFailsWhenNameMissing()
    {
        $data = [
            'description' => 'Category Description',
        ];

        $validator = Validator::make($data, (new CategoryRequest())->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('name'));
    }

    public function testValidationFailsWhenNameExceedsMaxLength()
    {
        $data = [
            'name'        => str_repeat('A', 251),
            'description' => 'Category Description',
        ];

        $validator = Validator::make($data, (new CategoryRequest())->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('name'));
    }

    public function testValidationFailsWhenNameNotUnique()
    {
        $data = [
            'name'        => self::categoryName(),
            'description' => 'Category Description',
        ];

        $validator = Validator::make($data, (new CategoryRequest())->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('name'));
    }

    public function testValidationFailsWhenDescriptionMissing()
    {
        $data = [
            'name' => self::categoryName(),
        ];

        $validator = Validator::make($data, (new CategoryRequest())->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('description'));
    }

    public function testEditCategory()
    {
        $category = Category::latest()->first();
        $categories = Category::pluck('name', 'id')->toArray();
        $response = $this->get(
            "/category/{$category->id}/edit",
            ['category'      => $category,
                'categories' => $categories,
            ]
        );
        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_update_an_existing_category()
    {
        // Retrieve an existing category from the database
        $category = Category::latest()->first();

        $data = [
            'name'        => 'Updated Category Name',
            'description' => 'Updated Description',
        ];

        $validator = Validator::make($data, (new CategoryRequest())->rules());

        $this->assertTrue($validator->passes());

        $response = $this->put(route('category.update', $category->id), $data);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('kb_category', $data);
    }

    /** @test */
    public function it_cannot_update_an_existing_category()
    {
        // Retrieve an existing category from the database
        $category = Category::latest()->first();

        $data = [
            'name'        => 'Updated Category Name',
            'description' => 'Updated Description',
        ];

        $validator = Validator::make($data, (new CategoryRequest())->rules());

        $this->assertFalse($validator->passes());
        $response = $this->put(route('category.update', $category->id), $data);
        $response->assertStatus(302);
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('name'));
    }

    /** @test */
    public function it_can_delete_a_category()
    {
        // Create a category
        $category = Category::latest()->first();

        // Create a related relationship
        $relation = Relationship::find($category->id);

        // Call the destroy method with the category ID
        $response = $this->get("/category/delete/{$category->id}");

        // Assert that the category is deleted from the database
        $this->assertDatabaseMissing('kb_category', ['id' => $category->id]);

        // Assert that the response is a redirect
        $response->assertRedirect();

        // Assert that the response has a success message
        $response->assertSessionHas('success', Lang::get('lang.category_deleted_successfully'));
    }
}
