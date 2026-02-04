# 🚀 Deployment Guide

Once you have finished developing your site locally, here is how you can move it to a public hosting provider (e.g., Bluehost, SiteGround, DigitalOcean).

## Option 1: Using a Migration Plugin (Easiest)

This is the recommended method for most users.

1.  **Install the Plugin:**
    - Go to your local WordPress Admin -> Plugins -> Add New.
    - Search for **"All-in-One WP Migration"** or **"Duplicator"**.
    - Install and Activate it.

2.  **Export the Site:**
    - Open the plugin menu.
    - Choose **Export** -> **File**.
    - Download the resulting file (e.g., `.wpress` or `.zip`) to your computer.

3.  **Import on Hosting:**
    - Install a fresh WordPress instance on your public host.
    - Install the *same* migration plugin on the live site.
    - Choose **Import** and upload the file you downloaded.
    - Follow the prompts to overwrite the database and files.

4.  **Save Permalinks:**
    - Go to Settings -> Permalinks and click "Save Changes" twice to regenerate the `.htaccess` file.

## Option 2: Manual Migration (Advanced)

If you prefer full control or the site is too large for plugins:

1.  **Export Database:**
    - Go to [http://localhost:8091](http://localhost:8091) (phpMyAdmin).
    - Log in (User: `root`, Pass: `root_password`).
    - Select the `wordpress` database.
    - Click **Export** -> **Go**. Save the `.sql` file.

2.  **Upload Files:**
    - Use FTP/SFTP to upload the contents of your local `wp-content` folder to the `/wp-content` folder on your live server.

3.  **Import Database:**
    - Use phpMyAdmin on your hosting to import the `.sql` file.

4.  **Search & Replace URLs:**
    - The database still points to `localhost:8090`. You must change this to `your-domain.com`.
    - Use a tool like **WP-CLI** or the **"Better Search Replace"** plugin on the live site to replace `http://localhost:8090` with `https://your-domain.com`.
