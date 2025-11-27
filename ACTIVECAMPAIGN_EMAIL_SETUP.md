# ActiveCampaign Email Setup Guide

## Overview

The application now sends emails via ActiveCampaign when users submit their application. Emails are sent automatically based on the application status (green/yellow/red).

## How It Works

1. User submits application
2. Application is evaluated and assigned a status (green/yellow/red)
3. Contact is synced to ActiveCampaign
4. Email is sent via ActiveCampaign using the configured message/campaign ID

## Setup Instructions

### Step 1: Choose Your Email Method

You have two options for sending emails:

#### Option A: Use Lists with Campaigns (Recommended)
1. **Log into ActiveCampaign**: https://bsaqeyan89737.activehosted.com
2. **Go to**: Lists
3. **Create 3 lists** (one for each status):
   - **Green Status List**: For approved applicants
   - **Yellow Status List**: For applicants needing follow-up
   - **Red Status List**: For alternative opportunities
4. **Add campaigns/automations** to each list that send emails
5. **Get List IDs** from the URL (e.g., `.../lists/123` → ID is `123`)

#### Option B: Let System Send Directly (Automatic)
If you don't configure list IDs, the system will automatically create and send emails directly using the contact information.

### Step 2: Configure in .env (Optional - Only if using Option A)

If you're using lists with campaigns, add the list IDs to your `.env` file:

```env
# ActiveCampaign Lists (List IDs) - Optional
# If not set, emails will be sent directly
AC_CAMPAIGN_GREEN=123
AC_CAMPAIGN_YELLOW=456
AC_CAMPAIGN_RED=789
```

Replace the numbers with your actual list IDs from ActiveCampaign.

**Note**: If you don't set these, the system will automatically send emails directly to contacts.

### Step 4: Test

Test the email sending:

```bash
# Test with a green status application
php artisan test:application --status=green

# Test with a yellow status application
php artisan test:application --status=yellow

# Test with a red status application
php artisan test:application --status=red
```

## Email Content Suggestions

### Green Status Email
- **Subject**: "Congratulations! Your Mission Trip Application Has Been Approved"
- **Content**: 
  - Congratulate the applicant
  - Include the signup link
  - Next steps for payment

### Yellow Status Email
- **Subject**: "Thank You for Your Mission Trip Application"
- **Content**:
  - Thank them for applying
  - Let them know someone will contact them
  - Provide contact information

### Red Status Email
- **Subject**: "Thank You for Your Interest in Our Mission Trip"
- **Content**:
  - Thank them for their interest
  - Mention alternative opportunities
  - Provide contact information

## Troubleshooting

### Emails Not Sending

1. **Check Logs**: Look for errors in `storage/logs/laravel.log`
   ```bash
   tail -f storage/logs/laravel.log
   ```
   Look for messages like:
   - "ActiveCampaign contact added to list - email will be sent"
   - "ActiveCampaign email sent directly"
   - Any error messages

2. **Verify Contact Sync**: Make sure contacts are being synced to ActiveCampaign
   ```bash
   php artisan activecampaign:test
   ```

3. **Check List IDs** (if using Option A): Verify list IDs in `.env` are correct
   - Go to ActiveCampaign → Lists
   - Check the URL for each list to get the ID

4. **Test Direct Email**: If list method fails, the system will automatically try direct email sending

### Common Issues

- **"No email list/campaign configured"**: This is OK - system will send directly
- **"Contact added to list"**: Success - email will be sent via list campaign/automation
- **"Email sent directly"**: Success - email was sent directly via API
- **Email not received**: 
  - Check spam folder
  - Verify email address in ActiveCampaign
  - Check logs for any errors
  - Verify ActiveCampaign API credentials

## Alternative: Using Automations

If you prefer to use ActiveCampaign automations instead of direct messages:

1. Create automations in ActiveCampaign that send emails
2. Configure automation IDs in `.env`:
   ```env
   AC_AUTOMATION_GREEN=10
   AC_AUTOMATION_YELLOW=11
   AC_AUTOMATION_RED=12
   ```
3. The existing automation system will trigger these automatically

## Notes

- Emails are sent **after** the contact is synced to ActiveCampaign
- If email sending fails, the application is still saved (non-blocking)
- All email sending attempts are logged for debugging
- You can disable email sending by not setting the message IDs in `.env`

