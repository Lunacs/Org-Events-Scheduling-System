<?php

namespace App\Livewire\Superadmin;

use App\Models\Faq;
use App\Models\User;
use App\Notifications\SystemSettingsUpdatedNotification;
use App\Services\TransactionLogService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('FAQ Editor')]
#[Layout('components.layouts.superadmin')]
class FaqEditor extends Component
{
    use Toast;

    public ?int $faqId = null;
    public string $question = '';
    public string $answer = '';
    public ?string $category = null;
    public int $displayOrder = 0;
    public bool $isActive = true;

    public bool $isEditing = false;

    // Category combobox state
    public string $categorySearch = '';
    public bool $showCategoryDropdown = false;
    public string $categoryMode = 'selecting'; // selecting, creating, editing
    public ?string $editingCategory = null;
    public string $editCategoryName = '';

    /**
     * Mount the component
     */
    public function mount(?int $id = null): void
    {
        if ($id) {
            $faq = Faq::findOrFail($id);
            $this->faqId = $faq->id;
            $this->question = $faq->question;
            $this->answer = $faq->answer;
            $this->category = $faq->category;
            $this->displayOrder = $faq->display_order;
            $this->isActive = (bool) $faq->is_active;
            $this->isEditing = true;
            $this->categorySearch = $faq->category ?? '';
        } else {
            // Set default display order for new FAQs
            $maxOrder = Faq::max('display_order') ?? 0;
            $this->displayOrder = $maxOrder + 1;
        }
    }

    public function render()
    {
        return view('livewire.superadmin.faq-editor', [
            'categories' => Faq::getCategories(),
        ]);
    }

    /**
     * Validation rules
     */
    protected function rules(): array
    {
        return [
            'question' => 'required|string|max:500',
            'answer' => 'required|string|max:5000',
            'category' => 'nullable|string|max:100',
            'displayOrder' => 'required|integer|min:0',
            'isActive' => 'boolean',
        ];
    }

    /**
     * Custom validation messages
     */
    protected function messages(): array
    {
        return [
            'question.required' => 'The question is required.',
            'question.max' => 'The question must not exceed 500 characters.',
            'answer.required' => 'The answer is required.',
            'answer.max' => 'The answer must not exceed 5000 characters.',
            'category.max' => 'The category must not exceed 100 characters.',
        ];
    }

    /**
     * Save the FAQ
     */
    public function save(): mixed
    {
        $this->validate();

        DB::beginTransaction();
        try {
            if ($this->isEditing) {
                $faq = Faq::find($this->faqId);
                $changes = [];

                // Track changes for logging
                if ($faq->question !== $this->question) {
                    $changes[] = "Question updated";
                }
                if ($faq->answer !== $this->answer) {
                    $changes[] = "Answer updated";
                }
                if ($faq->category !== $this->category) {
                    $oldCat = $faq->category ?: '(none)';
                    $newCat = $this->category ?: '(none)';
                    $changes[] = "Category: {$oldCat} → {$newCat}";
                }
                if ($faq->display_order !== $this->displayOrder) {
                    $changes[] = "Order: {$faq->display_order} → {$this->displayOrder}";
                }
                if ((bool) $faq->is_active !== $this->isActive) {
                    $oldStatus = $faq->is_active ? 'Active' : 'Inactive';
                    $newStatus = $this->isActive ? 'Active' : 'Inactive';
                    $changes[] = "Status: {$oldStatus} → {$newStatus}";
                }

                $faq->update([
                    'question' => $this->question,
                    'answer' => $this->answer,
                    'category' => $this->category ?: null,
                    'display_order' => $this->displayOrder,
                    'is_active' => $this->isActive,
                ]);

                if (!empty($changes)) {
                    TransactionLogService::log(
                        'faq_updated',
                        "FAQ updated (ID: {$faq->id}): " . implode(', ', $changes)
                    );
                }

                DB::commit();
                Faq::clearCache();
                $this->success('FAQ updated successfully!', position: 'toast-bottom');
            } else {
                $faq = Faq::create([
                    'question' => $this->question,
                    'answer' => $this->answer,
                    'category' => $this->category ?: null,
                    'display_order' => $this->displayOrder,
                    'is_active' => $this->isActive,
                ]);

                TransactionLogService::log(
                    'faq_created',
                    "FAQ created: {$faq->question} (ID: {$faq->id})"
                );

                DB::commit();

                // Send notification to superadmins
                $superadmins = User::where('role_id', User::getRoleId('superadmin'))->get();
                foreach ($superadmins as $admin) {
                    $admin->notify(new SystemSettingsUpdatedNotification(
                        'faq',
                        $faq->question,
                        'created',
                        auth()->user()
                    ));
                }

                Faq::clearCache();
                $this->success('FAQ created successfully!', position: 'toast-bottom');
            }

            return $this->redirect(route('superadmin.faqs'), navigate: true);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('FAQ save failed', ['error' => $e->getMessage()]);
            $this->error('Failed to save FAQ: ' . $e->getMessage(), position: 'toast-bottom');
            return null;
        }
    }

    /**
     * Cancel and go back
     */
    public function cancel(): mixed
    {
        return $this->redirect(route('superadmin.faqs'), navigate: true);
    }

