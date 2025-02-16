@component('mail::message')
    # Video Shared With You

    A video has been shared with you by {{ $videoShare->videoFile->user->name }}.

    Title: {{ $videoShare->videoFile->title }}

    You can access the video using the link below:

    @component('mail::button', ['url' => route('videos.shared', $videoShare->access_token)])
        View Video
    @endcomponent

    This link will expire on {{ $videoShare->expires_at->format('Y-m-d H:i') }}.

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
