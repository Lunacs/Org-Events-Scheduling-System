# Testing the Timezone Fix

## Issue

Events in Week and Day view were displaying at incorrect times. For example, the "Charity Fundraising Event" scheduled for 8:00 AM - 5:00 PM was appearing from 7:00 AM to 11:00 PM.

## What Was Fixed

Added explicit timezone offset (`+08:00`) to all event datetime strings sent to FullCalendar, ensuring consistent time display regardless of the user's browser timezone.

## How to Test

### 1. View the Calendar

1. Navigate to the OSA Event Calendar page
2. Switch to **Week View** or **Day View**
3. Find the "Charity Fundraising Event" (November 5-9, 2025)

### 2. Verify Correct Times

The event should now display:

-   **Start Time**: 8:00 AM (or equivalent in your local timezone)
-   **End Time**: 5:00 PM (or equivalent in your local timezone)
-   **NOT** extending from early morning to late night

### 3. Check Manila Time Display

Even if your computer is in a different timezone:

-   The event times should correspond to Manila time (UTC+8)
-   If you're in UTC timezone, 8:00 AM Manila = 12:00 AM UTC (midnight)
-   If you're in Manila timezone, it should show 8:00 AM directly

### 4. Enable Debug Mode

To see detailed time information in the browser console:

1. Add `?debug=times` to the URL:

    ```
    http://localhost/org-events-scheduling-system/admin/event-calendar?debug=times
    ```

2. Open Browser Developer Console (F12)

3. Look for output like:
    ```
    ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    Event: Charity Fundraising Event
      Start (Date obj): Wed Nov 05 2025 08:00:00 GMT+0800
      End (Date obj): Sun Nov 09 2025 17:00:00 GMT+0800
      Start (Manila): 11/5/2025, 8:00:00 AM
      End (Manila): 11/9/2025, 5:00:00 PM
      Raw Start Time: 08:00:00
      Raw End Time: 17:00:00
      Duration (hours): 105.00
    ```

### 5. Test Different Views

Test the fix across all calendar views:

| View           | What to Check                                                |
| -------------- | ------------------------------------------------------------ |
| **Month View** | Event appears on correct dates                               |
| **Week View**  | Event shows at 8:00 AM on Nov 5, extends to 5:00 PM on Nov 9 |
| **Day View**   | Event block height corresponds to time duration              |
| **List View**  | Times display as "8:00 AM - 5:00 PM"                         |

### 6. Test with Other Events

Check other events to ensure they also display correctly:

-   Single-day events should show proper start and end times
-   Multi-day events should span correctly across days
-   Events with different time ranges should have proportional heights in Week/Day view

### 7. Browser Timezone Simulation (Advanced)

To test how the fix works across timezones:

1. Open **Chrome DevTools** (F12)
2. Click **⋮** (three dots) → **More tools** → **Sensors**
3. In "Location" section, click "Manage"
4. Set a different timezone (e.g., "Los Angeles - PST")
5. Reload the calendar
6. Verify events still display at correct Manila times

## Verification Checklist

-   [ ] Calendar loads without errors
-   [ ] Events display in Week view at correct times
-   [ ] Events display in Day view at correct times
-   [ ] Multi-day events span correctly
-   [ ] Event tooltips show correct time ranges
-   [ ] Debug mode (with `?debug=times`) shows Manila times correctly
-   [ ] No console errors

## Expected Results

### Before Fix

```
Charity Fundraising Event
├─ Database: 08:00:00 - 17:00:00
└─ Calendar Display: 07:00 AM - 11:00 PM ❌ (WRONG)
```

### After Fix

```
Charity Fundraising Event
├─ Database: 08:00:00 - 17:00:00
└─ Calendar Display: 08:00 AM - 05:00 PM ✅ (CORRECT)
```

## Troubleshooting

### If times still appear wrong:

1. **Clear browser cache**: Ctrl+Shift+Delete
2. **Clear Laravel cache**: `php artisan optimize:clear`
3. **Rebuild assets**: `npm run build`
4. **Hard refresh**: Ctrl+Shift+R (or Cmd+Shift+R on Mac)

### If events don't appear:

1. Check browser console for JavaScript errors
2. Verify database has events with proper schedules
3. Run: `php check_charity_event.php` to verify data

### If debug mode doesn't work:

1. Make sure URL includes `?debug=times`
2. Open browser console (F12)
3. Refresh the page
4. Look for log output starting with `━━━━━━━`

## Additional Test Pages

-   **Timezone Verification**: `/public/test-timezone-fix.html`
-   **Database Check**: Run `php check_charity_event.php`

## Questions?

If you still see incorrect times, please provide:

1. Screenshot of the calendar view
2. Browser console output (with `?debug=times`)
3. Your computer's timezone setting
4. Expected vs actual time display
