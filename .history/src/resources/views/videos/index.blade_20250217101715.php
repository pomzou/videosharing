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
                                                    <div
                                                        class="flex items-center justify-between p-2 bg-gray-50 rounded-md">
                                                        <div>
                                                            <span
                                                                class="text-sm text-gray-600">{{ $share->email }}</span>
                                                            <span class="text-xs text-gray-500 ml-2">
                                                                Expires: {{ $share->expires_at->format('Y-m-d H:i') }}
                                                            </span>
                                                        </div>
                                                        <div class="flex items-center">
                                                            @if ($share->is_active)
                                                                <button onclick="revokeAccess({{ $share->id }})"
                                                                    class="px-2 py-1 text-xs text-red-600 hover:text-red-800 focus:outline-none">
                                                                    Revoke Access
                                                                </button>
                                                            @else
                                                                <span class="text-xs text-gray-500">Access
                                                                    Revoked</span>
                                                            @endif
                                                        </div>
                                                    </div>
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
                                            <!-- Timer -->
                                            <div id="timer-{{ $video->id }}" data-video-id="{{ $video->id }}"
                                                class="text-sm text-gray-600">
                                                <p>Time remaining: <span class="text-indigo-600 font-medium"></span>
                                                </p>
                                            </div>
                                        </div>

                                        <!-- URL Input and Copy Button -->
                                        <div id="url-{{ $video->id }}"
                                            class="space-y-2 {{ !$video->url_expires_at || !$video->url_expires_at->isFuture() ? 'hidden' : '' }}">
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

                                        <!-- Generate Button -->
                                        <button id="generate-btn-{{ $video->id }}"
                                            onclick="generateSignedUrl({{ $video->id }})"
                                            class="w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 {{ $video->url_expires_at && $video->url_expires_at->isFuture() ? 'hidden' : '' }}">
                                            Generate Download Link
                                        </button>
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
                        localStorage.setItem(`video-${videoId}-expires_at`, expiryTime); // タイマーの期限を保存
                        updateTimer(videoId, expiryTime); // タイマーを更新
                    }

                    showNotification('Download link generated successfully', 'success');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification(error.message, 'error');
            }
        }

        function updateTimer(videoId, expiryTime) {
            const timerDiv = document.getElementById(`timer-${videoId}`);
            const remainingTimeElem = document.getElementById(`time-remaining-${videoId}`);

            const updateTimerDisplay = () => {
                const now = new Date();
                const remainingTime = expiryTime - now.getTime();

                if (remainingTime > 0) {
                    const seconds = Math.floor((remainingTime / 1000) % 60);
                    const minutes = Math.floor((remainingTime / 1000 / 60) % 60);
                    const hours = Math.floor((remainingTime / 1000 / 60 / 60) % 24);
                    const days = Math.floor(remainingTime / (1000 * 60 * 60 * 24));

                    remainingTimeElem.textContent = `${days}d ${hours}h ${minutes}m ${seconds}s`;
                } else {
                    remainingTimeElem.textContent = 'Expired';
                    localStorage.removeItem(`video-${videoId}-expires_at`); // 期限が切れたらローカルストレージから削除
                }
            };

            setInterval(updateTimerDisplay, 1000); // 毎秒更新
            updateTimerDisplay(); // 最初に1回表示
        }

        // ページが読み込まれたときにタイマーを再設定
        window.onload = function() {
            const timerDiv = document.querySelector('.text-sm.text-gray-600');
            const videoId = timerDiv ? timerDiv.getAttribute('data-video-id') : null;

            if (videoId) {
                const storedExpiryTime = localStorage.getItem(`video-${videoId}-expires_at`);
                if (storedExpiryTime) {
                    updateTimer(videoId, parseInt(storedExpiryTime)); // 保存された期限を基にタイマーを更新
                }
            }
        };

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

                if (response.ok) {
                    // 成功時の処理
                    const element = document.getElementById(`video-${videoId}`).closest('.bg-white.rounded-lg');
                    element.remove();
                    // 削除成功メッセージを表示
                    showNotification('Video deleted successfully', 'success');
                } else {
                    throw new Error('Failed to delete video');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Failed to delete video', 'error');
            }
        }

        function openShareModal(videoId) {
            document.getElementById(`share-modal-${videoId}`).classList.remove('hidden');
        }

        function closeShareModal(videoId) {
            document.getElementById(`share-modal-${videoId}`).classList.add('hidden');
        }

        async function shareVideo(event, videoId) {
            event.preventDefault();

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
                        expires_at: expiresAt
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

                console.log('Share successful:', data);
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

                if (response.ok) {
                    showNotification('Access revoked successfully', 'success');
                    // ページをリロードするか、DOMを更新
                    window.location.reload();
                } else {
                    throw new Error('Failed to revoke access');
                }
            } catch (error) {
                showNotification(error.message, 'error');
            }
        }

        function toggleShareList(videoId) {
            const list = document.getElementById(`shares-list-${videoId}`);
            const arrow = document.getElementById(`share-arrow-${videoId}`);

            list.classList.toggle('hidden');
            arrow.classList.toggle('rotate-180');
        }
    </script>
</x-app-layout>
