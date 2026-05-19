<?php

namespace WeDevs\ERP\CRM\CLI\Seed;

class CrmDataProvider {

    /**
     * Company names.
     *
     * @return array
     */
    public static function companies() {
        return [
            [ 'name' => 'TechVision Solutions', 'website' => 'https://techvision.example.com' ],
            [ 'name' => 'Global Marketing Inc', 'website' => 'https://globalmarketing.example.com' ],
            [ 'name' => 'Pinnacle Industries', 'website' => 'https://pinnacle-ind.example.com' ],
            [ 'name' => 'Sunrise Healthcare', 'website' => 'https://sunrisehc.example.com' ],
            [ 'name' => 'MetroFinance Group', 'website' => 'https://metrofinance.example.com' ],
            [ 'name' => 'CloudNine Software', 'website' => 'https://cloudnine.example.com' ],
            [ 'name' => 'GreenLeaf Organics', 'website' => 'https://greenleaf.example.com' ],
            [ 'name' => 'Summit Consulting', 'website' => 'https://summitconsult.example.com' ],
            [ 'name' => 'Horizon Media', 'website' => 'https://horizonmedia.example.com' ],
            [ 'name' => 'BlueOcean Logistics', 'website' => 'https://blueocean.example.com' ],
            [ 'name' => 'Evergreen Construction', 'website' => 'https://evergreen-const.example.com' ],
            [ 'name' => 'Pioneer Education', 'website' => 'https://pioneered.example.com' ],
            [ 'name' => 'NexGen Retail', 'website' => 'https://nexgenretail.example.com' ],
            [ 'name' => 'Stellar Manufacturing', 'website' => 'https://stellarmfg.example.com' ],
            [ 'name' => 'Pacific Trading Co', 'website' => 'https://pacifictrading.example.com' ],
            [ 'name' => 'Apex Digital Agency', 'website' => 'https://apexdigital.example.com' ],
            [ 'name' => 'Ironclad Security', 'website' => 'https://ironcladsec.example.com' ],
            [ 'name' => 'Meridian Analytics', 'website' => 'https://meridiananalytics.example.com' ],
            [ 'name' => 'Cascade Renewable Energy', 'website' => 'https://cascaderenew.example.com' ],
            [ 'name' => 'Vanguard Pharma', 'website' => 'https://vanguardpharma.example.com' ],
            [ 'name' => 'Northstar Ventures', 'website' => 'https://northstarvc.example.com' ],
            [ 'name' => 'Redwood Hospitality', 'website' => 'https://redwoodhosp.example.com' ],
            [ 'name' => 'Titan Aerospace', 'website' => 'https://titanaero.example.com' ],
            [ 'name' => 'Luminary Creative', 'website' => 'https://luminarycreative.example.com' ],
            [ 'name' => 'Cobalt Fintech', 'website' => 'https://cobaltfintech.example.com' ],
            [ 'name' => 'Anchor Real Estate', 'website' => 'https://anchorre.example.com' ],
            [ 'name' => 'Vantage Telecom', 'website' => 'https://vantagetelecom.example.com' ],
            [ 'name' => 'Sterling Legal Group', 'website' => 'https://sterlinglegl.example.com' ],
            [ 'name' => 'Oasis Wellness', 'website' => 'https://oasiswellness.example.com' ],
            [ 'name' => 'Frontier Agritech', 'website' => 'https://frontieragri.example.com' ],
        ];
    }

