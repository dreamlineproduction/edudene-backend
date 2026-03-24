<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Faq;
use App\Models\FaqSection;

class FaqController extends Controller
{
    public function index($sectionID)
    {
        $faqs = Faq::query();
        $faqs = $faqs->where(['status'=>'Active']);
        $faqs = $faqs->get();

        return jsonResponse(true, 'Faqs fetched successfully', [
            'faqs' => $faqs,          
        ]);
    }


    /**
     * Display a listing of the resource.
     */
    public function getFsqsSection()
    {
        $faqSections = FaqSection::query();
        $faqSections = $faqSections->where(['status'=>'Active'])->with('faqs');
        $faqSections = $faqSections->get();

        return jsonResponse(true, 'Faq Sections fetched successfully', [
            'faq_sections' => $faqSections,          
        ]);
    }

    public function getHomeFaqs()
    {
        $faqs = Faq::query();
        $faqs = $faqs->where(['is_home'=>'Yes','status'=>'Active']);
        $faqs = $faqs->get();

        return jsonResponse(true, 'Faqs fetched successfully', [
            'faqs' => $faqs,          
        ]);
    }

}
