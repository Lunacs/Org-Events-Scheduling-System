<?php

namespace App\Livewire\Superadmin\SystemSettings;

use App\Models\Course;
use App\Models\Student_Organization;
use App\Models\User;
use App\Services\TransactionLogService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

class OrganizationManager extends Component
{
    use Toast, WithFileUploads;

    // Add form data
    public $newOrgCode = '';

    public $newOrgName = '';

    public $newCourseId = '';

    public $newAdviserName = '';

    public $newOrgStatus = 'active';

    public $newOrgLogo = null;

    public $addOrgModalOpen = false;

    // Edit form data
    public $editingOrgId = null;

    public $orgCode = '';

    public $orgName = '';

    public $courseId = '';

    public $adviserName = '';

    public $orgStatus = 'active';

    public $orgLogo = null;

    public $currentOrgLogo = null;

    public $currentOrgLogoUrl = null;

    public $logoWasDeleted = false;

    public $editOrgModalOpen = false;

    // Delete data
    public $deletingOrgId = null;

    public $deletingOrgName = '';

    public $hasAssociatedUsers = false;

    public $deleteOrgModalOpen = false;

    // Cache duration
    protected $cacheDuration = 10;

    public function render()
    {
        $organizations = $this->getOrganizations();

        return view('livewire.superadmin.system-settings.organization-manager', [
            'academicOrgs' => $organizations->filter(fn($org) => $org->course_id !== null)->values(),
            'nonAcademicOrgs' => $organizations->filter(fn($org) => $org->course_id === null)->values(),
            'allCourses' => $this->getCourses(),
        ]);
    }

    protected function getOrganizations()
    {
        return Cache::remember('organizations', $this->cacheDuration, function () {
            return Student_Organization::with('course')->orderBy('org_name', 'asc')->get();
        });
    }

    protected function getCourses()
    {
        return Cache::remember('courses', $this->cacheDuration, function () {
            return Course::orderBy('created_at', 'desc')->get();
        });
    }

    public function addOrganization()
    {
        $this->validate([
            'newOrgCode' => 'required|string|max:50|unique:student__organizations,org_code',
            'newOrgName' => 'required|string|max:255|unique:student__organizations,org_name',
            'newCourseId' => 'nullable|exists:courses,course_id',
            'newAdviserName' => 'required|string|max:255',
            'newOrgStatus' => 'required|in:active,inactive,suspended',
            'newOrgLogo' => 'nullable|image|mimes:jpg,jpeg,png,gif,svg,webp|max:10240',
        ], [
            'newOrgCode.unique' => 'The organization code has already been taken.',
            'newOrgCode.required' => 'The organization code is required.',
            'newOrgName.required' => 'The organization name is required.',
            'newOrgName.unique' => 'The organization name has already been taken.',
            'newCourseId.exists' => 'The selected course is invalid.',
            'newAdviserName.required' => 'The adviser name is required.',
            'newOrgStatus.required' => 'The organization status is required.',
            'newOrgLogo.image' => 'The logo must be an image file.',
            'newOrgLogo.mimes' => 'The logo must be a file of type: jpg, jpeg, png, gif, svg, webp.',
            'newOrgLogo.max' => 'The logo may not be greater than 10MB.',
        ]);

        $logoPath = null;
        if ($this->newOrgLogo) {
            $logoPath = $this->compressAndStoreLogo($this->newOrgLogo);
        }

        $organization = Student_Organization::create([
            'org_code' => $this->newOrgCode,
            'org_name' => $this->newOrgName,
            'course_id' => $this->newCourseId ?: null,
            'adviser_name' => $this->newAdviserName,
            'status' => $this->newOrgStatus,
            'logo' => $logoPath,
        ]);

        TransactionLogService::logOrgOperation('created', $organization);

        // Send notification to all superadmins
        $superadmins = User::where('role_id', User::getRoleId('superadmin'))->get();
        foreach ($superadmins as $admin) {
            $admin->notify(new \App\Notifications\OrganizationCreatedNotification($organization, auth()->user()));
        }

        $this->reset(['newOrgCode', 'newOrgName', 'newCourseId', 'newAdviserName', 'newOrgStatus', 'newOrgLogo']);
        $this->addOrgModalOpen = false;
        $this->resetErrorBag();
        $this->clearOrganizationsCache();
        $this->success('Student organization added successfully!', position: 'toast-top');
    }

