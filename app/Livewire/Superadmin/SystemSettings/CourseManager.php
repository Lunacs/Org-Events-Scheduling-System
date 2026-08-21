<?php

namespace App\Livewire\Superadmin\SystemSettings;

use App\Models\Course;
use App\Services\TransactionLogService;
use App\Support\Concerns\InteractsWithToasts as Toast;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class CourseManager extends Component
{
    use Toast;

    // Add form data
    public $newCourseCode = '';

    public $newCourseName = '';

    public $newDepartment = '';

    public $addCourseModalOpen = false;

    // Edit form data
    public $editingCourseId = null;

    public $courseCode = '';

    public $courseName = '';

    public $department = '';

    public $editCourseModalOpen = false;

    // Delete data
    public $deletingCourseId = null;

    public $deletingCourseName = '';

    public $hasAssociatedOrganizations = false;

    public $deleteCourseModalOpen = false;

    // Cache duration
    protected $cacheDuration = 10;

    public function render()
    {
        return view('livewire.superadmin.system-settings.course-manager', [
            'courses' => $this->getCourses(),
        ]);
    }

    protected function getCourses()
    {
        return Cache::remember('courses', $this->cacheDuration, function () {
            return Course::orderBy('created_at', 'desc')->get();
        });
    }

    public function addCourse()
    {
        $this->validate([
            'newCourseCode' => 'required|string|max:50|unique:courses,course_code',
            'newCourseName' => 'required|string|max:255',
            'newDepartment' => 'nullable|string|max:255',
        ], [
            'newCourseCode.required' => 'Course code is required.',
            'newCourseCode.string' => 'Course code must be a string.',
            'newCourseCode.unique' => 'Course code must be unique.',
            'newCourseName.required' => 'Course name is required.',
            'newCourseName.string' => 'Course name must be a string.',
            'newDepartment.string' => 'Department must be a string.',
        ]);

        DB::beginTransaction();
        try {
            $course = Course::create([
                'course_code' => $this->newCourseCode,
                'course_name' => $this->newCourseName,
                'department' => $this->newDepartment,
            ]);

            TransactionLogService::logCourseOperation('created', $course);

            DB::commit();

            $this->reset(['newCourseCode', 'newCourseName', 'newDepartment']);
            $this->addCourseModalOpen = false;
            $this->resetErrorBag();
            $this->clearCoursesCache();
            $this->success('Course added successfully!', position: 'toast-top');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Course creation failed', ['error' => $e->getMessage()]);
            $this->error('Failed to create course: '.$e->getMessage(), position: 'toast-top');
        }
    }

    public function resetAddCourseForm()
    {
        $this->reset(['newCourseCode', 'newCourseName', 'newDepartment']);
        $this->addCourseModalOpen = false;
        $this->resetErrorBag();
    }

    public function openEditCourseModal($courseId)
    {
        $course = Course::find($courseId);
        if ($course) {
            $this->editingCourseId = $courseId;
            $this->courseCode = $course->course_code;
            $this->courseName = $course->course_name;
            $this->department = $course->department ?? '';
            $this->editCourseModalOpen = true;
        }
    }

    public function editCourse()
    {
        $this->validate([
            'courseCode' => 'required|string|max:50|unique:courses,course_code,'.$this->editingCourseId.',course_id',
            'courseName' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
        ], messages: [
            'courseCode.required' => 'Course code is required.',
            'courseCode.string' => 'Course code must be a string.',
            'courseCode.unique' => 'Course code must be unique.',
            'courseName.required' => 'Course name is required.',
            'courseName.string' => 'Course name must be a string.',
            'department.string' => 'Department must be a string.',
        ]);

        $course = Course::find($this->editingCourseId);
        if ($course) {
            $origCourseCode = $course->course_code;
            $origCourseName = $course->course_name;
            $origDepartment = $course->department;

            $changes = [];

            if ($origCourseCode !== $this->courseCode) {
                $changes[] = "CourseCode: {$origCourseCode} → {$this->courseCode}";
            }

            if ($origCourseName !== $this->courseName) {
                $changes[] = "CourseName: {$origCourseName} → {$this->courseName}";
            }

            if ($origDepartment !== $this->department) {
                $changes[] = "Department: {$origDepartment} → {$this->department}";
            }

            // Log the course update with changes
            if (! empty($changes)) {
                TransactionLogService::logCourseOperation('updated', $course, $changes);

                $course->update([
                    'course_code' => $this->courseCode,
                    'course_name' => $this->courseName,
                    'department' => $this->department,
                ]);
                $this->success('Course updated successfully!', position: 'toast-top');
            } else {
                $this->info('Nothing updated!', position: 'toast-top');
            }

            $this->reset(['editingCourseId', 'courseCode', 'courseName', 'department']);
            $this->editCourseModalOpen = false;
            $this->resetErrorBag();
            $this->clearCoursesCache();
        }
    }

    public function resetEditCourseForm()
    {
        $this->reset(['editingCourseId', 'courseCode', 'courseName', 'department']);
        $this->editCourseModalOpen = false;
        $this->resetErrorBag();
    }

    public function openDeleteCourseModal($courseId)
    {
        $course = Course::find($courseId);
        if ($course) {
            $this->deletingCourseId = $courseId;
            $this->deletingCourseName = $course->course_name;
            $this->hasAssociatedOrganizations = $course->studentOrganizations()->count() > 0;
            $this->deleteCourseModalOpen = true;
        }
    }

    public function resetDeleteCourseModal()
    {
        $this->reset(['deletingCourseId', 'deletingCourseName', 'hasAssociatedOrganizations']);
        $this->deleteCourseModalOpen = false;
    }

    public function confirmDeleteCourse()
    {
        $course = Course::find($this->deletingCourseId);

        if (! $course) {
            $this->error('Course not found!', position: 'toast-top');

            return;
        }

        // Check if course has organizations
        if ($course->studentOrganizations()->count() > 0) {
            $this->error('Cannot delete course that has associated organizations!', position: 'toast-top');

            return;
        }

        DB::beginTransaction();
        try {
            // Log the course deletion before deleting
            TransactionLogService::logCourseOperation('deleted', $course);

            $course->delete();

            DB::commit();

            $this->reset(['deletingCourseId', 'deletingCourseName', 'hasAssociatedOrganizations']);
            $this->deleteCourseModalOpen = false;
            $this->clearCoursesCache();
            $this->success('Course deleted successfully!', position: 'toast-top');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Course deletion failed', ['error' => $e->getMessage()]);
            $this->error('Failed to delete course: '.$e->getMessage(), position: 'toast-top');
        }
    }

    protected function clearCoursesCache()
    {
        Cache::forget('courses');
        $this->dispatch('cache-cleared');
    }

    #[On('refresh-cache')]
    public function refreshCache()
    {
        Cache::forget('courses');
    }
}
