<?php

namespace App\Mail\School;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubjectRequestToAdmin extends Mailable
{
    use Queueable, SerializesModels;

	public $school;
    public $subjectRequest;

    /**
     * Create a new message instance.
     */
    public function __construct($school, $subjectRequest)
    {
        $this->school = $school;
        $this->subjectRequest = $subjectRequest;
    }

    public function build()
    {
        return $this->subject('New Subject Request from School')
                    ->view('emails.school.subject_request_to_admin')
                    ->with([
                        'school' => $this->school,
                        'subjectRequest' => $this->subjectRequest,
                    ]);
    }
}
