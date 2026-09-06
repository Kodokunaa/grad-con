<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Job;
use App\Models\PostComment;
use App\Models\PostReaction;
use App\Models\User;
use App\Services\SocialFeedService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class SocialFeedTest extends TestCase
{
    use DatabaseTransactions;

    private function user(string $role): User
    {
        $user = new User;
        $user->forceFill(['fullname' => ucfirst($role).' Feed Test', 'username' => $role.'_'.bin2hex(random_bytes(4)), 'email' => bin2hex(random_bytes(4)).'@example.test', 'password' => 'test-password-123', 'role' => $role, 'is_active' => true, 'status' => 'approved', 'course' => 'BSIS'])->save();

        return $user;
    }

    public function test_feed_reactions_comments_and_permissions_use_resource_routes(): void
    {
        $officer = $this->user('alumni_officer');
        $alumni = $this->user('alumni');
        $other = $this->user('alumni');
        $event = new Event;
        $event->forceFill(['title' => 'Shared feed event', 'content' => 'Event details', 'posted_by' => $officer->id, 'is_archived' => false, 'created_at' => now()])->save();

        $this->actingAs($alumni)->get('/alumni/feed')->assertOk()->assertSee('Shared feed event');
        $this->postJson('/feed/event/'.$event->id.'/reaction', ['reaction_type' => 'love'])->assertOk()->assertJsonPath('counts.total', 1);
        $this->assertDatabaseHas('post_reactions', ['post_type' => 'event', 'post_id' => $event->id, 'user_id' => $alumni->id, 'reaction_type' => 'love']);
        $this->post('/feed/event/'.$event->id.'/comments', ['comment' => 'Looking forward to this.'])->assertRedirect();
        $comment = PostComment::where('post_id', $event->id)->where('user_id', $alumni->id)->firstOrFail();
        $this->assertDatabaseHas('post_notifications', ['recipient_user_id' => $officer->id, 'post_id' => $event->id]);
        $this->actingAs($other)->delete('/feed/comments/'.$comment->id)->assertForbidden();
        $this->actingAs($officer)->delete('/feed/comments/'.$comment->id)->assertRedirect();
        $this->assertDatabaseMissing('post_comments', ['id' => $comment->id]);
    }

    public function test_feed_job_opportunities_open_the_job_details_page(): void
    {
        $employer = $this->user('employer');
        $alumni = $this->user('alumni');
        $job = new Job;
        $job->forceFill(['title' => 'Feed opportunity', 'company' => 'Test Company', 'employer_company' => 'Test Company', 'description' => 'Test', 'posted_by' => $employer->id, 'employer_id' => $employer->id, 'is_open' => true])->save();
        Cache::flush();

        $this->actingAs($alumni)->get(route('alumni.feed'))
            ->assertOk()->assertSee(route('alumni.job_details', ['id' => $job->id]), false);
    }

    public function test_reaction_endpoint_rejects_unknown_reactions_and_hidden_events(): void
    {
        $officer = $this->user('alumni_officer');
        $alumni = $this->user('alumni');
        $event = new Event;
        $event->forceFill(['title' => 'Future event', 'content' => 'Not visible', 'posted_by' => $officer->id, 'is_archived' => false, 'post_start_date' => now()->addDay(), 'created_at' => now()])->save();
        $this->actingAs($alumni)->postJson('/feed/event/'.$event->id.'/reaction', ['reaction_type' => 'invalid'])->assertUnprocessable();
        $this->postJson('/feed/event/'.$event->id.'/reaction', ['reaction_type' => 'like'])->assertNotFound();
        $this->assertSame(0, PostReaction::where('post_id', $event->id)->count());
    }

    public function test_management_feed_shows_scheduled_and_expired_events_without_exposing_them_to_alumni(): void
    {
        $admin = $this->user('admin');
        $officer = $this->user('alumni_officer');
        $alumni = $this->user('alumni');
        Event::forceCreate(['title' => 'Scheduled management event', 'content' => 'Future', 'posted_by' => $admin->id, 'is_archived' => false, 'post_start_date' => now()->addDay(), 'created_at' => now()]);
        Event::forceCreate(['title' => 'Expired management event', 'content' => 'Past', 'posted_by' => $admin->id, 'is_archived' => false, 'post_end_date' => now()->subDay(), 'created_at' => now()]);
        SocialFeedService::forgetEventCache();

        $this->actingAs($admin)->get(route('admin.events_list'))->assertOk()->assertSee('Scheduled management event')->assertSee('Expired management event');
        $this->actingAs($officer)->get(route('alumni_officer.events_list'))->assertOk()->assertSee('Scheduled management event')->assertSee('Expired management event');
        $this->actingAs($alumni)->get(route('alumni.feed'))->assertOk()->assertDontSee('Scheduled management event')->assertDontSee('Expired management event');
    }

    public function test_feed_is_bounded_and_reuses_its_short_lived_cache(): void
    {
        SocialFeedService::forgetEventCache();
        $officer = $this->user('alumni_officer');
        $alumni = $this->user('alumni');
        foreach (range(1, 35) as $number) {
            Event::forceCreate(['title' => 'Cached event '.$number, 'content' => 'Performance test', 'posted_by' => $officer->id, 'is_archived' => false, 'created_at' => now()->subSeconds($number)]);
        }
        $feed = app(SocialFeedService::class);

        $this->assertCount(config('performance.feed_limit'), $feed->postsFor($alumni));
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->assertCount(config('performance.feed_limit'), $feed->postsFor($alumni));
        $this->assertSame([], DB::getQueryLog());
        DB::disableQueryLog();
        SocialFeedService::forgetEventCache();
    }
}
