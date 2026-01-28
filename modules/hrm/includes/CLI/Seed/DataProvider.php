<?php

namespace WeDevs\ERP\HRM\CLI\Seed;

class DataProvider {

    public static function first_names_male() {
        return [
            'James', 'Robert', 'John', 'Michael', 'David',
            'William', 'Richard', 'Joseph', 'Thomas', 'Charles',
            'Christopher', 'Daniel', 'Matthew', 'Anthony', 'Mark',
            'Donald', 'Steven', 'Andrew', 'Paul', 'Joshua',
            'Kenneth', 'Kevin', 'Brian', 'George', 'Timothy',
        ];
    }

    public static function first_names_female() {
        return [
            'Mary', 'Patricia', 'Jennifer', 'Linda', 'Barbara',
            'Elizabeth', 'Susan', 'Jessica', 'Sarah', 'Karen',
            'Lisa', 'Nancy', 'Betty', 'Margaret', 'Sandra',
            'Ashley', 'Emily', 'Donna', 'Michelle', 'Carol',
            'Amanda', 'Dorothy', 'Melissa', 'Deborah', 'Stephanie',
        ];
    }

    public static function last_names() {
        return [
            'Smith', 'Johnson', 'Williams', 'Brown', 'Jones',
            'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez',
            'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson',
            'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin',
            'Lee', 'Perez', 'Thompson', 'White', 'Harris',
            'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson',
            'Walker', 'Young', 'Allen', 'King', 'Wright',
            'Scott', 'Torres', 'Nguyen', 'Hill', 'Flores',
            'Green', 'Adams', 'Nelson', 'Baker', 'Hall',
            'Rivera', 'Campbell', 'Mitchell', 'Carter', 'Roberts',
        ];
    }

    public static function departments() {
        return [
            [ 'title' => 'Engineering', 'description' => 'Software development and technical infrastructure' ],
            [ 'title' => 'Human Resources', 'description' => 'Employee management, recruitment and compliance' ],
            [ 'title' => 'Finance & Accounting', 'description' => 'Financial planning, budgeting and accounting' ],
            [ 'title' => 'Marketing', 'description' => 'Brand management, campaigns and digital marketing' ],
            [ 'title' => 'Sales', 'description' => 'Business development and client acquisition' ],
            [ 'title' => 'Operations', 'description' => 'Day-to-day business operations and logistics' ],
            [ 'title' => 'Product Management', 'description' => 'Product strategy, roadmap and feature planning' ],
            [ 'title' => 'Customer Support', 'description' => 'Customer service and technical support' ],
        ];
    }

    public static function designations() {
        return [
            [ 'title' => 'Chief Executive Officer', 'description' => 'Top executive responsible for company strategy' ],
            [ 'title' => 'Chief Technology Officer', 'description' => 'Head of technology and engineering' ],
            [ 'title' => 'VP of Engineering', 'description' => 'Vice president overseeing engineering teams' ],
            [ 'title' => 'Engineering Manager', 'description' => 'Manages engineering team and projects' ],
            [ 'title' => 'Senior Software Engineer', 'description' => 'Experienced developer with technical leadership' ],
            [ 'title' => 'Software Engineer', 'description' => 'Core software development role' ],
            [ 'title' => 'Junior Developer', 'description' => 'Entry-level development role' ],
            [ 'title' => 'HR Manager', 'description' => 'Manages human resources operations' ],
            [ 'title' => 'HR Executive', 'description' => 'Handles HR processes and employee relations' ],
            [ 'title' => 'Finance Manager', 'description' => 'Manages financial operations and reporting' ],
            [ 'title' => 'Accountant', 'description' => 'Handles bookkeeping and financial records' ],
            [ 'title' => 'Marketing Manager', 'description' => 'Leads marketing strategy and campaigns' ],
            [ 'title' => 'Sales Executive', 'description' => 'Handles sales and client relationships' ],
            [ 'title' => 'Operations Lead', 'description' => 'Leads daily operations and process improvement' ],
            [ 'title' => 'Product Manager', 'description' => 'Drives product vision and delivery' ],
        ];
    }

    public static function leave_types() {
        return [
            [ 'name' => 'Annual Leave', 'description' => 'Paid vacation days for personal time off', 'days' => 20, 'color' => '#4CAF50' ],
            [ 'name' => 'Sick Leave', 'description' => 'Leave for illness or medical appointments', 'days' => 14, 'color' => '#F44336' ],
            [ 'name' => 'Casual Leave', 'description' => 'Short-notice leave for personal matters', 'days' => 10, 'color' => '#2196F3' ],
            [ 'name' => 'Maternity Leave', 'description' => 'Leave for expecting mothers', 'days' => 90, 'color' => '#E91E63' ],
            [ 'name' => 'Paternity Leave', 'description' => 'Leave for new fathers', 'days' => 10, 'color' => '#9C27B0' ],
        ];
    }