    /**
     * Contact first names.
     *
     * @return array
     */
    public static function first_names() {
        return [
            'James', 'Emma', 'Michael', 'Olivia', 'William', 'Ava', 'David', 'Sophia',
            'Robert', 'Isabella', 'John', 'Mia', 'Richard', 'Charlotte', 'Thomas',
            'Amelia', 'Christopher', 'Harper', 'Daniel', 'Evelyn', 'Matthew', 'Abigail',
            'Anthony', 'Emily', 'Mark', 'Elizabeth', 'Paul', 'Sofia', 'Steven', 'Avery',
            'Andrew', 'Ella', 'Joshua', 'Madison', 'Kenneth', 'Scarlett', 'Kevin', 'Victoria',
            'Brian', 'Aria', 'George', 'Grace', 'Timothy', 'Chloe', 'Ronald', 'Camila',
            'Edward', 'Penelope', 'Jason', 'Riley',
            'Nathan', 'Zoe', 'Ryan', 'Hannah', 'Tyler', 'Lily', 'Brandon', 'Natalie',
            'Samuel', 'Leah', 'Benjamin', 'Audrey', 'Jacob', 'Savannah', 'Logan', 'Brooklyn',
            'Ethan', 'Bella', 'Dylan', 'Claire', 'Zachary', 'Skylar', 'Austin', 'Lucy',
            'Lucas', 'Paisley', 'Mason', 'Everly', 'Liam', 'Anna', 'Noah', 'Caroline',
            'Aiden', 'Genesis', 'Caleb', 'Aaliyah', 'Hunter', 'Kennedy', 'Connor', 'Sadie',
            'Jordan', 'Hailey', 'Adrian', 'Eva', 'Gavin', 'Naomi', 'Ian', 'Aurora',
            'Carlos', 'Maya', 'Luis', 'Layla', 'Alex', 'Elena', 'Omar', 'Fatima',
            'Ravi', 'Priya', 'Arjun', 'Ananya', 'Wei', 'Lin', 'Yusuf', 'Amira',
        ];
    }

    /**
     * Contact last names.
     *
     * @return array
     */
    public static function last_names() {
        return [
            'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis',
            'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson',
            'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin', 'Lee', 'Perez', 'Thompson',
            'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson',
            'Walker', 'Young', 'Allen', 'King', 'Wright', 'Scott', 'Torres', 'Nguyen',
            'Hill', 'Flores', 'Green', 'Adams', 'Nelson', 'Baker', 'Hall', 'Rivera',
            'Campbell', 'Mitchell', 'Carter', 'Roberts',
            'Chen', 'Patel', 'Kumar', 'Singh', 'Kim', 'Park', 'Yamamoto', 'Nakamura',
            'Ahmed', 'Hassan', 'Ali', 'Khan', 'Sharma', 'Gupta', 'Nair', 'Rao',
            'Santos', 'Silva', 'Oliveira', 'Ferreira', 'Dubois', 'Dupont', 'Bernard', 'Morin',
            'Müller', 'Schmidt', 'Fischer', 'Weber', 'Rossi', 'Ferrari', 'Russo', 'Esposito',
            'Murphy', 'Kelly', 'Sullivan', 'Walsh', 'Andersen', 'Larsen', 'Eriksson', 'Lindqvist',
            'Morrison', 'Dixon', 'Fletcher', 'Barnes', 'Owens', 'Bryant', 'Simmons', 'Ford',
        ];
    }

    /**
     * Job titles.
     *
     * @return array
     */
    public static function job_titles() {
        return [
            'CEO', 'CTO', 'CFO', 'COO', 'VP of Sales', 'VP of Marketing',
            'Director of Operations', 'Project Manager', 'Product Manager',
            'Marketing Manager', 'Sales Manager', 'Account Executive',
            'Software Engineer', 'Business Analyst', 'HR Manager',
            'Purchasing Manager', 'Operations Manager', 'Customer Success Manager',
            'IT Director', 'Finance Director', 'Creative Director',
            'Sales Representative', 'Marketing Coordinator', 'Office Manager',
            'Chief Revenue Officer', 'VP of Engineering', 'VP of Product',
            'Head of Growth', 'Head of Customer Experience', 'Head of Partnerships',
            'Solutions Architect', 'Senior Account Manager', 'Regional Sales Director',
            'Digital Marketing Specialist', 'Data Analyst', 'DevOps Engineer',
            'Procurement Specialist', 'Supply Chain Manager', 'Legal Counsel',
            'Brand Manager', 'Content Strategist', 'UX Designer',
            'Enterprise Architect', 'Security Engineer', 'Cloud Architect',
        ];
    }

    /**
     * Life stages with weighted distribution.
     *
     * @return array
     */
    public static function life_stages() {
        return [
            'subscriber'  => 20,
            'lead'        => 35,
            'opportunity' => 25,
            'customer'    => 20,
        ];
    }

