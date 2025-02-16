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
                                        </div>
                                        <button
                                            onclick="generateSignedUrl({{ $video->id }})"
                                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                                        >
                                            Generate Download Link
                                        </button>
                                        <div id="url-{{ $video->id }}" class="mt-2 hidden">
                                            <p class="text-sm text-gray-600">Link expires in 24 hours:</p>
                                            <a href="#" class="text-blue-500 break-all" target="_blank"></a>
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

    @push('scripts')
    <script>
        function generateSignedUrl(videoId) {
            fetch(`/videos/${videoId}/signed-url`)
                .then(response => response.json())
                .then(data => {
                    if (data.url) {
                        const urlDiv = document.getElementById(`url-${videoId}`);
                        const urlLink = urlDiv.querySelector('a');
                        urlLink.href = data.url;
                        urlLink.textContent = data.url;
                        urlDiv.classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to generate download link');
                });
        }
    </script>
    @endpush
</x-app-layout>
