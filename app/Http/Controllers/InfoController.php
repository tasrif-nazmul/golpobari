<?php

namespace App\Http\Controllers;

use App\Models\ContentPage;
use App\Models\Faq;
use App\Models\Package;
use Illuminate\Http\Request;

class InfoController extends Controller
{
    public function pricing()
    {
        return view('site.pricing', [
            'packages' => Package::active()->get(),
            'faqs' => Faq::active()->limit(4)->get(),
            'seo' => ['title' => 'প্যাকেজ ও দাম · GolpoBari', 'description' => '৭ দিন থেকে ১ বছর। আপনার গল্পের জন্য প্যাকেজ বেছে নিন, bKash-এ পেমেন্ট করুন।'],
        ]);
    }

    public function faq()
    {
        $faqs = Faq::active()->get();

        return view('site.faq', [
            'faqs' => $faqs,
            'seo' => [
                'title' => 'প্রশ্নোত্তর · GolpoBari',
                'description' => 'GolpoBari নিয়ে সাধারণ প্রশ্ন ও উত্তর: ডেলিভারি, পেমেন্ট, মেয়াদ, প্রাইভেসি।',
                'jsonld' => ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $faqs->map(fn ($f) => [
                    '@type' => 'Question', 'name' => $f->question, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f->answer],
                ])->all()],
            ],
        ]);
    }

    public function contact()
    {
        return view('site.contact', ['seo' => ['title' => 'যোগাযোগ · GolpoBari', 'description' => 'যেকোনো প্রশ্নে GolpoBari-র সাথে যোগাযোগ করুন: ফোন, WhatsApp, Facebook বা ইমেইল।']]);
    }

    public function content(Request $request)
    {
        $page = ContentPage::where('slug', $request->route()->defaults['slug'] ?? '')->firstOrFail();

        return view('site.content', [
            'page' => $page,
            'seo' => ['title' => $page->title.' · GolpoBari', 'description' => $page->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($page->html()), 155)],
        ]);
    }
}
