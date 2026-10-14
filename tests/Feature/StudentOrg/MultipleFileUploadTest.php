<?php

use App\Jobs\ProcessTicketAttachments;
use App\Livewire\StudentOrg\SubmitTicket;
use App\Models\Course;
use App\Models\Event_Type;
use App\Models\Fund_Sources;
use App\Models\Positions;
use App\Models\Roles;
use App\Models\Student_Organization;
use App\Models\Ticket;
use App\Models\TicketDraft;
use App\Models\TicketDraftAttachment;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function () {
    Cache::flush();
    Storage::fake(config('filesystems.default'));

    Course::firstOrCreate([
        'course_id' => 1,
    ], [
        'course_name' => 'BS Computer Science',
        'course_code' => 'BSCS',
    ]);

    Roles::firstOrCreate([
        'role_id' => User::getRoleId('student-org'),
    ], [
        'role_name' => 'student-org',
    ]);

    Positions::firstOrCreate([
        'position_id' => 1,
    ], [
        'position_name' => 'President',
    ]);

    // IDs are auto-increment (not fillable), so tests look them up by name
    Event_Type::create([
        'type_name' => 'Academic Conference',
        'description' => 'Academic events',
    ]);

    Fund_Sources::create([
        'source_name' => 'Org Funds',
    ]);

    Venue::create([
        'venue_name' => 'Main Hall',
        'venue_location' => 'Building A',
        'is_active' => true,
    ]);

    Venue::create([
        'venue_name' => 'Others (Please Specify)',
        'venue_location' => 'Building B',
        'is_active' => true,
    ]);
});

function createStudentOrgUser()
{
    $org = Student_Organization::factory()->create([
        'org_code' => 'TESTORG',
        'course_id' => 1,
    ]);

    return User::factory()->create([
        'role_id' => User::getRoleId('student-org'),
        'org_id' => $org->org_id,
        'position_id' => 1,
    ]);
}

function fillTicketForm($livewire)
{
    $livewire->set('currentStep', 1)
        ->set('adviser_contact', '09123456789')
        ->set('is_amended', false)
        ->set('eventTitle', 'My First Event')
        ->set('eventDescription', 'This event is going to be incredibly fun and educational for everyone involved.')
        ->set('eventType', Event_Type::where('type_name', 'Academic Conference')->value('event_type_id'))
        ->set('expectedPLVParticipants', 50)
        ->set('eventStartDate', now()->addDays(2)->format('Y-m-d'))
        ->set('eventEndDate', now()->addDays(2)->format('Y-m-d'))
        ->set('eventStartTime', '08:00')
        ->set('eventEndTime', '12:00')
        ->set('preferredVenue', Venue::where('venue_name', 'Main Hall')->value('venue_id'))
        ->set('totalBudget', 1500)
        ->set('fundingSource', Fund_Sources::where('source_name', 'Org Funds')->value('source_id'))
        ->set('igp_requested', 'false')
        ->set('agreeToTerms', true);
}

/**
 * Create a draft attachment the way TemporaryUploadController does:
 * a file under draft-attachments/{draftId}/ plus a TicketDraftAttachment row.
 */
function createDraftAttachment(User $user, string $fileName = 'document.pdf', string $mime = 'application/pdf', int $size = 1024): TicketDraftAttachment
{
    $draft = TicketDraft::firstOrCreate(
        ['user_id' => $user->user_id],
        ['current_step' => 1, 'data' => []]
    );

    $path = "draft-attachments/{$draft->id}/".Str::random(8).'_'.$fileName;
    Storage::put($path, str_repeat('x', $size));

    return TicketDraftAttachment::create([
        'ticket_draft_id' => $draft->id,
        'file_name' => $fileName,
        'file_path' => $path,
        'file_type' => $mime,
        'file_size' => $size,
    ]);
}

function createTicketRecord(string $ticketNumber): Ticket
{
    return Ticket::create([
        'user_id' => createStudentOrgUser()->user_id,
        'ticket_number' => $ticketNumber,
        'event_type_id' => Event_Type::where('type_name', 'Academic Conference')->value('event_type_id'),
        'plv_participants' => 50,
        'total_participants' => 50,
        'title' => 'My First Event',
        'description' => 'Detailed description of this event proposal.',
        'proponent_contact' => '123',
        'adviser_contact' => '09123456789',
        'date_from' => now()->addDays(2),
        'date_to' => now()->addDays(2),
        'time_from' => '08:00',
        'time_to' => '12:00',
        'venue_requested' => Venue::where('venue_name', 'Main Hall')->value('venue_id'),
        'fund_source_id' => Fund_Sources::where('source_name', 'Org Funds')->value('source_id'),
        'status' => 'received',
    ]);
}

