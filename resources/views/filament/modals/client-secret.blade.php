<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Client ID
        </label>
        <div class="p-3 bg-gray-100 dark:bg-gray-800 rounded-md font-mono text-sm">
            {{ $clientId }}
        </div>
    </div>
    
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Client Secret
        </label>
        <div class="p-3 bg-gray-100 dark:bg-gray-800 rounded-md font-mono text-sm break-all">
            {{ $clientSecret }}
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            ⚠️ Keep this secret secure and never share it publicly
        </p>
    </div>
</div>
