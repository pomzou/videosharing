<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 text-center">
        {{ __('新規登録') }}
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <!-- 名前 -->
        <div>
            <x-input-label for="name" :value="__('名前')" />
            <x-text-input id="name" class="block mt-1 w-full px-4 py-2 border border-gray-400 rounded-md bg-gray-50 text-gray-700 outline-none"
                type="text" name="name" placeholder="名前を入力" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- メールアドレス -->
        <div>
            <x-input-label for="email" :value="__('メールアドレス')" />
            <x-text-input id="email" class="block mt-1 w-full px-4 py-2 border border-gray-400 rounded-md bg-gray-50 text-gray-700 outline-none"
                type="email" name="email" placeholder="メールアドレスを入力" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- パスワード -->
        <div>
            <x-input-label for="password" :value="__('パスワード')" />
            <x-text-input id="password" class="block mt-1 w-full px-4 py-2 border border-gray-400 rounded-md bg-gray-50 text-gray-700 outline-none"
                type="password" name="password" placeholder="パスワードを入力" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- パスワード確認 -->
        <div>
            <x-input-label for="password_confirmation" :value="__('パスワード（確認）')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full px-4 py-2 border border-gray-400 rounded-md bg-gray-50 text-gray-700 outline-none"
                type="password" name="password_confirmation" placeholder="もう一度入力" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-4">
            <a class="text-sm text-indigo-600 hover:underline" href="{{ route('login') }}">
                {{ __('すでに登録済みの方はこちら') }}
            </a>

            <x-primary-button class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                {{ __('登録') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
