# ActiveCampaign Integration Setup Guide

## Step 1: Get Your ActiveCampaign API Credentials

### Finding Your API URL
Based on your ActiveCampaign account URL (`https://bsaqeyan89737.activehosted.com`), your API URL is likely:
- **US Account**: `https://bsaqeyan89737.api-us1.com`
- **EU Account**: `https://bsaqeyan89737.api-eu1.com`

To confirm:
1. Log into ActiveCampaign: https://bsaqeyan89737.activehosted.com
2. Go to **Settings** → **Developer**
3. Look for "API URL" - it will show your exact API endpoint

### Finding Your API Key
1. In ActiveCampaign, go to **Settings** → **Developer**
2. Click on **"API Access"** or **"API Key"**
3. Copy your API key (it will look like: `abc123def456...`)

## Step 2: Create Tags in ActiveCampaign

You need to create tags for each status. These will be used to categorize contacts:

1. Go to **Contacts** → **Tags** in ActiveCampaign
2. Create the following tags (or use existing ones):
   - **Yellow** - For applicants needing follow-up
   - **Red** - For applicants needing alternative opportunities
   - **Default** (optional) - Fallback tag

3. After creating each tag, note the **Tag ID**:
   - Click on the tag
   - The ID will be in the URL or tag details
   - Example: If URL is `.../tags/123`, then Tag ID is `123`

## Step 3: Create Automations (Optional but Recommended)

### For Yellow Status:
1. Go to **Automations** → **Create Automation**
2. Create an automation that:
   - Triggers when contact is tagged with "Yellow"
   - Sends a follow-up email or assigns to a team member
3. Note the **Automation ID** from the automation settings

### For Red Status:
1. Create another automation for "Red" tag
2. This can send a different message about alternative opportunities
3. Note the **Automation ID**

## Step 4: Configure Environment Variables

Add these to your `.env` file:

```env
# ActiveCampaign Configuration
ACTIVE_CAMPAIGN_URL=https://bsaqeyan89737.api-us1.com
ACTIVE_CAMPAIGN_KEY=your_api_key_here

# ActiveCampaign Tags (get IDs from Step 2)
AC_TAG_YELLOW=123
AC_TAG_RED=456
AC_TAG_DEFAULT=789

# ActiveCampaign Automations (optional, get IDs from Step 3)
AC_AUTOMATION_YELLOW=101
AC_AUTOMATION_RED=102

# Mission Trip Signup URL (for Green status)
MISSIONS_SIGNUP_URL=https://your-signup-page.com
```

## Step 5: Test the Integration

Run the test command to verify your setup:

```bash
php artisan activecampaign:test
```

This will:
- Test the API connection
- Verify tags exist
- Check automations (if configured)
- Show you what data will be sent

## How It Works

### Green Status (Approved)
- ✅ Contact is **NOT** sent to ActiveCampaign
- ✅ User receives signup link immediately
- ✅ Can proceed to payment

### Yellow Status (Needs Follow-up)
- ✅ Contact is synced to ActiveCampaign
- ✅ Tagged with "Yellow" tag
- ✅ Comprehensive note added with all form details
- ✅ Automation triggered (if configured)
- ✅ Team member can see all details for callback

### Red Status (Alternative Opportunities)
- ✅ Contact is synced to ActiveCampaign
- ✅ Tagged with "Red" tag
- ✅ Comprehensive note added with all form details
- ✅ Automation triggered (if configured)
- ✅ Team member can follow up about alternatives

## Troubleshooting

### Check Logs
If integration fails, check Laravel logs:
```bash
tail -f storage/logs/laravel.log
```

### Common Issues

1. **"API URL not found"**
   - Verify your API URL format
   - Check if you're using US vs EU endpoint

2. **"Invalid API Key"**
   - Regenerate API key in ActiveCampaign
   - Make sure there are no extra spaces in `.env`

3. **"Tag not found"**
   - Verify tag IDs are correct
   - Tags must exist before contacts are tagged

4. **"Contact sync failed"**
   - Check API permissions
   - Verify API key has write access

## Support

For ActiveCampaign API documentation:
- https://developers.activecampaign.com/reference/overview