function assertToastShown($livewire, string $expectedTitle)
{
    $livewire->assertDispatched('toast-show', fn ($event, $params) => ($params['message'] ?? null) === $expectedTitle);
}

/*
 * =========================================================================
 * Tier 1: Happy Path & Core Workflows (1-15)
 * =========================================================================
 */

test('1. test_filepond_component_renders_on_attachments_step', function () {
    $user = createStudentOrgUser();
    $component = Livewire::actingAs($user)
        ->test(SubmitTicket::class)
        ->set('currentStep', 5);

    $component->assertSeeHtml('id="filepond-upload"');
    $component->assertSeeHtml('data-process-url="'.route('upload.temp').'"');
});

test('2. test_single_file_upload_success', function () {
    $user = createStudentOrgUser();

    $response = $this->actingAs($user)->post(route('upload.temp'), [
        'filepond' => UploadedFile::fake()->create('document.pdf', 500, 'application/pdf'),
    ]);
    $response->assertOk();

    Livewire::actingAs($user)
        ->test(SubmitTicket::class)
        ->set('uploadedFileIds', [(int) $response->getContent()])
        ->call('syncFilePondUploads')
        ->assertSet('attachments', function ($attachments) {
            return count($attachments) === 1 && $attachments[0]['file_name'] === 'document.pdf';
        });
});

