<div>
    <x-ui.card shadow class="border-none bg-base-200/50 dark:bg-base-100/50">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-base-content">Academic Courses</h2>
                <p class="text-[10px] sm:text-xs text-base-content/60 mt-0.5">Manage institutional degree
                    programs</p>
            </div>
            <x-ui.button icon="o-plus" label="Add New" class="btn-primary btn-sm shadow-sm w-full sm:w-auto"
                wire:click="$set('addCourseModalOpen', true)" />
        </div>

        @if (count($courses) > 0)
            <div class="space-y-3">
                @foreach ($courses as $course)
                    <div
                        class="group flex items-center justify-between p-3 sm:p-4 bg-base-100 dark:bg-base-200 border border-base-300 dark:border-base-300 rounded-xl hover:border-primary/40  transition duration-200 gap-3">
                        <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                            <div
                                class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-lg bg-primary-50 dark:bg-base-300 text-primary-600 ">
                                <x-ui.icon name="o-academic-cap"
                                    class="w-5 h-5 sm:w-6 sm:h-6 text-base-content/60" />
                            </div>
                            <div class="min-w-0">
                                <p
                                    class="font-semibold text-base-content line-clamp-1 text-sm sm:text-base">
                                    {{ $course->course_name }}</p>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span
                                        class="text-[10px] sm:text-xs font-medium px-1.5 sm:px-2 py-0.5 bg-base-200 dark:bg-base-300 text-base-content/70 rounded whitespace-nowrap">
                                        {{ $course->course_code }}
                                    </span>
                                    @if ($course->department)
                                        <span class="text-xs text-base-content/60">•</span>
                                        <span
                                            class="text-[10px] sm:text-xs text-base-content/60 line-clamp-1">{{ $course->department }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div
                            class="flex gap-1 sm:gap-2 shrink-0 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">
                            <x-ui.button size="xs" icon="o-pencil-square"
                                class="btn-ghost btn-sm text-base-content/60 hover:text-primary-600 "
                                wire:click="openEditCourseModal({{ $course->course_id }})" wire:loading.attr="disabled">
                            </x-ui.button>
                            <x-ui.button size="xs" icon="o-trash"
                                class="btn-ghost btn-sm text-base-content/60 hover:text-error "
                                wire:click="openDeleteCourseModal({{ $course->course_id }})"
                                wire:loading.attr="disabled">
                            </x-ui.button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div
                class="text-center py-12 bg-base-100 dark:bg-base-200 border border-dashed border-base-300 dark:border-base-300 rounded-xl">
                <x-ui.icon name="o-academic-cap" class="w-12 h-12 mx-auto mb-3 text-base-content/40" />
                <p class="text-base-content/60 font-medium">No courses registered yet</p>
                <p class="text-base-content/60 text-sm mt-1">Get started by adding your first course.</p>
            </div>
        @endif
    </x-ui.card>

    {{-- Add Course Modal --}}
    <x-ui.modal-dialog wire:model="addCourseModalOpen" title="Add Course" subtitle="Create a new course" separator
        with-close-button close-on-escape>
        <form wire:submit.prevent="addCourse" class="space-y-4">
            <x-ui.input wire:model="newCourseCode" label="Course Code" placeholder="e.g., BSIT" icon="o-hashtag" />
            <x-ui.input wire:model="newCourseName" label="Course Name" placeholder="Enter course name"
                icon="o-academic-cap" />
            <x-ui.input wire:model="newDepartment" label="Department (Optional)" placeholder="Enter department"
                icon="o-building-office" />
        </form>

        <x-slot:actions>
            <x-ui.button label="Cancel" @click="$wire.addCourseModalOpen = false; $wire.resetAddCourseForm()" />
            <x-ui.button label="Create Course" wire:click="addCourse" class="btn-primary" spinner="addCourse" />
        </x-slot:actions>
    </x-ui.modal-dialog>

    {{-- Edit Course Modal --}}
    @if ($editingCourseId)
        <x-ui.modal-dialog wire:model="editCourseModalOpen" title="Edit Course" subtitle="Update course information"
            separator with-close-button close-on-escape>
            <form wire:submit.prevent="editCourse" class="space-y-4">
                <x-ui.input wire:model="courseCode" label="Course Code" placeholder="e.g., BSIT" icon="o-hashtag" />
                <x-ui.input wire:model="courseName" label="Course Name" placeholder="Enter course name"
                    icon="o-academic-cap" />
                <x-ui.input wire:model="department" label="Department (Optional)" placeholder="Enter department"
                    icon="o-building-office" />
            </form>

            <x-slot:actions>
                <x-ui.button label="Cancel" @click="$wire.editCourseModalOpen = false; $wire.resetEditCourseForm()" />
                <x-ui.button label="Update" wire:click="editCourse" class="btn-primary" spinner="editCourse" />
            </x-slot:actions>
        </x-ui.modal-dialog>
    @endif

    {{-- Delete Course Confirmation Modal --}}
    @if ($deletingCourseName)
        <x-ui.modal-dialog wire:model="deleteCourseModalOpen" title="Delete Course Confirmation"
            subtitle="This action cannot be undone" separator with-close-button close-on-escape>
            <div class="space-y-4">
                <div class="bg-error/10  border border-error/30  rounded-lg p-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-error  mr-2" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                            </path>
                        </svg>
                        <p class="text-error  font-medium">Warning: This action is permanent</p>
                    </div>
                </div>

                <p class="text-base-content/80">
                    Are you sure you want to delete the course
                    <strong class="text-base-content">{{ $deletingCourseName }}</strong>?
                    <br><br>
                    This will permanently remove the course from the system.
                </p>

                @if ($hasAssociatedOrganizations)
                    <div
                        class="bg-warning/10  border border-warning/30  rounded-lg p-4">
                        <p class="text-warning  text-sm">
                            <strong>Cannot delete:</strong> This course has associated student organizations.
                        </p>
                    </div>
                @endif
            </div>

            <x-slot:actions>
                <x-ui.button label="Cancel"
                    @click="$wire.deleteCourseModalOpen = false; $wire.resetDeleteCourseModal()" />
                <x-ui.button label="Delete Course" wire:click="confirmDeleteCourse" class="btn-error"
                    spinner="confirmDeleteCourse" :disabled="$hasAssociatedOrganizations" />
            </x-slot:actions>
        </x-ui.modal-dialog>
    @endif
</div>