    public function resetAddOrgForm()
    {
        $this->reset(['newOrgCode', 'newOrgName', 'newCourseId', 'newAdviserName', 'newOrgStatus', 'newOrgLogo']);
        $this->addOrgModalOpen = false;
        $this->resetErrorBag();
    }

    public function openEditOrgModal($orgId)
    {
        $organization = Student_Organization::find($orgId);
        if ($organization) {
            $this->editingOrgId = $orgId;
            $this->orgCode = $organization->org_code;
            $this->orgName = $organization->org_name;
            $this->courseId = $organization->course_id;
            $this->adviserName = $organization->adviser_name;
            $this->orgStatus = $organization->status;
            $this->currentOrgLogo = $organization->logo;
            $this->currentOrgLogoUrl = $organization->logo_url;
            $this->orgLogo = null;
            $this->logoWasDeleted = false;
            $this->editOrgModalOpen = true;
        }
    }

    public function editOrganization()
    {
        $this->validate([
            'orgCode' => 'required|string|max:50|unique:student__organizations,org_code,' . $this->editingOrgId . ',org_id',
            'orgName' => 'required|string|max:255',
            'courseId' => 'nullable|exists:courses,course_id',
            'adviserName' => 'required|string|max:255',
            'orgStatus' => 'required|in:active,inactive,suspended',
            'orgLogo' => 'nullable|image|mimes:jpg,jpeg,png,gif,svg,webp|max:10240',
        ]);

        $organization = Student_Organization::find($this->editingOrgId);
        if ($organization) {

            $origOrgCode = $organization->org_code;
            $origOrgName = $organization->org_name;
            $origCourseId = $organization->course_id;
            $origAdviserName = $organization->adviser_name;
            $origStatus = $organization->status;

            $changes = [];

            if ($origOrgCode !== $this->orgCode) {
                $changes[] = "OrgCode: {$origOrgCode} → {$this->orgCode}";
            }

            if ($origOrgName !== $this->orgName) {
                $changes[] = "OrgName: {$origOrgName} → {$this->orgName}";
            }

            if ($origCourseId !== $this->courseId) {
                $origCourseName = $origCourseId ? Course::find($origCourseId)?->course_name : 'None';
                $newCourseName = $this->courseId ? Course::find($this->courseId)?->course_name : 'None';
                $changes[] = "Course: {$origCourseName} → {$newCourseName}";
            }

            if ($origAdviserName !== $this->adviserName) {
                $changes[] = "AdviserName: {$origAdviserName} → {$this->adviserName}";
            }

            if ($origStatus !== $this->orgStatus) {
                $changes[] = "Status: {$origStatus} → {$this->orgStatus}";
            }

            // Handle logo deletion
            if ($this->logoWasDeleted) {
                $changes[] = 'Logo: Deleted';
            }

            // Handle logo upload
            $newLogoPath = null;
            if ($this->orgLogo) {
                // Delete old logo if exists
                if ($organization->logo && Storage::disk(config('filesystems.default'))->exists($organization->logo)) {
                    Storage::disk(config('filesystems.default'))->delete($organization->logo);
                }
                // Store new logo with compression
                $newLogoPath = $this->compressAndStoreLogo($this->orgLogo);
                $changes[] = 'Logo: Updated';
            }

            // Log the organization update with changes
            if (! empty($changes)) {
                TransactionLogService::logOrgOperation('updated', $organization, $changes);

                $updateData = [
                    'org_code' => $this->orgCode,
                    'org_name' => $this->orgName,
                    'course_id' => $this->courseId ?: null,
                    'adviser_name' => $this->adviserName,
                    'status' => $this->orgStatus,
                ];

                if ($newLogoPath) {
                    $updateData['logo'] = $newLogoPath;
                }

                $organization->update($updateData);
                $this->success('Student organization updated successfully!', position: 'toast-top');
            } else {
                $this->info('Nothing updated!', position: 'toast-top');
            }

            $this->reset(['editingOrgId', 'orgCode', 'orgName', 'courseId', 'adviserName', 'orgStatus', 'orgLogo', 'currentOrgLogo', 'currentOrgLogoUrl', 'logoWasDeleted']);
            $this->editOrgModalOpen = false;
            $this->resetErrorBag();
            $this->clearOrganizationsCache();
        }
    }