    /**
     * Contact sources.
     *
     * @return array
     */
    public static function sources() {
        return [
            'advert', 'chat', 'contact_form', 'employee_referral', 'external_referral',
            'marketing_campaign', 'newsletter', 'online_store', 'optin_form', 'partner',
            'phone', 'public_relations', 'search_engine', 'social_media', 'trade_show',
            'web_download', 'web_research',
        ];
    }

    /**
     * Contact group definitions.
     *
     * @return array
     */
    public static function contact_groups() {
        return [
            [ 'name' => 'Newsletter Subscribers', 'description' => 'Contacts subscribed to our newsletter' ],
            [ 'name' => 'VIP Customers', 'description' => 'High-value customers requiring premium support' ],
            [ 'name' => 'Event Attendees 2024', 'description' => 'Contacts who attended our 2024 events' ],
            [ 'name' => 'Product Beta Testers', 'description' => 'Users participating in beta testing programs' ],
            [ 'name' => 'Enterprise Leads', 'description' => 'Leads from enterprise-level organizations' ],
            [ 'name' => 'Partner Network', 'description' => 'Business partners and affiliates' ],
            [ 'name' => 'Webinar Registrants', 'description' => 'Contacts who registered for webinars' ],
            [ 'name' => 'Inactive Contacts', 'description' => 'Contacts with no activity in last 6 months' ],
            [ 'name' => 'Cold Leads', 'description' => 'Prospects with no recent engagement' ],
            [ 'name' => 'Warm Prospects', 'description' => 'Prospects showing active interest' ],
            [ 'name' => 'Churned Customers', 'description' => 'Previously active customers who cancelled' ],
            [ 'name' => 'Referral Sources', 'description' => 'Contacts who have referred new business' ],
            [ 'name' => 'SMB Segment', 'description' => 'Small and medium business contacts' ],
            [ 'name' => 'Mid-Market Segment', 'description' => 'Mid-market company contacts' ],
            [ 'name' => 'Strategic Accounts', 'description' => 'Key accounts with long-term potential' ],
            [ 'name' => 'Conference Leads 2025', 'description' => 'Leads acquired at 2025 conferences' ],
        ];
    }

    /**
     * Street addresses.
     *
     * @return array
     */
    public static function streets() {
        return [
            '123 Main St', '456 Oak Ave', '789 Pine Rd', '321 Elm Blvd', '654 Maple Dr',
            '987 Cedar Ln', '147 Birch Way', '258 Walnut St', '369 Cherry Ave', '741 Spruce Rd',
            '852 Willow Blvd', '963 Ash Dr', '159 Poplar Ln', '357 Hickory Way', '468 Chestnut St',
            '24 Riverside Dr', '88 Harbor View Rd', '200 Innovation Blvd', '15 Commerce St',
            '77 Parkside Ave', '1400 Technology Way', '300 Lakeshore Dr', '55 Gateway Pl',
            '901 Industrial Pkwy', '12 Summit Ridge Rd', '640 Westfield Ct', '18 Orchard Ln',
            '500 Executive Dr', '3300 Campus Way', '47 Millbrook Rd',
        ];
    }

