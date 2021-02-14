<?php
return [
    'columnsName' =>
        [
            'user' => 'User',
            'project' => 'Projects',
            'plan' => 'Plan',
            'location' => 'Location',
            'tags' => 'Tags',
            'businessInfo' => 'Business Info',
            'subscriptionStatus' => 'Subscription Status',
            'lastActivity' => 'Last Activity',
            'views' => 'Views',
            'bandwidth' => 'Bandwidth',
            'age' => 'Age(Member since)',
            'relatedUsers' => 'Related Users',
            'contactSize' => 'Contact Size',
            'compliance' => 'Compliance',
            'serviceCost' => 'Service Cost',
            'stagePage' => 'Stage Page',
            'signUpPage' => 'Sign Up Page',
            'referral' => 'Referral',
            'storage' => 'Storage',
            'role' => 'Role',
        ],
    'columnsData' =>
        [
            'project' => ['videosCount', 'projectsCount'],
            'plan' => ['firstUpgrade', 'lastRenewed', 'plan', 'managerAccount'],
            'location' => ['city', 'state', 'country'],
            'tags' => ['tag'],
            'businessInfo' => ['company', 'street', 'city', 'country', 'state', 'zipCode'],
            'subscriptionStatus' => ['status', 'cycle', 'renewalDue'],
            'lastActivity' => ['lastActivity'],
            'views' => ['videoViews', 'totalWatchTime'],
            'bandwidth' => ['bandwidthCurrentCycle', 'allTimeBandwidth', 'bandwidthLimit'],
            'age' => ['createdAt'],
            'relatedUsers' => ['relatedAllUsers', 'relatedActiveUsers'],
            'contactSize' => ['contactSize'],
            'compliance' => ['compliance'],
            'serviceCost' => ['thisMonth', 'lastMonth', 'allTimeServiceCost'],
            'stagePage' => ['stageUrl'],
            'signUpPage' => ['signUpPage'],
            'referral' => ['referral'],
            'storage' => ['availableStorage', 'usedStorage'],
        ],
];