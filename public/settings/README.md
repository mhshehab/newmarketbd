# Settings Upload Instructions

## How to Upload Images for Settings

### Step 1: Upload Files Manually
1. Upload your images to the `storage/settings/` directory
2. Use the following file paths in the admin panel:

### Recommended File Paths:
- **Website Logo:** `storage/settings/logo.png` (200x60px, PNG/JPG)
- **Favicon Icon:** `storage/settings/favicon.ico` (32x32px, ICO/PNG)
- **Facebook Icon:** `storage/settings/social/facebook.svg` (24x24px, SVG/PNG)
- **Twitter Icon:** `storage/settings/social/twitter.svg` (24x24px, SVG/PNG)
- **Instagram Icon:** `storage/settings/social/instagram.svg` (24x24px, SVG/PNG)

### Step 2: Update Settings in Admin Panel
1. Go to `/admin/settings`
2. Edit the setting you want to update
3. Set Type to "Image"
4. Enter the file path in the "Image Path" field
5. Save the setting

### Example:
- Upload `logo.png` to `storage/settings/logo.png`
- In admin panel, edit "website_logo" setting
- Set Type to "Image"
- Enter "logo.png" in Image Path field
- Save

### File Size Recommendations:
- Logo: 200x60px, PNG/JPG format
- Favicon: 32x32px, ICO/PNG format  
- Social Icons: 24x24px, SVG/PNG format

### Note:
Currently, the admin panel uses a text input for image paths. Upload your images to the `storage/settings/` directory and enter the file path in the admin panel.
