<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Test Activity Log</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="{{ asset('js/global-activity-logger.js') }}"></script>
</head>
<body>
    <h1>Test Activity Log</h1>
    <p>This page tests the activity logging functionality.</p>
    
    <button onclick="testLogActivity()">Test Log Activity</button>
    <button onclick="testCuisineCreate()">Test Cuisine Create</button>
    <button onclick="testCuisineUpdate()">Test Cuisine Update</button>
    <button onclick="testCuisineDelete()">Test Cuisine Delete</button>
    
    <div id="results"></div>
    
    <script>
        function testLogActivity() {
            console.log('Testing logActivity function...');
            logActivity('test', 'test_action', 'Test activity from test page');
            document.getElementById('results').innerHTML += '<p>Test logActivity called - check console</p>';
        }
        
        function testCuisineCreate() {
            console.log('Testing cuisine create...');
            logActivity('cuisines', 'created', 'Created new cuisine: Test Cuisine');
            document.getElementById('results').innerHTML += '<p>Test cuisine create called - check console</p>';
        }
        
        function testCuisineUpdate() {
            console.log('Testing cuisine update...');
            logActivity('cuisines', 'updated', 'Updated cuisine: Test Cuisine');
            document.getElementById('results').innerHTML += '<p>Test cuisine update called - check console</p>';
        }
        
        function testCuisineDelete() {
            console.log('Testing cuisine delete...');
            logActivity('cuisines', 'deleted', 'Deleted cuisine: Test Cuisine');
            document.getElementById('results').innerHTML += '<p>Test cuisine delete called - check console</p>';
        }
        
        // Test on page load
        $(document).ready(function() {
            console.log('Page loaded, testing activity logger...');
            logActivity('test', 'page_viewed', 'Test activity log page viewed');
        });
    </script>
</body>
</html>
