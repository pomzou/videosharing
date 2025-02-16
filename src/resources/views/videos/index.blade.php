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
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($videos as $video)
                                    <div class="border rounded-lg overflow-hidden shadow-sm">
                                        <div class="p-4">
                                            <h3 class="text-lg font-semibold mb-2">{{ $video->title }}</h3>
                                            <p class="text-sm text-gray-600 mb-2">{{ $video->description }}</p>
                                            <div class="text-sm text-gray-500 mb-4">
                                                <p>Size: {{ number_format($video->file_size / 1024 / 1024, 2) }} MB</p>
                                                <p>Uploaded: {{ $video->created_at->diffForHumans() }}</p>
                                                <p>Privacy: {{ ucfirst($video->privacy) }}</p>
                                                <div id="timer-{{ $video->id }}" class="text-sm text-gray-500 mt-2 hidden">
                                                    Time remaining: <span class="font-medium">calculating...</span>
                                                </div>
                                            </div>
                                            <button
                                                onclick="generateSignedUrl({{ $video->id }})"
                                                id="generate-btn-{{ $video->id }}"
                                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                                            >
                                                Generate Download Link
                                            </button>
                                            <div id="url-{{ $video->id }}" class="mt-2 hidden">
                                                <p class="text-sm text-gray-600">Link expires in 24 hours:</p>
                                                <div class="flex items-center space-x-2">
                                                    <input type="text"
                                                           id="url-input-{{ $video->id }}"
                                                           class="flex-1 p-2 border rounded"
                                                           readonly>
                                                    <button onclick="copyUrl({{ $video->id }})"
                                                            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                                        Copy
                                                    </button>
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
                    alert('URL copied to clipboard!');
                };
            });
        </script>
    </x-app-layout>