    public function deleteCurrentOrgLogo()
    {
        $organization = Student_Organization::find($this->editingOrgId);
        if ($organization) {
            if ($organization->logo && Storage::disk(config('filesystems.default'))->exists($organization->logo)) {
                Storage::disk(config('filesystems.default'))->delete($organization->logo);
            }
            $organization->logo = null;
            $organization->save();
            $this->logoWasDeleted = true;
        }
        $this->currentOrgLogo = null;
        $this->currentOrgLogoUrl = null;
    }

    public function resetEditOrgForm()
    {
        $this->reset(['editingOrgId', 'orgCode', 'orgName', 'courseId', 'adviserName', 'orgStatus', 'orgLogo', 'currentOrgLogo', 'currentOrgLogoUrl', 'logoWasDeleted']);
        $this->editOrgModalOpen = false;
        $this->resetErrorBag();
    }

    public function openDeleteOrgModal($orgId)
    {
        $organization = Student_Organization::find($orgId);
        if ($organization) {
            $this->deletingOrgId = $orgId;
            $this->deletingOrgName = $organization->org_name;
            $this->hasAssociatedUsers = $organization->users()->count() > 0;
            $this->deleteOrgModalOpen = true;
        }
    }

    public function resetDeleteOrgModal()
    {
        $this->reset(['deletingOrgId', 'deletingOrgName', 'hasAssociatedUsers']);
        $this->deleteOrgModalOpen = false;
    }

    public function confirmDeleteOrg()
    {
        $organization = Student_Organization::find($this->deletingOrgId);

        if (! $organization) {
            $this->error('Student organization not found!', position: 'toast-top');

            return;
        }

        // Check if organization has users
        if ($organization->users()->count() > 0) {
            $this->error('Cannot delete organization that has associated users!', position: 'toast-top');

            return;
        }

        DB::beginTransaction();
        try {
            // Log the organization deletion before deleting
            TransactionLogService::logOrgOperation('deleted', $organization);

            $organization->delete();

            DB::commit();

            $this->reset(['deletingOrgId', 'deletingOrgName', 'hasAssociatedUsers']);
            $this->deleteOrgModalOpen = false;
            $this->clearOrganizationsCache();
            $this->success('Student organization deleted successfully!', position: 'toast-top');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Organization deletion failed', [
                'org_id' => $this->deletingOrgId,
                'error' => $e->getMessage(),
            ]);
            $this->error('Failed to delete organization: ' . $e->getMessage(), position: 'toast-top');
        }
    }

    protected function clearOrganizationsCache()
    {
        Cache::forget('organizations');
        Cache::forget('courses'); // Also clear courses as org forms may show course data
        $this->dispatch('cache-cleared');
    }

    #[On('refresh-cache')]
    public function refreshCache()
    {
        Cache::forget('organizations');
        Cache::forget('courses');
    }

    /**
     * Compress and store a logo image.
     * Resizes to max 500x500 and converts to WebP at 80% quality.
     */
    protected function compressAndStoreLogo($uploadedFile): string
    {
        // Generate unique filename
        $filename = 'organizations/logos/' . uniqid('org_') . '_' . time() . '.webp';

        // Read, resize, and compress the image
        // Use get() instead of getRealPath() because S3 temp files aren't local
        $image = Image::read($uploadedFile->get());

        // Resize to max 500x500 while maintaining aspect ratio
        $image->scaleDown(500, 500);

        // Encode as WebP with 80% quality for compression
        $encoded = $image->toWebp(80);

        // Store the compressed image
        Storage::disk(config('filesystems.default'))->put($filename, (string) $encoded);

        return $filename;
    }
}
