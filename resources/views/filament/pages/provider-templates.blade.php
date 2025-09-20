<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                SMS Templates for {{ $provider->display_name }}
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Manage SMS templates for this provider
            </p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('filament.admin.resources.sms-templates.create') }}?provider_id={{ $provider->id }}" 
               class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Template
            </a>
        </div>
    </div>

    @if($templates->count() > 0)
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($templates as $template)
                    <li class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-3">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ $template->name }}
                                    </h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($template->status === 'approved') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                        @elseif($template->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100
                                        @else bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                                        @endif">
                                        {{ ucfirst($template->status) }}
                                    </span>
                                    @if($template->provider_template_id)
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                            ID: {{ $template->provider_template_id }}
                                        </span>
                                    @endif
                                </div>
                                <div class="mt-1">
                                    <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2">
                                        {{ $template->content }}
                                    </p>
                                </div>
                                <div class="mt-2 flex items-center text-xs text-gray-500 dark:text-gray-400 space-x-4">
                                    <span>Created: {{ $template->created_at->format('M j, Y') }}</span>
                                    @if($template->approved_at)
                                        <span>Approved: {{ $template->approved_at->format('M j, Y') }}</span>
                                    @endif
                                    @if($template->rejected_at)
                                        <span>Rejected: {{ $template->rejected_at->format('M j, Y') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('filament.admin.resources.sms-templates.view', $template) }}" 
                                   class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                                <a href="{{ route('filament.admin.resources.sms-templates.edit', $template) }}" 
                                   class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No templates</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Get started by creating a new SMS template.
            </p>
            <div class="mt-6">
                <a href="{{ route('filament.admin.resources.sms-templates.create') }}?provider_id={{ $provider->id }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Template
                </a>
            </div>
        </div>
    @endif
</div>
