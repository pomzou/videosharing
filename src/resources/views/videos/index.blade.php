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
                    @if($videos->isEmpty())
                        <p class="text-gray-500">No videos uploaded yet.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($videos as $video)
                                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                                    <!-- Video Preview -->
                                    <div class="relative pt-[56.25%]">
                                        <video
                                            id="video-{{ $video->id }}"
                                            class="absolute top-0 left-0 w-full h-full object-cover"
                                            controls
                                            preload="metadata"
                                            poster="{{ asset('images/video-placeholder.png') }}"
                                        >
                                            @if($video->current_signed_url)
                                                <source src="{{ $video->current_signed_url }}" type="{{ $video->mime_type }}">
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
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10H9m12-7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h14a2 2 0 002-2v-6z"/>
                                                </svg>
                                                <span>{{ number_format($video->file_size / 1024 / 1024, 2) }} MB</span>
                                            </div>
                                            <div class="flex items-center text-sm text-gray-500">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <span>{{ $video->created_at->diffForHumans() }}</span>
                                            </div>
                                            <div class="flex items-center text-sm text-gray-500">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                </svg>
                                                <span>{{ ucfirst($video->privacy) }}</span>
                                            </div>
                                        </div>

                                        <!-- Download Link Section -->
                                        <div class="space-y-3">
                                            @if($video->url_expires_at && $video->url_expires_at->isFuture())
                                                <div id="timer-{{ $video->id }}" class="text-sm text-gray-600">
                                                    <p class="font-medium">Link expires: {{ $video->url_expires_at->diffForHumans() }}</p>
                                                    <p>Time remaining: <span class="text-indigo-600 font-medium"></span></p>
                                                </div>
                                                <div id="url-{{ $video->id }}" class="space-y-2">
                                                    <div class="flex items-center gap-2">
                                                        <input
                                                            type="text"
                                                            id="url-input-{{ $video->id }}"
                                                            value="{{ $video->current_signed_url }}"
                                                            class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                                            readonly
                                                        >
                                                        <button
                                                            onclick="copyUrl({{ $video->id }})"
                                                            class="px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                                                        >
                                                            Copy
                                                        </button>
                                                    </div>
                                                </div>
                                            @else
                                                <button
                                                    id="generate-btn-{{ $video->id }}"
                                                    onclick="generateSignedUrl({{ $video->id }})"
                                                    class="w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                                >
                                                    Generate Download Link
                                                </button>
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
                    alert('URL copied to clipboard!');
                };
            });
        </script>
    </x-app-layout>
