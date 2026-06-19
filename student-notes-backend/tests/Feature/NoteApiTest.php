<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Notes;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NoteApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_note_can_be_created(): void
    {
        $response = $this->postJson('/api/notes', [
            'title' => 'Test Note',
            'content' => 'This is a test note.',
            'priority' => 'high',
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonFragment([
                'title' => 'Test Note',
                'priority' => 'high',
            ]);

        $this->assertDatabaseHas('notes', [
            'title' => 'Test Note',
        ]);
    }

    public function test_note_creation_validation_fails(): void
    {
        $response = $this->postJson('/api/notes', []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'title',
                'content',
                'priority',
            ]);
    }

    public function test_note_can_be_archived(): void
    {
        $note = Notes::create([
            'title' => 'Archive Test',
            'content' => 'Archive this note',
            'priority' => 'medium',
        ]);

        $response = $this->patchJson(
            "/api/notes/{$note->id}/archive"
        );

        $response->assertStatus(200);

        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'archived' => true,
        ]);
    }
}