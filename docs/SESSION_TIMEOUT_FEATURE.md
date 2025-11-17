# Session Timeout Warning Feature

## Overview
This feature provides users with a friendly warning before their session expires due to inactivity, allowing them to extend their session with a single click.

## How It Works

### 1. **Automatic Session Monitoring**
- The system monitors user activity (mouse movements, clicks, keyboard inputs, scrolling)
- Activity detection is throttled to once per minute to avoid performance issues
- Each detected activity resets the timeout timer

### 2. **Warning Display**
- When the session is about to expire (default: 5 minutes before expiration), a modal appears
- The modal shows:
  - A countdown timer showing remaining time
  - "Stay Logged In" button to extend the session
  - "Logout Now" button for manual logout

### 3. **Session Extension**
- Clicking "Stay Logged In" sends a request to the server
- The server refreshes the session's `last_activity` timestamp
- The timer resets and monitoring continues
- The modal disappears

### 4. **Automatic Logout**
- If the user doesn't respond to the warning, they are automatically logged out when time expires
- This prevents security risks from unattended sessions

## Configuration

### Environment Variables (.env)
```bash
# Session lifetime in minutes (default: 120 minutes = 2 hours)
SESSION_LIFETIME=120

# Warning time in minutes before expiration (default: 5 minutes)
SESSION_WARNING_TIME=5

# Session driver (database recommended for production)
SESSION_DRIVER=database
```

### Examples:
```bash
# Shorter session for high-security (30 minutes total, warn at 5 minutes before)
SESSION_LIFETIME=30
SESSION_WARNING_TIME=5

# Longer session for convenience (4 hours total, warn at 10 minutes before)
SESSION_LIFETIME=240
SESSION_WARNING_TIME=10

# Very short for testing (5 minutes total, warn at 1 minute before)
SESSION_LIFETIME=5
SESSION_WARNING_TIME=1
```

## Technical Implementation

### Files Created/Modified

#### New Files:
1. **`app/Livewire/SessionTimeout.php`** - Livewire component handling modal state
2. **`resources/views/livewire/session-timeout.blade.php`** - Modal UI and Alpine.js logic

#### Modified Files:
1. **`routes/web.php`** - Added `/keep-alive` endpoint
2. **`config/session.php`** - Added `warning_time` configuration
3. **`resources/views/components/layouts/app.blade.php`** - OSA layout
4. **`resources/views/components/layouts/superadmin.blade.php`** - SuperAdmin layout
5. **`resources/views/components/layouts/gso-layout.blade.php`** - GSO layout
6. **`resources/views/components/layouts/student-org-layout.blade.php`** - Student Org layout

### Component Architecture

```
┌─────────────────────────────────────────────────────┐
│            User Activity Detection                   │
│  (Alpine.js listening to mouse/keyboard/scroll)     │
└─────────────────┬───────────────────────────────────┘
                  │
                  │ Activity detected (throttled)
                  ▼
┌─────────────────────────────────────────────────────┐
│              Timer Reset                             │
│  (Restart countdown from session lifetime)          │
└─────────────────┬───────────────────────────────────┘
                  │
                  │ No activity for (lifetime - warning_time)
                  ▼
┌─────────────────────────────────────────────────────┐
│          Warning Modal Displayed                     │
│  (Livewire component shows modal)                   │
└─────────────────┬───────────────────────────────────┘
                  │
            ┌─────┴─────┐
            │           │
            ▼           ▼
    User clicks      Timer expires
   "Stay Logged In"  (no response)
            │           │
            ▼           ▼
    POST /keep-alive   POST /logout
    (refresh session)  (end session)
            │           │
            ▼           ▼
    Modal closes      Redirect to login
    Timer resets
```

### Key Features

#### 1. **Activity-Based Reset**
User actions automatically extend the session without server requests:
- Mouse movements
- Keyboard presses
- Scrolling
- Touch events
- Clicks

This reduces server load while keeping active users logged in.

#### 2. **Throttled Detection**
Activity detection is throttled to once per minute to prevent:
- Excessive timer resets
- Performance degradation
- Battery drain on mobile devices

#### 3. **User-Friendly Warning**
The modal provides:
- Clear warning message
- Visual countdown timer
- Two clear action buttons
- No abrupt logout
- Time to save work