test('3. test_multiple_file_upload_success', function () {
    $user = createStudentOrgUser();
    $first = createDraftAttachment($user, 'doc1.pdf');
    $second = createDraftAttachment($user, 'doc2.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

    Livewire::actingAs($user)
        ->test(SubmitTicket::class)
        ->set('uploadedFileIds', [$first->id, $second->id])
        ->call('syncFilePondUploads')
        ->assertSet('attachments', function ($attachments) {
            return count($attachments) === 2
                && $attachments[0]['file_name'] === 'doc1.pdf'
                && $attachments[1]['file_name'] === 'doc2.docx';
        });
});

test('4. test_file_list_item_displays_name_extension_and_size', function () {
    $file = UploadedFile::fake()->create('project_proposal.pdf', 2048); // 2 MB
    $html = Blade::render('<x-file-list-item :file="$file" :index="0" />', ['file' => $file]);

    expect($html)->toContain('project_proposal.pdf')
        ->toContain('PDF')
        ->toContain('2.0 MB');
});

test('5. test_remove_attachment_removes_from_array_disk_and_database', function () {
    $user = createStudentOrgUser();
    $attachment = createDraftAttachment($user);

    Livewire::actingAs($user)
        ->test(SubmitTicket::class)
        ->set('uploadedFileIds', [$attachment->id])
        ->call('syncFilePondUploads')
        ->call('removeAttachment', 0)
        ->assertSet('attachments', []);

    Storage::assertMissing($attachment->file_path);
    $this->assertDatabaseMissing('ticket_draft_attachments', ['id' => $attachment->id]);
});

test('6. test_ticket_submission_with_attachments_success', function () {
    $user = createStudentOrgUser();
    $attachment = createDraftAttachment($user);

    Queue::fake();

    $livewire = Livewire::actingAs($user)
        ->test(SubmitTicket::class);

    fillTicketForm($livewire);

    $livewire->set('uploadedFileIds', [$attachment->id])
        ->call('save')
        ->assertHasNoErrors();

    $ticket = Ticket::where('user_id', $user->user_id)->where('title', 'My First Event')->first();
    expect($ticket)->not->toBeNull();

    Queue::assertPushed(ProcessTicketAttachments::class, function ($job) use ($ticket) {
        return $job->ticketId === $ticket->ticket_id
            && count($job->attachments) === 1
            && $job->attachments[0]['file_name'] === 'document.pdf';
    });
});

test('7. test_ticket_submission_without_attachments_success', function () {
    $user = createStudentOrgUser();
    Queue::fake();

    $livewire = Livewire::actingAs($user)
        ->test(SubmitTicket::class);

    fillTicketForm($livewire);

    $livewire->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('tickets', [
        'user_id' => $user->user_id,
        'title' => 'My First Event',
    ]);
});

test('8. test_draft_attachment_preview_url_generation', function () {
    $user = createStudentOrgUser();
    $attachment = createDraftAttachment($user);

    Livewire::actingAs($user)
        ->test(SubmitTicket::class)
        ->set('uploadedFileIds', [$attachment->id])
        ->call('previewDraftAttachment', 0)
        ->assertDispatched('open-attachment-preview');
});

test('9. test_draft_attachment_download_url_generation', function () {
    $user = createStudentOrgUser();
    $attachment = createDraftAttachment($user);

    Livewire::actingAs($user)
        ->test(SubmitTicket::class)
        ->set('uploadedFileIds', [$attachment->id])
        ->call('downloadDraftAttachment', 0)
        ->assertDispatched('download-attachment');
});

test('10. test_preview_draft_attachment_route_streams_file', function () {
    $user = createStudentOrgUser();
    $token = (string) Str::uuid();

    Storage::put('draft-attachments/1/test.pdf', 'dummy pdf contents');

    Cache::put('draft-attachment-preview:'.$token, [
        'user_id' => $user->user_id,
        'storage_path' => 'draft-attachments/1/test.pdf',
        'file_name' => 'test.pdf',
        'mime' => 'application/pdf',
    ], 300);

    $url = URL::temporarySignedRoute('attachments.draft', now()->addMinutes(5), ['token' => $token]);

    $response = $this->actingAs($user)->get($url);

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/pdf');
    $response->assertHeader('Content-Disposition', 'inline; filename=test.pdf');
    expect($response->streamedContent())->toBe('dummy pdf contents');
});

test('11. test_download_draft_attachment_route_downloads_file', function () {
    $user = createStudentOrgUser();
    $token = (string) Str::uuid();

    Storage::put('draft-attachments/1/test.pdf', 'dummy pdf contents');

    Cache::put('draft-attachment-preview:'.$token, [
        'user_id' => $user->user_id,
        'storage_path' => 'draft-attachments/1/test.pdf',
        'file_name' => 'test.pdf',
        'mime' => 'application/pdf',
    ], 300);

    $url = URL::temporarySignedRoute('attachments.draft', now()->addMinutes(5), ['token' => $token, 'download' => 1]);

    $response = $this->actingAs($user)->get($url);

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/pdf');
    $response->assertHeader('Content-Disposition', 'attachment; filename=test.pdf');
});

test('12. test_process_ticket_attachments_job_moves_file_to_permanent_storage', function () {
    $ticket = createTicketRecord('TKT-TEST-0099');
    Storage::put('draft-attachments/1/unique_invoice.pdf', 'file content');

    (new ProcessTicketAttachments($ticket->ticket_id, [[
        'file_name' => 'invoice.pdf',
        'file_path' => 'draft-attachments/1/unique_invoice.pdf',
        'file_type' => 'application/pdf',
        'file_size' => 12,
    ]]))->handle();

    Storage::assertExists('tickets/'.$ticket->ticket_id.'/attachments/unique_invoice.pdf');
});

test('13. test_process_ticket_attachments_job_creates_attachment_record', function () {
    $ticket = createTicketRecord('TKT-TEST-0001');
    Storage::put('draft-attachments/1/unique_invoice.pdf', 'file content');

    (new ProcessTicketAttachments($ticket->ticket_id, [[
        'file_name' => 'invoice.pdf',
        'file_path' => 'draft-attachments/1/unique_invoice.pdf',
        'file_type' => 'application/pdf',
        'file_size' => 12,
    ]]))->handle();

    $this->assertDatabaseHas('attachments', [
        'ticket_id' => $ticket->ticket_id,
        'file_name' => 'invoice.pdf',
        'file_path' => 'tickets/'.$ticket->ticket_id.'/attachments/unique_invoice.pdf',
        'file_type' => 'application/pdf',
        'file_size' => 12,
    ]);
});

test('14. test_clear_draft_event_is_dispatched_on_successful_ticket_submission', function () {
    $user = createStudentOrgUser();
    Queue::fake();

    $livewire = Livewire::actingAs($user)
        ->test(SubmitTicket::class);

    fillTicketForm($livewire);

    $livewire->call('save')
        ->assertDispatched('clear-draft-immediate');
});

test('15. test_auto_save_is_disabled_during_ticket_submission_processing', function () {
    $user = createStudentOrgUser();
    Livewire::actingAs($user)
        ->test(SubmitTicket::class)
        ->set('isProcessing', true)
        ->set('eventTitle', 'New Title Title')
        ->assertNotDispatched('save-draft');
});

/*
 * =========================================================================
 * Tier 2: Validation, Boundary Conditions & Edge Cases (16-35)
 * =========================================================================
 */

test('16. test_file_upload_validation_rejects_unsupported_mimes', function () {
    $user = createStudentOrgUser();

    $this->actingAs($user)->post(route('upload.temp'), [
        'filepond' => UploadedFile::fake()->create('malicious.exe', 500, 'application/x-msdownload'),
    ])->assertSessionHasErrors(['filepond']);

    $this->assertDatabaseCount('ticket_draft_attachments', 0);
});

test('17. test_file_upload_validation_rejects_file_exceeding_10mb', function () {
    $user = createStudentOrgUser();

    $this->actingAs($user)->post(route('upload.temp'), [
        'filepond' => UploadedFile::fake()->create('huge.pdf', 10241, 'application/pdf'), // 10241 KB > 10MB
    ])->assertSessionHasErrors(['filepond']);
});

test('18. test_file_upload_validation_accepts_file_exactly_10mb', function () {
    $user = createStudentOrgUser();

    $this->actingAs($user)->post(route('upload.temp'), [
        'filepond' => UploadedFile::fake()->create('max_limit.pdf', 10240, 'application/pdf'), // exactly 10MB
    ])->assertOk()->assertSessionHasNoErrors();
});

test('19. test_file_upload_validation_rejects_total_files_exceeding_25', function () {
    $user = createStudentOrgUser();

    $ids = collect(range(1, 26))
        ->map(fn ($i) => createDraftAttachment($user, "doc_{$i}.pdf", size: 10)->id)
        ->all();

    Livewire::actingAs($user)
        ->test(SubmitTicket::class)
        ->set('currentStep', 5)
        ->set('uploadedFileIds', $ids)
        ->call('nextStep')
        ->assertHasErrors(['uploadedFileIds' => 'max'])
        ->assertSet('currentStep', 5);
});

test('20. test_file_upload_validation_accepts_exactly_25_files', function () {
    $user = createStudentOrgUser();

    $ids = collect(range(1, 25))
        ->map(fn ($i) => createDraftAttachment($user, "doc_{$i}.pdf", size: 10)->id)
        ->all();

    Livewire::actingAs($user)
        ->test(SubmitTicket::class)
        ->set('currentStep', 5)
        ->set('uploadedFileIds', $ids)
        ->call('nextStep')
        ->assertHasNoErrors()
        ->assertSet('currentStep', 6)
        ->call('syncFilePondUploads')
        ->assertSet('attachments', fn ($attachments) => count($attachments) === 25);
});

test('21. test_file_upload_with_duplicate_filenames', function () {
    $user = createStudentOrgUser();
    $first = createDraftAttachment($user, 'same_name.pdf', size: 100);
    $second = createDraftAttachment($user, 'same_name.pdf', size: 150);

    expect($first->file_path)->not->toBe($second->file_path);

    Livewire::actingAs($user)
        ->test(SubmitTicket::class)
        ->set('uploadedFileIds', [$first->id, $second->id])
        ->call('syncFilePondUploads')
        ->assertSet('attachments', function ($attachments) {
            return count($attachments) === 2
                && $attachments[0]['file_name'] === 'same_name.pdf'
                && $attachments[1]['file_name'] === 'same_name.pdf';
        });
});

test('22. test_draft_preview_fails_for_non_existent_attachment_index', function () {
    $user = createStudentOrgUser();
    $livewire = Livewire::actingAs($user)
        ->test(SubmitTicket::class)
        ->call('previewDraftAttachment', 99);

    assertToastShown($livewire, 'Attachment not found.');
});

test('23. test_draft_preview_fails_if_stored_file_is_missing_from_disk', function () {
    $user = createStudentOrgUser();
    $attachment = createDraftAttachment($user, 'nonexistent.pdf', size: 100);

    Storage::delete($attachment->file_path);

    $livewire = Livewire::actingAs($user)
        ->test(SubmitTicket::class)
        ->set('uploadedFileIds', [$attachment->id])
        ->call('previewDraftAttachment', 0)
        ->assertNotDispatched('open-attachment-preview');

    assertToastShown($livewire, 'File is no longer available. Re-upload if needed.');
});

test('24. test_draft_attachment_controller_aborts_for_expired_token', function () {
    $user = createStudentOrgUser();
    $url = URL::temporarySignedRoute('attachments.draft', now()->addMinutes(5), ['token' => 'invalid-expired-token']);

    $response = $this->actingAs($user)->get($url);
    $response->assertStatus(404);
});

test('25. test_draft_attachment_controller_aborts_for_unauthorized_user', function () {
    $user1 = createStudentOrgUser();
    $user2 = createStudentOrgUser();

    $token = (string) Str::uuid();
    Cache::put('draft-attachment-preview:'.$token, [
        'user_id' => $user1->user_id,
        'file_name' => 'test.pdf',
        'mime' => 'application/pdf',
        'storage_path' => 'draft-attachments/1/somefile.pdf',
    ], 300);

    $url = URL::temporarySignedRoute('attachments.draft', now()->addMinutes(5), ['token' => $token]);

    $response = $this->actingAs($user2)->get($url);
    $response->assertStatus(403);
});

test('26. test_draft_attachment_controller_aborts_for_unsigned_url', function () {
    $user = createStudentOrgUser();
    $token = (string) Str::uuid();
    Cache::put('draft-attachment-preview:'.$token, [
        'user_id' => $user->user_id,
        'file_name' => 'test.pdf',
        'mime' => 'application/pdf',
        'storage_path' => 'draft-attachments/1/somefile.pdf',
    ], 300);

    $url = route('attachments.draft', ['token' => $token]);

    $response = $this->actingAs($user)->get($url);
    $response->assertStatus(403);
});

test('27. test_process_ticket_attachments_job_handles_missing_source_file_gracefully', function () {
    $ticket = createTicketRecord('TKT-TEST-0027');

    (new ProcessTicketAttachments($ticket->ticket_id, [[
        'file_name' => 'original.pdf',
        'file_path' => 'draft-attachments/1/non_existent.pdf',
        'file_type' => 'application/pdf',
        'file_size' => 100,
    ]]))->handle();

    Storage::assertMissing('tickets/'.$ticket->ticket_id.'/attachments/non_existent.pdf');
});

test('28. test_process_ticket_attachments_job_retries_on_failure', function () {
    $job = new ProcessTicketAttachments(99, []);

    expect($job->tries)->toBe(3);
    expect($job->backoff)->toBe(10);
});

test('29. test_step_5_rejects_unknown_uploaded_file_ids', function () {
    $user = createStudentOrgUser();

    Livewire::actingAs($user)
        ->test(SubmitTicket::class)
        ->set('currentStep', 5)
        ->set('uploadedFileIds', [999999])
        ->call('nextStep')
        ->assertHasErrors(['uploadedFileIds.0' => 'exists'])
        ->assertSet('currentStep', 5);
});

test('30. test_rate_limiter_blocks_rapid_submissions', function () {
    $user = createStudentOrgUser();
    Queue::fake();

    $rateLimitKey = 'ticket-submit:'.$user->user_id;
    RateLimiter::hit($rateLimitKey, 60);
    RateLimiter::hit($rateLimitKey, 60);
    RateLimiter::hit($rateLimitKey, 60);

    $livewire = Livewire::actingAs($user)
        ->test(SubmitTicket::class);

    fillTicketForm($livewire);

    $livewire->call('save');
    assertToastShown($livewire, 'Too Many Attempts');

    $this->assertDatabaseMissing('tickets', ['user_id' => $user->user_id]);
});

test('31. test_ticket_locking_prevents_duplicate_submissions', function () {
    $user = createStudentOrgUser();
    Queue::fake();

    $lockKey = "lock:ticket:submit:{$user->user_id}";
    Cache::lock($lockKey, 10)->get();

    $livewire = Livewire::actingAs($user)
        ->test(SubmitTicket::class);

    fillTicketForm($livewire);

    $livewire->call('save');

    $this->assertDatabaseMissing('tickets', [
        'user_id' => $user->user_id,
        'title' => 'My First Event',
    ]);
});

test('32. test_submitting_invalid_adviser_contact_fails_validation', function () {
    $user = createStudentOrgUser();
    $livewire = Livewire::actingAs($user)
        ->test(SubmitTicket::class)
        ->set('adviser_contact', '123')
        ->call('nextStep')
        ->assertHasErrors(['adviser_contact']);
});

test('33. test_validation_errors_preserve_uploaded_attachments_state', function () {
    $user = createStudentOrgUser();
    $attachment = createDraftAttachment($user);

    Livewire::actingAs($user)
        ->test(SubmitTicket::class)
        ->set('uploadedFileIds', [$attachment->id])
        ->call('syncFilePondUploads')
        ->set('eventTitle', '123')
        ->set('currentStep', 2)
        ->call('nextStep')
        ->assertHasErrors(['eventTitle'])
        ->assertSet('uploadedFileIds', [$attachment->id])
        ->assertSet('attachments', function ($attachments) {
            return count($attachments) === 1;
        });
});

test('34. test_draft_loaded_event_dispatched_on_loading_draft', function () {
    $user = createStudentOrgUser();
    Livewire::actingAs($user)
        ->test(SubmitTicket::class)
        ->call('loadDraft', ['eventTitle' => 'Draft Event'])
        ->assertDispatched('draft-loaded');
});

test('35. test_discard_draft_clears_draft_state', function () {
    $user = createStudentOrgUser();
    Livewire::actingAs($user)
        ->test(SubmitTicket::class)
        ->call('discardDraft')
        ->assertDispatched('clear-draft');
});

/*
 * =========================================================================
 * Tier 3: UI Layout, Styles, Accessibility & Responsiveness (36-50)
 * =========================================================================
 */

test('36. test_dropzone_input_has_correct_attributes_and_accessibility', function () {
    $html = Blade::render('<x-file-upload-dropzone wireModel="newAttachments" inputId="test-id" />');

    expect($html)->toContain('id="test-id"')
        ->toContain('type="file"')
        ->toContain('accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx"')
        ->toContain('aria-describedby="file-upload-constraints"');
});

test('37. test_dropzone_label_has_matching_for_attribute', function () {
    $html = Blade::render('<x-file-upload-dropzone inputId="test-id" />');

    expect($html)->toContain('for="test-id"');
});

test('38. test_dropzone_has_aria_label_for_screen_readers', function () {
    $html = Blade::render('<x-file-upload-dropzone />');

    expect($html)->toContain('aria-label="Upload event documents"');
});

test('39. test_dropzone_focus_within_focus_ring_classes', function () {
    $html = Blade::render('<x-file-upload-dropzone />');

    expect($html)->toContain('focus-within:ring-2')
        ->toContain('focus-within:ring-primary/30')
        ->toContain('focus-within:border-primary');
});

test('40. test_dropzone_display_shows_correct_maximum_files_and_size', function () {
    $html = Blade::render('<x-file-upload-dropzone :maxFiles="20" :maxSizeMb="15" />');

    expect($html)->toContain('up to 20 files total')
        ->toContain('Maximum 15 MB per file')
        ->toContain('Up to 20 files per ticket');
});

test('41. test_dropzone_contains_loading_indicator_with_target', function () {
    $html = Blade::render('<x-file-upload-dropzone wireModel="customAttachments" />');

    expect($html)->toContain('wire:loading.flex')
        ->toContain('wire:target="customAttachments"');
});

test('42. test_dropzone_alpine_attributes_exist', function () {
    $html = Blade::render('<x-file-upload-dropzone />');

    expect($html)->toContain('x-data="{')
        ->toContain('isDragging: false')
        ->toContain('x-on:dragover="handleDragOver($event)"')
        ->toContain('x-on:dragleave="handleDragLeave()"')
        ->toContain('x-on:drop="handleDrop($event)"');
});

test('43. test_file_list_item_has_remove_button_with_aria_label', function () {
    $file = UploadedFile::fake()->create('remove_me.pdf', 100);
    $html = Blade::render('<x-file-list-item :file="$file" :index="3" />', ['file' => $file]);

    expect($html)->toContain('aria-label="Remove remove_me.pdf"')
        ->toContain('wire:click="removeAttachment(3)"');
});

test('44. test_file_list_item_badge_class_applied', function () {
    $file = UploadedFile::fake()->create('report.pdf', 100);
    $html = Blade::render('<x-file-list-item :file="$file" :index="0" />', ['file' => $file]);

    expect($html)->toContain('badge badge-ghost badge-sm');
});

test('45. test_file_list_item_responsive_padding_classes', function () {
    $file = UploadedFile::fake()->create('report.pdf', 100);
    $html = Blade::render('<x-file-list-item :file="$file" :index="0" />', ['file' => $file]);

    expect($html)->toContain('flex flex-col gap-2')
        ->toContain('sm:flex-row sm:items-center sm:justify-between');
});

test('46. test_file_list_item_icon_for_pdf_file', function () {
    $file = UploadedFile::fake()->create('doc.pdf', 100);
    $html = Blade::render('<x-file-list-item :file="$file" :index="0" />', ['file' => $file]);

    expect($html)->toContain('M19.5 14.25v-2.625');
});

test('47. test_file_list_item_icon_for_word_file', function () {
    $file = UploadedFile::fake()->create('doc.docx', 100);
    $html = Blade::render('<x-file-list-item :file="$file" :index="0" />', ['file' => $file]);

    expect($html)->toContain('m2.25 0H5.625');
});

test('48. test_file_list_item_icon_for_excel_file', function () {
    $file = UploadedFile::fake()->create('sheet.xlsx', 100);
    $html = Blade::render('<x-file-list-item :file="$file" :index="0" />', ['file' => $file]);

    expect($html)->toContain('M3.375 19.5');
});

test('49. test_file_list_item_icon_for_image_file', function () {
    $file = UploadedFile::fake()->create('photo.png', 100);
    $html = Blade::render('<x-file-list-item :file="$file" :index="0" />', ['file' => $file]);

    expect($html)->toContain('m2.25 15.75');
});

test('50. test_file_list_item_icon_for_other_file', function () {
    $file = UploadedFile::fake()->create('archive.zip', 100);
    $html = Blade::render('<x-file-list-item :file="$file" :index="0" />', ['file' => $file]);

    expect($html)->toContain('m18.375 12.739');
});

/*
 * =========================================================================
 * Tier 4: Storage, Queueing, Transactions, Cache & DB (51-60)
 * =========================================================================
 */

test('51. test_attachment_stored_in_correct_directory_structure', function () {
    $ticket = createTicketRecord('TKT-TEST-0045');
    Storage::put('draft-attachments/7/xyz.pdf', 'content');

    (new ProcessTicketAttachments($ticket->ticket_id, [[
        'file_name' => 'receipt.pdf',
        'file_path' => 'draft-attachments/7/xyz.pdf',
        'file_type' => 'application/pdf',
        'file_size' => 7,
    ]]))->handle();

    Storage::assertExists('tickets/'.$ticket->ticket_id.'/attachments/xyz.pdf');
});

test('52. test_job_handles_disk_configuration_dynamically', function () {
    config(['filesystems.default' => 'public']);
    Storage::fake('public');

    $ticket = createTicketRecord('TKT-TEST-0010');
    Storage::disk('public')->put('draft-attachments/1/stored.pdf', 'content');

    (new ProcessTicketAttachments($ticket->ticket_id, [[
        'file_name' => 'test.pdf',
        'file_path' => 'draft-attachments/1/stored.pdf',
        'file_type' => 'application/pdf',
        'file_size' => 7,
    ]]))->handle();

    Storage::disk('public')->assertExists('tickets/'.$ticket->ticket_id.'/attachments/stored.pdf');
});

test('53. test_job_cleans_up_draft_file_on_success', function () {
    $ticket = createTicketRecord('TKT-TEST-0010');
    Storage::put('draft-attachments/1/file_to_delete.pdf', 'content');

    (new ProcessTicketAttachments($ticket->ticket_id, [[
        'file_name' => 'test.pdf',
        'file_path' => 'draft-attachments/1/file_to_delete.pdf',
        'file_type' => 'application/pdf',
        'file_size' => 7,
    ]]))->handle();

    Storage::assertMissing('draft-attachments/1/file_to_delete.pdf');
});

test('54. test_database_transaction_rollbacks_on_submission_failure', function () {
    $user = createStudentOrgUser();
    Queue::fake();

    $livewire = Livewire::actingAs($user)
        ->test(SubmitTicket::class);

    fillTicketForm($livewire);

    Ticket::creating(function ($ticket) {
        throw new Exception('Database failure');
    });

    try {
        $livewire->call('save');
    } catch (Throwable $e) {
        // Expected
    } finally {
        Ticket::flushEventListeners();
    }

    $this->assertDatabaseMissing('tickets', [
        'user_id' => $user->user_id,
        'title' => 'My First Event',
    ]);
});

test('55. test_draft_attachment_preview_uses_cache_with_5_minutes_ttl', function () {
    $user = createStudentOrgUser();
    $attachment = createDraftAttachment($user, 'report.pdf', size: 100);

    $cacheMock = Cache::spy();

    Livewire::actingAs($user)
        ->test(SubmitTicket::class)
        ->set('uploadedFileIds', [$attachment->id])
        ->call('previewDraftAttachment', 0);

    $cacheMock->shouldHaveReceived('put')
        ->with(
            Mockery::on(fn ($key) => is_string($key) && str_contains($key, 'draft-attachment-preview:')),
            Mockery::any(),
            Mockery::on(function ($ttl) {
                return $ttl instanceof DateTimeInterface || $ttl >= 290;
            })
        );
});

test('56. test_save_ticket_clears_draft_immediate', function () {
    $user = createStudentOrgUser();
    Queue::fake();

    $livewire = Livewire::actingAs($user)
        ->test(SubmitTicket::class);

    fillTicketForm($livewire);

    $livewire->call('save')
        ->assertDispatched('clear-draft-immediate');

    $this->assertDatabaseMissing('ticket_drafts', ['user_id' => $user->user_id]);
});

test('57. test_process_ticket_attachments_job_is_dispatched_on_submission', function () {
    $user = createStudentOrgUser();
    Queue::fake();

    $livewire = Livewire::actingAs($user)
        ->test(SubmitTicket::class);

    fillTicketForm($livewire);

    $livewire->call('save');

    Queue::assertPushed(ProcessTicketAttachments::class, 1);
});

test('58. test_transaction_log_is_recorded_on_ticket_creation', function () {
    $user = createStudentOrgUser();
    Queue::fake();

    $livewire = Livewire::actingAs($user)
        ->test(SubmitTicket::class);

    fillTicketForm($livewire);

    $livewire->call('save');

    $this->assertDatabaseHas('transaction_logs', [
        'action' => 'TICKET_CREATED',
    ]);
});

test('59. test_ticket_number_generation_is_sequential_per_org', function () {
    $user = createStudentOrgUser();
    Queue::fake();

    $livewire1 = Livewire::actingAs($user)->test(SubmitTicket::class);
    fillTicketForm($livewire1);
    $livewire1->call('save');

    $livewire2 = Livewire::actingAs($user)->test(SubmitTicket::class);
    fillTicketForm($livewire2);
    $livewire2->call('save');

    $this->assertDatabaseHas('tickets', ['ticket_number' => 'TKT-TESTORG-0001']);
    $this->assertDatabaseHas('tickets', ['ticket_number' => 'TKT-TESTORG-0002']);
});

test('60. test_multiple_attachments_are_queued_in_a_single_job', function () {
    $user = createStudentOrgUser();
    Queue::fake();

    $first = createDraftAttachment($user, 'file1.pdf', size: 100);
    $second = createDraftAttachment($user, 'file2.png', 'image/png', 200);

    $livewire = Livewire::actingAs($user)->test(SubmitTicket::class);
    fillTicketForm($livewire);

    $livewire->set('uploadedFileIds', [$first->id, $second->id])
        ->call('save');

    Queue::assertPushed(ProcessTicketAttachments::class, function ($job) {
        return collect($job->attachments)->pluck('file_name')->sort()->values()->all() === ['file1.pdf', 'file2.png'];
    });
});

test('61. test_cannot_preview_another_users_draft_attachment', function () {
    $owner = createStudentOrgUser();
    $attacker = createStudentOrgUser();
    $attachment = createDraftAttachment($owner, 'private.pdf');

    $livewire = Livewire::actingAs($attacker)
        ->test(SubmitTicket::class)
        ->set('uploadedFileIds', [$attachment->id])
        ->call('previewDraftAttachment', 0)
        ->assertNotDispatched('open-attachment-preview');

    assertToastShown($livewire, 'Attachment not found.');
});