    public static function holidays() {
        return [
            [ 'title' => "New Year's Day", 'month' => 1, 'day' => 1 ],
            [ 'title' => 'Martin Luther King Jr. Day', 'month' => 1, 'day' => 20 ],
            [ 'title' => "Presidents' Day", 'month' => 2, 'day' => 17 ],
            [ 'title' => 'Memorial Day', 'month' => 5, 'day' => 26 ],
            [ 'title' => 'Independence Day', 'month' => 7, 'day' => 4 ],
            [ 'title' => 'Labor Day', 'month' => 9, 'day' => 1 ],
            [ 'title' => 'Columbus Day', 'month' => 10, 'day' => 13 ],
            [ 'title' => 'Veterans Day', 'month' => 11, 'day' => 11 ],
            [ 'title' => 'Thanksgiving Day', 'month' => 11, 'day' => 27 ],
            [ 'title' => 'Christmas Eve', 'month' => 12, 'day' => 24 ],
            [ 'title' => 'Christmas Day', 'month' => 12, 'day' => 25 ],
            [ 'title' => "New Year's Eve", 'month' => 12, 'day' => 31 ],
        ];
    }

    public static function training_topics() {
        return [
            [ 'title' => 'Leadership Development Program', 'description' => 'Build leadership skills for team management and strategic thinking.' ],
            [ 'title' => 'Agile & Scrum Methodology', 'description' => 'Master agile project management with Scrum framework.' ],
            [ 'title' => 'Data Analysis Fundamentals', 'description' => 'Learn data analysis techniques and tools for business insights.' ],
            [ 'title' => 'Effective Communication Workshop', 'description' => 'Improve professional communication and presentation skills.' ],
            [ 'title' => 'Project Management Essentials', 'description' => 'Fundamentals of project planning, execution and delivery.' ],
            [ 'title' => 'Cybersecurity Awareness', 'description' => 'Protect company data with security best practices.' ],
            [ 'title' => 'Customer Service Excellence', 'description' => 'Deliver exceptional customer experience and resolve issues.' ],
            [ 'title' => 'Financial Planning Basics', 'description' => 'Introduction to budgeting and financial forecasting.' ],
            [ 'title' => 'Team Building & Collaboration', 'description' => 'Strengthen team dynamics and cross-functional collaboration.' ],
            [ 'title' => 'Technical Writing Workshop', 'description' => 'Create clear documentation and technical content.' ],
        ];
    }

    public static function asset_categories() {
        return [
            'IT Equipment',
            'Furniture',
            'Vehicles',
            'Communication Devices',
            'Office Supplies',
        ];
    }

    public static function assets() {
        return [
            [ 'group' => 'Laptop', 'manufacturer' => 'Dell', 'model' => 'Latitude 5540', 'category' => 'IT Equipment', 'price' => 1200.00 ],
            [ 'group' => 'Laptop', 'manufacturer' => 'Apple', 'model' => 'MacBook Pro 14', 'category' => 'IT Equipment', 'price' => 2499.00 ],
            [ 'group' => 'Laptop', 'manufacturer' => 'Lenovo', 'model' => 'ThinkPad X1 Carbon', 'category' => 'IT Equipment', 'price' => 1450.00 ],
            [ 'group' => 'Monitor', 'manufacturer' => 'LG', 'model' => '27UK850-W', 'category' => 'IT Equipment', 'price' => 450.00 ],
            [ 'group' => 'Monitor', 'manufacturer' => 'Dell', 'model' => 'U2723QE', 'category' => 'IT Equipment', 'price' => 520.00 ],
            [ 'group' => 'Keyboard', 'manufacturer' => 'Logitech', 'model' => 'MX Keys', 'category' => 'IT Equipment', 'price' => 100.00 ],
            [ 'group' => 'Mouse', 'manufacturer' => 'Logitech', 'model' => 'MX Master 3', 'category' => 'IT Equipment', 'price' => 100.00 ],
            [ 'group' => 'Desk', 'manufacturer' => 'Autonomous', 'model' => 'SmartDesk Pro', 'category' => 'Furniture', 'price' => 600.00 ],
            [ 'group' => 'Chair', 'manufacturer' => 'Herman Miller', 'model' => 'Aeron', 'category' => 'Furniture', 'price' => 1400.00 ],
            [ 'group' => 'Chair', 'manufacturer' => 'Steelcase', 'model' => 'Leap V2', 'category' => 'Furniture', 'price' => 1000.00 ],
            [ 'group' => 'Phone', 'manufacturer' => 'Apple', 'model' => 'iPhone 15 Pro', 'category' => 'Communication Devices', 'price' => 999.00 ],
            [ 'group' => 'Phone', 'manufacturer' => 'Samsung', 'model' => 'Galaxy S24', 'category' => 'Communication Devices', 'price' => 800.00 ],
            [ 'group' => 'Headset', 'manufacturer' => 'Jabra', 'model' => 'Evolve2 85', 'category' => 'Communication Devices', 'price' => 380.00 ],
            [ 'group' => 'Printer', 'manufacturer' => 'HP', 'model' => 'LaserJet Pro M404', 'category' => 'Office Supplies', 'price' => 350.00 ],
            [ 'group' => 'Projector', 'manufacturer' => 'Epson', 'model' => 'PowerLite 1795F', 'category' => 'Office Supplies', 'price' => 750.00 ],
        ];
    }

