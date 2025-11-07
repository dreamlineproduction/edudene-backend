<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\Admin\AdminContactUsEmail;
use App\Mail\User\UserContactUsEmail;
use App\Models\Contact;
use App\Models\ContactTopic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ContactController extends Controller
{
  
    public function topics(){
        return jsonResponse(true, 'Topics fetched successfully', ContactTopic::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'name'      => 'required|max:150',
            'email'     => 'required|max:250|email',
            'topic'     => 'required|max:150|exists:contact_topics,id',
            'subject'   => 'required|max:150',
            'file_id'   => 'required|exists:files,id',
            'message'   => 'required',
        ]);

        try{
            $finalize = finalizeFile($request->file_id,'contact');

            
            $request->merge([
                'contact_topic_id' => $request->topic,
                'attachment' => $finalize['path'],
                'attachment_url' => $finalize['url'],
            ]);

            $contact  = Contact::create($request->toArray());

            $topic =  ContactTopic::find($request->topic);

            // Sent mail to user
            $mailData = [
                'fullName' => $request->name,
                'email' => $request->email,
                'subject' => $request->subject,
                'topic' => $topic->title,
                'message' => $request->message,
            ];

            if(notEmpty($contact->attachment)){
                $mailData['files'] = Storage::disk('s3')->url($contact->attachment);
            }
            // Send mail to user
            Mail::to($request->email)->send(new UserContactUsEmail($mailData));

            // Sent mail to Admin
            Mail::to(env('SUPPORT_EMAIL'))->send(new AdminContactUsEmail($mailData));
            return jsonResponse(true, 'Message sent successfully');
        } catch(\Exception $e){ 
            return jsonResponse(false, 'Something went wrong '. $e->getMessage(),null,500);
        }
        
    } 
    
}
