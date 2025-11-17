<div>
    <x-mary-card>
        <div class="flex justify-between mb-5">
            <h2 class="font-bold text-xl">Courses</h2>
            <x-mary-button icon="o-plus" class="btn-accent"
                wire:click="$set('addCourseModalOpen', true)">Add</x-mary-button>
        </div>

        @if (count($courses) > 0)
            <ul class="space-y-2">
                @foreach ($courses as $course)
                    <li class="flex items-center justify-between p-2 border rounded-lg">
                        <div>
                            <p class="font-medium">{{ $course->course_name }}</p>
                            <p class="text-xs text-gray-500">{{ $course->course_code }}
                                @if ($course->department)
                                    • {{ $course->department }}
                                @endif
                            </p>
                        </div>
                        <div class="flex gap-1">
                            <x-mary-button size="xs" icon="o-pencil-square" class="btn-ghost"
                                wire:click="openEditCourseModal({{ $course->course_id }})"
                                wire:loading.attr="disabled">
                            </x-mary-button>
                            <x-mary-button size="xs" icon="o-trash" class="btn-ghost text-red-600"
                                wire:click="openDeleteCourseModal({{ $course->course_id }})"
                                wire:loading.attr="disabled">
                            </x-mary-button>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="text-center py-4 text-gray-500">
                <x-mary-icon name="o-academic-cap" class="w-8 h-8 mx-auto mb-2" />
                <p>No courses found</p>
            </div>
        @endif
    </x-mary-card>

    {{-- Add Course Modal --}}
    <x-mary-modal wire:model="addCourseModalOpen" title="Add Course" subtitle="Create a new course" separator
        with-close-button close-on-escape>
        <form wire:submit.prevent="addCourse" class="space-y-4">
            <x-mary-input wire:model="newCourseCode" label="Course Code" placeholder="e.g., BSIT"
                icon="o-hashtag" />
            <x-mary-input wire:model="newCourseName" label="Course Name" placeholder="Enter course name"
                icon="o-academic-cap" />
            <x-mary-input wire:model="newDepartment" label="Department (Optional)" placeholder="Enter department"
                icon="o-building-office" />
        </form>

        <x-slot:actions>
            <x-mary-button label="Cancel" @click="$wire.addCourseModalOpen = false; $wire.resetAddCourseForm()" />
            <x-mary-button label="Create Course" wire:click="addCourse" class="btn-primary" spinner="addCourse" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- Edit Course Modal --}}
    @if ($editingCourseId)
        <x-mary-modal wire:model="editCourseModalOpen" title="Edit Course" subtitle="Update course information"
            separator with-close-button close-on-escape>
            <form wire:submit.prevent="editCourse" class="space-y-4">
                <x-mary-input wire:model="courseCode" label="Course Code" placeholder="e.g., BSIT"
                    icon="o-hashtag" />
                <x-mary-input wire:model="courseName" label="Course Name" placeholder="Enter course name"
                    icon="o-academic-cap" />
                <x-mary-input wire:model="department" label="Department (Optional)" placeholder="Enter department"
                    icon="o-building-office" />
            </form>

            <x-slot:actions>
                <x-mary-button label="Cancel"
                    @click="$wire.editCourseModalOpen = false; $wire.resetEditCourseForm()" />
                <x-mary-button label="Update" wire:click="editCourse" class="btn-primary" spinner="editCourse" />
            </x-slot:actions>
        </x-mary-modal>
    @endif

    {{-- Delete Course Confirmation Modal --}}
    @if ($deletingCourseName)
        <x-mary-modal wire:model="deleteCourseModalOpen" title="Delete Course Confirmation"
            subtitle="This action cannot be undone" separator with-close-button close-on-escape>
            <div class="space-y-4">
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                            </path>
                        </svg>
                        <p class="text-red-800 font-medium">Warning: This action is permanent</p>
                    </div>
                </div>

                <p class="text-gray-700">
                    Are you sure you want to delete the course
                    <strong class="text-gray-900">{{ $deletingCourseName }}</strong>?
                    <br><br>
                    This will permanently remove the course from the system.
                </p>

                @if ($hasAssociatedOrganizations)
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <p class="text-yellow-800 text-sm">
                            <strong>Cannot delete:</strong> This course has associated student organizations.
                        </p>
                    </div>
                @endif
            </div>

            <x-slot:actions>
                <x-mary-button label="Cancel"
                    @click="$wire.deleteCourseModalOpen = false; $wire.resetDeleteCourseModal()" />
                <x-mary-button label="Delete Course" wire:click="confirmDeleteCourse" class="btn-error"
                    spinner="confirmDeleteCourse" :disabled="$hasAssociatedOrganizations" />
            </x-slot:actions>
        </x-mary-modal>
    @endif
</div>

