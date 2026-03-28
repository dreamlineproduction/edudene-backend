<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index() {
		$emailTemplates = EmailTemplate::orderBy('name','ASC')->get();

		return jsonResponse(true, 'Email Templates fetched successfully', [
			'emailTemplates' => $emailTemplates,
		]);
	}

	public function show($id) {
		$emailTemplate = EmailTemplate::find($id);	

		return jsonResponse(true, 'Email Template fetched successfully', [
			'emailTemplate' => $emailTemplate
		]);
	}

	public function update($id, Request $request) {
		$emailTemplate = EmailTemplate::find($id);

		if (!$emailTemplate) {
			return jsonResponse(false, 'Email Template not found',[],404);
		}

		$emailTemplate->update($request->all());

		return jsonResponse(true, 'Email Template updated successfully', [
			'emailTemplate' => $emailTemplate
		]);
	}
}
