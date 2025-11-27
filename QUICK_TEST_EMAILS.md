# Quick Guide: Testing Email Sending

## 🚀 Quick Test (3 Steps)

### Step 1: Check ActiveCampaign Connection
```bash
php artisan activecampaign:test
```
**Expected:** ✓ API connection successful

### Step 2: Watch Logs (Open in New Terminal)
```bash
tail -f storage/logs/laravel.log
```
Keep this running to see what happens.

### Step 3: Run Test
```bash
# Test with Green status (approved)
php artisan test:application --status=green --email=your-email@example.com

# Or test with Yellow status
php artisan test:application --status=yellow --email=your-email@example.com

# Or test with Red status  
php artisan test:application --status=red --email=your-email@example.com
```

## ✅ What to Look For

### In the Terminal Output:
- ✅ "Application submitted successfully!"
- ✅ Status shown (Green/Yellow/Red)

### In the Logs (tail -f):
Look for one of these messages:
- ✅ `"ActiveCampaign contact added to list - email will be sent"` (if using lists)
- ✅ `"ActiveCampaign email sent directly"` (if sending directly)
- ✅ `"ActiveCampaign email sent successfully"`

### In ActiveCampaign:
1. Go to: https://bsaqeyan89737.activehosted.com
2. Click: **Contacts**
3. Search for: your test email address
4. Check:
   - ✅ Contact exists
   - ✅ Has correct tag (Green/Yellow/Red)
   - ✅ Go to contact → **Emails** tab → See sent email

### In Your Email:
- ✅ Check inbox (and spam folder)
- ✅ Should receive email based on status

## 🔍 Troubleshooting

### If you see errors in logs:

**"ActiveCampaign config missing"**
→ Check `.env` has `ACTIVE_CAMPAIGN_URL` and `ACTIVE_CAMPAIGN_KEY`

**"No email list/campaign configured"**
→ This is OK! System will send directly. Look for "email sent directly" message.

**"Contact sync failed"**
→ Check ActiveCampaign API credentials

### Check specific error:
```bash
grep -i "error\|exception\|failed" storage/logs/laravel.log | tail -20
```

## 📝 Test with Your Own Email

Replace `your-email@example.com` with your real email:

```bash
php artisan test:application \
  --status=green \
  --email=your-real-email@gmail.com \
  --name="Your Name"
```

Then check your email inbox!

## 🎯 Full Test (All Statuses)

Test all three statuses at once:

```bash
# Terminal 1: Watch logs
tail -f storage/logs/laravel.log

# Terminal 2: Run tests
php artisan test:application --status=green --email=test-green@example.com
php artisan test:application --status=yellow --email=test-yellow@example.com  
php artisan test:application --status=red --email=test-red@example.com
```

## 💡 Pro Tips

1. **Use a real email** you can access to verify delivery
2. **Check spam folder** - emails might go there
3. **Watch logs in real-time** to see what's happening
4. **Check ActiveCampaign** to see if contact was created
5. **Verify tags** are assigned correctly

## ❓ Still Not Working?

1. Check logs: `tail -100 storage/logs/laravel.log`
2. Test connection: `php artisan activecampaign:test`
3. Verify `.env` has correct ActiveCampaign credentials
4. Check ActiveCampaign account for the contact

