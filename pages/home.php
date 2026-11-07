
<?php

// =============================================
// NSBM NEWSDESK - HOMEPAGE
// Sample content for frontend demonstration.
// Replace with database content during integration.
// =============================================


// FEATURED NEWS

$featuredNews = [
    "category" => "Academic",
    "title" => "Discover a World of Learning at University",
    "description" => "Explore the latest academic developments, student experiences and exciting opportunities from university life.",
    "date" => "Campus Feature",
    "reading_time" => "3 min read",
    "image" => "https://images.unsplash.com/photo-1562774053-701939374585?auto=format&fit=crop&w=1600&q=85"
];


// LATEST NEWS

$latestNews = [

    [
        "id" => 1,
        "category" => "Academic",
        "title" => "A New Chapter in Learning and Innovation",
        "description" => "Students and educators explore new ideas, technology and opportunities through academic collaboration.",
        "date" => "Sample story",
        "reading_time" => "4 min read",
        "image" => "https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=900&q=85"
    ],

    [
        "id" => 2,
        "category" => "Sports",
        "title" => "Celebrating Teamwork and Sporting Spirit",
        "description" => "Discover the excitement of university sports, teamwork and student participation.",
        "date" => "Sample story",
        "reading_time" => "3 min read",
        "image" => "https://images.unsplash.com/photo-1531415074968-036ba1b575da?auto=format&fit=crop&w=900&q=85"
    ],

    [
        "id" => 3,
        "category" => "Events",
        "title" => "Innovation Takes Centre Stage",
        "description" => "Creative ideas, demonstrations and collaboration bring the university community together.",
        "date" => "Sample story",
        "reading_time" => "5 min read",
        "image" => "https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=900&q=85"
    ]

];


// SLIDESHOW

$featuredSlides = array_merge([$featuredNews], $latestNews);


// BREAKING NEWS

$breakingNews = [
    "Discover the latest stories, achievements and updates from the university community.",
    "Stay connected with academic news, student activities and campus events."
];


// ANNOUNCEMENTS
// Sample placeholders. Replace with verified announcements.

$announcements = [

    [
        "day" => "26",
        "month" => "SEP",
        "title" => "Academic Announcements",
        "details" => "Check official channels for academic updates."
    ],

    [
        "day" => "27",
        "month" => "SEP",
        "title" => "Career Opportunities",
        "details" => "Explore career guidance and opportunities."
    ],

    [
        "day" => "28",
        "month" => "SEP",
        "title" => "Student Activities",
        "details" => "Discover student activities and clubs."
    ],

    [
        "day" => "29",
        "month" => "SEP",
        "title" => "Research and Innovation",
        "details" => "Explore research-related updates."
    ],

    [
        "day" => "30",
        "month" => "SEP",
        "title" => "University Events",
        "details" => "Stay informed about campus events."
    ]

];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="NSBM NewsDesk - University news, student stories, academic updates and events.">

    <title>NSBM NewsDesk | Home</title>

    <!-- Homepage stylesheet -->
    <link rel="stylesheet" href="../assests/css/home.css">

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
   
    <!-- Font Awesome icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../assests/css/home.css">
    
</head>


<body>


<!-- =============================================
     HEADER / NAVIGATION
</html>
