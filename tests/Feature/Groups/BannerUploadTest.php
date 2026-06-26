<?php

use App\Models\InternshipGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

test('group leader can upload a banner', function () {
    $leader = User::factory()->create(['role' => 'student']);
    $group = InternshipGroup::factory()->create(['leader_id' => $leader->id]);

    $file = UploadedFile::fake()->image('banner.png', 1600, 533);

    $response = $this->actingAs($leader)
        ->post(route('groups.banner.update', $group), [
            'image' => $file,
        ]);

    $response->assertRedirect();

    $group->refresh();

    expect($group->banner_path)->not->toBeNull();
    Storage::disk('public')->assertExists($group->banner_path);
});

test('non-leader member cannot upload a banner', function () {
    $leader = User::factory()->create(['role' => 'student']);
    $member = User::factory()->create(['role' => 'student']);
    $group = InternshipGroup::factory()->create(['leader_id' => $leader->id]);

    $file = UploadedFile::fake()->image('banner.png', 1600, 533);

    $response = $this->actingAs($member)
        ->post(route('groups.banner.update', $group), [
            'image' => $file,
        ]);

    $response->assertForbidden();
    $group->refresh();
    expect($group->banner_path)->toBeNull();
});

test('unauthenticated user cannot upload a banner', function () {
    $leader = User::factory()->create(['role' => 'student']);
    $group = InternshipGroup::factory()->create(['leader_id' => $leader->id]);

    $file = UploadedFile::fake()->image('banner.png');

    $this->post(route('groups.banner.update', $group), ['image' => $file])
        ->assertRedirect(route('login'));
});

test('banner upload validates file type', function () {
    $leader = User::factory()->create(['role' => 'student']);
    $group = InternshipGroup::factory()->create(['leader_id' => $leader->id]);

    $file = UploadedFile::fake()->create('banner.pdf', 100, 'application/pdf');

    $this->actingAs($leader)
        ->post(route('groups.banner.update', $group), ['image' => $file])
        ->assertSessionHasErrors('image');
});

test('banner upload replaces old banner file', function () {
    $leader = User::factory()->create(['role' => 'student']);
    $group = InternshipGroup::factory()->create([
        'leader_id' => $leader->id,
        'banner_path' => 'banners/old-banner.webp',
    ]);

    Storage::disk('public')->put('banners/old-banner.webp', 'old content');

    $newFile = UploadedFile::fake()->image('new-banner.png', 1600, 533);

    $this->actingAs($leader)
        ->post(route('groups.banner.update', $group), ['image' => $newFile])
        ->assertRedirect();

    Storage::disk('public')->assertMissing('banners/old-banner.webp');

    $group->refresh();
    expect($group->banner_path)->not->toBe('banners/old-banner.webp');
});

test('group leader can upload both banner and og_image', function () {
    $leader = User::factory()->create(['role' => 'student']);
    $group = InternshipGroup::factory()->create(['leader_id' => $leader->id]);

    $bannerFile = UploadedFile::fake()->image('banner.png', 1600, 533);
    $ogFile = UploadedFile::fake()->image('og_image.png', 1200, 630);

    $response = $this->actingAs($leader)
        ->post(route('groups.banner.update', $group), [
            'image' => $bannerFile,
            'og_image' => $ogFile,
        ]);

    $response->assertRedirect();

    $group->refresh();

    expect($group->banner_path)->not->toBeNull();
    expect($group->og_image_path)->not->toBeNull();

    Storage::disk('public')->assertExists($group->banner_path);
    Storage::disk('public')->assertExists($group->og_image_path);
});

test('banner and og_image upload replaces both old files', function () {
    $leader = User::factory()->create(['role' => 'student']);
    $group = InternshipGroup::factory()->create([
        'leader_id' => $leader->id,
        'banner_path' => 'banners/old-banner.webp',
        'og_image_path' => 'og-banners/old-og.webp',
    ]);

    Storage::disk('public')->put('banners/old-banner.webp', 'old banner content');
    Storage::disk('public')->put('og-banners/old-og.webp', 'old og content');

    $newBanner = UploadedFile::fake()->image('new-banner.png', 1600, 533);
    $newOg = UploadedFile::fake()->image('new-og.png', 1200, 630);

    $this->actingAs($leader)
        ->post(route('groups.banner.update', $group), [
            'image' => $newBanner,
            'og_image' => $newOg,
        ])
        ->assertRedirect();

    Storage::disk('public')->assertMissing('banners/old-banner.webp');
    Storage::disk('public')->assertMissing('og-banners/old-og.webp');

    $group->refresh();
    expect($group->banner_path)->not->toBe('banners/old-banner.webp');
    expect($group->og_image_path)->not->toBe('og-banners/old-og.webp');
});

test('guest can visit public invite page and see OG tags', function () {
    $leader = User::factory()->create(['role' => 'student', 'name' => 'John Doe']);
    $group = InternshipGroup::factory()->create([
        'leader_id' => $leader->id,
        'code' => 'TESTCODE123',
        'og_image_path' => 'og-banners/uploaded-og.webp',
    ]);

    Storage::disk('public')->put('og-banners/uploaded-og.webp', 'fake content');

    $response = $this->get(route('groups.invite', 'TESTCODE123'));

    $response->assertOk();
    $response->assertSee('Kelompok Magang John Doe');
    $response->assertSee('og:image');
    $response->assertSee(Storage::disk('public')->url('og-banners/uploaded-og.webp'));
});

test('non-existent group code redirects to dashboard', function () {
    $response = $this->get(route('groups.invite', 'NONEXISTENT'));
    $response->assertRedirect(route('home'));
});
