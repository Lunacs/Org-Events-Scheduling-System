<x-layouts.gso-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Communication Center') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="communicationCenter()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Communication Options -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Communication Options
                            </h3>

                            <div class="space-y-3">
                                <button class="btn btn-emerald w-full justify-start" @click="openNewMessage('osa')"
                                    :class="activeTab === 'osa' ? 'btn-emerald' : 'btn-outline btn-emerald'">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                        </path>
                                    </svg>
                                    Send to OSA
                                </button>

                                <button class="btn btn-outline btn-emerald w-full justify-start"
                                    @click="openNewMessage('student_org')"
                                    :class="activeTab === 'student_org' ? 'btn-emerald' : 'btn-outline btn-emerald'">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                        </path>
                                    </svg>
                                    Send to Student Org
                                </button>

                                <button class="btn btn-outline btn-emerald w-full justify-start" @click="viewMessages()"
                                    :class="activeTab === 'messages' ? 'btn-emerald' : 'btn-outline btn-emerald'">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                        </path>
                                    </svg>
                                    Message History
                                </button>

                                <button class="btn btn-outline btn-emerald w-full justify-start"
                                    @click="viewTemplates()"
                                    :class="activeTab === 'templates' ? 'btn-emerald' : 'btn-outline btn-emerald'">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                    Message Templates
                                </button>
                            </div>

                            <!-- Quick Stats -->
                            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Quick Stats</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Messages Sent Today</span>
                                        <span class="font-medium text-emerald-600" x-text="todayMessageCount"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Pending Responses</span>
                                        <span class="font-medium text-yellow-600" x-text="pendingResponses"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Active Threads</span>
                                        <span class="font-medium text-blue-600" x-text="activeThreads"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <!-- New Message Form -->
                            <div x-show="activeTab === 'osa' || activeTab === 'student_org'">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4"
                                    x-text="activeTab === 'osa' ? 'Send Message to OSA' : 'Send Message to Student Organization'">
                                </h3>

                                <form @submit.prevent="sendMessage()" class="space-y-4">
                                    <!-- Recipient Selection -->
                                    <div x-show="activeTab === 'student_org'">
                                        <x-mary-select label="Select Organization" :options="[
                                            ['id' => 'student_council', 'name' => 'Student Council'],
                                            ['id' => 'science_club', 'name' => 'Science Club'],
                                            ['id' => 'cultural_society', 'name' => 'Cultural Society'],
                                            ['id' => 'drama_club', 'name' => 'Drama Club'],
                                            ['id' => 'sports_committee', 'name' => 'Sports Committee'],
                                        ]" option-value="id"
                                            option-label="name" x-model="newMessage.recipient" class="select-emerald" />
                                    </div>

                                    <!-- Related Ticket -->
                                    <div>
                                        <x-mary-select label="Related Ticket (Optional)" :options="[
                                            ['id' => '', 'name' => 'No related ticket'],
                                            ['id' => 'TKT-001', 'name' => 'TKT-001 - Leadership Summit'],
                                            ['id' => 'TKT-002', 'name' => 'TKT-002 - Science Fair'],
                                            ['id' => 'TKT-003', 'name' => 'TKT-003 - Cultural Night'],
                                        ]"
                                            option-value="id" option-label="name" x-model="newMessage.ticketId"
                                            class="select-emerald" />
                                    </div>

                                    <!-- Priority -->
                                    <div>
                                        <x-mary-select label="Priority" :options="[
                                            ['id' => 'low', 'name' => 'Low'],
                                            ['id' => 'medium', 'name' => 'Medium'],
                                            ['id' => 'high', 'name' => 'High'],
                                            ['id' => 'urgent', 'name' => 'Urgent'],
                                        ]" option-value="id"
                                            option-label="name" x-model="newMessage.priority" class="select-emerald" />
                                    </div>

                                    <!-- Subject -->
                                    <div>
                                        <x-mary-input label="Subject" placeholder="Enter message subject..."
                                            x-model="newMessage.subject" class="input-emerald" />
                                    </div>

                                    <!-- Message Body -->
                                    <div>
                                        <x-mary-textarea label="Message" placeholder="Type your message here..."
                                            x-model="newMessage.body" rows="6" class="textarea-emerald" />
                                    </div>

                                    <!-- Template Selector -->
                                    <div>
                                        <x-mary-select label="Use Template (Optional)" :options="[
                                            ['id' => '', 'name' => 'No template'],
                                            ['id' => 'approval_request', 'name' => 'Approval Request'],
                                            ['id' => 'rejection_notice', 'name' => 'Rejection Notice'],
                                            ['id' => 'more_info_needed', 'name' => 'More Information Needed'],
                                            ['id' => 'deadline_reminder', 'name' => 'Deadline Reminder'],
                                        ]"
                                            option-value="id" option-label="name" x-model="selectedTemplate"
                                            @change="applyTemplate()" class="select-emerald" />
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="flex space-x-3">
                                        <button type="submit" class="btn btn-emerald">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                            </svg>
                                            Send Message
                                        </button>
                                        <button type="button" class="btn btn-outline" @click="saveDraft()">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10">
                                                </path>
                                            </svg>
                                            Save as Draft
                                        </button>
                                        <button type="button" class="btn btn-ghost" @click="clearMessage()">
                                            Clear
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Message History -->
                            <div x-show="activeTab === 'messages'">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Message History
                                    </h3>
                                    <div class="flex space-x-2">
                                        <x-mary-select :options="[
                                            ['id' => 'all', 'name' => 'All Messages'],
                                            ['id' => 'sent', 'name' => 'Sent'],
                                            ['id' => 'received', 'name' => 'Received'],
                                        ]" option-value="id" option-label="name"
                                            x-model="messageFilter" @change="filterMessages()"
                                            class="select-emerald select-sm" />
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <template x-for="message in filteredMessages" :key="message.id">
                                        <div class="border rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer"
                                            @click="viewMessage(message)">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <div class="flex items-center space-x-3 mb-2">
                                                        <h4 class="font-medium text-gray-900 dark:text-gray-100"
                                                            x-text="message.subject"></h4>
                                                        <div class="badge" :class="getPriorityClass(message.priority)"
                                                            x-text="message.priority"></div>
                                                        <div class="badge"
                                                            :class="message.type === 'sent' ? 'badge-info' :
                                                                'badge-success'"
                                                            x-text="message.type"></div>
                                                    </div>

                                                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                                        <span
                                                            x-text="message.type === 'sent' ? 'To: ' + message.recipient : 'From: ' + message.sender"></span>
                                                        <span class="mx-2">•</span>
                                                        <span x-text="message.date"></span>
                                                    </div>

                                                    <p class="text-sm text-gray-700 dark:text-gray-300 line-clamp-2"
                                                        x-text="message.preview"></p>
                                                </div>

                                                <div class="ml-4">
                                                    <template x-if="message.hasReply">
                                                        <div class="badge badge-outline badge-sm">Has Reply</div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Templates -->
                            <div x-show="activeTab === 'templates'">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Message
                                        Templates</h3>
                                    <button class="btn btn-emerald btn-sm" @click="createTemplate()">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        New Template
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <template x-for="template in messageTemplates" :key="template.id">
                                        <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                                            <div class="flex justify-between items-start mb-3">
                                                <h4 class="font-medium text-gray-900 dark:text-gray-100"
                                                    x-text="template.name"></h4>
                                                <div class="dropdown dropdown-end">
                                                    <label tabindex="0" class="btn btn-ghost btn-sm btn-circle">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z">
                                                            </path>
                                                        </svg>
                                                    </label>
                                                    <ul tabindex="0"
                                                        class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-52">
                                                        <li><a @click="useTemplate(template)">Use Template</a></li>
                                                        <li><a @click="editTemplate(template)">Edit</a></li>
                                                        <li><a @click="deleteTemplate(template)"
                                                                class="text-red-600">Delete</a></li>
                                                    </ul>
                                                </div>
                                            </div>

                                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3"
                                                x-text="template.description"></p>
                                            <p class="text-sm text-gray-700 dark:text-gray-300 line-clamp-3"
                                                x-text="template.content"></p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Message View Modal -->
        <div x-show="showMessageModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
            x-transition:enter.duration.0ms x-transition:leave.duration.0ms>
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    @click="showMessageModal = false" x-transition:enter.opacity.duration.0ms
                    x-transition:leave.opacity.duration.0ms>
                </div>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full"
                    x-transition:enter.duration.0ms x-transition:enter.scale.origin.bottom
                    x-transition:leave.duration.0ms x-transition:leave.scale.origin.bottom>
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100"
                                x-text="selectedMessage?.subject"></h3>
                            <button @click="showMessageModal = false" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <template x-if="selectedMessage">
                            <div>
                                <div class="border-b pb-4 mb-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                <span
                                                    x-text="selectedMessage.type === 'sent' ? 'To: ' + selectedMessage.recipient : 'From: ' + selectedMessage.sender"></span>
                                            </p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400"
                                                x-text="selectedMessage.date"></p>
                                        </div>
                                        <div class="flex space-x-2">
                                            <div class="badge" :class="getPriorityClass(selectedMessage.priority)"
                                                x-text="selectedMessage.priority"></div>
                                            <div class="badge"
                                                :class="selectedMessage.type === 'sent' ? 'badge-info' : 'badge-success'"
                                                x-text="selectedMessage.type"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="prose dark:prose-invert max-w-none mb-4">
                                    <p x-text="selectedMessage.content"></p>
                                </div>

                                <template x-if="selectedMessage.type === 'received'">
                                    <div class="flex space-x-3">
                                        <button class="btn btn-emerald" @click="replyToMessage(selectedMessage)">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                            </svg>
                                            Reply
                                        </button>
                                        <button class="btn btn-outline" @click="forwardMessage(selectedMessage)">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                            </svg>
                                            Forward
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function communicationCenter() {
            return {
                activeTab: 'osa',
                showMessageModal: false,
                selectedMessage: null,
                messageFilter: 'all',
                selectedTemplate: '',

                newMessage: {
                    recipient: '',
                    ticketId: '',
                    priority: 'medium',
                    subject: '',
                    body: ''
                },

                messages: [{
                        id: 1,
                        type: 'sent',
                        recipient: 'OSA Admin',
                        subject: 'Equipment approval needed for Science Fair',
                        preview: 'We need additional equipment approval for the upcoming Science Fair event...',
                        content: 'We need additional equipment approval for the upcoming Science Fair event. The requested items include projectors, extension cords, and display tables. Please review at your earliest convenience.',
                        priority: 'high',
                        date: '2024-10-28 14:30',
                        hasReply: true
                    },
                    {
                        id: 2,
                        type: 'received',
                        sender: 'Student Council',
                        subject: 'Venue booking clarification',
                        preview: 'Could you please clarify the capacity limitations for the Main Auditorium...',
                        content: 'Could you please clarify the capacity limitations for the Main Auditorium? We are planning for 200 attendees but want to ensure we meet all safety requirements.',
                        priority: 'medium',
                        date: '2024-10-27 10:15',
                        hasReply: false
                    }
                ],

                messageTemplates: [{
                        id: 'approval_request',
                        name: 'Approval Request',
                        description: 'Request approval from OSA for venue/equipment',
                        content: 'We are requesting approval for [DETAILS]. The event is scheduled for [DATE] and requires [REQUIREMENTS]. Please review and approve at your earliest convenience.'
                    },
                    {
                        id: 'rejection_notice',
                        name: 'Rejection Notice',
                        description: 'Notify rejection with reason',
                        content: 'We regret to inform you that your request for [DETAILS] has been rejected due to [REASON]. Please contact us if you need further clarification or wish to submit a revised request.'
                    },
                    {
                        id: 'more_info_needed',
                        name: 'More Information Needed',
                        description: 'Request additional information',
                        content: 'We need additional information regarding your request for [DETAILS]. Please provide [SPECIFIC_REQUIREMENTS] to proceed with the approval process.'
                    }
                ],

                get todayMessageCount() {
                    return 3;
                },

                get pendingResponses() {
                    return this.messages.filter(m => m.type === 'received' && !m.hasReply).length;
                },

                get activeThreads() {
                    return 2;
                },

                get filteredMessages() {
                    if (this.messageFilter === 'all') return this.messages;
                    return this.messages.filter(m => m.type === this.messageFilter);
                },

                openNewMessage(type) {
                    this.activeTab = type;
                    this.clearMessage();
                },

                viewMessages() {
                    this.activeTab = 'messages';
                },

                viewTemplates() {
                    this.activeTab = 'templates';
                },

                sendMessage() {
                    // Here you would make an API call to send the message
                    alert(`Message sent to ${this.activeTab === 'osa' ? 'OSA' : this.newMessage.recipient}!`);
                    this.clearMessage();
                },

                saveDraft() {
                    alert('Message saved as draft!');
                },

                clearMessage() {
                    this.newMessage = {
                        recipient: '',
                        ticketId: '',
                        priority: 'medium',
                        subject: '',
                        body: ''
                    };
                    this.selectedTemplate = '';
                },

                applyTemplate() {
                    if (!this.selectedTemplate) return;

                    const template = this.messageTemplates.find(t => t.id === this.selectedTemplate);
                    if (template) {
                        this.newMessage.body = template.content;
                    }
                },

                filterMessages() {
                    // Messages are filtered via computed property
                },

                viewMessage(message) {
                    this.selectedMessage = message;
                    this.showMessageModal = true;
                },

                replyToMessage(message) {
                    this.showMessageModal = false;
                    this.activeTab = 'student_org';
                    this.newMessage.subject = 'Re: ' + message.subject;
                    this.newMessage.recipient = message.sender.toLowerCase().replace(' ', '_');
                },

                forwardMessage(message) {
                    this.showMessageModal = false;
                    this.activeTab = 'osa';
                    this.newMessage.subject = 'Fwd: ' + message.subject;
                    this.newMessage.body =
                        `\n\n--- Forwarded Message ---\nFrom: ${message.sender}\nSubject: ${message.subject}\n\n${message.content}`;
                },

                createTemplate() {
                    alert('Create new template functionality would be implemented here');
                },

                useTemplate(template) {
                    this.selectedTemplate = template.id;
                    this.applyTemplate();
                    this.activeTab = 'osa';
                },

                editTemplate(template) {
                    alert(`Edit template: ${template.name}`);
                },

                deleteTemplate(template) {
                    if (confirm(`Are you sure you want to delete the template "${template.name}"?`)) {
                        alert(`Template "${template.name}" deleted!`);
                    }
                },

                getPriorityClass(priority) {
                    const classes = {
                        'low': 'badge-success',
                        'medium': 'badge-warning',
                        'high': 'badge-error',
                        'urgent': 'badge-error'
                    };
                    return classes[priority] || 'badge-ghost';
                }
            }
        }
    </script>
</x-layouts.gso-layout>
