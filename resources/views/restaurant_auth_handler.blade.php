<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Panel Authentication</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .auth-container {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 400px;
            width: 90%;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .success-icon {
            color: #28a745;
            font-size: 48px;
            margin-bottom: 20px;
        }
        .error-icon {
            color: #dc3545;
            font-size: 48px;
            margin-bottom: 20px;
        }
        .message {
            font-size: 18px;
            margin-bottom: 20px;
            color: #333;
        }
        .restaurant-name {
            font-weight: bold;
            color: #667eea;
        }
        .btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        .btn:hover {
            background: #5a6fd8;
        }
        .error-details {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 15px;
            margin-top: 20px;
            font-size: 14px;
            color: #6c757d;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div id="loading-state">
            <div class="spinner"></div>
            <div class="message">Authenticating with restaurant panel...</div>
        </div>
        
        <div id="success-state" style="display: none;">
            <div class="success-icon">✓</div>
            <div class="message">
                Successfully logged in as <span class="restaurant-name" id="restaurant-name"></span>
            </div>
            <a href="/dashboard" class="btn">Go to Dashboard</a>
        </div>
        
        <div id="error-state" style="display: none;">
            <div class="error-icon">✗</div>
            <div class="message" id="error-message">Authentication failed</div>
            <div class="error-details" id="error-details"></div>
            <a href="/login" class="btn">Back to Login</a>
        </div>
    </div>

    <!-- Firebase SDK -->
    <script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-firestore-compat.js"></script>

    <script>
        // Firebase configuration (should match your restaurant panel config)
        const firebaseConfig = {
            apiKey: "{{ env('FIREBASE_APIKEY', 'AIzaSyAf_lICoxPh8qKE1QnVkmQYTFJXKkYmRXU') }}",
            authDomain: "{{ env('FIREBASE_AUTH_DOMAIN', 'jippymart-27c08.firebaseapp.com') }}",
            databaseURL: "{{ env('FIREBASE_DATABASE_URL', 'https://jippymart-27c08-default-rtdb.firebaseio.com') }}",
            projectId: "{{ env('FIREBASE_PROJECT_ID', 'jippymart-27c08') }}",
            storageBucket: "{{ env('FIREBASE_STORAGE_BUCKET', 'jippymart-27c08.firebasestorage.app') }}",
            messagingSenderId: "{{ env('FIREBASE_MESSAAGING_SENDER_ID', '592427852800') }}",
            appId: "{{ env('FIREBASE_APP_ID', '1:592427852800:web:f74df8ceb2a4b597d1a4e5') }}",
            measurementId: "{{ env('FIREBASE_MEASUREMENT_ID', 'G-ZYBQYPZWCF') }}"
        };

        // Initialize Firebase
        if (!firebase.apps.length) {
            firebase.initializeApp(firebaseConfig);
        }

        const auth = firebase.auth();
        const database = firebase.firestore();

        // Get URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const impersonationToken = urlParams.get('impersonation_token');
        const restaurantUid = urlParams.get('restaurant_uid');
        const cacheKey = urlParams.get('cache_key');

        // Validate required parameters
        if (!impersonationToken || !restaurantUid) {
            showError('Invalid impersonation link', 'Missing required parameters. Please contact support.');
            return;
        }

        // Start authentication process
        authenticateWithCustomToken();

        async function authenticateWithCustomToken() {
            try {
                console.log('Starting Firebase authentication with custom token...');
                
                // Sign in with custom token
                const userCredential = await auth.signInWithCustomToken(impersonationToken);
                const user = userCredential.user;
                
                console.log('Successfully authenticated:', user.uid);
                
                // Verify the user is the correct restaurant owner
                if (user.uid !== restaurantUid) {
                    throw new Error('Token UID mismatch. Security violation detected.');
                }
                
                // Get restaurant information
                const restaurantInfo = await getRestaurantInfo(restaurantUid);
                
                // Store authentication state
                localStorage.setItem('restaurant_impersonation', JSON.stringify({
                    isImpersonated: true,
                    restaurantUid: restaurantUid,
                    restaurantName: restaurantInfo.name || 'Unknown Restaurant',
                    impersonatedAt: new Date().toISOString(),
                    tokenUsed: true
                }));
                
                // Show success state
                showSuccess(restaurantInfo.name || 'Unknown Restaurant');
                
                // Log successful impersonation
                await logImpersonationSuccess(restaurantUid, restaurantInfo.name);
                
            } catch (error) {
                console.error('Authentication failed:', error);
                showError('Authentication Failed', error.message);
                
                // Log failed impersonation
                await logImpersonationFailure(restaurantUid, error.message);
            }
        }

        async function getRestaurantInfo(restaurantUid) {
            try {
                // Get user data
                const userDoc = await database.collection('users').doc(restaurantUid).get();
                const userData = userDoc.data();
                
                if (!userData || !userData.vendorID) {
                    throw new Error('Restaurant owner not found or not linked to a restaurant');
                }
                
                // Get restaurant data
                const restaurantDoc = await database.collection('vendors').doc(userData.vendorID).get();
                const restaurantData = restaurantDoc.data();
                
                return {
                    name: restaurantData?.title || 'Unknown Restaurant',
                    id: userData.vendorID,
                    owner: userData
                };
                
            } catch (error) {
                console.error('Error getting restaurant info:', error);
                return { name: 'Unknown Restaurant' };
            }
        }

        function showSuccess(restaurantName) {
            document.getElementById('loading-state').style.display = 'none';
            document.getElementById('success-state').style.display = 'block';
            document.getElementById('restaurant-name').textContent = restaurantName;
            
            // Redirect to dashboard after 2 seconds
            setTimeout(() => {
                window.location.href = '/dashboard';
            }, 2000);
        }

        function showError(title, details) {
            document.getElementById('loading-state').style.display = 'none';
            document.getElementById('error-state').style.display = 'block';
            document.getElementById('error-message').textContent = title;
            document.getElementById('error-details').textContent = details;
        }

        async function logImpersonationSuccess(restaurantUid, restaurantName) {
            try {
                await database.collection('impersonation_logs').add({
                    type: 'success',
                    restaurantUid: restaurantUid,
                    restaurantName: restaurantName,
                    timestamp: firebase.firestore.FieldValue.serverTimestamp(),
                    userAgent: navigator.userAgent,
                    ip: await getClientIP()
                });
            } catch (error) {
                console.error('Failed to log impersonation success:', error);
            }
        }

        async function logImpersonationFailure(restaurantUid, errorMessage) {
            try {
                await database.collection('impersonation_logs').add({
                    type: 'failure',
                    restaurantUid: restaurantUid,
                    error: errorMessage,
                    timestamp: firebase.firestore.FieldValue.serverTimestamp(),
                    userAgent: navigator.userAgent,
                    ip: await getClientIP()
                });
            } catch (error) {
                console.error('Failed to log impersonation failure:', error);
            }
        }

        async function getClientIP() {
            try {
                const response = await fetch('https://api.ipify.org?format=json');
                const data = await response.json();
                return data.ip;
            } catch (error) {
                return 'unknown';
            }
        }

        // Handle page visibility change (user switching tabs)
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                // User switched away from the page
                console.log('User switched away from impersonation page');
            } else {
                // User returned to the page
                console.log('User returned to impersonation page');
            }
        });

        // Handle beforeunload (user closing tab/navigating away)
        window.addEventListener('beforeunload', function(e) {
            // Optional: Show confirmation dialog
            // e.preventDefault();
            // e.returnValue = '';
        });
    </script>
    
</body>
</html>