    /**
     * Cities with states.
     *
     * @return array
     */
    public static function cities() {
        return [
            [ 'city' => 'New York', 'state' => 'NY', 'postal' => '10001', 'country' => 'US' ],
            [ 'city' => 'Los Angeles', 'state' => 'CA', 'postal' => '90001', 'country' => 'US' ],
            [ 'city' => 'Chicago', 'state' => 'IL', 'postal' => '60601', 'country' => 'US' ],
            [ 'city' => 'Houston', 'state' => 'TX', 'postal' => '77001', 'country' => 'US' ],
            [ 'city' => 'Phoenix', 'state' => 'AZ', 'postal' => '85001', 'country' => 'US' ],
            [ 'city' => 'Philadelphia', 'state' => 'PA', 'postal' => '19101', 'country' => 'US' ],
            [ 'city' => 'San Antonio', 'state' => 'TX', 'postal' => '78201', 'country' => 'US' ],
            [ 'city' => 'San Diego', 'state' => 'CA', 'postal' => '92101', 'country' => 'US' ],
            [ 'city' => 'Dallas', 'state' => 'TX', 'postal' => '75201', 'country' => 'US' ],
            [ 'city' => 'San Jose', 'state' => 'CA', 'postal' => '95101', 'country' => 'US' ],
            [ 'city' => 'Seattle', 'state' => 'WA', 'postal' => '98101', 'country' => 'US' ],
            [ 'city' => 'Denver', 'state' => 'CO', 'postal' => '80201', 'country' => 'US' ],
            [ 'city' => 'Boston', 'state' => 'MA', 'postal' => '02101', 'country' => 'US' ],
            [ 'city' => 'Atlanta', 'state' => 'GA', 'postal' => '30301', 'country' => 'US' ],
            [ 'city' => 'Miami', 'state' => 'FL', 'postal' => '33101', 'country' => 'US' ],
            [ 'city' => 'Portland', 'state' => 'OR', 'postal' => '97201', 'country' => 'US' ],
            [ 'city' => 'Austin', 'state' => 'TX', 'postal' => '78701', 'country' => 'US' ],
            [ 'city' => 'Minneapolis', 'state' => 'MN', 'postal' => '55401', 'country' => 'US' ],
            [ 'city' => 'London', 'state' => '', 'postal' => 'EC1A 1BB', 'country' => 'GB' ],
            [ 'city' => 'Manchester', 'state' => '', 'postal' => 'M1 1AA', 'country' => 'GB' ],
            [ 'city' => 'Toronto', 'state' => 'ON', 'postal' => 'M5H 2N2', 'country' => 'CA' ],
            [ 'city' => 'Vancouver', 'state' => 'BC', 'postal' => 'V6B 1A1', 'country' => 'CA' ],
            [ 'city' => 'Sydney', 'state' => 'NSW', 'postal' => '2000', 'country' => 'AU' ],
            [ 'city' => 'Melbourne', 'state' => 'VIC', 'postal' => '3000', 'country' => 'AU' ],
            [ 'city' => 'Berlin', 'state' => '', 'postal' => '10115', 'country' => 'DE' ],
            [ 'city' => 'Paris', 'state' => '', 'postal' => '75001', 'country' => 'FR' ],
            [ 'city' => 'Amsterdam', 'state' => '', 'postal' => '1011', 'country' => 'NL' ],
            [ 'city' => 'Singapore', 'state' => '', 'postal' => '018989', 'country' => 'SG' ],
            [ 'city' => 'Dubai', 'state' => '', 'postal' => '00000', 'country' => 'AE' ],
            [ 'city' => 'Mumbai', 'state' => 'MH', 'postal' => '400001', 'country' => 'IN' ],
        ];
    }

    /**
     * Note templates.
     *
     * @return array
     */
    public static function notes() {
        return [
            'Had a productive call discussing their requirements. They are interested in our enterprise solution.',
            'Follow-up scheduled for next week to present our proposal.',
            'Contact expressed concerns about pricing. Need to prepare a custom quote.',
            'Met at industry conference. Very interested in partnership opportunities.',
            'Requested product demo for their team. Scheduled for Friday.',
            'Customer reported minor issue with service. Support team is handling it.',
            'Renewal discussion planned for next month. High likelihood of upgrade.',
            'Introduced to decision maker. They want to see case studies.',
            'Budget approved for Q2. Ready to move forward with implementation.',
            'Waiting for legal review of contract terms.',
            'Competitor analysis requested. Need to highlight our unique features.',
            'Training session completed. Customer very satisfied with onboarding.',
            'Integration requirements documented. Technical team to review.',
            'Quarterly business review scheduled. Prepare performance metrics.',
            'Upsell opportunity identified. They need additional user licenses.',
            'Left voicemail. Will try again tomorrow.',
            'Sent intro email. Awaiting response.',
            'Contact referred us to their CTO. Meeting being arranged.',
            'Pilot evaluation extended by two weeks due to internal resourcing.',
            'Security questionnaire completed and submitted to procurement.',
            'Executive sponsor confirmed — deal fast-tracked to contract stage.',
            'Trial feedback very positive. Moving to commercial discussion.',
            'Pricing objection resolved with revised tier proposal.',
            'Decision delayed to next quarter due to board freeze.',
            'New contact at company after key champion left — re-engaging.',
            'Requested API documentation and integration guide.',
            'Confirmed mobile compatibility requirement — checking with product team.',
            'Customer attended webinar and asked follow-up questions on reporting.',
            'Annual review call completed. Customer expanded to 3 additional departments.',
            'Partner intro meeting went well. Joint go-to-market discussion started.',
        ];
    }

