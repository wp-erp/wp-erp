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
            [ 'city' => 'London', 'state' => '', 'postal' => 'EC1A 1BB', 'country' => 'GB' ],
            [ 'city' => 'Toronto', 'state' => 'ON', 'postal' => 'M5H 2N2', 'country' => 'CA' ],
            [ 'city' => 'Sydney', 'state' => 'NSW', 'postal' => '2000', 'country' => 'AU' ],
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
        ];
    }

    /**
     * Currencies.
     *
     * @return array
     */
    public static function currencies() {
        return [ 'USD', 'EUR', 'GBP', 'CAD', 'AUD' ];
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
