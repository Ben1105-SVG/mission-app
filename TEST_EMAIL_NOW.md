# Test Email Sending - Step by Step

## 🔧 Step 1: Clear Cache (Important!)
```bash
php artisan config:clear
php artisan cache:clear
```

## 📊 Step 2: Watch Logs in Real-Time
Open a **new terminal** and run:
```bash
tail -f storage/logs/laravel.log | grep -i "email\|activecampaign\|message"
```

## 🧪 Step 3: Run Test
In your **main terminal**, run:
```bash
php artisan test:application --status=green --email=your-real-email@gmail.com
```

**Replace `your-real-email@gmail.com` with your actual email address!**

## ✅ What You Should See

### In the Logs Terminal:
Look for these messages (in order):

1. **"Sending email directly via ActiveCampaign API"** ✅
2. **"Starting direct email send process"** ✅
3. **"Message created in ActiveCampaign"** ✅ (with message_id)
4. **"✅ ActiveCampaign email sent directly"** ✅

### If You See Errors:
- **"Failed to create message"** → Check ActiveCampaign API permissions
- **"Failed to send message activity"** → Message created but sending failed
- **"Could not fetch contact"** → Contact sync issue

## 🎯 Quick Test Command

```bash
# Clear cache first
php artisan config:clear

# Watch logs (Terminal 1)
tail -f storage/logs/laravel.log

# Run test (Terminal 2) - USE YOUR REAL EMAIL!
php artisan test:application --status=green --email=YOUR-EMAIL@gmail.com
```

## 📧 Check Your Email

1. Check **inbox** (and **spam folder**)
2. Subject should be: **"Congratulations! Your Mission Trip Application Has Been Approved"**
3. Should come from: **Adventures in Missions** (or your configured from address)

## 🔍 If Email Not Received

1. **Check logs for errors:**
   ```bash
   tail -50 storage/logs/laravel.log | grep -i "error\|failed\|exception"
   ```

2. **Verify contact in ActiveCampaign:**
   - Go to: https://bsaqeyan89737.activehosted.com
   - Search for your test email
   - Check if contact exists

3. **Check message creation:**
   ```bash
   grep "Message created" storage/logs/laravel.log
   ```

4. **Check if message was sent:**
   ```bash
   grep "email sent directly" storage/logs/laravel.log
   ```

## 💡 Expected Log Flow

```
[INFO] Sending email directly via ActiveCampaign API
[INFO] Starting direct email send process  
[INFO] Message created in ActiveCampaign {"message_id":123}
[INFO] ✅ ActiveCampaign email sent directly
```

If you see all 4 messages, the email should be sent! Check your inbox.

