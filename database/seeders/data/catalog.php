<?php

/*
| Demo catalogue: 15 categories and their services. Category-level defaults
| (documents, steps, benefits, audience, form fields) apply to every service in
| the category unless the service overrides them. Prices are professional fees
| excluding GST and government fees.
*/

$kyc = ['PAN Card of applicant', 'Aadhaar Card of applicant', 'Passport size photograph'];
$bizKyc = ['PAN Card of business / proprietor', 'Address proof of business premises', 'Certificate of Incorporation / Registration (if any)'];

return [
    [
        'name' => 'Business Registration', 'slug' => 'business-registration', 'icon' => 'bi-building',
        'tagline' => 'Incorporate your company or LLP in days, 100% online.',
        'description' => 'Choose the right business structure and get registered with the Ministry of Corporate Affairs — name approval, DSC, DIN, incorporation certificate, PAN and TAN included.',
        'defaults' => [
            'documents' => array_merge($kyc, ['Latest bank statement / utility bill of each director (address proof)', 'Registered office address proof (electricity bill / rent agreement)', 'NOC from owner of the premises']),
            'benefits' => ['Separate legal entity and limited liability', 'Easier access to funding and bank loans', 'Higher credibility with customers and vendors', 'Perpetual succession — business continues regardless of owners'],
            'who_needs' => ['Startups planning to raise investment', 'Growing businesses that want limited liability', 'Founders with co-founders or partners', 'Foreign companies entering India'],
            'steps' => [['Consultation & document collection', 'Our expert helps you choose the structure and collects KYC documents.', 'Day 1'], ['DSC & name approval', 'Digital signatures are issued and the proposed name is filed for approval.', '2–4 days'], ['Incorporation filing', 'SPICe+ / FiLLiP forms with MOA/AOA or LLP agreement are filed with MCA.', '3–5 days'], ['Certificate issued', 'You receive the Certificate of Incorporation with PAN & TAN.', '7–12 days']],
            'fields' => [['Proposed name (1st choice)', 'proposed_name_1', 'text', true], ['Proposed name (2nd choice)', 'proposed_name_2', 'text', false], ['Number of directors / partners', 'members', 'number', true], ['State of registered office', 'state', 'text', true], ['Main business activity', 'activity', 'textarea', true]],
        ],
        'services' => [
            ['name' => 'Private Limited Company', 'slug' => 'private-limited-company-registration', 'price' => 6999, 'discount' => 4999, 'time' => '7–12 working days', 'featured' => true, 'icon' => 'bi-building-check', 'short' => 'Register a Private Limited Company with DSC, DIN, name approval, MOA/AOA, PAN and TAN.'],
            ['name' => 'LLP Registration', 'slug' => 'llp-registration', 'price' => 5999, 'discount' => 4499, 'time' => '10–15 working days', 'featured' => true, 'icon' => 'bi-people', 'short' => 'Limited Liability Partnership with flexible management and low compliance cost.'],
            ['name' => 'One Person Company (OPC)', 'slug' => 'one-person-company-registration', 'price' => 5999, 'discount' => 4499, 'time' => '7–12 working days', 'icon' => 'bi-person-check', 'short' => 'Company structure for a single founder with limited liability.'],
            ['name' => 'Public Limited Company', 'slug' => 'public-limited-company-registration', 'price' => 24999, 'discount' => 19999, 'time' => '15–20 working days', 'icon' => 'bi-bank2', 'short' => 'Incorporate a public company that can raise capital from the public.'],
            ['name' => 'Section 8 Company', 'slug' => 'section-8-company-registration', 'price' => 12999, 'discount' => 9999, 'time' => '15–25 working days', 'icon' => 'bi-heart', 'short' => 'Non-profit company for charitable, educational or social objectives.'],
            ['name' => 'Nidhi Company', 'slug' => 'nidhi-company-registration', 'price' => 29999, 'discount' => 24999, 'time' => '20–30 working days', 'icon' => 'bi-piggy-bank', 'short' => 'Register a Nidhi company for mutual borrowing and lending among members.'],
            ['name' => 'Indian Subsidiary', 'slug' => 'indian-subsidiary-registration', 'price' => 39999, 'discount' => 34999, 'time' => '20–30 working days', 'icon' => 'bi-globe', 'short' => 'Set up a wholly-owned Indian subsidiary of a foreign company with FEMA compliance.'],
            ['name' => 'Business Registration', 'slug' => 'proprietorship-partnership-registration', 'price' => 2999, 'discount' => 1999, 'time' => '5–7 working days', 'icon' => 'bi-shop', 'short' => 'Start as a proprietorship or partnership firm with Udyam, GST and bank account support.', 'documents' => array_merge($kyc, ['Business address proof', 'Partnership deed draft (for partnerships)'])],
        ],
    ],
    [
        'name' => 'Trademark & IPR', 'slug' => 'trademark-ipr', 'icon' => 'bi-shield-check',
        'tagline' => 'Protect your brand name, logo and intellectual property.',
        'description' => 'Search, file and defend your trademark with experienced IP attorneys — from registration to objection replies, oppositions and renewals.',
        'defaults' => [
            'documents' => ['Brand name / logo (JPG or PNG)', 'PAN & Aadhaar of applicant', 'Certificate of Incorporation / MSME certificate (for businesses)', 'Signed Form TM-48 (authorisation — we prepare it)'],
            'benefits' => ['Exclusive legal right to use your brand', 'Legal protection against copycats', 'Builds brand value and trust', 'Use the ® symbol after registration'],
            'who_needs' => ['Startups and new brands', 'E-commerce sellers and D2C brands', 'Manufacturers and service businesses', 'Anyone launching a product or logo'],
            'steps' => [['Trademark search', 'We search the registry for identical or similar marks.', 'Same day'], ['Application drafting', 'Class selection and application drafted by an IP attorney.', '1 day'], ['Filing & TM number', 'Application filed with the Trademark Registry; you can use ™ immediately.', '1–2 days'], ['Examination & follow-up', 'We track the application through examination and publication.', '12–18 months']],
            'fields' => [['Brand name / wordmark', 'brand_name', 'text', true], ['Trademark class (if known)', 'tm_class', 'text', false], ['Is the brand already in use?', 'in_use', 'select', true, ['Yes', 'No — proposed to be used']], ['Describe your goods / services', 'goods', 'textarea', true]],
        ],
        'services' => [
            ['name' => 'Trademark Registration', 'slug' => 'trademark-registration', 'price' => 2499, 'discount' => 1499, 'time' => '1–2 days to file', 'featured' => true, 'icon' => 'bi-c-circle', 'short' => 'File your trademark application with a free search and attorney review.'],
            ['name' => 'Trademark Renewal', 'slug' => 'trademark-renewal', 'price' => 2999, 'discount' => 2499, 'time' => '2–3 working days', 'billing' => 'recurring', 'interval' => 'yearly', 'compliance' => 'trademark_renewal', 'icon' => 'bi-arrow-repeat', 'short' => 'Renew your registered trademark every 10 years and keep protection alive.'],
            ['name' => 'Trademark Objection', 'slug' => 'trademark-objection-reply', 'price' => 3999, 'discount' => 2999, 'time' => '3–5 working days', 'icon' => 'bi-file-earmark-text', 'short' => 'Drafting and filing of a strong reply to the examiner\'s objection.'],
            ['name' => 'Trademark Opposition', 'slug' => 'trademark-opposition', 'price' => 7999, 'discount' => 6999, 'time' => '5–7 working days', 'icon' => 'bi-shield-exclamation', 'short' => 'File or defend a trademark opposition with an experienced IP lawyer.'],
            ['name' => 'Trademark Assignment', 'slug' => 'trademark-assignment', 'price' => 5999, 'discount' => 4999, 'time' => '5–7 working days', 'icon' => 'bi-arrow-left-right', 'short' => 'Transfer ownership of a trademark through a registered assignment deed.'],
            ['name' => 'Series Trademark', 'slug' => 'series-trademark', 'price' => 4999, 'discount' => 3999, 'time' => '2–3 working days', 'icon' => 'bi-collection', 'short' => 'Register a series of similar marks in a single application.'],
            ['name' => 'Logo Design', 'slug' => 'logo-design', 'price' => 4999, 'discount' => 2999, 'time' => '3–5 working days', 'icon' => 'bi-palette', 'short' => 'Professional logo design with 3 concepts and source files, ready for trademark filing.', 'documents' => ['Brand name and tagline', 'Reference logos / colour preferences (optional)']],
        ],
    ],
    [
        'name' => 'GST & Tax', 'slug' => 'gst-tax', 'icon' => 'bi-receipt',
        'tagline' => 'GST registration, return filing and tax planning by experts.',
        'description' => 'Everything GST and TDS — registration, monthly and annual returns, LUT, e-way bills and year-round tax planning by chartered accountants.',
        'defaults' => [
            'documents' => array_merge(['PAN Card of business / proprietor', 'Aadhaar Card of proprietor / authorised signatory'], ['Address proof of business premises', 'Bank statement or cancelled cheque']),
            'benefits' => ['Avoid late fees, interest and notices', 'Claim input tax credit correctly', 'Expert CA review of every filing', 'Timely reminders before every due date'],
            'who_needs' => ['Businesses with turnover above the GST threshold', 'E-commerce and inter-state sellers', 'Service providers and exporters', 'Companies deducting TDS'],
            'steps' => [['Share details', 'Upload documents or sales/purchase data on the portal.', 'Day 1'], ['Expert preparation', 'A tax expert prepares and reconciles the filing.', '1–2 days'], ['Review & approval', 'You review the computation and approve.', '1 day'], ['Filing & acknowledgement', 'We file on the government portal and share the acknowledgement.', 'Same day']],
            'fields' => [['Trade / business name', 'trade_name', 'text', true], ['GSTIN (if registered)', 'gstin', 'text', false], ['State', 'state', 'text', true], ['Approximate annual turnover', 'turnover', 'select', true, ['Below ₹20 lakh', '₹20 lakh – ₹1.5 crore', '₹1.5 crore – ₹5 crore', 'Above ₹5 crore']]],
        ],
        'services' => [
            ['name' => 'GST Registration', 'slug' => 'gst-registration', 'price' => 1999, 'discount' => 999, 'time' => '3–7 working days', 'featured' => true, 'icon' => 'bi-receipt-cutoff', 'short' => 'Get your GSTIN with expert help — for proprietors, firms, companies and LLPs.'],
            ['name' => 'GST Return Filing', 'slug' => 'gst-return-filing', 'price' => 1499, 'discount' => 999, 'time' => 'Monthly', 'featured' => true, 'billing' => 'recurring', 'interval' => 'monthly', 'compliance' => 'gstr3b', 'icon' => 'bi-file-earmark-bar-graph', 'short' => 'Monthly GSTR-1 and GSTR-3B filing with ITC reconciliation by a CA.', 'documents' => ['Sales invoices / sales register', 'Purchase invoices / purchase register', 'GST portal login (or OTP access)']],
            ['name' => 'GST Nil Return', 'slug' => 'gst-nil-return', 'price' => 599, 'discount' => 399, 'time' => '1 working day', 'billing' => 'recurring', 'interval' => 'monthly', 'compliance' => 'gstr3b', 'icon' => 'bi-file-earmark-minus', 'short' => 'File nil GSTR-1 and GSTR-3B when there are no transactions in the period.', 'documents' => ['GSTIN and GST portal access']],
            ['name' => 'GST Modification', 'slug' => 'gst-modification', 'price' => 1499, 'discount' => 999, 'time' => '3–7 working days', 'icon' => 'bi-pencil-square', 'short' => 'Amend GST registration details — address, partners, business name or activities.'],
            ['name' => 'GSTR-9 Filing', 'slug' => 'gstr-9-annual-return', 'price' => 4999, 'discount' => 3999, 'time' => '5–7 working days', 'billing' => 'recurring', 'interval' => 'yearly', 'compliance' => 'gstr9', 'icon' => 'bi-calendar2-check', 'short' => 'GST annual return with reconciliation of monthly returns and books.', 'documents' => ['Books of accounts for the financial year', 'All GSTR-1 & GSTR-3B filed for the year', 'GST portal access']],
            ['name' => 'GST LUT Filing', 'slug' => 'gst-lut-filing', 'price' => 1499, 'discount' => 999, 'time' => '1–2 working days', 'billing' => 'recurring', 'interval' => 'yearly', 'compliance' => 'gst_lut', 'icon' => 'bi-airplane', 'short' => 'Letter of Undertaking to export goods and services without paying IGST.'],
            ['name' => 'GST E-Way Bill', 'slug' => 'gst-e-way-bill', 'price' => 999, 'discount' => 699, 'time' => '1 working day', 'icon' => 'bi-truck', 'short' => 'E-way bill registration and generation support for movement of goods.'],
            ['name' => 'TDS Return Filing', 'slug' => 'tds-return-filing', 'price' => 2499, 'discount' => 1799, 'time' => 'Quarterly', 'billing' => 'recurring', 'interval' => 'quarterly', 'compliance' => 'tds_return', 'icon' => 'bi-percent', 'short' => 'Quarterly TDS returns (24Q, 26Q, 27Q) with Form 16/16A generation.', 'documents' => ['TAN and TRACES login', 'Challan details of TDS deposited', 'Deductee details (PAN, amount, section)']],
            ['name' => 'Tax Planning & Consultancy', 'slug' => 'tax-planning-consultancy', 'price' => 2999, 'discount' => 1999, 'time' => '45-minute consultation', 'icon' => 'bi-calculator', 'short' => 'One-to-one session with a CA to plan income tax, GST and business structuring.', 'documents' => ['Last filed ITR (if any)', 'Summary of income and investments']],
        ],
    ],
    [
        'name' => 'Accounting & Bookkeeping', 'slug' => 'accounting-bookkeeping', 'icon' => 'bi-journal-text',
        'tagline' => 'Accurate books, MIS reports and project reports.',
        'description' => 'Cloud bookkeeping handled by qualified accountants, with monthly MIS reports and bank-ready project reports.',
        'defaults' => [
            'documents' => ['Bank statements for the period', 'Sales and purchase invoices', 'Expense bills and receipts', 'Previous year financial statements (if any)'],
            'benefits' => ['Always up-to-date, audit-ready books', 'Monthly profit & loss and cash-flow visibility', 'Dedicated accountant', 'Costs less than an in-house accountant'],
            'who_needs' => ['Startups and SMEs', 'Businesses without an in-house accountant', 'Companies preparing for audit or funding', 'Businesses applying for bank loans'],
            'steps' => [['Onboarding', 'We understand your business and set up the accounting software.', '1–2 days'], ['Data collection', 'Share bank statements and bills every month on the portal.', 'Monthly'], ['Bookkeeping & reconciliation', 'Entries are posted and reconciled with bank and GST.', 'Monthly'], ['MIS reports', 'You receive P&L, balance sheet and key metrics.', 'By 10th of every month']],
            'fields' => [['Accounting software used', 'software', 'select', true, ['Tally', 'Zoho Books', 'QuickBooks', 'Excel', 'None']], ['Monthly transactions (approx.)', 'transactions', 'select', true, ['Up to 100', '100 – 300', '300 – 1000', 'More than 1000']], ['Anything else we should know?', 'notes', 'textarea', false]],
        ],
        'services' => [
            ['name' => 'Online Bookkeeping', 'slug' => 'online-bookkeeping', 'price' => 4999, 'discount' => 3499, 'time' => 'Monthly', 'featured' => true, 'billing' => 'recurring', 'interval' => 'monthly', 'compliance' => 'bookkeeping', 'icon' => 'bi-journal-check', 'short' => 'Monthly bookkeeping, bank reconciliation and MIS reports by qualified accountants.'],
            ['name' => 'Project Report', 'slug' => 'project-report', 'price' => 7999, 'discount' => 5999, 'time' => '5–7 working days', 'icon' => 'bi-file-earmark-richtext', 'short' => 'Bank-ready project report with CMA data for loans and subsidies.', 'documents' => ['Business plan / project details', 'Quotations for machinery or assets', 'Last 2 years financials (existing businesses)', 'KYC of promoters']],
        ],
    ],
    [
        'name' => 'License & Certification', 'slug' => 'licenses-certification', 'icon' => 'bi-patch-check',
        'tagline' => 'FSSAI, ISO and every business licence you need.',
        'description' => 'Get the licences and certifications your business needs to operate legally — food licence, ISO certification, trade licences and more.',
        'defaults' => [
            'documents' => array_merge($kyc, ['Address proof of business premises', 'Layout plan of premises (for State/Central FSSAI licence)', 'List of food products / business activities']),
            'benefits' => ['Operate legally and avoid penalties', 'Build customer trust', 'Required by e-commerce and food aggregators', 'Expert handling of government queries'],
            'who_needs' => ['Restaurants, cloud kitchens and food brands', 'Manufacturers and traders', 'Home bakers and caterers', 'Businesses bidding for tenders'],
            'steps' => [['Eligibility check', 'We confirm the licence type you need.', 'Day 1'], ['Document preparation', 'Application and declarations are drafted.', '1–2 days'], ['Government filing', 'Application is filed with the licensing authority.', '1 day'], ['Licence issued', 'Licence / certificate is issued and shared with you.', '7–30 days']],
            'fields' => [['Business name', 'business_name', 'text', true], ['Nature of business', 'nature', 'text', true], ['Annual turnover (approx.)', 'turnover', 'select', true, ['Below ₹12 lakh', '₹12 lakh – ₹20 crore', 'Above ₹20 crore']], ['State', 'state', 'text', true]],
        ],
        'services' => [
            ['name' => 'FSSAI Registration', 'slug' => 'fssai-registration', 'price' => 1999, 'discount' => 999, 'time' => '7–15 working days', 'featured' => true, 'icon' => 'bi-egg-fried', 'short' => 'Basic, State or Central FSSAI food licence for food businesses.'],
            ['name' => 'FSSAI Renewal', 'slug' => 'fssai-renewal', 'price' => 1999, 'discount' => 1499, 'time' => '7–15 working days', 'billing' => 'recurring', 'interval' => 'yearly', 'compliance' => 'license_renewal', 'icon' => 'bi-arrow-clockwise', 'short' => 'Renew your FSSAI licence before expiry and avoid penalties.'],
            ['name' => 'FSSAI Modification', 'slug' => 'fssai-modification', 'price' => 1999, 'discount' => 1499, 'time' => '7–15 working days', 'icon' => 'bi-pencil', 'short' => 'Modify FSSAI licence details — address, products, capacity or ownership.'],
            ['name' => 'FSSAI Annual Return', 'slug' => 'fssai-annual-return', 'price' => 1999, 'discount' => 1499, 'time' => '2–3 working days', 'billing' => 'recurring', 'interval' => 'yearly', 'compliance' => 'fssai_return', 'icon' => 'bi-journal-arrow-up', 'short' => 'File Form D-1 annual return for FSSAI licensed manufacturers and importers.'],
            ['name' => 'ISO Registration / Certification', 'slug' => 'iso-certification', 'price' => 4999, 'discount' => 3499, 'time' => '7–15 working days', 'featured' => true, 'icon' => 'bi-award', 'short' => 'ISO 9001, 14001, 27001 and other certifications through accredited bodies.'],
            ['name' => 'Other Business Licenses', 'slug' => 'other-business-licenses', 'price' => 2999, 'discount' => 1999, 'time' => 'Varies by licence', 'icon' => 'bi-card-checklist', 'short' => 'Shop & Establishment, Trade Licence, Udyam, Professional Tax and more.'],
        ],
    ],
    [
        'name' => 'Annual Compliance', 'slug' => 'annual-compliance', 'icon' => 'bi-calendar-check',
        'tagline' => 'ROC filings, audits and a compliance calendar that never misses a date.',
        'description' => 'Annual ROC filings, director KYC, statutory registers and a personalised compliance calendar with automated reminders.',
        'defaults' => [
            'documents' => ['Certificate of Incorporation', 'Audited financial statements', 'MCA / ROC login details', 'Details of directors and shareholding'],
            'benefits' => ['Avoid heavy MCA late fees', 'Keep the company in "Active" status', 'Directors stay free of disqualification', 'Single point of contact for all filings'],
            'who_needs' => ['Private Limited Companies', 'LLPs', 'One Person Companies', 'Section 8 companies'],
            'steps' => [['Compliance review', 'We review pending filings and due dates.', 'Day 1'], ['Documents & audit', 'Financials are finalised and audit is coordinated.', 'As scheduled'], ['ROC filings', 'AOC-4, MGT-7 / Form 8, Form 11 and ITR are filed.', 'Before due dates'], ['Calendar & reminders', 'Your compliance calendar is updated for the next year.', 'Ongoing']],
            'fields' => [['Company / LLP name', 'entity_name', 'text', true], ['CIN / LLPIN', 'cin', 'text', true], ['Financial year', 'fy', 'select', true, ['2025-26', '2026-27']]],
        ],
        'services' => [
            ['name' => 'Annual Compliance', 'slug' => 'annual-compliance-package', 'price' => 14999, 'discount' => 11999, 'time' => 'Yearly', 'featured' => true, 'billing' => 'recurring', 'interval' => 'yearly', 'compliance' => 'annual_roc', 'icon' => 'bi-clipboard2-check', 'short' => 'Complete annual ROC compliance for companies and LLPs, including ITR and DIR-3 KYC.'],
            ['name' => 'Compliance Calendar', 'slug' => 'compliance-calendar', 'price' => 999, 'discount' => 0, 'time' => 'Yearly subscription', 'billing' => 'recurring', 'interval' => 'yearly', 'compliance' => 'annual_roc', 'icon' => 'bi-calendar-week', 'short' => 'A personalised calendar of every GST, TDS, ROC and other due date for your business.', 'documents' => ['Registration certificates (GST, company)']],
            ['name' => 'Compliance Reminder', 'slug' => 'compliance-reminder', 'price' => 499, 'discount' => 0, 'time' => 'Yearly subscription', 'billing' => 'recurring', 'interval' => 'yearly', 'compliance' => 'annual_roc', 'icon' => 'bi-bell', 'short' => 'Email and portal reminders before every statutory due date.', 'documents' => ['Registration certificates (GST, company)']],
        ],
    ],
    [
        'name' => 'Import & Export', 'slug' => 'import-export', 'icon' => 'bi-globe2',
        'tagline' => 'Start importing and exporting with IEC in 2–3 days.',
        'description' => 'Obtain and maintain your Import Export Code from DGFT, and get guidance on export incentives and LUT.',
        'defaults' => [
            'documents' => array_merge(['PAN Card of business / proprietor', 'Aadhaar Card of proprietor / director'], ['Cancelled cheque or bank certificate', 'Address proof of business premises']),
            'benefits' => ['Mandatory for import/export of goods', 'Lifetime validity with annual update', 'Access to export incentives', 'Expand to international markets'],
            'who_needs' => ['Exporters and importers', 'E-commerce sellers shipping abroad', 'Service exporters claiming benefits', 'Manufacturers sourcing internationally'],
            'steps' => [['Documents', 'Share KYC and bank details.', 'Day 1'], ['DGFT application', 'Application filed on the DGFT portal.', '1 day'], ['IEC issued', 'IEC certificate is generated.', '2–3 days']],
            'fields' => [['Business name', 'business_name', 'text', true], ['Products / services to import or export', 'products', 'textarea', true]],
        ],
        'services' => [
            ['name' => 'Import Export Code (IEC)', 'slug' => 'import-export-code', 'price' => 2499, 'discount' => 1499, 'time' => '2–3 working days', 'featured' => true, 'icon' => 'bi-box-seam', 'short' => 'Get your Import Export Code from DGFT to start international trade.'],
            ['name' => 'IEC Modification', 'slug' => 'iec-modification', 'price' => 1999, 'discount' => 1499, 'time' => '2–3 working days', 'billing' => 'recurring', 'interval' => 'yearly', 'compliance' => 'iec_update', 'icon' => 'bi-pencil-square', 'short' => 'Update or modify IEC details, including the mandatory annual update.'],
        ],
    ],
    [
        'name' => 'NGO Services', 'slug' => 'ngo-services', 'icon' => 'bi-heart',
        'tagline' => 'Registrations and tax exemptions for NGOs, trusts and societies.',
        'description' => '12A and 80G registration, NGO Darpan and CSR readiness for trusts, societies and Section 8 companies.',
        'defaults' => [
            'documents' => ['Trust deed / MOA & bye-laws / Section 8 incorporation certificate', 'PAN of the organisation', 'KYC of trustees / directors', 'Activity report and financial statements (last 3 years, if any)'],
            'benefits' => ['Income tax exemption for the NGO', 'Donors get tax deduction under 80G', 'Eligibility for government grants', 'Eligibility for CSR funding'],
            'who_needs' => ['Charitable trusts and societies', 'Section 8 companies', 'NGOs seeking donations or CSR funds'],
            'steps' => [['Eligibility review', 'We review your constitution documents.', 'Day 1'], ['Application drafting', 'Forms 10A / 10AB and annexures are prepared.', '2–3 days'], ['Filing', 'Application filed with the Income Tax Department.', '1 day'], ['Approval', 'Registration order is issued.', '1–3 months']],
            'fields' => [['Organisation name', 'org_name', 'text', true], ['Type of organisation', 'org_type', 'select', true, ['Trust', 'Society', 'Section 8 Company']], ['Main objectives', 'objectives', 'textarea', true]],
        ],
        'services' => [
            ['name' => '12A Registration', 'slug' => '12a-registration', 'price' => 7999, 'discount' => 5999, 'time' => '1–3 months', 'icon' => 'bi-journal-bookmark', 'short' => 'Income tax exemption registration for charitable organisations.'],
            ['name' => '80G Registration', 'slug' => '80g-registration', 'price' => 7999, 'discount' => 5999, 'time' => '1–3 months', 'icon' => 'bi-gift', 'short' => 'Enable your donors to claim a tax deduction on donations.'],
            ['name' => 'CSR Related Services', 'slug' => 'csr-services', 'price' => 9999, 'discount' => 7999, 'time' => '7–10 working days', 'icon' => 'bi-hand-thumbs-up', 'short' => 'CSR-1 registration, CSR policy drafting and project documentation.'],
            ['name' => 'NGO DARPAN Registration', 'slug' => 'ngo-darpan-registration', 'price' => 2999, 'discount' => 1999, 'time' => '5–7 working days', 'icon' => 'bi-person-heart', 'short' => 'Register your NGO on the NITI Aayog DARPAN portal for a unique ID.'],
        ],
    ],
    [
        'name' => 'Legal & ODR', 'slug' => 'legal-odr', 'icon' => 'bi-bank',
        'tagline' => 'Consumer disputes, online dispute resolution and legal support.',
        'description' => 'Resolve disputes faster through legal notices, consumer complaints and online dispute resolution, with lawyers on call.',
        'defaults' => [
            'documents' => ['Brief of the matter', 'Invoices / agreements / correspondence related to the dispute', 'ID proof of the complainant'],
            'benefits' => ['Experienced lawyers', 'Faster, lower-cost resolution', 'Clear advice on your options', 'Fixed transparent fees'],
            'who_needs' => ['Consumers with product or service complaints', 'Businesses facing unpaid invoices', 'Startups needing contracts and notices'],
            'steps' => [['Case review', 'A lawyer reviews your matter.', '1 day'], ['Strategy & drafting', 'Notice, complaint or ODR claim is drafted.', '2–3 days'], ['Filing / sending', 'Filed before the forum or sent to the other party.', '1 day'], ['Follow-up', 'Hearings and follow-ups till resolution.', 'Ongoing']],
            'fields' => [['Opposite party name', 'opposite_party', 'text', true], ['Claim amount (₹)', 'claim_amount', 'number', false], ['Brief description of the dispute', 'dispute', 'textarea', true]],
        ],
        'services' => [
            ['name' => 'Consumer Dispute', 'slug' => 'consumer-dispute', 'price' => 4999, 'discount' => 3999, 'time' => '3–5 working days to file', 'icon' => 'bi-person-exclamation', 'short' => 'Legal notice and consumer complaint before the Consumer Commission.'],
            ['name' => 'Online Dispute Resolution (ODR)', 'slug' => 'online-dispute-resolution', 'price' => 5999, 'discount' => 4999, 'time' => '30–60 days', 'icon' => 'bi-chat-square-text', 'short' => 'Resolve commercial disputes online through mediation and arbitration.'],
            ['name' => 'Legal Support', 'slug' => 'legal-support', 'price' => 2999, 'discount' => 1999, 'time' => '2–3 working days', 'icon' => 'bi-file-earmark-ruled', 'short' => 'Legal notices, agreements, contract review and lawyer consultation.'],
        ],
    ],
    [
        'name' => 'Business Consultancy', 'slug' => 'business-consultancy', 'icon' => 'bi-lightbulb',
        'tagline' => 'Strategy, structuring and growth advice from experts.',
        'description' => 'Practical advice on business structure, funding readiness, pricing, operations and scaling from seasoned consultants.',
        'defaults' => [
            'documents' => ['Brief about your business', 'Latest financial statements (if available)'],
            'benefits' => ['Clarity on your next steps', 'Avoid costly structural mistakes', 'Actionable growth plan', 'Access to a network of experts'],
            'who_needs' => ['Founders planning a new venture', 'SMEs looking to scale', 'Businesses preparing for investment'],
            'steps' => [['Discovery call', 'Understand goals and challenges.', 'Day 1'], ['Analysis', 'Review of business, market and financials.', '3–5 days'], ['Recommendations', 'Written report with an action plan.', '5–7 days'], ['Implementation support', 'Optional hand-holding during execution.', 'Ongoing']],
            'fields' => [['Business stage', 'stage', 'select', true, ['Idea', 'Early stage', 'Growing', 'Established']], ['What do you need help with?', 'need', 'textarea', true]],
        ],
        'services' => [
            ['name' => 'Business Consultancy', 'slug' => 'business-consultancy-services', 'price' => 4999, 'discount' => 3999, 'time' => '5–7 working days', 'icon' => 'bi-lightbulb', 'short' => 'Structured advice on strategy, operations and growth for your business.'],
        ],
    ],
    [
        'name' => 'Virtual CXO', 'slug' => 'virtual-cxo', 'icon' => 'bi-person-badge',
        'tagline' => 'Senior leadership on demand — CFO, COO and CHRO.',
        'description' => 'Get the expertise of an experienced CFO, COO or CHRO for a fraction of the cost of a full-time hire.',
        'defaults' => [
            'documents' => ['Company profile', 'Latest financial statements / MIS'],
            'benefits' => ['Senior expertise without full-time cost', 'Investor-ready financials and processes', 'Better cash-flow and cost control', 'Board-level reporting'],
            'who_needs' => ['Funded startups', 'SMEs scaling operations', 'Businesses preparing for fundraising or audit'],
            'steps' => [['Assessment', 'Understand scope and current gaps.', 'Week 1'], ['Engagement plan', 'Agree deliverables and cadence.', 'Week 1'], ['Monthly engagement', 'Regular reviews, reports and decisions.', 'Monthly']],
            'fields' => [['Role required', 'role', 'select', true, ['Virtual CFO', 'Virtual COO', 'Virtual CHRO', 'Not sure']], ['Company size (employees)', 'size', 'number', false]],
        ],
        'services' => [
            ['name' => 'Virtual CXO Services', 'slug' => 'virtual-cxo-services', 'price' => 29999, 'discount' => 24999, 'time' => 'Monthly', 'billing' => 'recurring', 'interval' => 'monthly', 'compliance' => 'advisory_review', 'icon' => 'bi-person-badge', 'short' => 'Part-time CFO, COO or CHRO to drive finance, operations and people strategy.'],
        ],
    ],
    [
        'name' => 'Corporate Advisory', 'slug' => 'corporate-advisory', 'icon' => 'bi-graph-up-arrow',
        'tagline' => 'Fundraising, valuation, due diligence and restructuring.',
        'description' => 'Transaction and corporate advisory — valuation reports, due diligence, ESOP structuring, mergers and restructuring.',
        'defaults' => [
            'documents' => ['Company financial statements (3 years)', 'Cap table / shareholding pattern', 'Brief of the transaction'],
            'benefits' => ['Registered valuers and experienced CAs/CSs', 'Investor-grade documentation', 'Regulatory compliance (FEMA, Companies Act)', 'Confidential and reliable'],
            'who_needs' => ['Startups raising funds', 'Companies planning M&A', 'Businesses issuing ESOPs'],
            'steps' => [['Scoping', 'Define the transaction and deliverables.', '1–2 days'], ['Analysis', 'Financial, legal and tax review.', '1–2 weeks'], ['Report', 'Valuation / due-diligence report issued.', '2–3 weeks']],
            'fields' => [['Type of advisory', 'advisory_type', 'select', true, ['Valuation', 'Due Diligence', 'ESOP', 'Merger / Restructuring', 'Other']], ['Transaction details', 'details', 'textarea', true]],
        ],
        'services' => [
            ['name' => 'Corporate Advisory', 'slug' => 'corporate-advisory-services', 'price' => 19999, 'discount' => 14999, 'time' => '2–3 weeks', 'icon' => 'bi-graph-up-arrow', 'short' => 'Valuation, due diligence, ESOP and restructuring advisory.'],
        ],
    ],
    [
        'name' => 'Business Technology', 'slug' => 'business-technology', 'icon' => 'bi-cpu',
        'tagline' => 'Websites, apps and business software to grow faster.',
        'description' => 'Websites, mobile apps and business software — ERP, CRM and accounting — built and supported by our technology team.',
        'defaults' => [
            'documents' => ['Requirement brief', 'Brand assets (logo, colours) if available'],
            'benefits' => ['Built for Indian SMEs', 'Fixed-scope pricing', 'Secure and scalable', 'Ongoing support and maintenance'],
            'who_needs' => ['Businesses going digital', 'SMEs replacing spreadsheets', 'Startups building their product'],
            'steps' => [['Requirement discussion', 'We understand your goals and scope.', '1–2 days'], ['Proposal & design', 'Scope, timeline and design are agreed.', '3–5 days'], ['Development', 'Built in sprints with regular demos.', '2–8 weeks'], ['Launch & support', 'Go-live, training and support.', 'Ongoing']],
            'fields' => [['Describe your requirement', 'requirement', 'textarea', true], ['Expected timeline', 'timeline', 'select', false, ['ASAP', '1 month', '1–3 months', 'Flexible']], ['Budget range', 'budget', 'select', false, ['Below ₹50,000', '₹50,000 – ₹2 lakh', '₹2 lakh – ₹10 lakh', 'Above ₹10 lakh']]],
        ],
        'services' => [
            ['name' => 'Website Development', 'slug' => 'website-development', 'price' => 14999, 'discount' => 9999, 'time' => '2–4 weeks', 'featured' => true, 'icon' => 'bi-window', 'short' => 'Professional, mobile-friendly business website with SEO basics.'],
            ['name' => 'Software Development', 'slug' => 'software-development', 'price' => 49999, 'discount' => null, 'time' => '4–12 weeks', 'icon' => 'bi-code-slash', 'short' => 'Custom software built around your business processes.'],
            ['name' => 'ERP', 'slug' => 'erp-software', 'price' => 99999, 'discount' => null, 'time' => '6–12 weeks', 'icon' => 'bi-diagram-3', 'short' => 'ERP implementation for inventory, production, sales and finance.'],
            ['name' => 'CRM', 'slug' => 'crm-software', 'price' => 29999, 'discount' => 24999, 'time' => '3–6 weeks', 'icon' => 'bi-person-lines-fill', 'short' => 'CRM to manage leads, customers, follow-ups and sales pipeline.'],
            ['name' => 'Accounting Software', 'slug' => 'accounting-software', 'price' => 19999, 'discount' => 14999, 'time' => '2–4 weeks', 'icon' => 'bi-calculator', 'short' => 'GST-ready accounting software setup and customisation.'],
            ['name' => 'Mobile App', 'slug' => 'mobile-app-development', 'price' => 79999, 'discount' => null, 'time' => '6–12 weeks', 'icon' => 'bi-phone', 'short' => 'Android and iOS app development for your business.'],
            ['name' => 'IT Support', 'slug' => 'it-support', 'price' => 4999, 'discount' => 3999, 'time' => 'Monthly', 'billing' => 'recurring', 'interval' => 'monthly', 'compliance' => 'advisory_review', 'icon' => 'bi-headset', 'short' => 'Monthly IT support for systems, email, security and backups.'],
        ],
    ],
];
