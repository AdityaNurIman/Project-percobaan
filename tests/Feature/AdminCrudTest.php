<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCrudTest extends TestCase
{
    public function test_category_crud(): void
    {
        fwrite(STDERR, "\nADMINCRUD first test — Tables: ".json_encode(\Illuminate\Support\Facades\Schema::getConnection()->getSchemaBuilder()->getTableListing())."\n");
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/admin/categories', [
                'name' => 'Test Category',
                'description' => 'Some description',
            ])
            ->assertRedirect('/admin/categories');

        $this->assertDatabaseHas('categories', ['name' => 'Test Category']);

        $category = Category::where('name', 'Test Category')->first();

        $this->actingAs($user)
            ->put('/admin/categories/'.$category->id, [
                'name' => 'Updated Category',
                'description' => 'Updated description',
            ])
            ->assertRedirect('/admin/categories');

        $this->assertDatabaseHas('categories', ['name' => 'Updated Category']);

        $this->actingAs($user)
            ->delete('/admin/categories/'.$category->id)
            ->assertRedirect('/admin/categories');

        $this->assertDatabaseMissing('categories', ['name' => 'Updated Category']);
    }

    public function test_post_crud(): void
    {
        $user = User::factory()->create();
        $category = Category::create([
            'name' => 'Test Dev Category',
            'slug' => 'test-dev-category',
        ]);

        $this->actingAs($user)
            ->post('/admin/posts', [
                'title' => 'Test Post',
                'text' => 'Post body text here.',
                'category_id' => $category->id,
            ])
            ->assertRedirect('/admin/posts');

        $this->assertDatabaseHas('posts', ['title' => 'Test Post']);

        $post = Post::where('title', 'Test Post')->first();

        $this->actingAs($user)
            ->put('/admin/posts/'.$post->id, [
                'title' => 'Updated Post',
                'text' => 'Updated body.',
                'category_id' => $category->id,
            ])
            ->assertRedirect('/admin/posts');

        $this->assertDatabaseHas('posts', ['title' => 'Updated Post']);

        $this->actingAs($user)
            ->delete('/admin/posts/'.$post->id)
            ->assertRedirect('/admin/posts');

        $this->assertDatabaseMissing('posts', ['title' => 'Updated Post']);
    }

    public function test_validation_errors(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/admin/categories', ['name' => ''])
            ->assertSessionHasErrors('name');

        $this->actingAs($user)
            ->post('/admin/posts', ['title' => '', 'category_id' => 9999])
            ->assertSessionHasErrors(['title', 'text', 'category_id']);
    }

    public function test_admin_requires_auth(): void
    {
        $this->get('/admin/categories')->assertRedirect('/login');
        $this->get('/admin/posts')->assertRedirect('/login');
    }
}