#### 4. **Security Compliance**
- Sessions expire after configured inactivity period
- Server-side session invalidation on logout
- No client-side manipulation possible
- Proper CSRF protection on keep-alive endpoint

## Testing

### Manual Testing Steps:

1. **Test Basic Functionality**
   - Login to any role (OSA/GSO/Student Org/SuperAdmin)
   - Wait for (SESSION_LIFETIME - SESSION_WARNING_TIME) minutes
   - Verify warning modal appears
   - Click "Stay Logged In"
   - Verify modal closes and you remain logged in

2. **Test Automatic Logout**
   - Login and wait for warning modal
   - Don't click any buttons
   - Wait for countdown to reach zero
   - Verify automatic redirect to logout

3. **Test Activity Detection**
   - Login and perform activities (move mouse, type, scroll)
   - Verify that timer resets with activity
   - Stop activity and wait
   - Verify warning appears after inactivity

4. **Test Quick Session (For Testing)**
   ```bash
   # In .env
   SESSION_LIFETIME=2
   SESSION_WARNING_TIME=1
   ```
   - Warning should appear after 1 minute
   - Automatic logout after 2 minutes total

### Automated Testing
Add to your test suite:

```php
// tests/Feature/SessionTimeoutTest.php
public function test_keep_alive_endpoint_refreshes_session()
{
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $response = $this->post('/keep-alive');
    
    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
}

public function test_keep_alive_requires_authentication()
{
    $response = $this->post('/keep-alive');
    
    $response->assertStatus(302);
    $response->assertRedirect('/login');
}
```

## User Experience

### Before Implementation:
- ❌ Sessions expire without warning
- ❌ Users lose unsaved work
- ❌ Frustrating re-login experience
- ❌ Confusion about why they were logged out

### After Implementation:
- ✅ Clear warning before expiration
- ✅ One-click session extension
- ✅ Time to save work before logout
- ✅ Transparent session management
- ✅ Better security without frustration

## Security Considerations

### What This Feature Does:
- ✅ Warns users before automatic logout
- ✅ Allows manual session extension
- ✅ Maintains server-side session control
- ✅ Logs out inactive users automatically

### What This Feature Does NOT Do:
- ❌ Extend sessions indefinitely
- ❌ Allow bypassing session expiration
- ❌ Store session state client-side
- ❌ Compromise security for convenience

### Security Best Practices:
1. Keep `SESSION_LIFETIME` reasonable (1-2 hours for normal operations)
2. Use shorter sessions for administrative actions
3. Always use `SESSION_DRIVER=database` in production
4. Enable `SESSION_SECURE_COOKIE=true` with HTTPS
5. Keep `SESSION_HTTP_ONLY=true` to prevent XSS

## Troubleshooting

### Warning doesn't appear:
1. Check browser console for JavaScript errors
2. Verify Alpine.js is loaded (`@vite(['resources/js/app.js'])`)
3. Check SESSION_LIFETIME and SESSION_WARNING_TIME in .env
4. Clear browser cache and reload

### Modal appears immediately:
1. Check if SESSION_LIFETIME is very short
2. Verify session is being created properly on login
3. Check server time vs. session expiration time

### "Stay Logged In" doesn't work:
1. Check `/keep-alive` route exists
2. Verify CSRF token is valid
3. Check server logs for errors
4. Ensure user is still authenticated

### Session expires too quickly:
1. Increase SESSION_LIFETIME in .env
2. Check if server session storage is working
3. Verify database sessions table exists and is accessible

## Future Enhancements

Potential improvements:
- [ ] Add session analytics (track how often users extend sessions)
- [ ] Different timeout values per role (shorter for admins)
- [ ] Customizable warning messages per role
- [ ] Remember user's preferred session duration
- [ ] Show session expiration time in profile
- [ ] Mobile-optimized modal design
- [ ] Sound/notification for warning
- [ ] Multi-tab synchronization

## Support

For issues or questions about this feature:
1. Check this documentation
2. Review Laravel session documentation: https://laravel.com/docs/session
3. Review Livewire documentation: https://livewire.laravel.com/docs/
4. Check application logs: `storage/logs/laravel.log`