    /**
     * Meeting subjects.
     *
     * @return array
     */
    public static function meeting_subjects() {
        return [
            'Initial Discovery Call',
            'Product Demo Session',
            'Quarterly Business Review',
            'Contract Negotiation',
            'Technical Requirements Discussion',
            'Partnership Opportunity Meeting',
            'Onboarding Kickoff',
            'Support Escalation Review',
            'Strategic Planning Session',
            'Budget Review Meeting',
            'Implementation Planning',
            'Executive Briefing',
            'Renewal Discussion',
            'Feature Request Review',
            'Training Session',
            'Security Review Meeting',
            'Integration Scoping Session',
            'Pilot Kickoff',
            'Champion Alignment Call',
            'Competitive Differentiator Walkthrough',
            'Post-Implementation Review',
            'Annual Account Planning',
            'Stakeholder Introduction',
            'Procurement Process Overview',
            'Pricing Workshop',
        ];
    }

    /**
     * Call subjects.
     *
     * @return array
     */
    public static function call_subjects() {
        return [
            'Follow-up on proposal',
            'Check-in call',
            'Support inquiry',
            'Pricing discussion',
            'Feature clarification',
            'Renewal reminder',
            'Introduction call',
            'Demo scheduling',
            'Technical support',
            'Account review',
            'Feedback collection',
            'Referral request',
            'Contract status check',
            'Escalation follow-up',
            'Onboarding progress check',
            'Decision timeline inquiry',
            'Reference check assistance',
            'Stakeholder introduction',
            'Win/loss debrief',
            'Reactivation outreach',
        ];
    }

    /**
     * Task titles.
     *
     * @return array
     */
    public static function task_titles() {
        return [
            'Send proposal document',
            'Prepare custom quote',
            'Schedule product demo',
            'Follow up on meeting',
            'Review contract terms',
            'Create case study',
            'Update CRM records',
            'Prepare presentation',
            'Research competitor pricing',
            'Send renewal reminder',
            'Coordinate with technical team',
            'Gather customer testimonial',
            'Update contact information',
            'Process refund request',
            'Schedule training session',
            'Complete security questionnaire',
            'Draft SOW document',
            'Arrange reference call',
            'Submit legal NDA for review',
            'Confirm budget approval',
            'Upload signed contract',
            'Create onboarding checklist',
            'Send product roadmap summary',
            'Identify upsell opportunity',
            'Log call notes in CRM',
            'Send invoice to accounts',
            'Coordinate with support on open ticket',
            'Prepare executive summary',
            'Request LinkedIn introduction',
        ];
    }

    /**
     * Email subjects.
     *
     * @return array
     */
    public static function email_subjects() {
        return [
            'Thank you for your inquiry',
            'Your proposal is ready',
            'Meeting confirmation',
            'Following up on our conversation',
            'Important updates to your account',
            'Your subscription renewal',
            'Welcome to our platform',
            'Product update announcement',
            'Your feedback matters',
            'Special offer just for you',
            'Invoice attached',
            'Contract for your review',
            'Next steps after our call',
            'Resources from today\'s demo',
            'Action items from our meeting',
            'Your trial is expiring soon',
            'Case study relevant to your industry',
            'Referral program invitation',
            'Quarterly newsletter — Q1 2025',
            'Security compliance documentation enclosed',
            'Integration guide for your team',
            'Checking in — any questions?',
        ];
    }