    /**
     * Get filtered categories based on search
     */
    public function getFilteredCategoriesProperty(): array
    {
        $allCategories = Faq::getCategories();

        if (empty($this->categorySearch)) {
            return $allCategories;
        }

        return array_values(array_filter($allCategories, function ($cat) {
            return stripos($cat, $this->categorySearch) !== false;
        }));
    }

    /**
     * Check if search matches any existing category exactly
     */
    public function getIsExactMatchProperty(): bool
    {
        $allCategories = Faq::getCategories();
        return in_array($this->categorySearch, $allCategories, true);
    }

    /**
     * Open category dropdown
     */
    public function openCategoryDropdown(): void
    {
        $this->showCategoryDropdown = true;
        $this->categoryMode = 'selecting';
    }

    /**
     * Close category dropdown
     */
    public function closeCategoryDropdown(): void
    {
        $this->showCategoryDropdown = false;
        $this->categoryMode = 'selecting';
        $this->editingCategory = null;
        $this->editCategoryName = '';
    }

    /**
     * Handle category search input change
     */
    public function updatedCategorySearch(): void
    {
        $this->showCategoryDropdown = true;
        $this->categoryMode = 'selecting';

        // Update the actual category value
        $this->category = !empty($this->categorySearch) ? $this->categorySearch : null;
    }

    /**
     * Select a category from the dropdown
     */
    public function selectCategory(string $categoryName): void
    {
        $this->category = $categoryName;
        $this->categorySearch = $categoryName;
        $this->closeCategoryDropdown();
    }

    /**
     * Create new category (just sets the value, saved with FAQ)
     */
    public function createNewCategory(): void
    {
        $trimmed = trim($this->categorySearch);
        if (empty($trimmed)) {
            $this->error('Category name cannot be empty.', position: 'toast-bottom');
            return;
        }

        if (strlen($trimmed) > 100) {
            $this->error('Category name must not exceed 100 characters.', position: 'toast-bottom');
            return;
        }

        $this->category = $trimmed;
        $this->categorySearch = $trimmed;
        $this->closeCategoryDropdown();
        $this->success('New category set! It will be created when you save the FAQ.', position: 'toast-bottom');
    }

    /**
     * Clear selected category
     */
    public function clearCategory(): void
    {
        $this->category = null;
        $this->categorySearch = '';
        $this->closeCategoryDropdown();
    }

    /**
     * Start editing a category
     */
    public function startEditCategory(string $categoryName): void
    {
        $this->categoryMode = 'editing';
        $this->editingCategory = $categoryName;
        $this->editCategoryName = $categoryName;
    }

    /**
     * Cancel editing category
     */
    public function cancelEditCategory(): void
    {
        $this->categoryMode = 'selecting';
        $this->editingCategory = null;
        $this->editCategoryName = '';
    }

    /**
     * Save edited category (renames across all FAQs)
     */
    public function saveEditCategory(): void
    {
        $newName = trim($this->editCategoryName);

        if (empty($newName)) {
            $this->error('Category name cannot be empty.', position: 'toast-bottom');
            return;
        }

        if (strlen($newName) > 100) {
            $this->error('Category name must not exceed 100 characters.', position: 'toast-bottom');
            return;
        }

        if ($newName === $this->editingCategory) {
            $this->cancelEditCategory();
            return;
        }

        // Check if new name already exists
        $existingCategories = Faq::getCategories();
        if (in_array($newName, $existingCategories)) {
            $this->error('A category with this name already exists.', position: 'toast-bottom');
            return;
        }

        DB::beginTransaction();
        try {
            $affectedCount = Faq::where('category', $this->editingCategory)
                ->update(['category' => $newName]);

            TransactionLogService::log(
                'faq_category_renamed',
                "FAQ category renamed: '{$this->editingCategory}' → '{$newName}' ({$affectedCount} FAQs affected)"
            );

            DB::commit();
            Faq::clearCache();

            // Update current selection if it was the renamed category
            if ($this->category === $this->editingCategory) {
                $this->category = $newName;
                $this->categorySearch = $newName;
            }

            $this->success("Category renamed! {$affectedCount} FAQ(s) updated.", position: 'toast-bottom');
            $this->cancelEditCategory();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Category rename failed', ['error' => $e->getMessage()]);
            $this->error('Failed to rename category.', position: 'toast-bottom');
        }
    }

    /**
     * Delete a category (sets to null for all FAQs using it)
     */
    public function deleteCategory(string $categoryName): void
    {
        DB::beginTransaction();
        try {
            $affectedCount = Faq::where('category', $categoryName)
                ->update(['category' => null]);

            TransactionLogService::log(
                'faq_category_deleted',
                "FAQ category deleted: '{$categoryName}' ({$affectedCount} FAQs moved to uncategorized)"
            );

            DB::commit();
            Faq::clearCache();

            // Clear current selection if it was the deleted category
            if ($this->category === $categoryName) {
                $this->category = null;
                $this->categorySearch = '';
            }

            $this->success("Category deleted! {$affectedCount} FAQ(s) are now uncategorized.", position: 'toast-bottom');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Category delete failed', ['error' => $e->getMessage()]);
            $this->error('Failed to delete category.', position: 'toast-bottom');
        }
    }
}
