# Troubleshooting ActiveCampaign Integration

## Issue: Tags/Automations Not Found

If your test shows the API connection works but tags/automations aren't found, here's how to fix it:

## Step 1: List What's Available

Run these commands to see what exists in your ActiveCampaign account:

```bash
# List all tags
php artisan activecampaign:list --tags

# List all automations
php artisan activecampaign:list --automations

# List both
php artisan activecampaign:list
```

## Step 2: Create Missing Tags

If tags don't exist, create them:

1. **Go to ActiveCampaign Tags Page:**
   https://bsaqeyan89737.activehosted.com/app/contacts/tags

2. **Create these tags:**
   - **Yellow** (for applicants needing follow-up)
   - **Red** (for alternative opportunities)
   - **Default** (optional fallback)

3. **Get the Tag IDs:**
   - After creating, click on each tag
   - The ID is in the URL: `.../tags/123` → ID is `123`
   - Or use: `php artisan activecampaign:list --tags` to see all tags with IDs

4. **Add to .env:**
   ```env
   AC_TAG_YELLOW=123
   AC_TAG_RED=456
   AC_TAG_DEFAULT=789
   ```

## Step 3: Create Automations (Optional)

Automations are optional but recommended:

1. **Go to Automations:**
   https://bsaqeyan89737.activehosted.com/app/automations

2. **Create automation for Yellow:**
   - Trigger: When contact is tagged with "Yellow"
   - Action: Send email or assign to team member
   - Note the Automation ID

3. **Create automation for Red:**
   - Trigger: When contact is tagged with "Red"
   - Action: Send different message about alternatives
   - Note the Automation ID

4. **Add to .env:**
   ```env
   AC_AUTOMATION_YELLOW=101
   AC_AUTOMATION_RED=102
   ```

## Step 4: Verify Configuration

After adding tag/automation IDs, test again:

```bash
php artisan activecampaign:test
```

## Common Issues

### Issue: "Tag not found" but tag exists
- **Solution:** Verify the Tag ID is correct
- Use `php artisan activecampaign:list --tags` to see exact IDs
- Make sure there are no extra spaces in .env values

### Issue: "Automation not found" but automation exists
- **Solution:** Verify the Automation ID is correct
- Use `php artisan activecampaign:list --automations` to see exact IDs
- Make sure automation is active (status = 1)

### Issue: Tags/Automations work but contacts aren't being tagged
- **Solution:** Check Laravel logs: `tail -f storage/logs/laravel.log`
- Verify queue is running: `php artisan queue:work`
- Check if job is being dispatched in `InquiryController`

## Quick Fix Commands

```bash
# Clear config cache
php artisan config:clear

# List all tags with IDs
php artisan activecampaign:list --tags

# List all automations with IDs
php artisan activecampaign:list --automations

# Test full integration
php artisan activecampaign:test
```

## Need Help?

1. Run `php artisan activecampaign:list` to see what exists
2. Create missing tags/automations in ActiveCampaign
3. Update .env with correct IDs
4. Run `php artisan activecampaign:test` to verify

