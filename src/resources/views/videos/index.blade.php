<x-alert />
<meta name="csrf-token" content="{{ csrf_token() }}">
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Videos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if ($videos->isEmpty())
                        <p class="text-gray-500">No videos uploaded yet.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach ($videos as $video)
                                <div class="border rounded-lg overflow-hidden shadow-sm"
                                    data-file-id="{{ $video->id }}">
                                    <!-- File Preview -->
                                    <div class="relative pt-[56.25%] bg-gray-100 rounded-lg overflow-hidden">
                                        @switch($video->getFileType())
                                            @case('video')
                                                <video id="video-{{ $video->id }}"
                                                    class="absolute top-0 left-0 w-full h-full object-cover" controls
                                                    preload="metadata"
                                                    onplay="checkExpiry(this, {{ $video->id }}, {{ $video->isOwner() ? 'true' : 'false' }})">
                                                    @if ($video->isOwner() || $video->preview_url)
                                                        <source src="{{ $video->preview_url }}" type="{{ $video->mime_type }}">
                                                    @endif
                                                </video>
                                            @break

                                            @case('image')
                                                <img src="{{ $video->preview_url }}" alt="{{ $video->title }}"
                                                    class="absolute top-0 left-0 w-full h-full object-contain">
                                            @break

                                            @case('audio')
                                                <div
                                                    class="absolute top-0 left-0 w-full h-full flex items-center justify-center bg-gray-800">
                                                    <audio controls class="w-3/4">
                                                        <source src="{{ $video->preview_url }}"
                                                            type="{{ $video->mime_type }}">
                                                    </audio>
                                                </div>
                                            @break

                                            @case('pdf')
                                                <iframe src="{{ $video->preview_url }}"
                                                    class="absolute top-0 left-0 w-full h-full" type="application/pdf"></iframe>
                                            @break

                                            @case('text')
                                                <div class="absolute top-0 left-0 w-full h-full overflow-auto p-4 bg-white">
                                                    @if ($video->isOwner())
                                                        <pre class="text-sm whitespace-pre-wrap">{{ Storage::disk('s3')->get($video->s3_path) }}</pre>
                                                    @endif
                                                </div>
                                            @break

                                            @default
                                                <div
                                                    class="absolute top-0 left-0 w-full h-full flex items-center justify-center">
                                                    <div class="text-center">
                                                        <div class="flex justify-center mb-4">
                                                            @switch($video->getFileType())
                                                                @case('document')
                                                                    <svg class="w-16 h-16 text-gray-400" fill="none"
                                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                                            stroke-width="2"
                                                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                                    </svg>
                                                                @break

                                                                @case('spreadsheet')
                                                                    <svg class="w-16 h-16 text-gray-400" fill="none"
                                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                                            stroke-width="2"
                                                                            d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                                    </svg>
                                                                @break

                                                                @case('archive')
                                                                    <svg class="w-16 h-16 text-gray-400" fill="none"
                                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                                            stroke-width="2"
                                                                            d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                                                    </svg>
                                                                @break

                                                                @default
                                                                    <svg class="w-16 h-16 text-gray-400" fill="none"
                                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                                            stroke-width="2"
                                                                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                                    </svg>
                                                            @endswitch
                                                        </div>
                                                        <p class="text-gray-500">
                                                            {{ strtoupper(pathinfo($video->original_name, PATHINFO_EXTENSION)) }}
                                                            File</p>
                                                        <p class="text-sm text-gray-400">
                                                            {{ number_format($video->file_size / 1024 / 1024, 2) }} MB</p>
                                                    </div>
                                                </div>
                                        @endswitch
                                    </div>

                                    <!-- Video Info -->
                                    <div class="p-4 space-y-4">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900">{{ $video->title }}</h3>
                                            <p class="mt-1 text-sm text-gray-600">{{ $video->description }}</p>
                                        </div>
                                        <!-- 情報リスト -->
                                        <div class="space-y-2">
                                            <ul class="space-y-1 text-sm text-gray-600">
                                                <li class="flex items-center">
                                                    <svg class="w-5 h-5 mr-2 text-gray-500" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 7h6m0 10H9m12-7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h14a2 2 0 002-2v-6z" />
                                                    </svg>
                                                    <span><strong>Type:</strong>
                                                        {{ strtoupper(pathinfo($video->original_name, PATHINFO_EXTENSION)) }}</span>
                                                </li>
                                                <li class="flex items-center">
                                                    <svg class="w-5 h-5 mr-2 text-gray-500" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <span><strong>Size:</strong>
                                                        {{ number_format($video->file_size / 1024 / 1024, 2) }}
                                                        MB</span>
                                                </li>
                                                <li class="flex items-center">
                                                    <svg class="w-5 h-5 mr-2 text-gray-500" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                    </svg>
                                                    <span><strong>Uploaded:</strong>
                                                        {{ $video->created_at->diffForHumans() }}</span>
                                                </li>
                                                <li class="flex items-center">
                                                    <svg class="w-5 h-5 mr-2 text-gray-500" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                    </svg>
                                                    <span><strong>Privacy:</strong>
                                                        {{ ucfirst($video->privacy) }}</span>
                                                </li>
                                            </ul>
                                        </div>

                                        <!-- Video Controls -->
                                        <div class="flex justify-between items-center mt-4 space-x-2">
                                            <button onclick="openShareModal({{ $video->id }})"
                                                class="flex-1 px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                Share Video
                                            </button>
                                            <button onclick="confirmDelete({{ $video->id }})"
                                                class="flex-1 px-4 py-2 text-sm font-medium text-red-600 border border-red-600 rounded-md hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                Delete Video
                                            </button>
                                        </div>

                                        <!-- Share Modal -->
                                        <div id="share-modal-{{ $video->id }}"
                                            class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                                            <div
                                                class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                                                <div class="mt-3">
                                                    <h3 class="text-lg font-medium text-gray-900">Share Video</h3>
                                                    <div class="mt-2">
                                                        <div id="share-form-{{ $video->id }}">
                                                            <form onsubmit="confirmShare(event, {{ $video->id }})">
                                                                <div class="mt-4">
                                                                    <label for="email-{{ $video->id }}"
                                                                        class="block text-sm font-medium text-gray-700">Email</label>
                                                                    <input type="email"
                                                                        id="email-{{ $video->id }}" required
                                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                                </div>
                                                                <div class="mt-4">
                                                                    <label for="expires-{{ $video->id }}"
                                                                        class="block text-sm font-medium text-gray-700">Expires
                                                                        At</label>
                                                                    <input type="datetime-local"
                                                                        id="expires-{{ $video->id }}" required
                                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                                </div>
                                                                <div class="mt-4 flex justify-end space-x-3">
                                                                    <button type="button"
                                                                        onclick="closeShareModal({{ $video->id }})"
                                                                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                                                        Cancel
                                                                    </button>
                                                                    <button type="submit"
                                                                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                                        Next
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </div>

                                                        <div id="share-confirm-{{ $video->id }}" class="hidden">
                                                            <div class="mt-4">
                                                                <h4 class="text-lg font-medium text-gray-900 mb-4">
                                                                    Confirm Share Details</h4>

                                                                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                                                    <div class="mb-2">
                                                                        <span
                                                                            class="text-sm font-medium text-gray-500">Recipient
                                                                            Email:</span>
                                                                        <span id="confirm-email-{{ $video->id }}"
                                                                            class="ml-2 text-gray-900"></span>
                                                                    </div>
                                                                    <div class="mb-2">
                                                                        <span
                                                                            class="text-sm font-medium text-gray-500">Access
                                                                            Expires:</span>
                                                                        <span id="confirm-expires-{{ $video->id }}"
                                                                            class="ml-2 text-gray-900"></span>
                                                                    </div>
                                                                </div>

                                                                <div
                                                                    class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                                                                    <div class="flex">
                                                                        <div class="flex-shrink-0">
                                                                            <svg class="h-5 w-5 text-yellow-400"
                                                                                viewBox="0 0 20 20"
                                                                                fill="currentColor">
                                                                                <path fill-rule="evenodd"
                                                                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                                                    clip-rule="evenodd" />
                                                                            </svg>
                                                                        </div>
                                                                        <div class="ml-3">
                                                                            <p class="text-sm text-yellow-700">
                                                                                Please verify the email address
                                                                                carefully. The file will be accessible
                                                                                only to this email address.
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="flex justify-end space-x-3">
                                                                    <button type="button"
                                                                        onclick="backToShareForm({{ $video->id }})"
                                                                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                                                        Back
                                                                    </button>
                                                                    <button type="button"
                                                                        onclick="executeShare({{ $video->id }})"
                                                                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                                        Confirm & Share
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Shared List with Collapse -->
                                        <div class="mt-4">
                                            <button onclick="toggleShareList({{ $video->id }})"
                                                class="flex items-center justify-between w-full px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none">
                                                <span>Shared With ({{ $video->shares->count() }})</span>
                                                <svg id="share-arrow-{{ $video->id }}"
                                                    class="w-5 h-5 transform transition-transform" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                            <div id="shares-list-{{ $video->id }}" class="mt-2 space-y-2 hidden">
                                                @foreach ($video->shares->sortByDesc('created_at') as $share)
                                                    @if ($share->isEmailShare())
                                                        <div class="flex items-center justify-between p-2 bg-gray-50 rounded-md"
                                                            data-share-id="{{ $share->id }}">
                                                            <div>
                                                                <span
                                                                    class="text-sm text-gray-600">{{ $share->email }}</span>
                                                                <span class="text-xs text-gray-500 ml-2">
                                                                    Expires:
                                                                    {{ $share->expires_at->format('Y-m-d H:i') }}
                                                                </span>
                                                            </div>
                                                            <div class="flex items-center">
                                                                @if (!$share->is_active || $share->isExpired())
                                                                    <span class="text-xs text-red-500">Expired</span>
                                                                @else
                                                                    <button
                                                                        onclick="revokeAccess({{ $share->id }})"
                                                                        class="px-2 py-1 text-xs text-red-600 hover:text-red-800 focus:outline-none">
                                                                        Revoke Access
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>

                                        <!-- Delete Confirmation Modal -->
                                        <div id="delete-modal-{{ $video->id }}"
                                            class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                                            <div
                                                class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                                                <div class="mt-3 text-center">
                                                    <div
                                                        class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                                                        <svg class="h-6 w-6 text-red-600" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                        </svg>
                                                    </div>
                                                    <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Delete
                                                        File</h3>
                                                    <div class="mt-2 px-7 py-3">
                                                        <p class="text-sm text-gray-500">
                                                            Are you sure you want to delete this file? This action
                                                            cannot be undone.
                                                        </p>
                                                    </div>
                                                    <div class="items-center px-4 py-3">
                                                        <button onclick="deleteVideo({{ $video->id }})"
                                                            class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 mr-2">
                                                            Delete
                                                        </button>
                                                        <button
                                                            onclick="document.getElementById('delete-modal-{{ $video->id }}').classList.add('hidden')"
                                                            class="bg-gray-100 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                                            Cancel
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Download Link Section -->
                                        <div class="space-y-3">
                                            <!-- Timer & URL Display (期限内の場合) -->
                                            @if ($video->url_expires_at && $video->url_expires_at->isFuture())
                                                <div id="timer-{{ $video->id }}" class="text-sm text-gray-600">
                                                    <div class="flex justify-between items-center">
                                                        <p>Time remaining: <span
                                                                class="text-indigo-600 font-medium"></span></p>
                                                        <button onclick="revokeAccess({{ $video->id }})"
                                                            class="px-3 py-1 text-sm font-medium text-red-600 border border-red-600 rounded-md hover:bg-red-50 focus:outline-none">
                                                            Revoke URL
                                                        </button>
                                                    </div>
                                                </div>
                                                <div id="url-{{ $video->id }}" class="space-y-2">
                                                    <div class="flex items-center gap-2">
                                                        <input type="text" id="url-input-{{ $video->id }}"
                                                            value="{{ $video->current_signed_url ?? '' }}"
                                                            class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                                            readonly>
                                                        <button onclick="copyUrl({{ $video->id }})"
                                                            class="px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                                            Copy
                                                        </button>
                                                    </div>
                                                </div>
                                            @else
                                                <!-- 期限切れメッセージ (期限切れの場合) -->
                                                @if ($video->url_expires_at && $video->url_expires_at->isPast())
                                                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                                                        <div class="flex">
                                                            <div class="flex-shrink-0">
                                                                <svg class="h-5 w-5 text-yellow-400"
                                                                    viewBox="0 0 20 20" fill="currentColor">
                                                                    <path fill-rule="evenodd"
                                                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                                        clip-rule="evenodd" />
                                                                </svg>
                                                            </div>
                                                            <div class="ml-3">
                                                                <p class="text-sm text-yellow-700">
                                                                    This download link has expired. Would you like to
                                                                    extend it?
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- Generate/Extend Button -->
                                                <button id="generate-btn-{{ $video->id }}"
                                                    onclick="showExpiryOptions({{ $video->id }})"
                                                    class="w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                    {{ $video->url_expires_at && $video->url_expires_at->isPast() ? 'Extend Access' : 'Generate Download Link' }}
                                                </button>
                                            @endif
                                        </div>

                                        <!-- Expiry Options Modal (常に存在) -->
                                        <div id="expiry-modal-{{ $video->id }}"
                                            class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                                            <div
                                                class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                                                <div class="mt-3">
                                                    <h3 class="text-lg font-medium text-gray-900">
                                                        {{ $video->url_expires_at && $video->url_expires_at->isPast() ? 'Extend Access Period' : 'Select Expiry Time' }}
                                                    </h3>
                                                    <div class="mt-4 space-y-3">
                                                        <!-- Preset buttons -->
                                                        <div class="grid grid-cols-2 gap-2">
                                                            <button
                                                                onclick="generateWithPreset({{ $video->id }}, 3)"
                                                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                                                                3 Hours
                                                            </button>
                                                            <button
                                                                onclick="generateWithPreset({{ $video->id }}, 12)"
                                                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                                                                12 Hours
                                                            </button>
                                                            <button
                                                                onclick="generateWithPreset({{ $video->id }}, 24)"
                                                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                                                                1 Day
                                                            </button>
                                                            <button
                                                                onclick="generateWithPreset({{ $video->id }}, 168)"
                                                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                                                                7 Days
                                                            </button>
                                                        </div>

                                                        <!-- Custom datetime picker -->
                                                        <div class="mt-4">
                                                            <label
                                                                class="block text-sm font-medium text-gray-700">Custom
                                                                Expiry Time</label>
                                                            <input type="datetime-local"
                                                                id="custom-expiry-{{ $video->id }}"
                                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                                min="{{ now()->format('Y-m-d\TH:i') }}"
                                                                max="{{ now()->addDays(7)->format('Y-m-d\TH:i') }}">
                                                            <p class="mt-1 text-sm text-gray-500">
                                                                Note: Maximum allowed duration is 7 days. For durations
                                                                over 12 hours, additional authentication will be
                                                                required.
                                                            </p>
                                                            <button onclick="generateWithCustom({{ $video->id }})"
                                                                class="mt-2 w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                                                                Set Custom Time
                                                            </button>
                                                        </div>

                                                        <!-- Cancel button -->
                                                        <button onclick="closeExpiryModal({{ $video->id }})"
                                                            class="mt-4 w-full px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                                                            Cancel
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.generateSignedUrl = async function(videoId) {
                try {
                    const response = await fetch(`/videos/${videoId}/signed-url`);
                    const data = await response.json();

                    if (data.url) {
                        const urlDiv = document.getElementById(`url-${videoId}`);
                        const urlInput = document.getElementById(`url-input-${videoId}`);
                        const timerDiv = document.getElementById(`timer-${videoId}`);
                        const generateBtn = document.getElementById(`generate-btn-${videoId}`);

                        urlInput.value = data.url;
                        urlDiv.classList.remove('hidden');
                        timerDiv.classList.remove('hidden');
                        generateBtn.classList.add('hidden');

                        const expiryTime = new Date(data.expires_at).getTime();
                        updateTimer(videoId, expiryTime);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Failed to generate download link');
                }
            };

            window.formatTime = function(seconds) {
                const hours = Math.floor(seconds / 3600);
                const minutes = Math.floor((seconds % 3600) / 60);
                const remainingSeconds = seconds % 60;
                return `${hours}h ${minutes}m ${remainingSeconds}s`;
            };

            window.updateTimer = function(videoId, expiryTime) {
                const timerElement = document.querySelector(`#timer-${videoId} span`);
                const interval = setInterval(() => {
                    const now = new Date().getTime();
                    const timeLeft = Math.floor((expiryTime - now) / 1000);

                    if (timeLeft <= 0) {
                        clearInterval(interval);
                        document.getElementById(`url-${videoId}`).classList.add('hidden');
                        document.getElementById(`timer-${videoId}`).classList.add('hidden');
                        document.getElementById(`generate-btn-${videoId}`).classList.remove('hidden');
                        return;
                    }

                    timerElement.textContent = formatTime(timeLeft);
                }, 1000);
            };

            window.copyUrl = function(videoId) {
                const urlInput = document.getElementById(`url-input-${videoId}`);
                urlInput.select();
                document.execCommand('copy');
            };
        });

        function confirmDelete(videoId) {
            document.getElementById(`delete-modal-${videoId}`).classList.remove('hidden');
        }

        async function generateSignedUrl(videoId) {
            try {
                const response = await fetch(`/videos/${videoId}/signed-url`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || 'Failed to generate download link');
                }

                if (data.url) {
                    const urlDiv = document.getElementById(`url-${videoId}`);
                    const urlInput = document.getElementById(`url-input-${videoId}`);
                    const timerDiv = document.getElementById(`timer-${videoId}`);
                    const generateBtn = document.getElementById(`generate-btn-${videoId}`);

                    if (urlInput) {
                        urlInput.value = data.url;
                    }
                    if (urlDiv) {
                        urlDiv.classList.remove('hidden');
                    }
                    if (generateBtn) {
                        generateBtn.classList.add('hidden');
                    }
                    if (timerDiv) {
                        timerDiv.classList.remove('hidden');
                        const expiryTime = new Date(data.expires_at).getTime();
                        updateTimer(videoId, expiryTime);
                    }

                    showNotification('Download link generated successfully', 'success');

                    // ページをリロードして最新の状態を表示
                    window.location.reload();
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification(error.message, 'error');
            }
        }

        function showExpiryOptions(videoId) {
            document.getElementById(`expiry-modal-${videoId}`).classList.remove('hidden');
        }

        function closeExpiryModal(videoId) {
            document.getElementById(`expiry-modal-${videoId}`).classList.add('hidden');
        }

        async function generateWithPreset(videoId, hours) {
            const expiryDate = new Date();
            expiryDate.setHours(expiryDate.getHours() + hours);
            await generateSignedUrlWithExpiry(videoId, expiryDate.toISOString());
        }

        async function generateWithCustom(videoId) {
            const customExpiry = document.getElementById(`custom-expiry-${videoId}`).value;
            if (!customExpiry) {
                showNotification('Please select a custom expiry time', 'error');
                return;
            }
            await generateSignedUrlWithExpiry(videoId, new Date(customExpiry).toISOString());
        }

        function generateWithCustom(videoId) {
            const customExpiry = document.getElementById(`custom-expiry-${videoId}`).value;
            if (!customExpiry) {
                showNotification('Please select a custom expiry time', 'error');
                return;
            }

            const expiryDate = new Date(customExpiry);
            const now = new Date();
            const hoursDiff = (expiryDate - now) / (1000 * 60 * 60);

            if (hoursDiff > 168) {
                showNotification('The expiry time cannot exceed 7 days', 'error');
                return;
            }

            generateSignedUrlWithExpiry(videoId, customExpiry);
        }

        async function generateSignedUrlWithExpiry(videoId, expiryTime) {
            try {
                const response = await fetch(`/videos/${videoId}/signed-url`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        expires_at: expiryTime
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || 'Failed to generate download link');
                }

                if (data.url) {
                    closeExpiryModal(videoId);
                    const urlDiv = document.getElementById(`url-${videoId}`);
                    const urlInput = document.getElementById(`url-input-${videoId}`);
                    const timerDiv = document.getElementById(`timer-${videoId}`);
                    const generateBtn = document.getElementById(`generate-btn-${videoId}`);

                    if (urlInput) {
                        urlInput.value = data.url;
                    }
                    if (urlDiv) {
                        urlDiv.classList.remove('hidden');
                    }
                    if (generateBtn) {
                        generateBtn.classList.add('hidden');
                    }
                    if (timerDiv) {
                        timerDiv.classList.remove('hidden');
                        initializeTimer(videoId, new Date(data.expires_at).getTime());
                    }

                    showNotification('Download link generated successfully', 'success');
                    window.location.reload();
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification(error.message, 'error');
            }
        }

        // ページ読み込み時にタイマーを初期化
        document.addEventListener('DOMContentLoaded', function() {
            // すべてのビデオのタイマーを初期化
            @foreach ($videos as $video)
                @if ($video->url_expires_at && $video->url_expires_at->isFuture())
                    initializeTimer(
                        {{ $video->id }},
                        new Date('{{ $video->url_expires_at->toISOString() }}').getTime()
                    );
                @endif
            @endforeach
        });

        function initializeTimer(videoId, expiryTime) {
            const timerElement = document.querySelector(`#timer-${videoId} span`);
            if (!timerElement) return;

            function updateRemainingTime() {
                const now = new Date().getTime();
                const timeLeft = Math.floor((expiryTime - now) / 1000);

                if (timeLeft <= 0) {
                    clearInterval(interval);
                    // 非所有者の場合のみURLをクリア
                    const videoElement = document.getElementById(`video-${videoId}`);
                    if (videoElement && !videoElement.hasAttribute('data-owner')) {
                        videoElement.pause();
                        videoElement.src = '';
                    }
                    document.getElementById(`url-${videoId}`).classList.add('hidden');
                    document.getElementById(`timer-${videoId}`).classList.add('hidden');
                    const generateBtn = document.getElementById(`generate-btn-${videoId}`);
                    if (generateBtn) {
                        generateBtn.classList.remove('hidden');
                        generateBtn.textContent = 'Extend Access';
                    }
                    showNotification('The download link has expired.', 'info');
                    return;
                }

                timerElement.textContent = formatTime(timeLeft);
            }

            updateRemainingTime();
            const interval = setInterval(updateRemainingTime, 1000);
        }

        function formatTime(seconds) {
            const days = Math.floor(seconds / 86400);
            const hours = Math.floor((seconds % 86400) / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const remainingSeconds = seconds % 60;

            let timeString = '';
            if (days > 0) {
                timeString += `${days}d `;
            }
            if (hours > 0 || days > 0) {
                timeString += `${hours}h `;
            }
            timeString += `${minutes}m ${remainingSeconds}s`;

            return timeString;
        }

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-md ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    } text-white shadow-lg transition-opacity duration-500`;
            notification.textContent = message;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 500);
            }, 3000);
        }

        async function deleteVideo(videoId) {
            try {
                const response = await fetch(`/videos/${videoId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error('Failed to delete file');
                }

                const data = await response.json();

                // ファイル要素の削除
                const fileElement = document.querySelector(`[data-file-id="${videoId}"]`);
                if (fileElement) {
                    fileElement.remove();
                }

                // モーダルを閉じる
                const modal = document.getElementById(`delete-modal-${videoId}`);
                if (modal) {
                    modal.classList.add('hidden');
                }

                showNotification('File deleted successfully', 'success');
            } catch (error) {
                console.error('Delete error:', error);
                showNotification('Failed to delete file', 'error');
            }
        }

        function openShareModal(videoId) {
            document.getElementById(`share-modal-${videoId}`).classList.remove('hidden');
        }

        function closeShareModal(videoId) {
            document.getElementById(`share-modal-${videoId}`).classList.add('hidden');
        }

        async function confirmShare(event, videoId) {
            event.preventDefault();

            const email = document.getElementById(`email-${videoId}`).value;
            const expiresAt = document.getElementById(`expires-${videoId}`).value;

            try {
                const response = await fetch(`/videos/${videoId}/share/confirm`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        email,
                        expires_at: expiresAt
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || data.message || 'Failed to validate share details');
                }

                // 確認画面に情報を表示
                document.getElementById(`confirm-email-${videoId}`).textContent = email;
                document.getElementById(`confirm-expires-${videoId}`).textContent = new Date(expiresAt)
                    .toLocaleString();

                // フォームを隠して確認画面を表示
                document.getElementById(`share-form-${videoId}`).classList.add('hidden');
                document.getElementById(`share-confirm-${videoId}`).classList.remove('hidden');

            } catch (error) {
                console.error('Confirmation error:', error);
                showNotification(error.message, 'error');
            }
        }

        function backToShareForm(videoId) {
            document.getElementById(`share-form-${videoId}`).classList.remove('hidden');
            document.getElementById(`share-confirm-${videoId}`).classList.add('hidden');
        }

        async function executeShare(videoId) {
            const email = document.getElementById(`email-${videoId}`).value;
            const expiresAt = document.getElementById(`expires-${videoId}`).value;

            try {
                const response = await fetch(`/videos/${videoId}/share`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        email,
                        expires_at: expiresAt,
                        confirmed: true
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || data.message || 'Failed to share video');
                }

                showNotification('Video shared successfully', 'success');
                closeShareModal(videoId);

                // 共有リストを更新
                window.location.reload();
            } catch (error) {
                console.error('Share error:', error);
                showNotification(error.message, 'error');
            }
        }

        async function revokeAccess(shareId) {
            if (!confirm('Are you sure you want to revoke access?')) {
                return;
            }

            try {
                const response = await fetch(`/shares/${shareId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    const data = await response.json();
                    throw new Error(data.error || 'Failed to revoke access');
                }

                // 成功したら即座にUIを更新
                const shareElement = document.querySelector(`[data-share-id="${shareId}"]`);
                if (shareElement) {
                    const actionDiv = shareElement.querySelector('.flex.items-center');
                    if (actionDiv) {
                        actionDiv.innerHTML = '<span class="text-xs text-red-500">Expired</span>';
                    }
                }

                showNotification('Access revoked successfully', 'success');
            } catch (error) {
                console.error('Revoke error:', error);
                showNotification(error.message, 'error');
            }
        }

        async function confirmShare(event, videoId) {
            event.preventDefault();

            const email = document.getElementById(`email-${videoId}`).value;
            const expiresAt = document.getElementById(`expires-${videoId}`).value;

            // 有効期限のチェック
            const expiryDate = new Date(expiresAt);
            const now = new Date();
            const hoursDiff = (expiryDate - now) / (1000 * 60 * 60);

            if (hoursDiff > 168) {
                showNotification('The expiration time cannot exceed 7 days', 'error');
                return;
            }

            try {
                // 確認画面に情報を表示
                document.getElementById(`confirm-email-${videoId}`).textContent = email;
                document.getElementById(`confirm-expires-${videoId}`).textContent = new Date(expiresAt)
                    .toLocaleString();

                // フォームを隠して確認画面を表示
                document.getElementById(`share-form-${videoId}`).classList.add('hidden');
                document.getElementById(`share-confirm-${videoId}`).classList.remove('hidden');
            } catch (error) {
                console.error('Confirmation error:', error);
                showNotification(error.message, 'error');
            }
        }

        async function executeShare(videoId) {
            const email = document.getElementById(`email-${videoId}`).value;
            const expiresAt = document.getElementById(`expires-${videoId}`).value;

            try {
                const response = await fetch(`/videos/${videoId}/share`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        email,
                        expires_at: expiresAt,
                        confirmed: true
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || data.message || 'Failed to share video');
                }

                showNotification('Video shared successfully. An email has been sent to ' + email, 'success');
                closeShareModal(videoId);
                window.location.reload();
            } catch (error) {
                console.error('Share error:', error);
                showNotification(error.message, 'error');
            }
        }

        function toggleShareList(videoId) {
            const list = document.getElementById(`shares-list-${videoId}`);
            const arrow = document.getElementById(`share-arrow-${videoId}`);

            list.classList.toggle('hidden');
            arrow.classList.toggle('rotate-180');
        }

        function checkExpiry(videoElement, videoId, isOwner) {
            if (isOwner) {
                // 所有者は常に再生可能
                return;
            }

            const timerDiv = document.getElementById(`timer-${videoId}`);
            const hasExpired = timerDiv ? timerDiv.classList.contains('hidden') : true;

            if (hasExpired) {
                videoElement.pause();
                showNotification('This video link has expired. Please generate a new download link.', 'error');
            }
        }

        async function revokeAccess(videoId) {
            if (!confirm(
                    'Are you sure you want to revoke access to this URL? This will invalidate the current download link.'
                )) {
                return;
            }

            try {
                const response = await fetch(`/videos/${videoId}/revoke`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error('Failed to revoke access');
                }

                showNotification('Access revoked successfully', 'success');

                // ページをリロード
                window.location.reload();

            } catch (error) {
                console.error('Error:', error);
                showNotification('Failed to revoke access. Please try again.', 'error');
            }
        }
    </script>
</x-app-layout>
