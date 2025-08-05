#!/bin/bash

# Webmail Login Form - Server Startup Script

echo "🚀 Starting Webmail Login Form Server..."
echo "📁 Current directory: $(pwd)"
echo "📋 Files in directory:"
ls -la *.php *.html

echo ""
echo "🔧 Checking PHP installation..."
if command -v php &> /dev/null; then
    echo "✅ PHP found: $(php --version | head -n 1)"
else
    echo "❌ PHP not found. Please install PHP first."
    echo "   Ubuntu/Debian: sudo apt install php"
    echo "   CentOS/RHEL: sudo yum install php"
    echo "   macOS: brew install php"
    exit 1
fi

echo ""
echo "🌐 Starting PHP development server..."
echo "📍 Server will be available at: http://localhost:8080"
echo "🔗 Access your form at: http://localhost:8080/webmail_login.html"
echo ""
echo "💡 Testing commands (run in browser console):"
echo "   testServer()           - Test server setup"
echo "   testConnection()       - Test PHP connection"
echo "   testWithDifferentUrls() - Test multiple URLs"
echo ""
echo "🛑 Press Ctrl+C to stop the server"
echo "================================================"

# Kill any existing PHP server on port 8080
pkill -f "php -S localhost:8080" 2>/dev/null

# Start the PHP development server
php -S localhost:8080