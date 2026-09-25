<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\Page;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class CmsSeeder extends Seeder
{
    public function run(): void
    {
        $company = setting('company_name', 'BizSetu');

        $pages = [
            'about-us' => ['About Us', <<<HTML
<h2>Your partner from registration to growth</h2>
<p>{$company} is an online business services platform that helps entrepreneurs start, run and grow their businesses in India. We bring together chartered accountants, company secretaries, lawyers and business consultants on one simple platform.</p>
<p>From company registration and trademark protection to GST, compliance and technology — we take care of the paperwork so you can focus on building your business.</p>
<h3>Our mission</h3>
<p>To make business compliance simple, transparent and affordable for every Indian entrepreneur.</p>
<h3>What makes us different</h3>
<ul><li>Qualified professionals handle every case</li><li>Transparent, upfront pricing — no hidden charges</li><li>Real-time tracking of every application</li><li>Automated compliance reminders so you never miss a due date</li></ul>
HTML],
            'privacy-policy' => ['Privacy Policy', <<<HTML
<p>This Privacy Policy explains how {$company} collects, uses and protects your personal information when you use our website and services.</p>
<h3>Information we collect</h3>
<p>We collect information you provide — such as your name, email, mobile number, business details and documents uploaded for your applications — and technical information such as IP address and browser type.</p>
<h3>How we use information</h3>
<p>We use your information only to deliver the services you request, communicate with you, process payments, comply with legal obligations and improve our services.</p>
<h3>Document security</h3>
<p>Documents are stored in secure, access-controlled storage and are only accessible to you and the professionals assigned to your application.</p>
<h3>Sharing</h3>
<p>We share information with government authorities only as required to complete your filings, and with professionals working on your case. We never sell your data.</p>
<h3>Your rights</h3>
<p>You may request access, correction or deletion of your personal data by contacting us.</p>
HTML],
            'terms-and-conditions' => ['Terms & Conditions', <<<HTML
<p>By using the {$company} website and services you agree to these terms.</p>
<h3>Services</h3>
<p>We provide professional assistance for registrations, filings and compliance. Government approvals are at the discretion of the respective authorities and timelines are indicative.</p>
<h3>Customer responsibilities</h3>
<p>You are responsible for the accuracy and authenticity of the information and documents you provide.</p>
<h3>Fees</h3>
<p>Professional fees are as displayed at the time of purchase. Government fees, stamp duty and taxes are payable as applicable.</p>
<h3>Limitation of liability</h3>
<p>Our liability is limited to the professional fee paid for the specific service.</p>
<h3>Governing law</h3>
<p>These terms are governed by the laws of India, and courts at Mumbai have exclusive jurisdiction.</p>
HTML],
            'refund-policy' => ['Refund Policy', <<<HTML
<p>We want you to be satisfied with our services. This policy explains when refunds are available.</p>
<h3>Full refund</h3>
<p>If you cancel before our team has started work on your application, the professional fee is refunded in full.</p>
<h3>Partial refund</h3>
<p>If work has started but no filing has been made, we refund the fee after deducting the cost of work completed.</p>
<h3>No refund</h3>
<p>Government fees already paid and services where the filing has been completed are not refundable.</p>
<h3>How to request</h3>
<p>Raise a support ticket from your customer portal. Approved refunds are processed within 7–10 working days to the original payment method.</p>
HTML],
        ];

        foreach ($pages as $slug => [$title, $body]) {
            Page::updateOrCreate(['slug' => $slug], [
                'title' => $title, 'body' => $body, 'status' => true,
                'seo_title' => $title.' | '.$company,
                'seo_description' => strip_tags(explode('</p>', $body)[0]),
            ]);
        }

        $faqs = [
            ['How do I start?', 'Choose a service, click "Apply Now", create your account and fill the short application form. Our expert will contact you within one working day.', 'general'],
            ['Is the process completely online?', 'Yes. You can apply, upload documents, pay and download your certificates from the customer portal — no office visit required.', 'process'],
            ['Who will handle my application?', 'Every application is assigned to a relationship manager and, where required, a qualified CA, CS or lawyer.', 'process'],
            ['How can I track my application?', 'Log in to your customer portal to see the real-time status, timeline and any documents requested.', 'process'],
            ['What payment methods do you accept?', 'UPI, bank transfer (NEFT/RTGS/IMPS), cards and net banking. You receive a GST invoice for every payment.', 'payments'],
            ['Are government fees included in the price?', 'Our price is the professional fee. Government fees and stamp duty, where applicable, are charged at actuals and shown before payment.', 'payments'],
            ['Is my data and documents safe?', 'Yes. Documents are stored in private, encrypted-at-rest storage and are only accessible to you and your assigned team.', 'account'],
            ['Will you remind me about compliance due dates?', 'Yes. For recurring services we maintain your compliance calendar and send reminders before every due date.', 'general'],
        ];

        foreach ($faqs as $i => [$question, $answer, $group]) {
            Faq::updateOrCreate(['question' => $question], compact('answer', 'group') + ['status' => true, 'sort_order' => $i]);
        }

        $testimonials = [
            ['Rohit Sharma', 'Founder', 'CloudNest Technologies', 'Bengaluru', 'Our Private Limited Company was registered in 9 days. The team handled DSC, name approval and bank account opening without a single office visit.'],
            ['Priya Nair', 'Director', 'Nair Foods Pvt Ltd', 'Kochi', 'FSSAI licence and GST registration done together, and now they file our GST returns every month. The reminders are a life-saver.'],
            ['Amit Verma', 'Proprietor', 'Verma Exports', 'Jaipur', 'Got my IEC in two days and LUT filed before my first export shipment. Very professional and responsive team.'],
            ['Sneha Kulkarni', 'Co-founder', 'Brewline Coffee', 'Pune', 'The trademark search and filing were explained clearly. When we got an objection, their attorney handled the reply and it was accepted.'],
            ['Faizan Qureshi', 'CEO', 'Qureshi Logistics', 'Hyderabad', 'We moved our bookkeeping and GST filings to them. Monthly MIS reports have made decision-making much easier.'],
            ['Meera Iyer', 'Trustee', 'Asha Foundation', 'Chennai', '12A and 80G registrations were handled end to end. We are now receiving CSR funding thanks to their guidance.'],
        ];

        foreach ($testimonials as $i => [$name, $designation, $company, $city, $message]) {
            Testimonial::updateOrCreate(['name' => $name], compact('designation', 'company', 'city', 'message') + ['rating' => 5, 'status' => true, 'sort_order' => $i]);
        }
    }
}
