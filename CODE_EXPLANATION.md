# Azzeman Hotel PHP - Complete Code Explanation

## Table of Contents
1. [Project Overview](#project-overview)
2. [Configuration Files](#configuration-files)
3. [Core Architecture](#core-architecture)
4. [Database Layer](#database-layer)
5. [Helper Classes](#helper-classes)
6. [Controllers](#controllers)
7. [Services](#services)
8. [Views](#views)
9. [Routing System](#routing-system)
10. [Security Features](#security-features)

---

## Project Overview

This is a **PHP-based hotel booking system** converted from React + Node.js. It's designed to run on cPanel (Apache + PHP + MySQL) without requiring Node.js.

**Architecture Pattern**: MVC (Model-View-Controller)
- **Models**: Database abstraction layer
- **Views**: PHP templates for rendering HTML
- **Controllers**: Handle HTTP requests and business logic

---

## Configuration Files

### `config.php` (Main Configuration)

**Lines 1-7**: File header comment explaining this is the main config file that should never be committed to version control.

**Lines 9-14**: Database Configuration
- `DB_HOST`: MySQL server hostname (localhost)
- `DB_NAME`: Database name (azzeman_hotel)
- `DB_USER`: Database username (root)
- `DB_PASS`: Database password (empty for XAMPP)
- `DB_CHARSET`: Character encoding (utf8mb4 for full Unicode support)

**Lines 16-20**: Application Configuration
- `APP_NAME`: Hotel name displayed throughout the app
- `APP_URL`: Base URL for the application
- `APP_ENV`: Environment mode (development/production)
- `APP_DEBUG`: Enable/disable debug mode

**Lines 22-24**: Session Configuration
- `SESSION_LIFETIME`: How long sessions last (7 days = 3600*24*7 seconds)
- `SESSION_NAME`: Name of the session cookie

**Lines 26-28**: Security Settings
- `CSRF_TOKEN_NAME`: Name for CSRF token in sessions
- `PASSWORD_MIN_LENGTH`: Minimum password length requirement

**Lines 30-33**: File Upload Configuration
- `UPLOAD_DIR`: Directory where uploaded files are stored
- `UPLOAD_MAX_SIZE`: Maximum file size (2MB)
- `ALLOWED_IMAGE_TYPES`: MIME types allowed for image uploads

**Lines 35-38**: Google Gemini API Configuration
- `GEMINI_API_KEY`: API key for chatbot functionality
- `GEMINI_MODEL`: Model name for text generation
- `GEMINI_IMAGE_MODEL`: Model name for image generation

**Lines 40-47**: Email Configuration (SMTP)
- `SMTP_HOST`: SMTP server address
- `SMTP_PORT`: SMTP port (587 for TLS)
- `SMTP_USER`: Email account username
- `SMTP_PASS`: Email account password
- `SMTP_FROM_EMAIL`: Sender email address
- `SMTP_FROM_NAME`: Sender display name
- `ADMIN_EMAIL`: Email address for admin notifications

**Lines 49-51**: Rate Limiting
- `CHATBOT_RATE_LIMIT`: Max requests per time window (10 per minute)
- `CHATBOT_RATE_WINDOW`: Time window in seconds (60 seconds)

**Lines 53-57**: Path Definitions
- `BASE_PATH`: Root directory of the application
- `PUBLIC_PATH`: Public web root directory
- `VIEWS_PATH`: Directory containing view templates
- `LOGS_PATH`: Directory for error logs

**Lines 59-60**: Timezone Configuration
- Sets timezone to Africa/Addis_Ababa (Ethiopia)

**Lines 62-69**: Error Reporting Configuration
- In development: Reports all errors except warnings/notices, logs to file
- In production: Disables all error reporting and display

**Lines 71-73**: Error Logging
- Enables error logging to a file in the logs directory

### `config.sample.php`

This is a template file showing what configuration values need to be set. It's identical to `config.php` but with placeholder values. Users copy this to `config.php` and fill in their actual values.

### `.htaccess` (Root Directory)

**Lines 1-4**: Enable URL rewriting engine and allow direct access to assets (CSS, JS, images)

**Lines 11-14**: Front Controller Pattern - redirects all non-file requests to `index.php`

**Lines 16-21**: Security Headers
- `X-Content-Type-Options: nosniff`: Prevents MIME type sniffing
- `X-Frame-Options: SAMEORIGIN`: Prevents clickjacking
- `X-XSS-Protection`: Enables browser XSS protection

**Lines 23-24**: Prevents directory listing (security)

**Lines 26-30**: Protects sensitive files (`config.php`, `.env`) from direct access

### `public/.htaccess`

Similar to root `.htaccess` but simpler - just redirects all requests to `index.php` (the front controller).

---

## Core Architecture

### `index.php` (Front Controller / Router)

This is the **entry point** for all HTTP requests. It implements a simple routing system.

**Lines 8-9**: Load configuration file

**Lines 11-21**: Load all core classes and controllers
- Database model
- Helper classes (Auth, View, RateLimiter)
- Controllers (Home, Admin, API, Booking)

**Line 24**: Start PHP session for authentication

**Lines 26-40**: Parse Request URI
- Extracts the path from `$_SERVER['REQUEST_URI']`
- Removes the base path (e.g., `/azzemanhotel_PHP`)
- Normalizes the path (ensures leading/trailing slashes are correct)
- Gets HTTP method (GET, POST, etc.)

**Lines 50-68**: API Routes
- `/api/chatbot` (POST): Chatbot endpoint
- `/api/health` (GET): Health check
- `/api/health/db` (GET): Database health check
- `/api/bootstrap` (GET): Returns all data for frontend

**Lines 71-185**: Admin Routes
- `/admin-login` or `/admin/login`: Login page
- `/admin/logout`: Logout handler
- `/admin` or `/admin/dashboard`: Admin dashboard (requires auth)
- `/admin/bookings`: Get bookings (JSON)
- `/admin/bookings/update`: Update booking status
- `/admin/bookings/delete`: Delete booking
- `/admin/gallery`: Gallery management
- `/admin/gallery/upload`: Upload gallery image
- `/admin/gallery/delete`: Delete gallery image
- `/admin/site-images`: Site images management
- `/admin/site-images/update`: Update site image
- `/admin/virtual-tour`: Virtual tour management
- `/admin/virtual-tour/update`: Update virtual tour
- `/admin/profile`: Admin profile page
- `/admin/profile/update`: Update admin profile

**Lines 188-202**: Booking Routes
- `/booking/room` (POST): Create room booking
- `/booking/spa` (POST): Create spa booking
- `/booking/meeting` (POST): Create meeting booking

**Lines 205-217**: Public Routes
- `/` or `/home`: Homepage
- `/gallery`: Gallery page
- `/gallery/{category}`: Gallery category page

**Lines 219-236**: Error Handling
- Catches all exceptions
- Logs errors to file
- Shows detailed errors in development mode
- Shows generic error in production mode

---

## Database Layer

### `src/models/Database.php` (PDO Wrapper)

This class provides a **singleton pattern** database connection using PDO (PHP Data Objects) for secure database access.

**Lines 9-10**: Singleton Pattern
- `$instance`: Holds the single instance of Database class
- `$pdo`: PDO connection object

**Lines 12-64**: Constructor (Private - only called once)
- **Lines 13-18**: Checks if PDO extension is installed
- **Lines 20-26**: Checks if MySQL PDO driver is available
- **Lines 28-34**: Builds DSN (Data Source Name) connection string
- **Lines 36-40**: Sets PDO options:
  - `ERRMODE_EXCEPTION`: Throws exceptions on errors
  - `FETCH_ASSOC`: Returns arrays with column names as keys
  - `EMULATE_PREPARES`: Disabled for security (uses real prepared statements)
- **Line 42**: Creates PDO connection
- **Lines 43-64**: Error handling with helpful messages for common issues

**Lines 70-75**: `getInstance()` - Singleton getter
- Returns existing instance or creates new one

**Lines 80-82**: `getConnection()` - Returns raw PDO object

**Lines 87-96**: `query()` - Execute SELECT and return all rows
- Prepares SQL statement
- Executes with parameters (prevents SQL injection)
- Returns all matching rows

**Lines 101-110**: `queryOne()` - Execute SELECT and return single row
- Same as `query()` but returns only first row

**Lines 115-124**: `execute()` - Execute INSERT/UPDATE/DELETE
- Returns number of affected rows

**Lines 129-131**: `lastInsertId()` - Get ID of last inserted record

**Lines 136-152**: Transaction Methods
- `beginTransaction()`: Start transaction
- `commit()`: Commit transaction
- `rollback()`: Rollback transaction

---

## Helper Classes

### `src/helpers/AuthHelper.php` (Authentication Helper)

Handles session management, authentication, and CSRF protection.

**Lines 12-38**: `startSession()` - Initialize PHP session
- Checks if session already started
- Sets session name
- Calculates base path for cookie
- Configures session cookie parameters:
  - Lifetime: 7 days
  - Path: Application base path
  - Secure: Only in production (HTTPS)
  - HttpOnly: Prevents JavaScript access
  - SameSite: Strict (CSRF protection)
- Starts the session

**Lines 43-46**: `isAuthenticated()` - Check if user is logged in
- Verifies `admin_id` and `admin_username` exist in session

**Lines 51-54**: `getAdminId()` - Get current admin user ID from session

**Lines 59-62**: `getAdminUsername()` - Get current admin username from session

**Lines 67-72**: `login()` - Log user in
- Stores user ID and username in session
- Records last activity timestamp

**Lines 77-81**: `logout()` - Log user out
- Clears all session data
- Destroys the session

**Lines 86-99**: `requireAuth()` - Require authentication (redirect if not logged in)
- Checks if authenticated
- Redirects to login page if not

**Lines 104-110**: `generateCsrfToken()` - Generate CSRF token
- Creates random 32-byte token
- Stores in session
- Returns token

**Lines 115-119**: `verifyCsrfToken()` - Verify CSRF token
- Uses `hash_equals()` for timing-safe comparison
- Prevents timing attacks

**Lines 124-127**: `csrfField()` - Generate hidden input field for CSRF token
- Returns HTML input field with token

### `src/helpers/ViewHelper.php` (View Rendering Helper)

Provides utilities for rendering views and generating URLs.

**Lines 12-17**: `render()` - Render a view template
- Extracts data array into variables
- Uses output buffering to capture template output
- Returns rendered HTML string

**Lines 22-24**: `e()` - Escape HTML output
- Wrapper for `htmlspecialchars()` to prevent XSS attacks

**Lines 29-34**: `formatDate()` - Format date string
- Converts date string to DateTime object
- Formats according to specified format

**Lines 39-44**: `formatDateTime()` - Format datetime string
- Similar to `formatDate()` but for datetime

**Lines 49-87**: `asset()` - Generate asset URL
- Calculates base path from APP_URL or script path
- Handles both XAMPP and cPanel deployments
- Returns full URL or relative path

**Lines 92-105**: `siteImage()` - Get site image from database
- Queries database for image by key
- Returns image URL or default fallback
- Handles database errors gracefully

**Lines 110-113**: `partial()` - Include partial view
- Extracts data and includes partial template
- Used for reusable components (header, footer, navbar)

### `src/helpers/RateLimiter.php` (Rate Limiting)

Implements IP-based rate limiting to prevent API abuse.

**Lines 11-13**: Constructor - Gets database instance

**Lines 18-37**: `checkLimit()` - Check if request is within rate limit
- Gets client IP address
- Creates unique key: `endpoint:ip`
- Cleans up old entries
- Counts requests in time window
- Records new request if under limit
- Returns true if allowed, false if rate limited

**Lines 42-55**: `getClientIp()` - Get real client IP address
- Checks multiple headers (handles proxies/load balancers)
- Validates IP address
- Falls back to `REMOTE_ADDR`

**Lines 60-81**: `getRequestCount()` - Count requests in time window
- Uses file-based storage (JSON files)
- Reads request timestamps from file
- Counts timestamps within time window

**Lines 86-102**: `recordRequest()` - Record a new request
- Loads existing data from file
- Adds current timestamp
- Keeps only last 100 entries (prevents file bloat)
- Saves to file

**Lines 107-122**: `cleanup()` - Remove old entries
- Finds all rate limit files
- Filters out timestamps older than window
- Deletes files if empty

### `src/helpers/EnvHelper.php` (Environment Variables)

Loads environment variables from `.env.local` file (optional feature).

**Lines 9-10**: `$loaded` - Flag to prevent multiple loads

**Lines 14-57**: `load()` - Load environment variables
- Checks if already loaded
- Looks for `.env.local` or `.env` file
- Parses each line:
  - Skips comments (lines starting with #)
  - Parses `KEY=VALUE` format
  - Removes quotes if present
  - Sets environment variable if not already set

**Lines 62-71**: `get()` - Get environment variable
- Loads env file if not already loaded
- Returns value or default

---

## Controllers

### `src/controllers/HomeController.php` (Public Pages Controller)

Handles all public-facing pages.

**Lines 11-13**: Constructor - Gets database instance

**Lines 18-37**: `index()` - Homepage
- Prepares data array:
  - Page title
  - Hero background image (from database or default)
  - About section image
  - Rooms section image
  - Meetings section image
  - Spa images array
  - Rooms data
  - Gallery categories and images
- Renders `home.php` view

**Lines 42-50**: `gallery()` - Gallery page
- Gets all gallery categories and images
- Renders `gallery.php` view

**Lines 55-80**: `galleryCategory()` - Gallery category page
- Gets category by ID
- Returns 404 if not found
- Gets all images in category
- Renders `gallery-category.php` view

**Lines 85-87**: `getRooms()` - Get all rooms from database

**Lines 92-94**: `getGalleryCategories()` - Get all gallery categories

**Lines 99-101**: `getGalleryImages()` - Get all gallery images

### `src/controllers/AdminController.php` (Admin Dashboard Controller)

Handles all admin functionality.

**Lines 18-31**: `loginForm()` - Display login page
- Redirects to dashboard if already logged in
- Generates CSRF token
- Renders login form

**Lines 36-74**: `login()` - Handle login
- Verifies POST method
- Validates CSRF token
- Gets username and password from POST
- Validates input
- Queries database for user
- Verifies password using `password_verify()`
- Logs user in via `AuthHelper::login()`
- Redirects to dashboard

**Lines 79-88**: `getBasePath()` - Calculate base path for redirects
- Handles different deployment scenarios

**Lines 93-96**: `redirectToLogin()` - Redirect to login page

**Lines 101-104**: `redirectToAdmin()` - Redirect to admin dashboard

**Lines 109-113**: `logout()` - Handle logout
- Calls `AuthHelper::logout()`
- Redirects to login

**Lines 118-143**: `dashboard()` - Admin dashboard
- Requires authentication
- Gets current tab from query string
- Fetches all data:
  - Room bookings
  - Spa bookings
  - Meeting bookings
  - Gallery categories and images
  - Site images
  - Virtual tour data
  - Current user info
- Renders dashboard view

**Lines 148-161**: `bookings()` - Get bookings (JSON API)
- Returns bookings as JSON based on type

**Lines 166-214**: `updateBooking()` - Update booking status
- Requires authentication
- Validates POST method and CSRF token
- Gets booking ID, type, and status
- Updates database
- If cancelling room booking, frees up the room

**Lines 219-263**: `deleteBooking()` - Delete booking
- Requires authentication
- Validates POST method and CSRF token
- If deleting room booking, frees up the room
- Deletes from database

**Lines 268-278**: `gallery()` - Gallery management page
- Requires authentication
- Gets gallery data
- Renders gallery management view

**Lines 283-314**: `uploadGalleryImage()` - Upload gallery image
- Requires authentication
- Validates POST and CSRF token
- Creates unique ID
- Inserts image into database

**Lines 375-401**: `deleteGalleryImage()` - Delete gallery image
- Requires authentication
- Validates POST and CSRF token
- Deletes image from database

**Lines 406-415**: `siteImages()` - Site images management page
- Requires authentication
- Gets all site images
- Renders site images view

**Lines 420-503**: `updateSiteImage()` - Update site image
- Requires authentication
- Validates POST and CSRF token
- Handles base64 data URLs:
  - Extracts image data
  - Validates and saves to file
  - Stores file path in database
- Updates database

**Lines 508-517**: `virtualTour()` - Virtual tour management page
- Requires authentication
- Gets virtual tour data
- Renders virtual tour view

**Lines 522-572**: `updateVirtualTour()` - Update virtual tour
- Requires authentication
- Validates POST and CSRF token
- Updates tour image
- Updates hotspots (uses transaction)

**Lines 577-591**: `profile()` - Admin profile page
- Requires authentication
- Gets current user data
- Renders profile view

**Lines 596-662**: `updateProfile()` - Update admin profile
- Requires authentication
- Validates POST and CSRF token
- Verifies current password
- Updates username if provided
- Updates password if provided (with validation)
- Returns success message

**Lines 665-702**: Helper methods to fetch data from database

### `src/controllers/BookingController.php` (Booking Controller)

Handles booking submissions from the frontend.

**Lines 18-118**: `bookRoom()` - Create room booking
- Sets JSON response header
- Validates POST method
- Parses JSON input
- Validates all required fields
- Validates email format
- Validates check-out is after check-in
- Checks room availability
- Creates booking ID (AZZ-timestamp)
- Uses transaction:
  - Inserts booking
  - Updates room booked count
- Sends email notifications
- Returns success JSON

**Lines 123-180**: `bookSpa()` - Create spa booking
- Similar to `bookRoom()` but for spa appointments
- Creates SPA-timestamp ID
- No availability checking (spa services are time-based)

**Lines 185-242**: `bookMeeting()` - Create meeting/event booking
- Similar structure
- Creates MEET-timestamp ID
- Company name is optional

### `src/controllers/ApiController.php` (API Controller)

Handles API endpoints for AJAX requests.

**Lines 18-25**: `health()` - Health check endpoint
- Returns JSON with status, timestamp, and environment

**Lines 30-47**: `healthDb()` - Database health check
- Tests database connection
- Returns status JSON

**Lines 52-99**: `bootstrap()` - Get all data for frontend
- Fetches all data from database:
  - Rooms
  - Bookings (all types)
  - Gallery images and categories
  - Site images
  - Virtual tour
- Formats data appropriately
- Returns JSON

**Lines 104-143**: `chatbot()` - Chatbot endpoint
- Sets JSON response header
- Applies rate limiting
- Gets message from JSON input
- Validates message
- Forwards to GeminiProxy service
- Returns response or error

---

## Services

### `src/services/EmailService.php` (Email Service)

Handles sending email notifications. Has two implementations:
1. **Basic mail() fallback** (if PHPMailer not available)
2. **PHPMailer implementation** (preferred)

**Basic Implementation (Lines 16-258)**:
- `getAdminEmails()`: Gets all admin emails from config
- `sendRoomBookingConfirmation()`: Sends room booking emails
- `sendSpaBookingConfirmation()`: Sends spa booking emails
- `sendMeetingBookingConfirmation()`: Sends meeting booking emails
- `getSmtpConfig()`: Determines which SMTP server to use based on recipient
- `sendBasicEmail()`: Uses PHP `mail()` function
- `getLogoUrl()`: Constructs absolute URL for logo in emails
- `renderTemplate()`: Renders email template from PHP file
- `buildBasicEmail()`: Fallback HTML email template
- `getBookingDetails()`: Formats booking details for email

**PHPMailer Implementation (Lines 267-607)**:
- Similar methods but uses PHPMailer library
- `configureMailer()`: Configures PHPMailer with SMTP settings
- `sendEmail()`: Sends email via PHPMailer, falls back to basic mail() on error
- Supports dual SMTP servers (Gmail and custom domain)

**Key Features**:
- Sends to multiple admin emails
- Sends confirmation to customer
- Uses appropriate SMTP server based on recipient domain
- Renders HTML email templates
- Falls back gracefully if PHPMailer unavailable

### `src/services/GeminiProxy.php` (Gemini API Proxy)

Server-side proxy for Google Gemini API. Keeps API key secure.

**Lines 12-18**: Constructor
- Gets API key from config
- Validates API key is set

**Lines 23-75**: `chat()` - Chat with Gemini
- Builds system instruction (defines chatbot role)
- Constructs API URL with model and API key
- Creates request payload:
  - User message
  - System instruction
- Makes cURL request to Gemini API
- Handles errors
- Extracts response text
- Returns response

**Lines 80-135**: `generateImage()` - Generate image with Gemini Imagen
- Builds prompt with hotel context
- Constructs API URL for image model
- Creates request payload
- Makes cURL request
- Extracts base64 image data
- Returns data URL

---

## Views

### View Structure

Views are PHP templates that render HTML. They use:
- **ViewHelper** for escaping and asset URLs
- **PHP variables** passed from controllers
- **Partial includes** for reusable components

### `src/views/home.php` (Homepage)

**Line 1**: Includes header partial (HTML head, CSS, JS)

**Lines 3-7**: Includes navbar partial (passes gallery categories)

**Lines 10-23**: Hero Section
- Background image from database
- Title and subtitle
- "Book Your Stay" button
- Scroll down indicator

**Lines 26-73**: About Section
- Hotel description
- Feature list with icons
- About image from database

**Lines 76-94**: Rooms Section
- Room description
- Rooms image from database
- "Book Now" button

**Lines 97+**: Services, Spa, Meetings, Gallery sections (similar structure)

### `src/views/partials/header.php` (HTML Head)

**Lines 1-6**: HTML head with meta tags, title, description

**Lines 9-13**: Favicon links

**Lines 15-32**: Tailwind CSS (CDN) with custom config
- Brand colors (green, gold)
- Custom fonts

**Lines 34-35**: Lucide Icons (CDN)

**Lines 37-39**: Custom CSS files

**Lines 41-45**: JavaScript files (deferred loading)

**Line 48**: Body tag

### `src/views/admin/dashboard.php` (Admin Dashboard)

**Lines 1-24**: PHP setup
- Sets default values for all variables
- Calculates base path

**Lines 25-58**: HTML head (similar to header partial)

**Lines 60-78**: Header
- Title
- Refresh button
- Logout button

**Lines 81+**: Tab navigation and content
- Room bookings tab
- Spa bookings tab
- Meeting bookings tab
- Gallery management tab
- Site images tab
- Virtual tour tab
- Profile tab

Each tab includes a partial view for its content.

---

## Routing System

The routing is handled in `index.php` using a simple if-else structure:

1. **Parse Request**: Extract path and method from `$_SERVER`
2. **Route Matching**: Check path against known routes
3. **Controller Instantiation**: Create appropriate controller
4. **Method Call**: Call controller method
5. **Response**: Controller renders view or returns JSON

**Route Patterns**:
- `/api/*` → ApiController
- `/admin/*` → AdminController
- `/booking/*` → BookingController
- `/` or `/home` → HomeController
- `/gallery` → HomeController

---

## Security Features

1. **CSRF Protection**: All forms include CSRF tokens verified server-side
2. **SQL Injection Prevention**: All queries use PDO prepared statements
3. **XSS Prevention**: All output escaped with `htmlspecialchars()`
4. **Password Hashing**: Uses `password_hash()` with bcrypt
5. **Input Validation**: All user input validated before processing
6. **Rate Limiting**: Chatbot endpoint has IP-based rate limiting
7. **Session Security**: Secure session configuration (HttpOnly, SameSite)
8. **File Upload Validation**: File type and size validation
9. **Authentication Required**: Admin routes check authentication
10. **Error Handling**: Errors logged, not displayed in production

---

## Database Schema

### Tables

1. **admin_users**: Admin user accounts
   - id, username, password_hash, created_at

2. **rooms**: Room types and availability
   - id, type, quantity, facilities, booked

3. **booking_rooms**: Room bookings
   - id, guest_name, email, phone_number, check_in_date, check_out_date, room_type, number_of_guests, status, created_at

4. **spa_bookings**: Spa appointments
   - id, guest_name, email, phone_number, service, date, time, status, created_at

5. **meeting_bookings**: Meeting/event bookings
   - id, contact_name, email, phone_number, company_name, venue_name, date, status, created_at

6. **gallery_categories**: Gallery categories
   - id, name, description

7. **gallery_images**: Gallery images
   - id, src, category_id, created_at

8. **site_images**: Site-wide images (hero, about, etc.)
   - key, src

9. **virtual_tours**: Virtual tour configuration
   - id, image_url

10. **hotspots**: Virtual tour hotspots
    - id, pitch, yaw, text, tour_id

---

## File Structure Summary

```
azzemanhotel_PHP/
├── index.php                 # Front controller/router
├── config.php                # Configuration (not in git)
├── config.sample.php         # Configuration template
├── .htaccess                 # Apache rewrite rules
├── azzemanhotel_mysql.sql    # Database schema
│
├── public/                   # Web root
│   ├── index.php             # Entry point (redirects to root index.php)
│   └── .htaccess             # Public rewrite rules
│
├── src/
│   ├── controllers/          # Request handlers
│   │   ├── HomeController.php
│   │   ├── AdminController.php
│   │   ├── BookingController.php
│   │   └── ApiController.php
│   │
│   ├── models/              # Database layer
│   │   └── Database.php
│   │
│   ├── helpers/             # Utility classes
│   │   ├── AuthHelper.php
│   │   ├── ViewHelper.php
│   │   ├── RateLimiter.php
│   │   └── EnvHelper.php
│   │
│   ├── services/            # External services
│   │   ├── EmailService.php
│   │   └── GeminiProxy.php
│   │
│   └── views/               # PHP templates
│       ├── home.php
│       ├── gallery.php
│       ├── gallery-category.php
│       ├── admin/
│       │   ├── dashboard.php
│       │   ├── login.php
│       │   └── partials/
│       └── partials/
│           ├── header.php
│           ├── footer.php
│           ├── navbar.php
│           └── chatbot.php
│
├── assets/                  # Static assets
│   ├── css/
│   ├── js/
│   ├── images/
│   └── uploads/
│
└── logs/                    # Error logs
```

---

## Key Design Patterns

1. **Front Controller**: All requests go through `index.php`
2. **MVC**: Separation of Models, Views, Controllers
3. **Singleton**: Database class uses singleton pattern
4. **Dependency Injection**: Controllers receive Database instance
5. **Template Method**: View rendering uses template pattern
6. **Strategy**: EmailService has two strategies (PHPMailer vs basic)

---

## Data Flow Example: Room Booking

1. **User submits form** → JavaScript sends POST to `/booking/room`
2. **index.php** → Routes to `BookingController::bookRoom()`
3. **BookingController** → Validates input, checks availability
4. **Database** → Inserts booking, updates room count (transaction)
5. **EmailService** → Sends emails to admin and customer
6. **Response** → Returns JSON success message
7. **Frontend** → Displays success message to user

---

## Error Handling

- **Database Errors**: Logged, user sees generic message
- **Validation Errors**: Returned as JSON with error message
- **Authentication Errors**: Redirect to login
- **CSRF Errors**: Return 403 error
- **Rate Limit Errors**: Return 429 error
- **All Errors**: Logged to `logs/php_errors.log`

---

This completes the comprehensive explanation of the Azzeman Hotel PHP codebase. Every file, class, and method has been documented with its purpose and functionality.

