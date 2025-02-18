<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Upload Video') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('videos.store') }}" enctype="multipart/form-data"
                        class="space-y-6">
                        @csrf

                        <!-- Title -->
                        <div>
                            <x-input-label for="title" :value="__('Title')" />
                            <x-text-input id="title"
                                class="block mt-1 w-full px-4 py-2 border border-gray-400 rounded-md bg-gray-50 text-gray-700 outline-none"
                                type="text" name="title" :value="old('title')" required autofocus />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <!-- Description -->
                        <div>
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description"
                                class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 px-4 py-2 border border-gray-400 rounded-md bg-gray-50 text-gray-700 outline-none"
                                rows="3">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <!-- Video File -->
                        <div>
                            <x-input-label for="video" :value="__('Video File')" />
                            <input id="video" name="video" type="file" accept="video/*"
                                class="block mt-1 w-full" required />
                            <x-input-error :messages="$errors->get('video')" class="mt-2" />
                            <div class="space-y-6 bg-white p-6 rounded-lg shadow">
                                <div class="mb-6">
                                    <h2 class="text-xl font-semibold text-gray-900 mb-2">File Upload</h2>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <h3 class="text-lg font-medium text-gray-900 mb-3">Supported File Types:</h3>

                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                            <!-- 動画 -->
                                            <div class="space-y-2">
                                                <h4 class="font-medium text-indigo-600">Video Files</h4>
                                                <p class="text-sm text-gray-600">MP4, AVI, MOV, MKV, WebM, FLV</p>
                                            </div>

                                            <!-- 画像 -->
                                            <div class="space-y-2">
                                                <h4 class="font-medium text-indigo-600">Image Files</h4>
                                                <p class="text-sm text-gray-600">JPG, JPEG, PNG, GIF, BMP, TIFF, WebP
                                                </p>
                                            </div>

                                            <!-- 音声 -->
                                            <div class="space-y-2">
                                                <h4 class="font-medium text-indigo-600">Audio Files</h4>
                                                <p class="text-sm text-gray-600">MP3, WAV, OGG, FLAC</p>
                                            </div>

                                            <!-- 文書 -->
                                            <div class="space-y-2">
                                                <h4 class="font-medium text-indigo-600">Documents</h4>
                                                <p class="text-sm text-gray-600">PDF, DOC(X), XLS(X), PPT(X), ODT, ODS,
                                                    RTF</p>
                                            </div>

                                            <!-- 圧縮ファイル -->
                                            <div class="space-y-2">
                                                <h4 class="font-medium text-indigo-600">Archive Files</h4>
                                                <p class="text-sm text-gray-600">ZIP, RAR, TAR, GZ, 7Z</p>
                                            </div>

                                            <!-- コード -->
                                            <div class="space-y-2">
                                                <h4 class="font-medium text-indigo-600">Code Files</h4>
                                                <p class="text-sm text-gray-600">HTML, CSS, JS, PHP, PY, JAVA, CPP</p>
                                            </div>
                                        </div>

                                        <div class="mt-4 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                                            <div class="flex">
                                                <div class="flex-shrink-0">
                                                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20"
                                                        fill="currentColor">
                                                        <path fill-rule="evenodd"
                                                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                                <div class="ml-3">
                                                    <h3 class="text-sm font-medium text-yellow-800">Upload Limits</h3>
                                                    <div class="mt-2 text-sm text-yellow-700">
                                                        <ul class="list-disc pl-5 space-y-1">
                                                            <li>Maximum file size: 100MB</li>
                                                            <li>Maximum sharing period: 7 days</li>
                                                            <li>Sharing periods over 12 hours require additional
                                                                authentication</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 既存のフォーム部分 -->
                            </div>
                        </div>

                        <!-- Privacy Settings -->
                        <div>
                            <x-input-label for="privacy" :value="__('Privacy')" />
                            <select id="privacy" name="privacy"
                                class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 px-4 py-2 border border-gray-400 rounded-md bg-gray-50 text-gray-700 outline-none">
                                <option value="public">Public</option>
                                <option value="private">Private</option>
                            </select>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ml-4">
                                {{ __('Upload') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
