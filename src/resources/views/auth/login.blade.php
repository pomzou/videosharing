<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 text-center">
        {{ __('動画共有サービス - ログイン') }}
    </div>

    <!-- セッションステータス -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- メールアドレス -->
        <div>
            <x-input-label for="email" :value="__('メールアドレス')" />
            <x-text-input id="email" class="block mt-1 w-full px-4 py-2 border border-gray-400 rounded-md bg-gray-50 text-gray-700 outline-none"
                type="email" name="email" placeholder="メールアドレスを入力" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- パスワード -->
        <div>
            <x-input-label for="password" :value="__('パスワード')" />
            <x-text-input id="password" class="block mt-1 w-full px-4 py-2 border border-gray-400 rounded-md bg-gray-50 text-gray-700 outline-none"
                type="password" name="password" placeholder="パスワードを入力" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- ログイン情報を記憶 -->
        <div class="flex items-center">
            <input id="remember_me" type="checkbox" class="rounded border-gray-400 text-indigo-600 outline-none" name="remember">
            <label for="remember_me" class="ml-2 text-sm text-gray-700">{{ __('ログイン情報を記憶する') }}</label>
        </div>

        <div class="flex items-center justify-between mt-4">
            @if (Route::has('password.request'))
                <a class="text-sm text-indigo-600 hover:underline" href="{{ route('password.request') }}">
                    {{ __('パスワードを忘れた場合') }}
                </a>
            @endif

            <x-primary-button class="ml-3 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                {{ __('ログイン') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