    public static function shift_definitions() {
        return [
            [ 'name' => 'Morning Shift', 'start' => '06:00:00', 'end' => '14:00:00' ],
            [ 'name' => 'Day Shift', 'start' => '09:00:00', 'end' => '17:00:00' ],
            [ 'name' => 'Evening Shift', 'start' => '14:00:00', 'end' => '22:00:00' ],
        ];
    }

    public static function leave_reasons() {
        return [
            'Family event',
            'Personal appointment',
            'Vacation travel',
            'Home maintenance',
            'Medical checkup',
            'Feeling unwell',
            'Family emergency',
            'Mental health day',
            'Religious observance',
            'Moving house',
        ];
    }

    public static function announcement_templates() {
        return [
            [ 'title' => 'Company Annual General Meeting', 'body' => 'We are pleased to announce the upcoming Annual General Meeting. All employees are expected to attend. The agenda includes a review of company performance, future plans, and an open Q&A session with leadership.' ],
            [ 'title' => 'Updated Work From Home Policy', 'body' => 'Please be informed about updates to our work from home policy. Employees may now work remotely up to 3 days per week with manager approval. Please review the full policy document on the HR portal.' ],
            [ 'title' => 'Employee Wellness Program Launch', 'body' => 'We are excited to launch our new Employee Wellness Program. This program includes gym memberships, mental health support, and monthly wellness workshops. Sign up through the HR portal.' ],
            [ 'title' => 'Quarterly Performance Review Cycle', 'body' => 'The quarterly performance review cycle begins next week. Please complete your self-assessment by the deadline. Your managers will schedule one-on-one sessions to discuss goals and feedback.' ],
            [ 'title' => 'New Office Safety Guidelines', 'body' => 'Updated safety guidelines have been issued for all office locations. Please review the emergency procedures, fire exit routes, and first aid station locations posted on each floor.' ],
            [ 'title' => 'IT Security Update - Password Policy', 'body' => 'As part of our ongoing security improvements, all employees must update their passwords by end of month. New passwords must be at least 12 characters with mixed case, numbers, and symbols.' ],
            [ 'title' => 'Holiday Season Office Closure', 'body' => 'The office will be closed during the holiday season. Please plan your tasks accordingly and ensure all critical deliverables are completed before the closure period begins.' ],
            [ 'title' => 'Team Building Event Announcement', 'body' => 'We are organizing a team building event next month. Activities include outdoor sports, team challenges, and a group dinner. Please RSVP through the events portal by end of this week.' ],
            [ 'title' => 'New Employee Benefits Package', 'body' => 'We are pleased to announce enhanced employee benefits starting next quarter. This includes increased healthcare coverage, additional paid time off, and expanded education assistance.' ],
            [ 'title' => 'Office Renovation Schedule', 'body' => 'Renovation work will begin on the 3rd floor next month. Affected teams will be temporarily relocated. Please check with your manager for your temporary seating arrangement.' ],
            [ 'title' => 'Annual Company Picnic', 'body' => 'Join us for the annual company picnic! Food, games, and fun for employees and their families. Details about the venue and schedule will be shared soon.' ],
            [ 'title' => 'Professional Development Budget', 'body' => 'Each employee has been allocated a professional development budget for this fiscal year. Use it for conferences, courses, certifications, or books. Submit requests through the learning portal.' ],
        ];
    }

    public static function pay_item_categories() {
        return [
            'allowance' => [
                [ 'name' => 'Basic Salary', 'type' => 1 ],
                [ 'name' => 'House Rent Allowance', 'type' => 1 ],
                [ 'name' => 'Transport Allowance', 'type' => 1 ],
                [ 'name' => 'Medical Allowance', 'type' => 1 ],
            ],
            'deduction' => [
                [ 'name' => 'Provident Fund', 'type' => 0 ],
                [ 'name' => 'Professional Tax', 'type' => 0 ],
            ],
            'tax' => [
                [ 'name' => 'Income Tax', 'type' => 2 ],
            ],
        ];
    }

    public static function random_element( $array ) {
        return $array[ array_rand( $array ) ];
    }

    public static function random_date_between( $start, $end ) {
        $start_ts = strtotime( $start );
        $end_ts   = strtotime( $end );
        $rand_ts  = mt_rand( $start_ts, $end_ts );

        return date( 'Y-m-d', $rand_ts );
    }

    public static function random_working_date_between( $start, $end ) {
        $max_attempts = 50;

        for ( $i = 0; $i < $max_attempts; $i++ ) {
            $date = self::random_date_between( $start, $end );
            $dow  = date( 'N', strtotime( $date ) );

            if ( $dow <= 5 ) {
                return $date;
            }
        }

        return $start;
    }
}
