<?php

namespace App\Livewire\Superadmin\SystemSettings;

use App\Models\ContentSection;
use App\Services\TransactionLogService;
use App\Support\Concerns\InteractsWithToasts as Toast;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class ContentSectionManager extends Component
{
    use Toast;

    // Delete data
    public $deletingId = null;

    public $deletingTitle = '';

    public $deleteModalOpen = false;

    // Preview data
    public $previewSection = null;

    public $previewModalOpen = false;

    // Filter & Search
    public $filterType = '';

    public $search = '';

    public function render()
    {
        return view('livewire.superadmin.system-settings.content-section-manager', [
            'contentSections' => $this->getContentSections(),
            'sectionTypes' => ContentSection::getSectionTypes(),
        ]);
    }

    protected function getContentSections()
    {
        $query = ContentSection::query()->orderBy('display_order')->orderBy('created_at', 'desc');

        if ($this->filterType) {
            $query->where('section_type', $this->filterType);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                    ->orWhere('section_key', 'like', "%{$this->search}%");
            });
        }

        return $query->get();
    }

    // Navigate to create page
    public function createSection()
    {
        return $this->redirect(route('superadmin.content-section.create'), navigate: true);
    }

    // Navigate to edit page
    public function editSection($id)
    {
        return $this->redirect(route('superadmin.content-section.edit', $id), navigate: true);
    }

    public function toggleActive($id)
    {
        $section = ContentSection::find($id);
        if ($section) {
            $section->update(['is_active' => ! $section->is_active]);

            $status = $section->is_active ? 'activated' : 'deactivated';
            TransactionLogService::log(
                "content_section_{$status}",
                "Content section '{$section->title}' (ID: {$section->id}) was {$status}"
            );

            $this->clearCache();
            $this->success("Content section {$status} successfully!", position: 'toast-top');
        }
    }

    public function openDeleteModal($id)
    {
        $section = ContentSection::find($id);
        if ($section) {
            $this->deletingId = $id;
            $this->deletingTitle = $section->title;
            $this->deleteModalOpen = true;
        }
    }

    public function resetDeleteModal()
    {
        $this->reset(['deletingId', 'deletingTitle']);
        $this->deleteModalOpen = false;
    }

    public function confirmDelete()
    {
        $section = ContentSection::find($this->deletingId);

        if (! $section) {
            $this->error('Content section not found!', position: 'toast-top');

            return;
        }

        DB::beginTransaction();
        try {
            TransactionLogService::log(
                'content_section_deleted',
                "Deleted content section: {$section->title} (ID: {$section->id})"
            );

            $section->delete();

            DB::commit();

            $this->reset(['deletingId', 'deletingTitle']);
            $this->deleteModalOpen = false;
            $this->clearCache();
            $this->success('Content section deleted successfully!', position: 'toast-bottom');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Content section deletion failed', ['error' => $e->getMessage()]);
            $this->error('Failed to delete content section. Please try again.', position: 'toast-top');
        }
    }

    public function openPreview($id)
    {
        $this->previewSection = ContentSection::find($id);
        $this->previewModalOpen = true;
    }

    public function closePreview()
    {
        $this->previewSection = null;
        $this->previewModalOpen = false;
    }

    protected function clearCache()
    {
        // Clear individual section caches
        $sections = ContentSection::all();
        foreach ($sections as $section) {
            Cache::forget("content_section_{$section->section_key}");
            Cache::forget("content_sections_type_{$section->section_type}");
        }
        $this->dispatch('cache-cleared');
    }

    #[On('refresh-cache')]
    public function refreshCache()
    {
        $this->clearCache();
    }
}
