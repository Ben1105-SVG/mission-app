# ActiveCampaign Integration Checklist

## ✅ Current Status Check

### 1. Tags in ActiveCampaign
**Status:** ⚠️ **NEEDS ACTION**

**Found:** 4 tags exist but they're unnamed
- Tag ID: 1 (Unnamed)
- Tag ID: 2 (Unnamed)
- Tag ID: 3 (Unnamed)
- Tag ID: 4 (Unnamed)

**Action Required:**
1. Go to: https://bsaqeyan89737.activehosted.com/app/contacts/tags
2. Either:
   - **Option A:** Rename existing tags to "Yellow" and "Red"
   - **Option B:** Create new tags named "Yellow" and "Red"
3. After updating, run: `php artisan activecampaign:list --tags`
4. Update `.env` with correct tag IDs:
   ```env
   AC_TAG_YELLOW=tag_id_here
   AC_TAG_RED=tag_id_here
   ```

### 2. Automations
**Status:** ℹ️ **OPTIONAL**

**Found:** 1 automation exists but doesn't match "yellow" or "red"
- Automation ID: 1 (Product Interest Targeted Follow-up) - Inactive

**Action Required (Optional):**
1. Go to: https://bsaqeyan89737.activehosted.com/app/automations
2. Create automations that trigger when contacts are tagged:
   - **Yellow Automation:** Triggers when tagged with "Yellow"
   - **Red Automation:** Triggers when tagged with "Red"
3. Update `.env`:
   ```env
   AC_AUTOMATION_YELLOW=automation_id
   AC_AUTOMATION_RED=automation_id
   ```

### 3. Signup URL for Green Status
**Status:** ⚠️ **NEEDS UPDATE**

**Current:** `MISSIONS_SIGNUP_URL=https://trip-signup-link.com` (default placeholder)

**Action Required:**
1. Update `.env` with your actual signup page URL:
   ```env
   MISSIONS_SIGNUP_URL=https://your-actual-signup-page.com
   ```

### 4. Test Application Submission
**Status:** ✅ **READY TO TEST**

**API Endpoint:** `POST /api/inquiries`

**Test Script:** See `test-application.php` below

---

## Quick Fix Steps

1. **Fix Tags (5 minutes):**
   ```bash
   # After renaming/creating tags in ActiveCampaign
   php artisan activecampaign:list --tags
   # Copy the IDs and update .env
   ```

2. **Update Signup URL (1 minute):**
   ```env
   MISSIONS_SIGNUP_URL=https://your-real-signup-url.com
   ```

3. **Clear Config & Test:**
   ```bash
   php artisan config:clear
   php artisan activecampaign:test
   ```

4. **Test Application:**
   ```bash
   php artisan test:application
   ```

