<?php

use App\Models\TicketDraft;
use App\Models\TicketDraftAttachment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake();
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('can upload a valid file via FilePond', function () {
    $file = UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf');

    $response = $this->post(route('upload.temp'), [
        'filepond' => $file,
    ]);

    $response->assertStatus(200);

    // Should return the ID of the created TicketDraftAttachment
    $attachmentId = $response->getContent();
    expect(is_numeric($attachmentId))->toBeTrue();

    // Verify DB record
    $attachment = TicketDraftAttachment::find($attachmentId);
    expect($attachment)->not->toBeNull()
        ->and($attachment->file_name)->toBe('document.pdf')
        ->and($attachment->file_type)->toBe('application/pdf');

    // Verify draft was auto-created for the user
    $draft = TicketDraft::find($attachment->ticket_draft_id);
    expect($draft->user_id)->toBe($this->user->user_id);

    // Verify file exists on disk
    Storage::assertExists($attachment->file_path);
});

test('rejects files exceeding size limit', function () {
    // 11 MB file
    $file = UploadedFile::fake()->create('large.pdf', 11 * 1024, 'application/pdf');

    $response = $this->post(route('upload.temp'), [
        'filepond' => $file,
    ]);

    $response->assertSessionHasErrors(['filepond']);
});

test('rejects unsupported file types', function () {
    $file = UploadedFile::fake()->create('app.exe', 1000, 'application/x-msdownload');

    $response = $this->post(route('upload.temp'), [
        'filepond' => $file,
    ]);

    $response->assertSessionHasErrors(['filepond']);
});

test('accepts broad range of supported file types', function ($extension, $mime) {
    $file = UploadedFile::fake()->create("test.{$extension}", 1000, $mime);

    $response = $this->post(route('upload.temp'), [
        'filepond' => $file,
    ]);

    $response->assertStatus(200);
})->with([
    ['pdf', 'application/pdf'],
    ['doc', 'application/msword'],
    ['docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
    ['jpg', 'image/jpeg'],
    ['png', 'image/png'],
    ['xls', 'application/vnd.ms-excel'],
    ['xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
]);

test('can revert/delete an uploaded file', function () {
    // Create draft and attachment
    $draft = TicketDraft::create([
        'user_id' => $this->user->user_id,
        'current_step' => 1,
        'data' => [],
    ]);

    $file = UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf');
    $path = $file->storeAs("draft-attachments/{$draft->id}", 'test.pdf');

    $attachment = TicketDraftAttachment::create([
        'ticket_draft_id' => $draft->id,
        'file_name' => 'document.pdf',
        'file_path' => $path,
        'file_type' => 'application/pdf',
        'file_size' => 1000,
    ]);

    Storage::assertExists($path);

    // Send delete request
    $response = $this->call('DELETE', route('upload.temp.delete'), [], [], [], [], $attachment->id);

    $response->assertStatus(200);

    // Verify deleted
    expect(TicketDraftAttachment::find($attachment->id))->toBeNull();
    Storage::assertMissing($path);
});

test('cannot delete another users attachment', function () {
    $otherUser = User::factory()->create();
    $draft = TicketDraft::create([
        'user_id' => $otherUser->user_id,
        'current_step' => 1,
        'data' => [],
    ]);

    $file = UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf');
    $path = $file->storeAs("draft-attachments/{$draft->id}", 'test.pdf');

    $attachment = TicketDraftAttachment::create([
        'ticket_draft_id' => $draft->id,
        'file_name' => 'document.pdf',
        'file_path' => $path,
        'file_type' => 'application/pdf',
        'file_size' => 1000,
    ]);

    // Send delete request as $this->user
    $response = $this->call('DELETE', route('upload.temp.delete'), [], [], [], [], $attachment->id);

    $response->assertStatus(200);

    // Verify NOT deleted because user mismatch
    expect(TicketDraftAttachment::find($attachment->id))->not->toBeNull();
    Storage::assertExists($path);
});

test('can restore an uploaded file', function () {
    // Create draft and attachment
    $draft = TicketDraft::create([
        'user_id' => $this->user->user_id,
        'current_step' => 1,
        'data' => [],
    ]);

    $fileContent = 'dummy file content';
    $path = 'draft-attachments/'.$draft->id.'/test.txt';
    Storage::put($path, $fileContent);

    $attachment = TicketDraftAttachment::create([
        'ticket_draft_id' => $draft->id,
        'file_name' => 'test.txt',
        'file_path' => $path,
        'file_type' => 'text/plain',
        'file_size' => strlen($fileContent),
    ]);

    // Send restore request via GET
    $response = $this->get(route('upload.temp.restore', ['id' => $attachment->id]));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'text/plain; charset=utf-8');
    $response->assertHeader('Content-Length', strlen($fileContent));

    // Check if the response content matches the file content
    expect($response->getContent())->toBe($fileContent);
});
