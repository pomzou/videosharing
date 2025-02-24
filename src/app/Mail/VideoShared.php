<?php

namespace App\Mail;

use App\Models\VideoShare;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VideoShared extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The share instance.
     *
     * @var VideoShare
     */
    public $share;

    /**
     * Create a new message instance.
     */
    public function __construct(VideoShare $share)
    {
        $this->share = $share;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('ファイルが共有されました')
            ->view('emails.video-shared');
    }
}
