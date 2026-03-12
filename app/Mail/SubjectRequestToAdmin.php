<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubjectRequestToAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public $tutor;
    public $subjectRequest;

    /**
     * Create a new message instance.
     *
     * @param $tutor
     * @param $subjectRequest
     */
    public function __construct($tutor, $subjectRequest)
    {
        $this->tutor = $tutor;
        $this->subjectRequest = $subjectRequest;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('New Subject Request from Tutor')
                    ->view('emails.subject_request_to_admin')
                    ->with([
                        'tutor' => $this->tutor,
                        'subjectRequest' => $this->subjectRequest,
                    ]);
    }
}
