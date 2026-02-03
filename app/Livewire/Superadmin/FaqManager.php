<?php

namespace App\Livewire\Superadmin;

use App\Models\Faq;
use App\Services\TransactionLogService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

#[Title('FAQ Management')]
#[Layout('components.layouts.superadmin')]
class FaqManager extends Component
{
    use Toast, WithPagination;

    // Search and filters
    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $filterCategory = '';

    #[Url]
    public string $filterStatus = '';

    // Delete modal state
    public $deletingFaqId = null;
    public $deletingFaqQuestion = '';
    public bool $deleteModalOpen = false;

    // Cache duration
    protected int $cacheDuration = 10;

    public function render()
    {
        return view('livewire.superadmin.faq-manager', [
            'faqs' => $this->getFaqs(),
            'categories' => $this->getCategories(),
        ]);
    }

    protected function getFaqs()
    {
        $query = Faq::query()->ordered();

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('question', 'like', '%' . $this->search . '%')
                    ->orWhere('answer', 'like', '%' . $this->search . '%')
                    ->orWhere('category', 'like', '%' . $this->search . '%');
            });
        }

        // Apply category filter
        if ($this->filterCategory) {
            if ($this->filterCategory === '__none__') {
                $query->whereNull('category');
            } else {
                $query->where('category', $this->filterCategory);
            }
        }

        // Apply status filter
        if ($this->filterStatus !== '') {
            $query->where('is_active', $this->filterStatus === 'active');
        }

        return $query->paginate(10);
    }

    protected function getCategories(): array
    {
        return Faq::whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values()
            ->toArray();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterCategory()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'filterCategory', 'filterStatus']);
        $this->resetPage();
    }

    /**
     * Open delete confirmation modal
     */
    public function openDeleteModal(int $faqId): void
    {
        $faq = Faq::find($faqId);
        if ($faq) {
            $this->deletingFaqId = $faqId;
            $this->deletingFaqQuestion = $faq->question;
            $this->deleteModalOpen = true;
        }
    }

    /**
     * Reset delete modal state
     */
    public function resetDeleteModal(): void
    {
        $this->reset(['deletingFaqId', 'deletingFaqQuestion']);
        $this->deleteModalOpen = false;
    }

    /**
     * Confirm and execute deletion
     */
    public function confirmDelete(): void
    {
        $faq = Faq::find($this->deletingFaqId);

        if (!$faq) {
            $this->error('FAQ not found!', position: 'toast-top');
            return;
        }

        DB::beginTransaction();
        try {
            // Log the deletion
            TransactionLogService::log(
                'faq_deleted',
                "FAQ deleted: {$faq->question} (ID: {$faq->id})"
            );

            $faq->delete();

            DB::commit();

            $this->resetDeleteModal();
            Faq::clearCache();
            $this->success('FAQ deleted successfully!', position: 'toast-top');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('FAQ deletion failed', ['error' => $e->getMessage()]);
            $this->error('Failed to delete FAQ: ' . $e->getMessage(), position: 'toast-top');
        }
    }

    /**
     * Toggle FAQ active status
     */
    public function toggleActive(int $faqId): void
    {
        $faq = Faq::find($faqId);

        if (!$faq) {
            $this->error('FAQ not found!', position: 'toast-top');
            return;
        }

        DB::beginTransaction();
        try {
            $previousStatus = $faq->is_active ? 'active' : 'inactive';
            $faq->is_active = !$faq->is_active;
            $faq->save();

            $newStatus = $faq->is_active ? 'active' : 'inactive';
            TransactionLogService::log(
                'faq_status_changed',
                "FAQ status changed: {$previousStatus} → {$newStatus} - {$faq->question} (ID: {$faq->id})"
            );

            DB::commit();

            Faq::clearCache();
            $statusLabel = $faq->is_active ? 'activated' : 'deactivated';
            $this->success("FAQ {$statusLabel} successfully!", position: 'toast-bottom');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('FAQ status toggle failed', ['error' => $e->getMessage()]);
            $this->error('Failed to update FAQ status: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }

    /**
     * Update FAQ order after drag and drop
     */
    public function updateOrder(array $orderedIds): void
    {
        DB::beginTransaction();
        try {
            foreach ($orderedIds as $index => $faqId) {
                Faq::where('id', $faqId)->update(['display_order' => $index + 1]);
            }

            TransactionLogService::log(
                'faq_reordered',
                "FAQ display order updated via drag and drop"
            );

            DB::commit();
            Faq::clearCache();
            // $this->success('FAQ order updated!', position: 'toast-bottom');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('FAQ reorder failed', ['error' => $e->getMessage()]);
            $this->error('Failed to update order.', position: 'toast-bottom');
        }
    }
}
