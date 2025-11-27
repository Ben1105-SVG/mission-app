# Testing Email Sending via ActiveCampaign

## Quick Test Methods

### Method 1: Using Test Command (Recommended)

Test email sending with different statuses:

```bash
# Test Green status (approved)
php artisan test:application --status=green

# Test Yellow status (needs follow-up)
php artisan test:application --status=yellow

# Test Red status (alternative opportunities)
php artisan test:application --status=red
```

### Method 2: Test via Web Form

1. Start your Laravel server:
   ```bash
   php artisan serve
   ```

2. Open your browser and go to: `http://localhost:8000`

3. Fill out and submit the application form

4. Check logs and ActiveCampaign

### Method 3: Direct API Test

Test the API endpoint directly:

```bash
curl -X POST http://localhost:8000/api/inquiries \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "phone": "+1234567890",
    "answers": {
      "1": "Yes",
      "2": "Comfortable"
    }
  }'
```

## Step-by-Step Testing Guide

### Step 1: Check ActiveCampaign Connection

First, verify your ActiveCampaign connection is working:

```bash
php artisan activecampaign:test
```

You should see:
- ✓ API connection successful
- ✓ Account information

### Step 2: Monitor Logs

Open a terminal and watch the logs in real-time:

```bash
tail -f storage/logs/laravel.log
```

Keep this running while you test.

### Step 3: Run Test Command

In another terminal, run:

```bash
php artisan test:application --status=green
```

### Step 4: Check Log Output

In the logs, you should see messages like:

**If using lists:**
```
[INFO] ActiveCampaign contact added to list - email will be sent
```

**If sending directly:**
```
[INFO] ActiveCampaign email sent directly
```

**If there's an error:**
```
[ERROR] ActiveCampaign email send exception
```

### Step 5: Verify in ActiveCampaign

1. **Log into ActiveCampaign**: https://bsaqeyan89737.activehosted.com
2. **Go to**: Contacts
3. **Search for**: The test email address you used
4. **Check**:
   - Contact was created/updated
   - Contact has the correct tag (Green/Yellow/Red)
   - If using lists: Contact is in the correct list
   - Check "Emails" tab to see if email was sent

### Step 6: Check Email Delivery

- Check the contact's email inbox (including spam folder)
- In ActiveCampaign, go to the contact → "Emails" tab
- Look for sent emails

## What to Look For

### Success Indicators

✅ **In Logs:**
- "ActiveCampaign contact added to list - email will be sent" OR
- "ActiveCampaign email sent directly"
- "ActiveCampaign email sent successfully"

✅ **In ActiveCampaign:**
- Contact exists with correct information
- Contact has correct tag
- Email appears in contact's email history

✅ **In Email:**
- Email received in inbox (check spam if not in inbox)

### Error Indicators

❌ **In Logs:**
- "ActiveCampaign email send exception"
- "Failed to add contact to list"
- "Could not fetch contact for direct email"

❌ **Common Issues:**
- API credentials incorrect
- Message/List ID doesn't exist
- Contact email invalid
- ActiveCampaign API rate limit

## Troubleshooting

### Issue: No email sent

1. **Check logs:**
   ```bash
   tail -n 100 storage/logs/laravel.log | grep -i "email\|activecampaign"
   ```

2. **Verify configuration:**
   ```bash
   php artisan activecampaign:test
   ```

3. **Check if contact was created:**
   - Log into ActiveCampaign
   - Search for the test email
   - If contact doesn't exist, contact sync failed

### Issue: "No email list/campaign configured"

This is OK! The system will send emails directly. Check logs for:
- "ActiveCampaign email sent directly"

### Issue: Email in spam

- Check spam/junk folder
- Verify sender email in ActiveCampaign settings
- Check email content for spam triggers

### Issue: Contact not found in ActiveCampaign

1. Check if contact sync worked:
   ```bash
   grep "contact sync" storage/logs/laravel.log
   ```

2. Verify API credentials:
   ```bash
   php artisan activecampaign:test
   ```

## Testing Checklist

- [ ] ActiveCampaign connection works (`php artisan activecampaign:test`)
- [ ] Test command runs without errors
- [ ] Logs show email sending attempt
- [ ] Contact appears in ActiveCampaign
- [ ] Contact has correct tag
- [ ] Email appears in ActiveCampaign contact history
- [ ] Email received in inbox (or spam)

## Advanced Testing

### Test All Statuses at Once

```bash
php artisan test:application --status=green --email=green@test.com
php artisan test:application --status=yellow --email=yellow@test.com
php artisan test:application --status=red --email=red@test.com
```

### Test with Custom Email

```bash
php artisan test:application \
  --status=green \
  --email=your-email@example.com \
  --name="Your Name"
```

### Check Specific Inquiry

After testing, check a specific inquiry in the database:

```bash
php artisan tinker
```

Then:
```php
$inquiry = App\Models\Inquiry::latest()->first();
echo "Status: " . $inquiry->status . "\n";
echo "Email: " . $inquiry->email . "\n";
echo "AC Contact ID: " . $inquiry->ac_contact_id . "\n";
```

## Next Steps

If emails are not sending:

1. Check the logs for specific error messages
2. Verify ActiveCampaign API credentials
3. Check if message/list IDs are correct (if using lists)
4. Verify the contact email address is valid
5. Check ActiveCampaign account limits/restrictions

