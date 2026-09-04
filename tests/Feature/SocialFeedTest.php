<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\PostComment;
use App\Models\PostReaction;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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

        $this->actingAs($alumni)->get('/alumni/feed.php')->assertOk()->assertSee('Shared feed event');
        $this->postJson('/feed/event/'.$event->id.'/reaction', ['reaction_type' => 'love'])->assertOk()->assertJsonPath('counts.total', 1);
        $this->assertDatabaseHas('post_reactions', ['post_type' => 'event', 'post_id' => $event->id, 'user_id' => $alumni->id, 'reaction_type' => 'love']);
        $this->post('/feed/event/'.$event->id.'/comments', ['comment' => 'Looking forward to this.'])->assertRedirect();
        $comment = PostComment::where('post_id', $event->id)->where('user_id', $alumni->id)->firstOrFail();
        $this->assertDatabaseHas('post_notifications', ['recipient_user_id' => $officer->id, 'post_id' => $event->id]);
        $this->actingAs($other)->delete('/feed/comments/'.$comment->id)->assertForbidden();
        $this->actingAs($officer)->delete('/feed/comments/'.$comment->id)->assertRedirect();
        $this->assertDatabaseMissing('post_comments', ['id' => $comment->id]);
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
}