    /**
     * Pipeline definitions for deals.
     *
     * @return array
     */
    public static function pipelines() {
        return [
            [
                'title'  => 'Sales Pipeline',
                'stages' => [
                    [ 'title' => 'Qualified', 'probability' => 10, 'order' => 1, 'life_stage' => 'lead' ],
                    [ 'title' => 'Meeting Scheduled', 'probability' => 25, 'order' => 2, 'life_stage' => 'lead' ],
                    [ 'title' => 'Proposal Sent', 'probability' => 50, 'order' => 3, 'life_stage' => 'opportunity' ],
                    [ 'title' => 'Negotiation', 'probability' => 75, 'order' => 4, 'life_stage' => 'opportunity' ],
                    [ 'title' => 'Closed Won', 'probability' => 100, 'order' => 5, 'life_stage' => 'customer' ],
                ],
            ],
            [
                'title'  => 'Enterprise Pipeline',
                'stages' => [
                    [ 'title' => 'Discovery', 'probability' => 5, 'order' => 1, 'life_stage' => 'subscriber' ],
                    [ 'title' => 'Requirements Gathering', 'probability' => 15, 'order' => 2, 'life_stage' => 'lead' ],
                    [ 'title' => 'Solution Design', 'probability' => 30, 'order' => 3, 'life_stage' => 'lead' ],
                    [ 'title' => 'POC/Trial', 'probability' => 50, 'order' => 4, 'life_stage' => 'opportunity' ],
                    [ 'title' => 'Procurement', 'probability' => 70, 'order' => 5, 'life_stage' => 'opportunity' ],
                    [ 'title' => 'Legal Review', 'probability' => 85, 'order' => 6, 'life_stage' => 'opportunity' ],
                    [ 'title' => 'Closed Won', 'probability' => 100, 'order' => 7, 'life_stage' => 'customer' ],
                ],
            ],
            [
                'title'  => 'Partner Pipeline',
                'stages' => [
                    [ 'title' => 'Initial Contact', 'probability' => 10, 'order' => 1, 'life_stage' => 'lead' ],
                    [ 'title' => 'Partner Evaluation', 'probability' => 30, 'order' => 2, 'life_stage' => 'lead' ],
                    [ 'title' => 'Agreement Draft', 'probability' => 60, 'order' => 3, 'life_stage' => 'opportunity' ],
                    [ 'title' => 'Executive Sign-off', 'probability' => 85, 'order' => 4, 'life_stage' => 'opportunity' ],
                    [ 'title' => 'Active Partner', 'probability' => 100, 'order' => 5, 'life_stage' => 'customer' ],
                ],
            ],
        ];
    }

    /**
     * Deal titles.
     *
     * @return array
     */
    public static function deal_titles() {
        return [
            'Enterprise License Agreement',
            'Annual Subscription Renewal',
            'Custom Development Project',
            'Professional Services Engagement',
            'Training Package Deal',
            'Platform Migration',
            'Multi-year Contract',
            'Pilot Program',
            'Integration Services',
            'Support Upgrade',
            'Additional User Licenses',
            'White Label Partnership',
            'Consulting Engagement',
            'Security Audit Services',
            'Data Analytics Package',
            'Cloud Infrastructure Setup',
            'Managed Services Contract',
            'SaaS Expansion Deal',
            'API Access Agreement',
            'Compliance Toolkit License',
            'Customer Portal Deployment',
            'Mobile App Development',
            'ERP Implementation',
            'Business Intelligence Dashboard',
            'Automation Workflow Package',
            'Staff Augmentation Contract',
            'Digital Transformation Retainer',
            'Co-Marketing Partnership',
            'Reseller Agreement',
            'Premium Support Plan',
        ];
    }

    /**
     * Lost deal reasons.
     *
     * @return array
     */
    public static function lost_reasons() {
        return [
            'Budget constraints',
            'Chose competitor',
            'Project cancelled',
            'Timing not right',
            'No response',
            'Features missing',
            'Price too high',
            'Internal restructuring',
            'Decision maker changed',
            'Requirements changed',
            'Went with in-house solution',
            'Procurement process stalled',
            'Security concerns not resolved',
            'Integration complexity too high',
            'Company acquired',
            'Evaluation criteria shifted',
            'ROI not justified',
            'Executive sponsor left',
        ];
    }

    /**
     * Currencies.
     *
     * @return array
     */
    public static function currencies() {
        return [ 'USD', 'EUR', 'GBP', 'CAD', 'AUD', 'SGD', 'AED', 'INR', 'JPY', 'NZD' ];
    }

    /**
     * Get a weighted random life stage.
     *
     * @return string
     */
    public static function weighted_life_stage() {
        $stages  = self::life_stages();
        $total   = array_sum( $stages );
        $random  = mt_rand( 1, $total );
        $current = 0;

        foreach ( $stages as $stage => $weight ) {
            $current += $weight;
            if ( $random <= $current ) {
                return $stage;
            }
        }

        return 'lead';
    }
}
