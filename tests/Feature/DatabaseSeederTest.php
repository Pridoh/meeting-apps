<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_seeding_again_reuses_demo_users_and_rooms(): void
    {
        $existingUser = User::factory()->create();

        $this->seed(DatabaseSeeder::class);
        $demoUserIds = User::query()->whereKeyNot($existingUser->id)->orderBy('id')->pluck('id')->all();
        $roomIds = DB::table('rooms')->orderBy('id')->pluck('id')->all();

        $this->assertNotEmpty($demoUserIds);
        $this->assertNotEmpty($roomIds);

        $this->seed(DatabaseSeeder::class);

        $this->assertSame(
            $demoUserIds,
            User::query()->whereKeyNot($existingUser->id)->orderBy('id')->pluck('id')->all(),
        );
        $this->assertSame($roomIds, DB::table('rooms')->orderBy('id')->pluck('id')->all());
        $this->assertDatabaseHas('users', $existingUser->only('id', 'name', 'email'));
    }
}
