# Leave Management System - Setup & Usage Guide

## Overview
The Leave Management System allows employees to submit leave requests and HR administrators to approve or decline them.

## Features Implemented

### Employee Features:
- ✅ Submit new leave requests with date range, type, and reason
- ✅ View history of all submitted leave requests
- ✅ Cancel pending leave requests
- ✅ See status updates (Pending, Approved, Rejected, Cancelled)
- ✅ Form validation (dates, leave type, reason)

### HR Admin Features:
- ✅ View all employee leave requests in a centralized dashboard
- ✅ Approve or decline pending leave requests
- ✅ Filter requests by status (All, Pending, Approved, Rejected)
- ✅ View statistics (Total, Pending, Approved, Rejected)
- ✅ See employee details for each request
- ✅ Track reviewer and review timestamps

## Database Setup

### 1. Run Migrations
The migrations are already created. Simply run:
```bash
php artisan migrate
```

This will:
- Create the `leave_requests` table
- Add `is_admin` column to the `users` table

### 2. Create Admin User
To create an admin user who can access the HR management panel, use Tinker:

```bash
php artisan tinker
```

Then run:
```php
App\Models\User::create([
    'name' => 'HR Admin',
    'email' => 'admin@beeconnected.com',
    'password' => bcrypt('password'),
    'is_admin' => true,
]);
```

Or update an existing user to be admin:
```php
$user = App\Models\User::find(1);
$user->update(['is_admin' => true]);
```

### 3. Create Regular Employee User
```php
App\Models\User::create([
    'name' => 'John Employee',
    'email' => 'employee@beeconnected.com',
    'password' => bcrypt('password'),
    'is_admin' => false,
]);
```

## Routes

### Employee Routes (Available to all authenticated users):
- `GET /leave-requests` - View leave request history
- `GET /leave-requests/create` - Create new leave request form
- `POST /leave-requests` - Submit new leave request
- `POST /leave-requests/{id}/cancel` - Cancel a pending request

### HR Admin Routes (Available only to users with is_admin = true):
- `GET /leave-requests/admin` - HR management dashboard
- `POST /leave-requests/{id}/approve` - Approve a leave request
- `POST /leave-requests/{id}/decline` - Decline a leave request

## Usage

### For Employees:
1. Log in to the system
2. Click "Leave Requests" in the sidebar
3. Click "New Request" button
4. Fill in the form:
   - Select leave type (Vacation, Sick, Personal, Unpaid, Emergency)
   - Choose start and end dates
   - Enter reason for leave
5. Submit the request
6. View status in the leave history page
7. Cancel pending requests if needed

### For HR Administrators:
1. Log in with an admin account
2. Click "Leave Management (HR)" in the sidebar (only visible to admins)
3. View all employee leave requests with statistics
4. Filter by status: All, Pending, Approved, or Rejected
5. For pending requests:
   - Click "Approve" to approve
   - Click "Decline" to reject
6. View employee information and request details
7. Track who reviewed each request and when

## Leave Types Available:
- **Vacation** - Planned time off
- **Sick** - Medical leave
- **Personal** - Personal matters
- **Unpaid** - Unpaid leave
- **Emergency** - Emergency situations

## Status Types:
- **Pending** - Awaiting HR review
- **Approved** - Approved by HR
- **Rejected** - Declined by HR
- **Cancelled** - Cancelled by employee

## Security
- Only authenticated users can access the leave system
- Employees can only view and cancel their own requests
- Only admin users (is_admin = true) can:
  - Access the HR management dashboard
  - Approve or decline leave requests
  - View all employee requests

## Database Schema

### leave_requests table:
- `id` - Primary key
- `user_id` - Foreign key to users table
- `start_date` - Leave start date
- `end_date` - Leave end date
- `reason` - Text reason for leave
- `type` - Leave type (Vacation, Sick, etc.)
- `status` - Current status (Pending, Approved, Rejected, Cancelled)
- `reviewed_at` - Timestamp when reviewed
- `reviewer_id` - Foreign key to users table (HR who reviewed)
- `created_at` - Timestamp when created
- `updated_at` - Timestamp when updated

### users table additions:
- `is_admin` - Boolean flag for HR admin access

## Testing

### Test as Employee:
1. Create a regular user (is_admin = false)
2. Log in and submit leave requests
3. Verify you can see your requests
4. Verify you cannot access /leave-requests/admin
5. Cancel a pending request

### Test as HR Admin:
1. Create an admin user (is_admin = true)
2. Log in and access HR management dashboard
3. View all employee requests
4. Approve and decline requests
5. Verify statistics update correctly
6. Test filtering by status

## Next Steps / Future Enhancements:
- Email notifications when requests are approved/declined
- Calendar integration showing approved leaves
- Leave balance tracking
- Multiple approval levels
- Leave policies and rules engine
- Bulk operations for HR
- Export reports
- Comments/notes on requests
