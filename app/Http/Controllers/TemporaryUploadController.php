<?php

namespace App\Http\Controllers;

use App\Models\TicketDraft;
use App\Models\TicketDraftAttachment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

/**
 * Stateless controller for FilePond's process / revert endpoints.
 *
 * Process: accepts a single file upload, validates it, stores it in
 *          draft-attachments/{draftId}/, creates a TicketDraftAttachment
 *          record, and returns the record's ID as plain text.
 *
 * Revert:  receives the attachment ID, deletes the file and DB record.
 */
class TemporaryUploadController extends Controller
{
    /**
     * Handle a FilePond `process` upload.
     *
     * The uploaded file is validated server-side (type + size), stored to disk,
     * and linked to the user's active draft. If no draft exists yet, one is
     * created automatically.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $file = $request->file('filepond');

        if ($file && ! $file->isValid()) {
            $uploadError = $file->getError();
            if ($uploadError === UPLOAD_ERR_INI_SIZE || $uploadError === UPLOAD_ERR_FORM_SIZE) {
                $maxServerSize = ini_get('upload_max_filesize');
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'filepond' => ["The uploaded file exceeds the server limit of {$maxServerSize}."],
                ]);
            }
        }

        $request->validate([
            'filepond' => [
                'required',
                'file',
                'max:10240',
                'mimes:pdf,doc,docx,jpg,jpeg,png,xls,xlsx',
                'extensions:pdf,doc,docx,jpg,jpeg,png,xls,xlsx',
            ],
        ], [
            'filepond.required' => 'Please select a valid file to upload.',
            'filepond.file' => 'The uploaded item must be a valid file.',
            'filepond.max' => 'The file size must not exceed 10 MB.',
            'filepond.mimes' => 'The file must be of type: PDF, DOC, DOCX, JPG, PNG, XLS, XLSX.',
            'filepond.extensions' => 'The file extension must be one of: pdf, doc, docx, jpg, jpeg, png, xls, xlsx.',
        ]);

        $file = $request->file('filepond');

        // Ensure a draft row exists for the authenticated user so we can
        // satisfy the foreign key on ticket_draft_attachments.
        $draft = TicketDraft::firstOrCreate(
            ['user_id' => auth()->id()],
            ['current_step' => 1, 'data' => []]
        );

        $originalName = $file->getClientOriginalName();
        $storedName = time().'_'.uniqid().'_'.$originalName;
        $path = $file->storeAs(
            "draft-attachments/{$draft->id}",
            $storedName
        );

        $record = TicketDraftAttachment::create([
            'ticket_draft_id' => $draft->id,
            'file_name' => $originalName,
            'file_path' => $path,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);

        // FilePond expects a plain-text unique server ID.
        return response($record->id, 200)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Handle a FilePond `revert` request.
     *
     * Receives the attachment ID as the raw request body, deletes the
     * file from disk and the DB record.
     *
     * @return Response
     */
    public function destroy(Request $request)
    {
        $attachmentId = $request->getContent();

        $record = TicketDraftAttachment::where('id', $attachmentId)
            ->whereHas('draft', fn ($q) => $q->where('user_id', auth()->id()))
            ->first();

        if ($record) {
            Storage::delete($record->file_path);
            $record->delete();
        }

        return response('', 200)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Handle a FilePond `restore` request.
     *
     * Receives the attachment ID in the URL.
     * Returns the actual file contents so FilePond can display the file.
     *
     * @return Response
     */
    public function restore(Request $request, $id = null)
    {
        // FilePond might send the ID via query string `?id=` or in the path `/restore/{id}`
        $attachmentId = $id ?? $request->query('id') ?? $request->getContent();

        $record = TicketDraftAttachment::where('id', $attachmentId)
            ->whereHas('draft', fn ($q) => $q->where('user_id', auth()->id()))
            ->first();

        if (! $record || ! Storage::exists($record->file_path)) {
            return response('File not found', 404);
        }

        $fileContent = Storage::get($record->file_path);

        return response($fileContent, 200)
            ->header('Content-Type', $record->file_type)
            ->header('Content-Disposition', 'inline; filename="'.$record->file_name.'"')
            ->header('Content-Length', $record->file_size)
            ->header('Access-Control-Expose-Headers', 'Content-Disposition, Content-Length');
    }
}
