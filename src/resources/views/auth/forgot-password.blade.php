<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('パスワードを忘れましたか? 問題ありません。メールアドレスをお知らせいただければ、新しいパスワードを選択できるパスワードリセットリンクをメールでお送りします。') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full px-4 py-2 border border-gray-400 rounded-md bg-gray-50 text-gray-700 outline-none" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Email Password Reset Link') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
