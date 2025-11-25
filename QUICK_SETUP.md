# Quick ActiveCampaign Setup

## Your ActiveCampaign Account
Based on your URL: `https://bsaqeyan89737.activehosted.com`

Your API URL is likely: **`https://bsaqeyan89737.api-us1.com`**

## Step-by-Step Setup

### 1. Get Your API Key
1. Go to: https://bsaqeyan89737.activehosted.com/app/settings/api
2. Copy your **API Key**

### 2. Create Tags
1. Go to: https://bsaqeyan89737.activehosted.com/app/contacts/tags
2. Create tags:
   - **Yellow** (for follow-up needed)
   - **Red** (for alternative opportunities)
3. Note the Tag IDs (found in URL or tag details)

### 3. Add to .env File
```env
ACTIVE_CAMPAIGN_URL=https://bsaqeyan89737.api-us1.com
ACTIVE_CAMPAIGN_KEY=your_api_key_here
AC_TAG_YELLOW=your_yellow_tag_id
AC_TAG_RED=your_red_tag_id
MISSIONS_SIGNUP_URL=https://your-signup-page.com
```

### 4. Test Connection
```bash
php artisan activecampaign:test
```

This will verify:
- ✅ API connection works
- ✅ Tags exist and are accessible
- ✅ Configuration is correct

## What Happens When Someone Applies?

### 🟢 Green (Approved)
- Gets signup link immediately
- **NOT** sent to ActiveCampaign
- Can proceed to payment

### 🟡 Yellow (Needs Follow-up)
- Sent to ActiveCampaign automatically
- Tagged with "Yellow" tag
- Complete form details in notes
- Team can call them back

### 🔴 Red (Alternative Opportunities)
- Sent to ActiveCampaign automatically
- Tagged with "Red" tag
- Complete form details in notes
- Team can follow up about alternatives

## Need Help?

Check the detailed guide: `ACTIVECAMPAIGN_SETUP.md`

