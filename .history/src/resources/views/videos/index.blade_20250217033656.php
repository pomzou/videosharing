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
                                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                                    <!-- Video Preview -->
                                    <div class="relative pt-[56.25%]">
                                        <video id="video-{{ $video->id }}"
                                            class="absolute top-0 left-0 w-full h-full object-cover" controls
                                            preload="metadata">
                                            @if ($video->preview_url)
                                                <source src="{{ $video->preview_url }}" type="{{ $video->mime_type }}">
                                            @endif
                                            Your browser does not support the video tag.
                                        </video>
                                    </div>

                                    <!-- Video Info -->
                                    <div class="p-4 space-y-4">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900">{{ $video->title }}</h3>
                                            <p class="mt-1 text-sm text-gray-600">{{ $video->description }}</p>
                                        </div>

                                        <div class="space-y-2">
                                            <div class="flex items-center text-sm text-gray-500">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 7h6m0 10H9m12-7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h14a2 2 0 002-2v-6z" />
                                                </svg>
                                                <span>{{ number_format($video->file_size / 1024 / 1024, 2) }} MB</span>
                                            </div>
                                            <div class="flex items-center text-sm text-gray-500">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span>{{ $video->created_at->diffForHumans() }}</span>
                                            </div>
                                            <div class="flex items-center text-sm text-gray-500">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                </svg>
                                                <span>{{ ucfirst($video->privacy) }}</span>
                                            </div>
                                        </div>

                                        <!-- Video Controls -->
                                        <div class="flex justify-between items-center mt-4 space-x-2">
                                            <div class="flex space-x-2">
                                                <!-- Action Buttons -->
                                                <div class="flex space-x-2">
                                                    <button id="generate-btn-{{ $video->id }}"
                                                        onclick="generateSignedUrl({{ $video->id }})"
                                                        class="px-3 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                        Generate Download Link
                                                    </button>

                                                    <button onclick="openShareModal({{ $video->id }})"
                                                        class="px-3 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                        Share Video
                                                    </button>

                                                    <button onclick="confirmDelete({{ $video->id }})"
                                                        class="px-3 py-2 text-sm font-medium text-red-600 border border-red-600 rounded-md hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                        Delete Video
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <!-- Share Modal -->
                                    <div id="share-modal-{{ $video->id }}"
                                        class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                                        <div
                                            class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                                            <div class="mt-3">
                                                <h3 class="text-lg font-medium text-gray-900">Share Video</h3>
                                                <div class="mt-2">
                                                    <form onsubmit="shareVideo(event, {{ $video->id }})">
                                                        <div class="mt-4">
                                                            <label for="email-{{ $video->id }}"
                                                                class="block text-sm font-medium text-gray-700">Email</label>
                                                            <input type="email" id="email-{{ $video->id }}"
                                                                required
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
                                                                Share
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Shared List Section -->
                                    <div class="mt-4">
                                        <button onclick="toggleSection('shares-{{ $video->id }}')"
                                            class="flex items-center justify-between w-full px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                                            <span>Shared With</span>
                                            <svg id="shares-arrow-{{ $video->id }}"
                                                class="w-5 h-5 transform transition-transform duration-200"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                        <div id="shares-{{ $video->id }}" class="hidden mt-2">
                                            <div class="space-y-2">
                                                @forelse($video->shares->where('is_active', true)->sortByDesc('created_at') as $share)
                                                    <div
                                                        class="flex items-center justify-between p-2 bg-gray-50 rounded-md">
                                                        <div>
                                                            <span
                                                                class="text-sm text-gray-600">{{ $share->email }}</span>
                                                            <span class="text-xs text-gray-500 ml-2">
                                                                Expires: {{ $share->expires_at->format('Y-m-d H:i') }}
                                                            </span>
                                                        </div>
                                                        <button onclick="revokeAccess({{ $share->id }})"
                                                            class="px-2 py-1 text-xs text-red-600 hover:text-red-800 focus:outline-none">
                                                            Revoke Access
                                                        </button>
                                                    </div>
                                                @empty
                                                    <p class="text-sm text-gray-500 p-2">No active shares</p>
                                                @endforelse
                                            </div>
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
                                                <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">
                                                    Permanent Delete</h3>
                                                <div class="mt-2 px-7 py-3">
                                                    <p class="text-sm text-gray-500">
                                                        This will permanently delete this video from both the
                                                        website and storage. This action cannot be undone and the
                                                        video cannot be recovered.
                                                    </p>
                                                </div>
                                                <div class="items-center px-4 py-3">
                                                    <button onclick="deleteVideo({{ $video->id }})"
                                                        class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                                        Permanently Delete
                                                    </button>
                                                    <button
                                                        onclick="document.getElementById('delete-modal-{{ $video->id }}').classList.add('hidden')"
                                                        class="ml-3 px-4 py-2 bg-gray-100 text-gray-700 text-base font-medium rounded-md shadow-sm hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                                        Cancel
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Download Link Section -->
                                    <div class="space-y-3">
                                        @if ($video->url_expires_at && $video->url_expires_at->isFuture())
                                            <div id="timer-{{ $video->id }}" class="text-sm text-gray-600">
                                                <p class="font-medium">Link expires:
                                                    {{ $video->url_expires_at->diffForHumans() }}</p>
                                                <p>Time remaining: <span class="text-indigo-600 font-medium"></span>
                                                </p>
                                            </div>
                                            <div id="url-{{ $video->id }}" class="space-y-2">
                                                <div class="flex items-center gap-2">
                                                    <input type="text" id="url-input-{{ $video->id }}"
                                                        value="{{ $video->current_signed_url }}"
                                                        class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                                        readonly>
                                                    <button onclick="copyUrl({{ $video->id }})"
                                                        class="px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                                        Copy
                                                    </button>
                                                </div>
                                            </div>
                                        @else
                                            <button id="generate-btn-{{ $video->id }}"
                                                onclick="generateSignedUrl({{ $video->id }})"
                                                class="w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                Generate Download Link
                                            </button>
                                            <div id="url-{{ $video->id }}" class="space-y-2 hidden">
                                                <div class="flex items-center gap-2">
                                                    <input type="text" id="url-input-{{ $video->id }}"
                                                        class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                                        readonly>
                                                    <button onclick="copyUrl({{ $video->id }})"
                                                        class="px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                                        Copy
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
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

        function toggleSection(sectionId) {
            const section = document.getElementById(sectionId);
            const arrowId = sectionId.replace('download-', 'download-arrow-'); // 修正
            const arrow = document.getElementById(arrowId);

            section.classList.toggle('hidden');
            if (arrow) {
                arrow.classList.toggle('rotate-180');
            }
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

        function formatTime(seconds) {
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const remainingSeconds = seconds % 60;
            return `${hours}h ${minutes}m ${remainingSeconds}s`;
        }

        function updateTimer(videoId, expiryTime) {
            const timerElement = document.querySelector(`#timer-${videoId} span`);
            if (!timerElement) return;

            const interval = setInterval(() => {
                const now = new Date().getTime();
                const timeLeft = Math.floor((expiryTime - now) / 1000);

                if (timeLeft <= 0) {
                    clearInterval(interval);
                    document.getElementById(`url-${videoId}`).classList.add('hidden');
                    document.getElementById(`timer-${videoId}`).classList.add('hidden');
                    return;
                }

                timerElement.textContent = formatTime(timeLeft);
            }, 1000);
        }

        async function generateSignedUrl(videoId) {
            try {
                const response = await fetch(`/videos/${videoId}/signed-url`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error('Failed to generate URL');
                }

                const data = await response.json();

                if (data.url) {
                    const downloadSection = document.getElementById(`download-${videoId}`);
                    if (!downloadSection) return;

                    downloadSection.innerHTML = `
                    <div id="timer-${videoId}" class="text-sm text-gray-600">
                        <p class="font-medium">Link expires: 24 hours from now</p>
                        <p>Time remaining: <span class="text-indigo-600 font-medium"></span></p>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="text"
                            id="url-input-${videoId}"
                            value="${data.url}"
                            class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            readonly
                        >
                        <button onclick="copyUrl(${videoId})"
                            class="px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                        >
                            Copy
                        </button>
                    </div>
                `;

                    // 表示とタイマー開始
                    downloadSection.classList.remove('hidden');
                    const expiryTime = new Date(data.expires_at).getTime();
                    updateTimer(videoId, expiryTime);
                    showNotification('Download link generated successfully', 'success');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Failed to generate download link', 'error');
            }
        }

        function copyUrl(videoId) {
            const urlInput = document.getElementById(`url-input-${videoId}`);
            if (urlInput) {
                urlInput.select();
                document.execCommand('copy');
                showNotification('URL copied to clipboard', 'success');
            }
        }

        function openShareModal(videoId) {
            const modal = document.getElementById(`share-modal-${videoId}`);
            if (modal) {
                modal.classList.remove('hidden');
            }
        }

        function closeShareModal(videoId) {
            const modal = document.getElementById(`share-modal-${videoId}`);
            if (modal) {
                modal.classList.add('hidden');
            }
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

                if (response.ok) {
                    const element = document.getElementById(`video-${videoId}`).closest('.bg-white.rounded-lg');
                    element.remove();
                    showNotification('Video deleted successfully', 'success');
                } else {
                    throw new Error('Failed to delete video');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Failed to delete video', 'error');
            }
        }

        // ページ読み込み時の初期化
        document.addEventListener('DOMContentLoaded', function() {
            // 既存の有効期限付きURLのタイマーを初期化
            document.querySelectorAll('[id^="timer-"]').forEach(timer => {
                const videoId = timer.id.replace('timer-', '');
                const expiresAt = timer.getAttribute('data-expires-at');
                if (expiresAt) {
                    updateTimer(videoId, new Date(expiresAt).getTime());
                }
            });
        });
    </script>
</x-app-layout>
