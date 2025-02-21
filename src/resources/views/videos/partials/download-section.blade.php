@if ($video->url_expires_at && $video->url_expires_at->isFuture())
    <div id="timer-{{ $video->id }}" class="text-sm text-gray-600">
        <div class="flex justify-between items-center">
            <p>Time remaining: <span class="text-indigo-600 font-medium"></span></p>
            <button onclick="revokeAccess({{ $video->id }})"
                class="px-3 py-1 text-sm font-medium text-red-600 border border-red-600 rounded-md hover:bg-red-50 focus:outline-none">
                Revoke URL
            </button>
        </div>
    </div>
    <div id="url-{{ $video->id }}" class="space-y-2">
        <div class="flex items-center gap-2">
            <input type="text" id="url-input-{{ $video->id }}" value="{{ $video->current_signed_url ?? '' }}"
                class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                readonly>
            <button onclick="copyUrl({{ $video->id }})"
                class="px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                Copy
            </button>
        </div>
    </div>
@else
    <button id="generate-btn-{{ $video->id }}" onclick="showExpiryOptions({{ $video->id }})"
        class="w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        {{ $video->url_expires_at && $video->url_expires_at->isPast() ? 'Extend Access' : 'Generate Download Link' }}
    </button>
@endif
