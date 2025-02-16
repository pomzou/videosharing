<?php

namespace App\Mail;

use App\Models\VideoShare;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VideoShared extends Mailable
{
    use Queueable, SerializesModels;

    public $share;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(VideoShare $share)
    {
        $this->share = $share;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Your video has been shared')
            ->view('emails.video_shared')
            ->with([
                'share' => $this->share,
            ]);
    }
}
